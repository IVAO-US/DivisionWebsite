<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $activeTab = 'Calendar';
    
    // Calendar data
    public array $currentDate;
    public array $events;
    public array $monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    // Event cards data
    public array $upcomingEvents = [
        [
            'title' => 'Cross Country Event',
            'date' => 'Saturday, March 15th',
            'image' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png'
        ],
        [
            'title' => 'IFR Training Session', 
            'date' => 'Sunday, March 23rd',
            'image' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg'
        ],
        [
            'title' => 'Friday Night Ops',
            'date' => 'Friday, March 28th', 
            'image' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png'
        ]
    ];
    
    public function mount(): void
    {
        $this->currentDate = [
            'year' => 2025,
            'month' => 1, // February (0-indexed)
            'day' => 1
        ];
        
        $this->events = [
            '2025-02-24' => [
                'title' => 'SEC Exam',
                'time' => '20:00 UTC',
                'location' => 'KN90_APP'
            ]
        ];
    }
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
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
        
        // Get first day of month and last date
        $firstDay = date('N', mktime(0, 0, 0, $month + 1, 1, $year)) % 7; // 0=Monday
        $lastDate = date('t', mktime(0, 0, 0, $month + 1, 1, $year));
        
        $days = [];
        $dayCounter = 1;
        
        for ($week = 0; $week < 6; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 7; $day++) {
                if (($week === 0 && $day < $firstDay) || $dayCounter > $lastDate) {
                    $weekDays[] = ['number' => '', 'hasEvent' => false, 'event' => null];
                } else {
                    $eventKey = sprintf('%d-%02d-%02d', $year, $month + 1, $dayCounter);
                    $hasEvent = isset($this->events[$eventKey]);
                    
                    $weekDays[] = [
                        'number' => $dayCounter,
                        'hasEvent' => $hasEvent,
                        'event' => $hasEvent ? $this->events[$eventKey] : null
                    ];
                    $dayCounter++;
                }
            }
            $days[] = $weekDays;
        }
        
        return $days;
    }
}; ?>

<section class="artists-section section-padding py-16 bg-base-100">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-12 text-center">

                {{-- Tab Navigation --}}
                <nav class="flex justify-center">
                    <div class="nav nav-tabs flex items-center justify-center p-4 rounded-full bg-light shadow text-center" 
                         id="nav-tab" role="tablist">
                        <h2 class="mb-4 w-full text-3xl font-bold">Events & Daily Bookings</h2>

                        <div class="flex gap-3">
                            <button 
                                wire:click="setActiveTab('Calendar')"
                                class="nav-link rounded-full px-4 py-2 {{ $activeTab === 'Calendar' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} transition-colors">
                                <h5 class="mb-0">Calendar</h5>
                            </button>

                            <button 
                                wire:click="setActiveTab('Events')"
                                class="nav-link rounded-full px-4 py-2 {{ $activeTab === 'Events' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} transition-colors">
                                <h5 class="mb-0">Events</h5>
                            </button>
                        </div>
                    </div>
                </nav>

                {{-- Tab Content --}}
                <div class="tab-content shadow-lg py-4 mb-3 mt-5" id="nav-tabContent">
                    
                    {{-- Calendar Tab --}}
                    @if($activeTab === 'Calendar')
                        <div class="tab-pane fade show active">
                            <h3 class="text-center text-2xl font-bold mb-4">Division Calendar</h3>
                            <div class="flex justify-center mt-3">
                                <div class="container mt-4 max-w-2xl">
                                    <div class="calendar p-3 rounded bg-white shadow-lg">
                                        {{-- Calendar Header --}}
                                        <div class="flex justify-between items-center mb-3">
                                            <button wire:click="previousMonth" 
                                                    class="btn btn-outline-primary px-3 py-1 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white rounded">
                                                &lt;
                                            </button>
                                            <h4 class="text-center text-xl font-bold">{{ $this->getCurrentMonthName() }}</h4>
                                            <button wire:click="nextMonth" 
                                                    class="btn btn-outline-primary px-3 py-1 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white rounded">
                                                &gt;
                                            </button>
                                        </div>
                                        
                                        {{-- Days of week --}}
                                        <div class="grid grid-cols-7 text-center font-bold border-b pb-2 mb-2">
                                            <div class="col">MON</div>
                                            <div class="col">TUE</div>
                                            <div class="col">WED</div>
                                            <div class="col">THU</div>
                                            <div class="col">FRI</div>
                                            <div class="col">SAT</div>
                                            <div class="col">SUN</div>
                                        </div>
                                        
                                        {{-- Calendar Days --}}
                                        <div class="calendar-days">
                                            @foreach($this->getCalendarDays() as $week)
                                                <div class="grid grid-cols-7 border-b">
                                                    @foreach($week as $day)
                                                        <div class="col border py-2 text-center relative {{ $day['hasEvent'] ? 'bg-blue-100' : '' }} {{ $day['number'] ? 'hover:bg-gray-100' : 'text-gray-400' }}">
                                                            @if($day['number'])
                                                                @if($day['hasEvent'])
                                                                    <div class="day-number font-bold text-blue-600">{{ $day['number'] }}</div>
                                                                    {{-- Event Tooltip --}}
                                                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-1 bg-black text-white text-xs p-2 rounded shadow-lg opacity-0 hover:opacity-100 transition-opacity z-10 whitespace-nowrap">
                                                                        <strong>{{ $day['event']['title'] }}</strong><br>
                                                                        {{ $day['event']['time'] }}<br>
                                                                        <em>{{ $day['event']['location'] }}</em>
                                                                    </div>
                                                                @else
                                                                    <span class="day-number">{{ $day['number'] }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Events Tab --}}
                    @if($activeTab === 'Events')
                        <div class="tab-pane fade show active">
                            <h3 class="text-center text-2xl font-bold mb-4">Upcoming Events & Training Sessions</h3>
                            
                            {{-- Event Carousel --}}
                            <div class="relative max-w-4xl mx-auto">
                                <div class="overflow-hidden rounded-lg">
                                    <div class="flex transition-transform duration-300" id="eventCarousel">
                                        @foreach($upcomingEvents as $event)
                                            <div class="w-full flex-shrink-0 px-4">
                                                <a href="#" class="event-card-link block">
                                                    <div class="event-card bg-white rounded-lg shadow-lg overflow-hidden">
                                                        <img src="{{ $event['image'] }}" 
                                                             alt="{{ $event['title'] }}" 
                                                             class="event-img w-full h-64 object-cover">
                                                        <div class="event-body p-4 text-center bg-blue-50">
                                                            <div class="event-title text-xl font-bold mb-2">{{ $event['title'] }}</div>
                                                            <div class="event-date text-red-600">{{ $event['date'] }}</div>
                                                            <div class="event-fake-button inline-block bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded font-bold mt-2 transition-colors">
                                                                Click for more information
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                {{-- Carousel Indicators --}}
                                <div class="flex justify-center mt-4 space-x-2">
                                    @foreach($upcomingEvents as $index => $event)
                                        <button class="w-3 h-3 rounded-full {{ $index === 0 ? 'bg-blue-600' : 'bg-gray-300' }} hover:bg-blue-600 transition-colors"></button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>