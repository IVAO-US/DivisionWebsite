<?php
use Livewire\Volt\Component;

new class extends Component {
    // Props from parent component
    public array $events = [];
    public array $eventTypeColors = [];
    public bool $showLegend = true;
    public bool $showEventDetails = true;
    public bool $displayWeekly = false;
    public bool $useTodayBtn = false;
    
    // Navigation limits (months before and after current month)
    public int $monthsBeforeLimit = 3;
    public int $monthsAfterLimit = 3;
    
    // Internal state
    public array $currentDate;
    public string $selectedDate;
    public bool $weeklyMode = false;
    
    // Calendar configuration
    public array $monthNames = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];
    public array $dayNames = ["MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN"];
    
    // Default event type colors (can be overridden by props)
    public array $defaultEventTypeColors = [
        'default' => [
            'badge' => 'bg-neutral text-neutral-content',
            'card' => 'bg-neutral text-neutral-content',
            'dot' => 'bg-neutral',
            'icon' => 'phosphor.calendar'
        ]
    ];
    
    public function mount(
        array $events = [], 
        array $eventTypeColors = [], 
        bool $showLegend = true,
        bool $showEventDetails = true,
        bool $displayWeekly = false,
        bool $useTodayBtn = false,
        ?string $initialDate = null,
        int $monthsBeforeLimit = 3,
        int $monthsAfterLimit = 3
    ): void {
        $this->events = $events;
        $this->eventTypeColors = array_merge($this->defaultEventTypeColors, $eventTypeColors);
        $this->showLegend = $showLegend;
        $this->showEventDetails = $showEventDetails;
        $this->displayWeekly = $displayWeekly;
        $this->useTodayBtn = $useTodayBtn;
        $this->weeklyMode = $displayWeekly;
        $this->monthsBeforeLimit = $monthsBeforeLimit;
        $this->monthsAfterLimit = $monthsAfterLimit;
        
        // Initialize current date
        $startDate = $initialDate ? \Carbon\Carbon::parse($initialDate) : now();
        $this->currentDate = [
            'year' => $startDate->year,
            'month' => $startDate->month - 1,
            'day' => $startDate->day
        ];
        
        // Set initial selected date
        $this->selectedDate = $startDate->format('Y-m-d');
    }
    
    /**
     * Check if we can navigate to previous month
     */
    public function canNavigatePrevious(): bool
    {
        if ($this->weeklyMode) {
            return false;
        }
        
        $currentViewDate = \Carbon\Carbon::create(
            $this->currentDate['year'], 
            $this->currentDate['month'] + 1, 
            1
        );
        
        $today = now();
        $minAllowedDate = $today->copy()->subMonths($this->monthsBeforeLimit)->startOfMonth();
        
        return $currentViewDate->greaterThan($minAllowedDate);
    }
    
    /**
     * Check if we can navigate to next month
     */
    public function canNavigateNext(): bool
    {
        if ($this->weeklyMode) {
            return false;
        }
        
        $currentViewDate = \Carbon\Carbon::create(
            $this->currentDate['year'], 
            $this->currentDate['month'] + 1, 
            1
        );
        
        $today = now();
        $maxAllowedDate = $today->copy()->addMonths($this->monthsAfterLimit)->startOfMonth();
        
        return $currentViewDate->lessThan($maxAllowedDate);
    }
    
    public function selectDate(int $day): void
    {
        if ($day) {
            if ($this->weeklyMode) {
                // In weekly mode, calculate the actual date from the position
                $today = now();
                $selectedDate = $today->copy()->addDays($day - 1);
                $this->selectedDate = $selectedDate->format('Y-m-d');
            } else {
                // Normal monthly mode
                $this->selectedDate = sprintf('%d-%02d-%02d', 
                    $this->currentDate['year'], 
                    $this->currentDate['month'] + 1, 
                    $day
                );
            }
            
            // Emit event for parent component if needed
            $this->dispatch('date-selected', [
                'date' => $this->selectedDate,
                'events' => $this->getSelectedDayEvents()
            ]);
        }
    }
    
    public function previousMonth(): void
    {
        if ($this->weeklyMode || !$this->canNavigatePrevious()) {
            return;
        }
        
        if ($this->currentDate['month'] == 0) {
            $this->currentDate['month'] = 11;
            $this->currentDate['year']--;
        } else {
            $this->currentDate['month']--;
        }
        
        $this->dispatch('month-changed', [
            'year' => $this->currentDate['year'],
            'month' => $this->currentDate['month'] + 1
        ]);
    }

    public function nextMonth(): void
    {
        if ($this->weeklyMode || !$this->canNavigateNext()) {
            return;
        }
        
        if ($this->currentDate['month'] == 11) {
            $this->currentDate['month'] = 0;
            $this->currentDate['year']++;
        } else {
            $this->currentDate['month']++;
        }
        
        $this->dispatch('month-changed', [
            'year' => $this->currentDate['year'],
            'month' => $this->currentDate['month'] + 1
        ]);
    }
    
    public function goToToday(): void
    {
        $today = now();
        $this->currentDate = [
            'year' => $today->year,
            'month' => $today->month - 1,
            'day' => $today->day
        ];
        $this->selectedDate = $today->format('Y-m-d');
    }
    
    public function toggleViewMode(): void
    {
        $this->weeklyMode = !$this->weeklyMode;
        
        // Emit event to notify parent about view change
        $this->dispatch('view-mode-changed', [
            'weekly' => $this->weeklyMode
        ]);
    }
    
    public function getCurrentMonthName(): string
    {
        if ($this->weeklyMode) {
            return 'Next 15 Days';
        }
        
        return $this->monthNames[$this->currentDate['month']] . ' ' . $this->currentDate['year'];
    }
    
    public function getWeeklyDays(): array
    {
        $today = now();
        $days = [];
        
        // Create 3 rows of 5 days each (15 days total)
        for ($week = 0; $week < 3; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 5; $day++) {
                $dayOffset = ($week * 5) + $day;
                $currentDay = $today->copy()->addDays($dayOffset);
                
                $eventKey = $currentDay->format('Y-m-d');
                $hasEvent = isset($this->events[$eventKey]);
                $isToday = $dayOffset === 0;
                $isSelected = ($eventKey === $this->selectedDate);
                
                $weekDays[] = [
                    'number' => $dayOffset + 1,
                    'displayNumber' => $currentDay->day,
                    'hasEvent' => $hasEvent,
                    'events' => $hasEvent ? $this->events[$eventKey] : [],
                    'isToday' => $isToday,
                    'isSelected' => $isSelected,
                    'fullDate' => $currentDay,
                    'monthName' => $currentDay->format('M')
                ];
            }
            $days[] = $weekDays;
        }
        
        return $days;
    }
    
    public function getCalendarDays(): array
    {
        if ($this->weeklyMode) {
            return $this->getWeeklyDays();
        }
        
        $year = $this->currentDate['year'];
        $month = $this->currentDate['month'] + 1;
        
        // Get first day of month (Monday = 0) and last date
        $firstDay = date('N', mktime(0, 0, 0, $month, 1, $year)) - 1;
        $lastDate = date('t', mktime(0, 0, 0, $month, 1, $year));
        
        $days = [];
        $dayCounter = 1;
        
        // Calculate how many weeks we need to display all days of the month
        $totalCells = $firstDay + $lastDate;
        $weeksNeeded = ceil($totalCells / 7);
        
        // Generate weeks based on actual need (minimum 4, maximum 6)
        for ($week = 0; $week < $weeksNeeded; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 7; $day++) {
                if (($week === 0 && $day < $firstDay) || $dayCounter > $lastDate) {
                    $weekDays[] = [
                        'number' => '', 
                        'displayNumber' => '',
                        'hasEvent' => false, 
                        'events' => [], 
                        'isToday' => false, 
                        'isSelected' => false
                    ];
                } else {
                    $eventKey = sprintf('%d-%02d-%02d', $year, $month, $dayCounter);
                    $hasEvent = isset($this->events[$eventKey]);
                    $isToday = ($year === now()->year && $month === now()->month && $dayCounter === now()->day);
                    $isSelected = ($eventKey === $this->selectedDate);
                    
                    $weekDays[] = [
                        'number' => $dayCounter,
                        'displayNumber' => $dayCounter,
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
    
    /**
     * Convert string boolean to actual boolean
     */
    private function toBool(bool|string $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get 15 days displayed on 3 rows of 5 days each
     */
    public function getFifteenDaysGrid(): array
    {
        $today = now();
        $days = [];
        
        // Create 3 rows of 5 days each (15 days total)
        for ($week = 0; $week < 3; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 5; $day++) {
                $dayOffset = ($week * 5) + $day;
                $currentDay = $today->copy()->addDays($dayOffset);
                
                $eventKey = $currentDay->format('Y-m-d');
                $hasEvent = isset($this->events[$eventKey]);
                $isToday = $dayOffset === 0;
                $isSelected = ($eventKey === $this->selectedDate);
                
                $weekDays[] = [
                    'number' => $dayOffset + 1,
                    'displayNumber' => $currentDay->day,
                    'hasEvent' => $hasEvent,
                    'events' => $hasEvent ? $this->events[$eventKey] : [],
                    'isToday' => $isToday,
                    'isSelected' => $isSelected,
                    'fullDate' => $currentDay,
                    'monthName' => $currentDay->format('M')
                ];
            }
            $days[] = $weekDays;
        }
        
        return $days;
    }
    
    private function todayButton(): string 
    {
        return '<x-button 
                    wire:click="goToToday" 
                    icon="phosphor.calendar-check"
                    class="btn-xs btn-outline"
                    title="Go to Today"
                >
                    Today
                </x-button>';
    }
    
    public function getGridRowsClass(): string
    {
        if ($this->weeklyMode) {
            return 'grid-rows-3';
        }
        
        $calendarDays = $this->getCalendarDays();
        $numberOfWeeks = count($calendarDays);
        
        return match($numberOfWeeks) {
            4 => 'grid-rows-4',
            5 => 'grid-rows-5',
            6 => 'grid-rows-6',
            default => 'grid-rows-6'
        };
    }
    
    public function getGridColsClass(): string
    {
        return $this->weeklyMode ? 'grid-cols-5' : 'grid-cols-7';
    }
    
    public function getDayNamesForHeader(): array
    {
        if ($this->weeklyMode) {
            return array_slice($this->dayNames, 0, 5);
        }
        
        return $this->dayNames;
    }
    
    public function getSelectedDayEvents(): array
    {
        return $this->events[$this->selectedDate] ?? [];
    }
    
    public function getEventTypeClass($type): string
    {
        return $this->eventTypeColors[$type]['card'] ?? $this->defaultEventTypeColors['default']['card'];
    }
    
    public function getEventDotClass($type): string
    {
        return $this->eventTypeColors[$type]['dot'] ?? $this->defaultEventTypeColors['default']['dot'];
    }
    
    public function getEventBadgeClass($type): string
    {
        return $this->eventTypeColors[$type]['badge'] ?? $this->defaultEventTypeColors['default']['badge'];
    }
    
    public function getEventIcon($type): string
    {
        return $this->eventTypeColors[$type]['icon'] ?? $this->defaultEventTypeColors['default']['icon'];
    }
}; ?>

<x-card class="w-full h-full flex flex-col">
    {{-- Calendar Header --}}
    <div class="flex justify-between items-center mb-6">
        {{-- Previous Month Button (conditional display) --}}
        @if(!$displayWeekly && $this->canNavigatePrevious())
            <x-button 
                wire:click="previousMonth" 
                icon="phosphor.caret-left" 
                class="btn-circle btn-sm btn-ghost"
                title="Previous Month"
            />
        @else
            <div class="w-8"></div>
        @endif
        
        <div class="flex items-center gap-3">
            <h4 class="text-lg font-bold text-center">{{ $this->getCurrentMonthName() }}</h4>
            
            @if ($useTodayBtn)
                {!! $this->todayButton() !!}
            @endif
        </div>
        
        {{-- Next Month Button or View Toggle (conditional display) --}}
        @if($displayWeekly)
            <x-button 
                wire:click="toggleViewMode" 
                icon="{{ $weeklyMode ? 'phosphor.calendar' : 'phosphor.calendar-dots' }}"
                class="btn-circle btn-sm {{ $weeklyMode ? 'btn-primary' : 'btn-secondary' }}"
                title="{{ $weeklyMode ? 'Switch to Monthly View' : 'Switch to Weekly View' }}"
            />
        @elseif(!$displayWeekly && $this->canNavigateNext())
            <x-button 
                wire:click="nextMonth" 
                icon="phosphor.caret-right" 
                class="btn-circle btn-sm btn-ghost"
                title="Next Month"
            />
        @else
            <div class="w-8"></div>
        @endif
    </div>
    
    {{-- Main Content Container --}}
    <div class="flex-1 flex flex-col">
        {{-- Calendar Grid --}}
        <div class="w-full flex-1 flex flex-col">
            {{-- Days of Week Header : Cannot be displayed on mobile --}}
            <div class="{{ $weeklyMode ? 'hidden' : 'grid' }} lg:grid {{ $this->getGridColsClass() }} gap-1 mb-2">
                @foreach($this->getDayNamesForHeader() as $dayName)
                    <div class="text-center font-bold text-base-content/70 py-1 text-xs">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="grid {{ $this->getGridColsClass() }} gap-1 flex-1 {{ $this->getGridRowsClass() }}">
                @foreach($this->getCalendarDays() as $weekIndex => $week)
                    @foreach($week as $day)
                        <div class="relative {{ $weeklyMode ? 'min-h-16' : 'min-h-18' }} h-full border border-base-200 rounded-md p-2 transition-all duration-200 cursor-pointer
                            {{ $day['isSelected'] ? 'bg-accent/30 ring-2 ring-accent' : 'hover:bg-base-200' }}"
                            @if($day['number']) wire:click="selectDate({{ $day['number'] }})" @endif>
                            
                            @if($day['number'])
                                {{-- Day number with circle for today --}}
                                <div class="text-sm font-medium mb-1 {{ $day['isToday'] ? 'text-primary font-bold mr-1' : 'text-base-content' }}">
                                    @if($day['isToday'])
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent text-accent-content text-xs font-bold">
                                            {{ $day['displayNumber'] }}
                                        </span>
                                    @else
                                        <div class="flex items-center gap-1 mx-auto text-center">
                                            <span>{{ $day['displayNumber'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Event indicators (colored dots) --}}
                                @if($day['hasEvent'])
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($day['events'] as $event)
                                            <div class="w-5 h-5 rounded-full {{ $this->getEventDotClass($event['type']) }} flex items-center justify-center">
                                                <x-icon name="{{ $this->getEventIcon($event['type']) }}" 
                                                        class="w-4 h-4 text-white" />
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
        @if($showEventDetails)
            <div class="mt-6 mb-4">
                <h5 class="w-full text-md font-semibold mb-3 text-base-content text-center lg:text-left">
                    Events for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                </h5>
                
                @if(count($this->getSelectedDayEvents()) > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        @foreach($this->getSelectedDayEvents() as $event)
                            <x-card class="p-4 {{ $this->getEventTypeClass($event['type']) }}">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-icon name="{{ $this->getEventIcon($event['type']) }}" class="w-4 h-4" />
                                    <h6 class="text-white font-semibold text-sm">{{ $event['title'] }}</h6>
                                </div>
                                
                                <div class="space-y-1 text-xs opacity-90">
                                    @if(isset($event['time']))
                                        <div class="flex items-center gap-2">
                                            <x-icon name="phosphor.clock" class="w-3 h-3" />
                                            <span>{{ $event['time'] }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($event['location']) && $event['location'] !== 'TBA')
                                        <div class="flex items-center gap-2">
                                            <x-icon name="phosphor.map-pin" class="w-3 h-3" />
                                            <span>{{ strtoupper($event['location']) }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if(isset($event['description']))
                                    <p class="text-sm mt-2 opacity-80">{!! $event['description'] !!}</p>
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
        @endif
    
        {{-- Legend --}}
        @if($showLegend)
            <x-slot:actions separator>
                <div class="w-full mt-auto pt-4 flex flex-wrap gap-3 justify-center border-t border-base-300">
                    @foreach($eventTypeColors as $type => $colors)
                        @if($type !== 'default')
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $colors['icon'] }}" class="w-5 h-5 {{ str_replace('bg-', 'text-', $colors['dot']) }}" />
                                <span class="text-base-content text-sm">{{ str($type)->replace('_', ' ')->title() }}</span>
                            </div>
                        @endif
                    @endforeach
                    
                    @if($weeklyMode)
                        <div class="w-full text-center">
                            <span class="text-xs text-base-content/60">Showing next 15 days starting from today</span>
                        </div>
                    @endif
                </div>
            </x-slot:actions>
        @endif
    </div>
</x-card>
