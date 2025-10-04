<?php

namespace App\Console\Commands;

use App\Models\DivisionSession;
use App\Enums\SessionType;
use App\Enums\TrainingType;
use App\Enums\ATCRating;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDivisionSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'division_sessions:sync {--force : Force sync from beginning} {--forever : Check all sessions including past ones}';

    /**
     * The console command description.
     */
    protected $description = 'Sync division sessions from awards_db cms_logs table';


    /**
     * The default banner for missing banner
     */
    private $default_banner = "https://assets.us.ivao.aero/uploads/OnlineDay4.png";


    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting division sessions synchronization...');

        // Get the last processed log ID
        $lastLogId = $this->option('force') 
            ? 0 
            : DivisionSession::max('last_log_id') ?? 0;

        $this->info("Starting from log ID: {$lastLogId}");

        // Fetch new logs from remote database
        $logs = DB::connection('awards_db')
            ->table('cms_logs')
            ->where('log_resource', 'forms')
            ->where('id', '>', $lastLogId)
            ->orderBy('id')
            ->get();

        $this->info("Found {$logs->count()} new logs to process");

        $processedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($logs as $log) {
            try {
                $postParams = json_decode($log->post_params, true);

                if (!$postParams) {
                    continue;
                }

                $formDesignator = $postParams['formDesignator'] ?? null;

                // Process add_events form
                if ($formDesignator === 'add_events') {
                    $result = $this->processEvent($postParams, $log->id);
                    if ($result === 'created') {
                        $processedCount++;
                    } elseif ($result === 'updated') {
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                }

                // Process add_t_sessions form
                if ($formDesignator === 'add_t_sessions') {
                    $result = $this->processTrainingSession($postParams, $log->id);
                    if ($result === 'created') {
                        $processedCount++;
                    } elseif ($result === 'updated') {
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error processing log ID {$log->id}: " . $e->getMessage());
                Log::error("Event sync error for log {$log->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Synchronization complete!");
        $this->info("Created: {$processedCount} sessions");
        $this->info("Updated: {$updatedCount} sessions");
        $this->info("Skipped: {$skippedCount} sessions (discord disabled)");

        $this->newLine();
        $this->info('Cleaning up cancelled sessions...');
        $deletedCount = $this->cleanupCancelledSessions($this->option('forever'));
        $this->info("Deleted: {$deletedCount} cancelled sessions");

        return self::SUCCESS;
    }

    /**
     * Clean up cancelled sessions by comparing with remote division_sessions table
     * 
     * @param bool $checkPast Whether to check past sessions or only upcoming ones
     */
    private function cleanupCancelledSessions(bool $checkPast = false): int
    {
        $deletedCount = 0;

        try {
            // Determine date filter
            $dateFilter = $checkPast ? '1900-01-01' : now()->toDateString();
            
            // Get local sessions to verify (today and future, or all if --forever)
            $localSessions = DivisionSession::where('date', '>=', $dateFilter)
                ->get(['id', 'title', 'date', 'time_start', 'time_end']);

            if ($localSessions->isEmpty()) {
                return 0;
            }

            $scope = $checkPast ? 'all' : 'upcoming';
            $this->line("Found {$localSessions->count()} {$scope} local sessions to verify");

            // Get active sessions from remote database (with same date filter)
            $remoteSessions = DB::connection('awards_db')
                ->table('division_sessions')
                ->select('title', 'date', 'timespan')
                ->where('date', '>=', $dateFilter)
                ->get();

            // Build a set of remote session keys for quick lookup
            $remoteKeys = [];
            foreach ($remoteSessions as $remote) {
                // Remote key format: "title|date|timespan"
                // Example: "AS3 Training at KMIA|2025-07-26|19:00-21:00"
                $key = $remote->title . '|' . $remote->date . '|' . $remote->timespan;
                $remoteKeys[$key] = true;
            }

            $this->line("Found {$remoteSessions->count()} active sessions in remote database");

            // Check each local session
            foreach ($localSessions as $local) {
                // Build local key matching remote format
                $timeStart = substr($local->time_start, 0, 5); // HH:MM
                $timeEnd = substr($local->time_end, 0, 5);     // HH:MM
                $timespan = "{$timeStart}-{$timeEnd}";
                $localKey = $local->title . '|' . $local->date->format('Y-m-d') . '|' . $timespan;

                // If local session doesn't exist in remote, it was cancelled
                if (!isset($remoteKeys[$localKey])) {
                    $this->warn("Deleting cancelled session: {$local->title} on {$local->date->format('Y-m-d')} at {$timespan}");
                    
                    Log::info("Deleted cancelled session", [
                        'id' => $local->id,
                        'title' => $local->title,
                        'date' => $local->date->format('Y-m-d'),
                        'timespan' => $timespan,
                    ]);

                    $local->delete();
                    $deletedCount++;
                }
            }

        } catch (\Exception $e) {
            $this->error("Error during cleanup: " . $e->getMessage());
            Log::error("Session cleanup error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $deletedCount;
    }

    /**
     * Process an event from add_events form
     * 
     * @return string|false Returns 'created', 'updated', or false if skipped
     */
    private function processEvent(array $data, int $logId): string|false
    {
        // Determine event type
        $type = $this->determineEventType($data);

        // Use updateOrCreate to avoid duplicates
        $session = DivisionSession::updateOrCreate(
            ['last_log_id' => $logId],
            [
                'title' => $data['title'] ?? 'Untitled Event',
                'date' => $data['date'] ?? now()->toDateString(),
                'time_start' => $data['timeStart'] ?? '00:00:00',
                'time_end' => $data['timeEnd'] ?? '23:59:59',
                'type' => $type,
                'illustration' => !empty($data['discord_illustration']) 
                    ? $data['discord_illustration'] 
                    : $this->default_banner,
                'description' => $data['discord_description'] ?? null,
                'training_details' => null,
            ]
        );

        return $session->wasRecentlyCreated ? 'created' : 'updated';
    }

    /**
     * Process a training session from add_t_sessions form
     */
    private function processTrainingSession(array $data, int $logId): bool
    {
        // Check if discord notification is enabled
        $discord = $data['discord'] ?? [];
        if (!in_array('1', $discord)) {
            return false;
        }

        // Check if FRA waiver is enabled
        $fraWaiver = $data['fra_waiver'] ?? [];
        $hasFraWaiver = in_array('1', $fraWaiver);

        // Prepare training details
        $trainingDetails = [
            'student_vid' => $data['student_vid'] ?? null,
            'callsign' => $data['callsign'] ?? null,
            'rating' => $data['rating'] ?? null,
        ];

        // Generate title based on training type
        $title = $this->generateTrainingTitle($data);

        // Determine session type from optionsTrainingType
        $type = $this->determineTrainingSessionType($data);

        $session = DivisionSession::updateOrCreate(
            ['last_log_id' => $logId],
            [
                'title' => $title,
                'date' => $data['date'] ?? now()->toDateString(),
                'time_start' => $data['timeStart'] ?? '00:00:00',
                'time_end' => $data['timeEnd'] ?? '23:59:59',
                'type' => $type,
                'illustration' => !empty($data['discord_illustration']) 
                    ? $data['discord_illustration'] 
                    : $this->default_banner,
                'description' => $data['discord_description'] ?? null,
                'training_details' => $trainingDetails,
            ]
        );

        return true;
    }

    /**
     * Determine event type based on form data
     */
    private function determineEventType(array $data): SessionType
    {
        $title = strtolower($data['title'] ?? '');

        if (str_contains($title, 'exam')) {
            return SessionType::EXAM;
        }

        if (str_contains($title, 'gca') || str_contains($title, 'guest controller')) {
            return SessionType::GCA;
        }

        if (str_contains($title, 'online day')) {
            return SessionType::ONLINE_DAY;
        }

        return SessionType::EVENT;
    }

    /**
     * Determine session type from training session data
     * Maps optionsTrainingType to SessionType enum
     */
    private function determineTrainingSessionType(array $data): SessionType
    {
        $trainingType = strtolower($data['optionsTrainingType'] ?? 'training');

        return match($trainingType) {
            'exam' => SessionType::EXAM,
            'gca' => SessionType::GCA,
            'training' => SessionType::TRAINING,
            'checkout' => SessionType::TRAINING, // Checkout is a type of training
            default => SessionType::TRAINING,
        };
    }

    /**
     * Generate title for training session
     */
    private function generateTrainingTitle(array $data): string
    {
        // Get rating (e.g., "ASx", "ADC", "APC", etc.)
        $rating = $data['rating'] ?? 'Unknown';
        
        // Get training type and convert to label
        $trainingTypeValue = $data['optionsTrainingType'] ?? 'training';
        $trainingType = TrainingType::fromString($trainingTypeValue);
        $typeLabel = $trainingType ? $trainingType->label() : ucfirst($trainingTypeValue);
        
        // Get callsign and convert to uppercase
        $callsign = strtoupper($data['callsign'] ?? 'Unknown');
        
        // Format: "{rating} {type} at {callsign}"
        // Example: "ASx Training at KMIA"
        return "{$rating} {$typeLabel} at {$callsign}";
    }
}