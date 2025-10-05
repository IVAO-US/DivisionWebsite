<?php
use Livewire\Volt\Component;
use App\Models\DivisionSession;

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
        
        // Format events for calendar component
        $formattedEvents = [];
        
        foreach ($sessions as $session) {
            $dateKey = $session->date->format('Y-m-d');
            
            // Initialize array for this date if it doesn't exist
            if (!isset($formattedEvents[$dateKey])) {
                $formattedEvents[$dateKey] = [];
            }
            
            // Format time display (remove seconds)
            $timeStart = substr($session->time_start, 0, 5);
            $timeEnd = substr($session->time_end, 0, 5);
            $timeDisplay = "{$timeStart} - {$timeEnd} UTC";
            
            // Extract location from training details if available
            $location = 'TBA';
            if ($session->training_details && isset($session->training_details['callsign'])) {
                $callsign = $session->training_details['callsign'];
                $location = explode('_', $callsign)[0];
            }
            
            // Add event to the date
            $formattedEvents[$dateKey][] = [
                'title' => $session->title,
                'time' => $timeDisplay,
                'location' => $location,
                'type' => $session->type->value,
                'description' => $session->formatted_description ?? 'No description available',
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
    <livewire:app_component-calendar 
        :events="$events"
        :event-type-colors="$eventTypeColors"
        :display-weekly="$displayWeekly"
        :show-legend="true"
        wire:on.date-selected="handleDateSelected"
        wire:on.month-changed="handleMonthChanged"
    />
</div>