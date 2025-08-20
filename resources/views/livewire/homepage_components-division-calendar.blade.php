<?php
use Livewire\Volt\Component;

new class extends Component {
    // Props
    public bool $compact = false;
    
    // Calendar data
    public array $currentDate;
    public array $events;
    public string $selectedDate; // Selected day for details display
    public array $monthNames = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];
    public array $dayNames = ["MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN"];
    
    // Event type colors - scalable system
    public array $eventTypeColors = [
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
    
    public function mount(bool $compact = false): void
    {
        $this->compact = $compact;
        $this->currentDate = [
            'year' => now()->year,
            'month' => now()->month - 1, // 0-indexed for JavaScript compatibility
            'day' => now()->day
        ];
        
        // Set today as selected by default
        $this->selectedDate = now()->format('Y-m-d');
        
        // Sample events - in a real application, this would come from a database
        $this->events = [
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
                    'title' => 'Morning Briefing',
                    'time' => '08:00 UTC',
                    'location' => 'Main Hall',
                    'type' => 'training',
                    'description' => 'Daily operational briefing for all controllers'
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
            ]
        ];
    }
    
    public function selectDate(int $day): void
    {
        if ($day) {
            $this->selectedDate = sprintf('%d-%02d-%02d', 
                $this->currentDate['year'], 
                $this->currentDate['month'] + 1, 
                $day
            );
        }
    }
    
    public function previousMonth(): void
    {
        if ($this->currentDate['month'] == 0) {
            $this->currentDate['month'] = 11;
            $this->currentDate['year']--;
        } else {
            $this->currentDate['month']--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->currentDate['month'] == 11) {
            $this->currentDate['month'] = 0;
            $this->currentDate['year']++;
        } else {
            $this->currentDate['month']++;
        }
    }
    
    public function getCurrentMonthName(): string
    {
        return $this->monthNames[$this->currentDate['month']] . ' ' . $this->currentDate['year'];
    }
    
    public function getCalendarDays(): array
    {
        $year = $this->currentDate['year'];
        $month = $this->currentDate['month'];
        
        // Get first day of month (Monday = 0) and last date
        $firstDay = date('N', mktime(0, 0, 0, $month + 1, 1, $year)) - 1; // 0=Monday
        $lastDate = date('t', mktime(0, 0, 0, $month + 1, 1, $year));
        
        $days = [];
        $dayCounter = 1;
        
        // Always display 6 weeks for visual consistency
        for ($week = 0; $week < 6; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 7; $day++) {
                if (($week === 0 && $day < $firstDay) || $dayCounter > $lastDate) {
                    $weekDays[] = ['number' => '', 'hasEvent' => false, 'events' => [], 'isToday' => false, 'isSelected' => false];
                } else {
                    $eventKey = sprintf('%d-%02d-%02d', $year, $month + 1, $dayCounter);
                    $hasEvent = isset($this->events[$eventKey]);
                    $isToday = ($year === now()->year && $month === now()->month - 1 && $dayCounter === now()->day);
                    $isSelected = ($eventKey === $this->selectedDate);
                    
                    $weekDays[] = [
                        'number' => $dayCounter,
                        'hasEvent' => $hasEvent,
                        'events' => $hasEvent ? $this->events[$eventKey] : [],
                        'isToday' => $isToday,
                        'isSelected' => $isSelected
                    ];
                    $dayCounter++;
                }
            }
            $days[] = $weekDays;
        }
        
        return $days;
    }
    
    public function getSelectedDayEvents(): array
    {
        return $this->events[$this->selectedDate] ?? [];
    }
    
    public function getEventTypeClass($type): string
    {
        return $this->eventTypeColors[$type]['card'] ?? 'bg-neutral text-neutral-content';
    }
    
    public function getEventDotClass($type): string
    {
        return $this->eventTypeColors[$type]['dot'] ?? 'bg-neutral';
    }
    
    public function getEventBadgeClass($type): string
    {
        return $this->eventTypeColors[$type]['badge'] ?? 'bg-neutral text-neutral-content';
    }
}; ?>

<x-card class="w-full h-full flex flex-col">
    {{-- Calendar Header --}}
    <div class="flex justify-between items-center mb-10">
        <x-button 
            wire:click="previousMonth" 
            icon="phosphor.caret-left" 
            class="btn-circle btn-sm btn-ghost"
            title="Previous Month"
        />
        
        <h4 class="text-lg font-bold text-center">{{ $this->getCurrentMonthName() }}</h4>
        
        <x-button 
            wire:click="nextMonth" 
            icon="phosphor.caret-right" 
            class="btn-circle btn-sm btn-ghost"
            title="Next Month"
        />
    </div>
    
    {{-- Main Content Container --}}
    <div class="flex-1 flex flex-col">
        {{-- Calendar Grid --}}
        <div class="w-full flex-1 flex flex-col">
            {{-- Days of Week Header --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach($dayNames as $dayName)
                    <div class="text-center font-bold text-base-content/70 py-1 text-xs">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="grid grid-cols-7 gap-1 flex-1 grid-rows-6">
                @foreach($this->getCalendarDays() as $week)
                    @foreach($week as $day)
                        <div class="relative min-h-18 h-full border border-base-200 rounded-md p-2 transition-all duration-200 cursor-pointer
                            {{ $day['isSelected'] ? 'bg-accent/30 ring-2 ring-accent' : 'hover:bg-base-200' }}"
                            @if($day['number']) wire:click="selectDate({{ $day['number'] }})" @endif>
                            
                            @if($day['number'])
                                {{-- Day number with circle for today --}}
                                <div class="text-sm font-medium mb-2 {{ $day['isToday'] ? 'text-primary font-bold' : 'text-base-content' }}">
                                    @if($day['isToday'])
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-primary-content text-xs font-bold">
                                            {{ $day['number'] }}
                                        </span>
                                    @else
                                        {{ $day['number'] }}
                                    @endif
                                </div>
                                
                                {{-- Event indicators (colored dots) --}}
                                @if($day['hasEvent'])
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($day['events'] as $event)
                                            <div class="w-5 h-5 rounded-full {{ $this->getEventDotClass($event['type']) }} flex items-center justify-center">
                                                <x-icon name="{{ $this->eventTypeColors[$event['type']]['icon'] ?? 'phosphor.calendar' }}" 
                                                        class="w-3 h-3 text-white" />
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
        
        {{-- Selected Day Events Details --}}
        <div class="mt-6 mb-4">
            <h5 class="w-full text-md font-semibold mb-3 text-base-content text-center lg:text-left">
                Events for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
            </h5>
            
            @if(count($this->getSelectedDayEvents()) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($this->getSelectedDayEvents() as $event)
                        <x-card class="p-4 {{ $this->getEventTypeClass($event['type']) }}">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="{{ $this->eventTypeColors[$event['type']]['icon'] ?? 'phosphor.calendar' }}" class="w-4 h-4" />
                                <h6 class="!text-white font-semibold text-sm">{{ $event['title'] }}</h6>
                            </div>
                            
                            <div class="space-y-1 text-xs opacity-90">
                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.clock" class="w-3 h-3" />
                                    <span>{{ $event['time'] }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.map-pin" class="w-3 h-3" />
                                    <span>{{ $event['location'] }}</span>
                                </div>
                            </div>
                            
                            @if($event['description'])
                                <p class="text-xs mt-2 opacity-80">{{ Str::limit($event['description'], 60) }}</p>
                            @endif
                        </x-card>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-base-content/60">
                    <x-icon name="phosphor.calendar-x" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>No events scheduled for this day</p>
                </div>
            @endif
        </div>
    
        {{-- Legend --}}
        <x-slot:actions separator>
            <div class="w-full mt-auto pt-4 flex flex-wrap gap-3 justify-center border-t border-base-300">
                @foreach($eventTypeColors as $type => $colors)
                    <div class="flex items-center gap-2">
                        <x-icon name="{{ $colors['icon'] }}" class="w-4 h-4 {{ str_replace('bg-', 'text-', $colors['dot']) }}" />
                        <span class="text-base-content text-sm">{{ ucfirst($type) }}</span>
                    </div>
                @endforeach
            </div>
        </x-slot:actions>
    </div>
</x-card>