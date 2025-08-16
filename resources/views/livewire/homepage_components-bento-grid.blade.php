<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $activeTab = 'tours';
    
    // Bento source images with optimized data structure
    public array $bentoImages = [
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'small',
            'href' => '#',
            'title' => 'Atlanta RFE Event',
            'description' => 'Real Flight Event in Atlanta'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'medium', 
            'href' => '#',
            'title' => 'European Tour',
            'description' => 'Explore European airports'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'large',
            'href' => '#',
            'title' => 'Major RFE Atlanta',
            'description' => 'Large scale aviation event'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'medium',
            'href' => '#',
            'title' => 'EBUSCA Airport',
            'description' => 'Scenic European destination'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png', 
            'size' => 'large',
            'href' => '#',
            'title' => 'Premium Atlanta Event',
            'description' => 'High-profile aviation gathering'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'small',
            'href' => '#',
            'title' => 'Quick European Hop',
            'description' => 'Short distance tour'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'medium',
            'href' => '#',
            'title' => 'Regional Atlanta',
            'description' => 'Regional aviation focus'
        ]
    ];
    
    public array $shuffledImages = [];
    public bool $autoShuffle = true;
    
    public function mount(): void
    {
        $this->shuffleImages();
    }
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
        
        // Optionally refresh images when switching tabs
        if ($tab === 'tours') {
            $this->shuffleImages();
        }
    }
    
    public function shuffleImages(): void
    {
        if (!$this->autoShuffle) {
            return;
        }
        
        $this->shuffledImages = $this->bentoImages;
        
        // Fisher-Yates shuffle algorithm for true randomness
        for ($i = count($this->shuffledImages) - 1; $i > 0; $i--) {
            $j = rand(0, $i);
            [$this->shuffledImages[$i], $this->shuffledImages[$j]] = 
            [$this->shuffledImages[$j], $this->shuffledImages[$i]];
        }
        
        // Smart layout optimization - ensure variety in grid sizes
        $sizePattern = ['large', 'medium', 'small', 'medium', 'large', 'small', 'medium'];
        foreach ($this->shuffledImages as $index => &$img) {
            $img['size'] = $sizePattern[$index % count($sizePattern)];
        }
    }
    
    public function stopAutoShuffle(): void
    {
        $this->autoShuffle = false;
    }
    
    public function startAutoShuffle(): void
    {
        $this->autoShuffle = true;
        $this->shuffleImages();
    }
    
    public function forceReshuffle(): void
    {
        $this->shuffleImages();
    }
    
    /**
     * Get Tailwind grid classes for different bento sizes
     */
    public function getBentoGridClasses($size): string
    {
        return match($size) {
            'small' => 'col-span-1 row-span-1',
            'medium' => 'col-span-2 row-span-2', 
            'large' => 'col-span-3 row-span-2',
            default => 'col-span-1 row-span-1'
        };
    }
}; ?>

<div class="w-full">
    {{-- Section Header --}}
    <div class="text-center mb-8">
        <h2 class="text-4xl font-bold text-base-content mb-4">Flight Operations</h2>
        <p class="text-base-content/70 text-lg">Discover our tours and certified virtual airlines</p>
    </div>

    {{-- MaryUI Tabs Navigation --}}
    <div class="flex justify-center mb-8">
        <x-tabs wire:model="activeTab" class="bg-base-200 rounded-2xl p-2 shadow-lg">
            <x-tab name="tours" label="Tours" icon="phosphor.airplane-takeoff">
                {{-- Tours Content --}}
                <div class="mt-8">
                    {{-- Shuffle Control --}}
                    <div class="flex justify-center items-center gap-4 mb-6">
                        <span class="text-sm text-base-content/60">Auto-shuffle:</span>
                        <input 
                            type="checkbox" 
                            class="toggle toggle-primary" 
                            wire:model.live="autoShuffle"
                            wire:change="autoShuffle ? startAutoShuffle() : stopAutoShuffle()"
                        />
                        <button 
                            wire:click="forceReshuffle"
                            class="btn btn-sm btn-outline btn-primary"
                            title="Shuffle now"
                        >
                            <span class="iconify" data-icon="phosphor:shuffle"></span>
                            Shuffle
                        </button>
                    </div>

                    {{-- Bento Grid Container --}}
                    <div class="grid grid-cols-4 auto-rows-[150px] gap-3 p-4 bg-base-100 rounded-xl shadow-inner">
                        @foreach($shuffledImages as $index => $image)
                            <div 
                                class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02] {{ $this->getBentoGridClasses($image['size']) }}"
                                wire:mouseenter="stopAutoShuffle" 
                                wire:mouseleave="startAutoShuffle"
                            >
                                <a 
                                    href="{{ $image['href'] }}" 
                                    target="_blank" 
                                    class="block w-full h-full relative"
                                >
                                    {{-- Image --}}
                                    <img 
                                        src="{{ $image['url'] }}" 
                                        alt="{{ $image['title'] ?? 'Tour Image' }}"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        loading="lazy"
                                    />
                                    
                                    {{-- Overlay with gradient --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="absolute bottom-0 left-0 right-0 p-3">
                                            <h4 class="text-white font-semibold text-sm mb-1">
                                                {{ $image['title'] ?? 'Tour' }}
                                            </h4>
                                            @if(isset($image['description']))
                                                <p class="text-white/80 text-xs">
                                                    {{ $image['description'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Hover icon --}}
                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="bg-primary text-primary-content rounded-full p-1">
                                            <span class="iconify text-sm" data-icon="phosphor:arrow-square-out"></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Auto-shuffle polling --}}
                    @if($autoShuffle)
                        <div wire:poll.5s="shuffleImages"></div>
                    @endif
                </div>
            </x-tab>

            <x-tab name="virtual-airlines" label="Virtual Airlines" icon="phosphor.buildings">
                {{-- Virtual Airlines Content --}}
                <div class="mt-8">
                    <div class="text-center py-16">
                        <div class="max-w-md mx-auto">
                            <div class="mb-6">
                                <span class="iconify text-6xl text-base-content/30" data-icon="phosphor:buildings"></span>
                            </div>
                            <h3 class="text-2xl font-bold text-base-content mb-4">
                                US Certified Virtual Airlines
                            </h3>
                            <p class="text-base-content/60 mb-6">
                                Our certified virtual airlines are coming soon. Stay tuned for exciting partnerships and exclusive flight opportunities.
                            </p>
                            <div class="alert alert-info">
                                <span class="iconify" data-icon="phosphor:info"></span>
                                <span>Virtual Airlines content is being prepared and will be available soon!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-tab>
        </x-tabs>
    </div>
</div>