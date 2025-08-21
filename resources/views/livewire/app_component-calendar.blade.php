<?php
use Livewire\Volt\Component;

new class extends Component {
    // Props from parent component
    public array $events = [];
    public array $eventTypeColors = [];
    public bool $compact = false;
    public bool $showLegend = true;
    public bool $showEventDetails = true;
    public bool $useTodayBtn = false;
    
    // Internal state
    public array $currentDate;
    public string $selectedDate;
    
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
        bool $compact = false,
        bool $showLegend = true,
        bool $showEventDetails = true,
        ?string $initialDate = null
    ): void {
        $this->events = $events;
        $this->eventTypeColors = array_merge($this->defaultEventTypeColors, $eventTypeColors);
        $this->compact = $compact;
        $this->showLegend = $showLegend;
        $this->showEventDetails = $showEventDetails;
        
        // Initialize current date
        $startDate = $initialDate ? \Carbon\Carbon::parse($initialDate) : now();
        $this->currentDate = [
            'year' => $startDate->year,
            'month' => $startDate->month - 1, // 0-indexed for JavaScript compatibility
            'day' => $startDate->day
        ];
        
        // Set initial selected date
        $this->selectedDate = $startDate->format('Y-m-d');
    }
    
    public function selectDate(int $day): void
    {
        if ($day) {
            $this->selectedDate = sprintf('%d-%02d-%02d', 
                $this->currentDate['year'], 
                $this->currentDate['month'] + 1, 
                $day
            );
            
            // Emit event for parent component if needed
            $this->dispatch('date-selected', [
                'date' => $this->selectedDate,
                'events' => $this->getSelectedDayEvents()
            ]);
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
        
        $this->dispatch('month-changed', [
            'year' => $this->currentDate['year'],
            'month' => $this->currentDate['month'] + 1
        ]);
    }

    public function nextMonth(): void
    {
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
                    $weekDays[] = [
                        'number' => '', 
                        'hasEvent' => false, 
                        'events' => [], 
                        'isToday' => false, 
                        'isSelected' => false
                    ];
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
}; ?>

<x-card class="w-full h-full flex flex-col {{ $compact ? 'max-h-96' : '' }}">
    {{-- Calendar Header --}}
    <div class="flex justify-between items-center mb-6">
        <x-button 
            wire:click="previousMonth" 
            icon="phosphor.caret-left" 
            class="btn-circle btn-sm btn-ghost"
            title="Previous Month"
        />
        
        <div class="flex items-center gap-3">
            <h4 class="text-lg font-bold text-center">{{ $this->getCurrentMonthName() }}</h4>
            

            @if ($useTodayBtn)
                {{ todayButton() }}
            @endif
        </div>
        
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
        <div class="w-full {{ $compact ? 'flex-1' : 'flex-1' }} flex flex-col">
            {{-- Days of Week Header --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach($dayNames as $dayName)
                    <div class="text-center font-bold text-base-content/70 py-1 text-xs">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="grid grid-cols-7 gap-1 flex-1 {{ $compact ? 'grid-rows-4' : 'grid-rows-6' }}">
                @foreach($this->getCalendarDays() as $weekIndex => $week)
                    @if(!$compact || $weekIndex < 4)
                        @foreach($week as $day)
                            <div class="relative {{ $compact ? 'min-h-12' : 'min-h-18' }} h-full border border-base-200 rounded-md p-2 transition-all duration-200 cursor-pointer
                                {{ $day['isSelected'] ? 'bg-accent/30 ring-2 ring-accent' : 'hover:bg-base-200' }}"
                                @if($day['number']) wire:click="selectDate({{ $day['number'] }})" @endif>
                                
                                @if($day['number'])
                                    {{-- Day number with circle for today --}}
                                    <div class="text-sm font-medium mb-1 {{ $day['isToday'] ? 'text-primary font-bold' : 'text-base-content' }}">
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
                                                    <x-icon name="{{ $this->getEventIcon($event['type']) }}" 
                                                            class="w-4 h-4 text-white" />
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
        
        {{-- Selected Day Events Details --}}
        @if($showEventDetails && !$compact)
            <div class="mt-6 mb-4">
                <h5 class="w-full text-md font-semibold mb-3 text-base-content text-center lg:text-left">
                    Events for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                </h5>
                
                @if(count($this->getSelectedDayEvents()) > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
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
                                    
                                    @if(isset($event['location']))
                                        <div class="flex items-center gap-2">
                                            <x-icon name="phosphor.map-pin" class="w-3 h-3" />
                                            <span>{{ $event['location'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if(isset($event['description']))
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
        @endif
    
        {{-- Legend --}}
        @if($showLegend)
            <x-slot:actions separator>
                <div class="w-full mt-auto pt-4 flex flex-wrap gap-3 justify-center border-t border-base-300">
                    @foreach($eventTypeColors as $type => $colors)
                        @if($type !== 'default')
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $colors['icon'] }}" class="w-5 h-5 {{ str_replace('bg-', 'text-', $colors['dot']) }}" />
                                <span class="text-base-content text-sm">{{ ucfirst($type) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-slot:actions>
        @endif
    </div>
</x-card>