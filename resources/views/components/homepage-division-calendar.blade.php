<?php
use Livewire\Component;
use App\Models\DivisionSession;
use App\Services\RecurringEventService;

new class extends Component {
    // Configuration props
    public bool $displayWeekly = false;
    
    // Data initialization
    public array $events;
    public array $eventTypeColors;
    
    public function mount(bool $displayWeekly = false): void
    {
        $this->displayWeekly = $displayWeekly;
        
        // Initialize division-specific event type colors
        $this->eventTypeColors = [
            'event' => [
                'badge' => '!bg-success !text-success-content',
                'card' => '!bg-success !text-success-content',
                'dot' => '!bg-success',
                'icon' => 'phosphor.star'
            ],
            'online_day' => [
                'badge' => '!bg-info !text-info-content',
                'card' => '!bg-info !text-info-content',
                'dot' => '!bg-info',
                'icon' => 'phosphor.calendar-heart'
            ],
            'exam' => [
                'badge' => '!bg-accent !text-accent-content',
                'card' => '!bg-accent !text-accent-content',
                'dot' => '!bg-accent',
                'icon' => 'phosphor.graduation-cap'
            ],
            'training' => [
                'badge' => '!bg-primary !text-primary-content',
                'card' => '!bg-primary !text-primary-content',
                'dot' => '!bg-primary',
                'icon' => 'phosphor.chalkboard-teacher'
            ],
            'gca' => [
                'badge' => '!bg-secondary !text-secondary-content',
                'card' => '!bg-secondary !text-secondary-content',
                'dot' => '!bg-secondary',
                'icon' => 'phosphor.alien'
            ],
        ];
        
        // Initialize division-specific events data
        $this->events = $this->loadDivisionEvents();
    }
    
    /**
     * Load division-specific events from database
     * Fetches all future events and past events from the last 3 complete months
     */
    private function loadDivisionEvents(): array
    {
        // Calculate the start date: first day of the month 3 months ago
        // Example: if today is Oct 15, 2025 -> we want from July 1, 2025
        $startDate = now()->startOfMonth()->subMonths(3)->toDateString();
        
        // Fetch sessions from database
        $sessions = DivisionSession::where('date', '>=', $startDate)
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();
        

        // Get recurring Online Days (weekly) for the next 4 months
        $onlineDays = RecurringEventService::getCalendarOnlineDays(3, 4);

        // Get recurring SpecOps Online Days (monthly) for the next 4 months
        $specOpsOnlineDays = RecurringEventService::getCalendarSpecOpsOnlineDays(3, 4);

        
        // Merge database sessions with all recurring events
        $allSessions = $sessions->concat($onlineDays)->concat($specOpsOnlineDays)->sortBy([
            ['date', 'asc'],
            ['time_start', 'asc']
        ]);
        
        // Format events for calendar component
        $formattedEvents = [];
        
        foreach ($allSessions as $session) {
            // Handle both Eloquent models and arrays (recurring events)
            $date = is_array($session) ? $session['date'] : $session->date;
            $dateKey = $date->format('Y-m-d');
            
            // Initialize array for this date if it doesn't exist
            if (!isset($formattedEvents[$dateKey])) {
                $formattedEvents[$dateKey] = [];
            }
            
            // Format time display (remove seconds)
            $timeStart = is_array($session) 
                ? substr($session['time_start'], 0, 5) 
                : substr($session->time_start, 0, 5);
                
            $timeEnd = is_array($session) 
                ? substr($session['time_end'], 0, 5) 
                : substr($session->time_end, 0, 5);
                
            $timeDisplay = "{$timeStart} - {$timeEnd} UTC";
            
            // Extract location from training details if available
            $location = 'TBA';
            if (!is_array($session) && $session->training_details && isset($session->training_details['callsign'])) {
                $callsign = $session->training_details['callsign'];
                $location = strtoupper(explode('_', $callsign)[0]);
            }
            
            // Get description
            $description = is_array($session)
                ? $session['description']
                : ($session->formatted_description ?? 'No description available');
            
            // Get type
            $type = is_array($session)
                ? $session['type']
                : $session->type->value;
            
            // Get title
            $title = is_array($session)
                ? $session['title']
                : $session->title;
            
            // Add event to the date
            $formattedEvents[$dateKey][] = [
                'title' => $title,
                'time' => $timeDisplay,
                'location' => $location,
                'type' => $type,
                'description' => $description,
            ];
        }
        
        return $formattedEvents;
    }
    
    /**
     * Handle date selection from calendar component
     */
    public function handleDateSelected($eventData): void
    {
        // Custom logic when a date is selected
        // Can be used to update other components, load additional data, etc.
        $this->dispatch('division-calendar-date-selected', $eventData);
    }
    
    /**
     * Handle month change from calendar component
     */
    public function handleMonthChanged($monthData): void
    {
        // Custom logic when month changes
        // Can be used to load events for the new month, analytics, etc.
        $this->dispatch('division-calendar-month-changed', $monthData);
    }
    
    /**
     * Refresh events data
     * Can be called from parent components or triggered by events
     */
    public function refreshEvents(): void
    {
        $this->events = $this->loadDivisionEvents();
    }
}; ?>

{{-- Division Calendar Component --}}
<div>
    <livewire:app-calendar 
        :events="$events"
        :event-type-colors="$eventTypeColors"
        :display-weekly="$displayWeekly"
        :show-legend="true"
        wire:on.date-selected="handleDateSelected"
        wire:on.month-changed="handleMonthChanged"
    />
</div>