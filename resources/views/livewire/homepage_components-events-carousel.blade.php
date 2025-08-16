<?php
use Livewire\Volt\Component;

new class extends Component {
    // Event carousel data
    public array $upcomingEvents = [
        [
            'title' => 'ATC Night - New York',
            'date' => 'Saturday, March 15th',
            'description' => 'Join us for an intensive ATC training session covering New York airspace. Join us for an intensive ATC training session covering New York airspace. Join us for an intensive ATC training session covering New York airspace',
            'image' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'link' => '#'
        ],
        [
            'title' => 'Fly-In Miami',
            'date' => 'Sunday, March 23rd',
            'description' => 'Mass arrival event at Miami International Airport',
            'image' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'link' => '#'
        ],
        [
            'title' => 'IFR Training Session',
            'date' => 'Friday, March 28th',
            'description' => 'Advanced IFR procedures and approach training',
            'image' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'link' => '#'
        ]
    ];
    
    public int $currentEventIndex = 0;
    public bool $isPaused = false;
    public int $lastInteractionTime = 0;
    
    public function nextEvent(): void
    {
        $this->currentEventIndex = ($this->currentEventIndex + 1) % count($this->upcomingEvents);
        $this->lastInteractionTime = time();
    }
    
    public function previousEvent(): void
    {
        $this->currentEventIndex = ($this->currentEventIndex - 1 + count($this->upcomingEvents)) % count($this->upcomingEvents);
        $this->lastInteractionTime = time();
    }
    
    public function setEventIndex($index): void
    {
        $this->currentEventIndex = $index;
        $this->lastInteractionTime = time();
    }
    
    public function autoAdvance(): void
    {
        // Only auto-advance if not paused and if enough time has passed since last interaction
        if (!$this->isPaused && (time() - $this->lastInteractionTime) > 6) {
            $this->currentEventIndex = ($this->currentEventIndex + 1) % count($this->upcomingEvents);
        }
    }
}; ?>

<div wire:poll.4s="autoAdvance" 
     wire:mouseenter="$set('isPaused', true)" 
     wire:mouseleave="$set('isPaused', false)"
     class="h-full">
    
    <x-card class="w-full h-full overflow-hidden !pt-0 !px-0 flex flex-col min-h-0">
        {{-- Event Carousel --}}
        <div class="relative flex-1">
            {{-- Current Event Card --}}
            <div class="w-full h-full relative flex flex-col transition-all duration-500 ease-in-out">
                @if(isset($upcomingEvents[$currentEventIndex]))
                    @php $event = $upcomingEvents[$currentEventIndex]; @endphp
                    <div class="flex flex-col h-full">
                        <x-slot:figure>
                            <img src="{{ $event['image'] }}" 
                                 alt="{{ $event['title'] }}"
                                 class="w-full w-[45vw] h-[30vh] transition-transform duration-700 ease-out" />
                        </x-slot:figure>
                        
                        <div class="w-full py-4 px-15 relative bg-base-100">
                            <div class="transition-all duration-500 delay-200 transform">
                                <h4 class="text-xl font-bold mb-3 text-primary transition-all duration-300">{{ $event['title'] }}</h4>
                                <p class="text-base text-accent font-medium mb-4 transition-all duration-300 delay-100">{{ $event['date'] }}</p>
                                <p class="text-sm text-base-content/80 mb-6 line-clamp-3 leading-relaxed transition-all duration-300 delay-150">{{ $event['description'] }}</p>
                                <x-button 
                                    label="More information" 
                                    link="{{ $event['link'] }}"
                                    icon="phosphor.link"
                                    class="btn-primary btn-sm mt-4 transition-all duration-300 hover:scale-105 hover:-translate-y-0.5 hover:shadow-lg"
                                    external
                                />
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>    

        {{-- Event Indicators --}}
        <x-slot:actions separator>
            <div class="w-full flex justify-between items-center py-4 px-4">
                {{-- Previous Button --}}
                <div class="flex justify-center w-12">
                    <x-button 
                        wire:click="previousEvent"
                        icon="phosphor.caret-left"
                        class="btn-circle btn-sm btn-secondary shadow-lg pb-1 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5 hover:shadow-xl"
                        title="Previous Event"
                    />
                </div>
                
                {{-- Indicators --}}
                <div class="flex justify-center gap-2 flex-1">
                    @foreach($upcomingEvents as $index => $event)
                        <button 
                            wire:click="setEventIndex({{ $index }})"
                            class="h-2.5 rounded-full transition-all duration-500 ease-out transform hover:scale-125 
                                {{ $index === $currentEventIndex 
                                    ? 'bg-secondary w-8 shadow-lg shadow-secondary/30' 
                                    : 'bg-secondary/50 hover:bg-secondary/75 w-2.5' }}"
                            title="{{ $event['title'] }}"
                        ></button>
                    @endforeach
                </div>
                
                {{-- Next Button --}}
                <div class="flex justify-center w-12">
                    <x-button 
                        wire:click="nextEvent"
                        icon="phosphor.caret-right"
                        class="btn-circle btn-sm btn-secondary shadow-lg pb-1 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5 hover:shadow-xl"
                        title="Next Event"
                    />
                </div>
            </div>
        </x-slot:actions>
    </x-card>
</div>