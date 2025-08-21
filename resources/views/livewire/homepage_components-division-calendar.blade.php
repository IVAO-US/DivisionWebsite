<?php
use Livewire\Volt\Component;

new class extends Component {
    // Configuration props
    public bool $compact = false;
    
    // Data initialization
    public array $events;
    public array $eventTypeColors;
    
    public function mount(bool $compact = false): void
    {
        $this->compact = $compact;
        
        // Initialize division-specific event type colors
        $this->eventTypeColors = [
            'event' => [
                'badge' => 'bg-success text-success-content',
                'card' => 'bg-success text-success-content',
                'dot' => 'bg-success',
                'icon' => 'phosphor.star'
            ],
            'exam' => [
                'badge' => 'bg-error text-error-content',
                'card' => 'bg-error text-error-content',
                'dot' => 'bg-error',
                'icon' => 'phosphor.graduation-cap'
            ],
            'training' => [
                'badge' => 'bg-secondary text-secondary-content',
                'card' => 'bg-secondary text-secondary-content',
                'dot' => 'bg-secondary',
                'icon' => 'phosphor.chalkboard-teacher'
            ],
        ];
        
        // Initialize division-specific events data
        // In a real application, this would come from a database/service
        $this->events = $this->loadDivisionEvents();
    }
    
    /**
     * Load division-specific events
     * This method can be moved to a service/repository in the future
     */
    private function loadDivisionEvents(): array
    {
        return [
            '2025-08-15' => [
                [
                    'title' => 'Morning Briefing',
                    'time' => '08:00 UTC',
                    'location' => 'Main Hall',
                    'type' => 'training',
                    'description' => 'Daily operational briefing for all controllers'
                ],
                [
                    'title' => 'Equipment Check',
                    'time' => '10:30 UTC',
                    'location' => 'Control Tower',
                    'type' => 'event',
                    'description' => 'Monthly equipment maintenance and verification'
                ],
                [
                    'title' => 'Advanced Training Session',
                    'time' => '14:00 UTC',
                    'location' => 'Training Room A',
                    'type' => 'training',
                    'description' => 'Advanced procedures for complex airspace management'
                ],
            ],
            '2025-08-24' => [
                [
                    'title' => 'SEC Exam',
                    'time' => '20:00 UTC',
                    'location' => 'KN90_APP',
                    'type' => 'exam',
                    'description' => 'Standard Endorsement Course examination for new controllers'
                ],
                [
                    'title' => 'SEC Exam',
                    'time' => '21:00 UTC',
                    'location' => 'KA11_APP',
                    'type' => 'exam',
                    'description' => 'Standard Endorsement Course examination for new controllers'
                ]
            ],
            '2025-09-15' => [
                [
                    'title' => 'ATC Night',
                    'time' => '19:00 UTC', 
                    'location' => 'New York',
                    'type' => 'event',
                    'description' => 'Join us for an intensive ATC training session covering New York airspace'
                ]
            ],
            '2025-09-23' => [
                [
                    'title' => 'Fly-In Miami',
                    'time' => '18:00 UTC',
                    'location' => 'KMIA',
                    'type' => 'event',
                    'description' => 'Mass arrival event at Miami International Airport with full ATC coverage'
                ]
            ],
            '2025-08-21' => [
                [
                    'title' => 'Today\'s Special Event',
                    'time' => '15:00 UTC',
                    'location' => 'Virtual',
                    'type' => 'training',
                    'description' => 'Special training session for today'
                ]
            ]
        ];
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
        :compact="$compact"
        :show-legend="true"
        :show-event-details="!$compact"
        wire:on.date-selected="handleDateSelected"
        wire:on.month-changed="handleMonthChanged"
    />
</div>