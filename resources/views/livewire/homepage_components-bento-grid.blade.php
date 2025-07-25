<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $activeTab = 'Tours';
    
    // Bento source images with data attributes
    public array $bentoImages = [
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'small',
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'medium', 
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'large',
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'medium',
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png', 
            'size' => 'large',
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'size' => 'small',
            'href' => '#'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'size' => 'medium',
            'href' => '#'
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
    }
    
    public function shuffleImages(): void
    {
        $this->shuffledImages = $this->bentoImages;
        
        // Shuffle array (Fisher-Yates algorithm)
        for ($i = count($this->shuffledImages) - 1; $i > 0; $i--) {
            $j = rand(0, $i);
            $temp = $this->shuffledImages[$i];
            $this->shuffledImages[$i] = $this->shuffledImages[$j];
            $this->shuffledImages[$j] = $temp;
        }
        
        // Optimize layout - alternate sizes for more natural rendering
        foreach ($this->shuffledImages as $index => &$img) {
            if ($index % 4 === 0) {
                $img['size'] = 'large';
            } elseif ($index % 2 === 0) {
                $img['size'] = 'medium';
            } else {
                $img['size'] = 'small';
            }
        }
    }
    
    public function stopAutoShuffle(): void
    {
        $this->autoShuffle = false;
    }
    
    public function startAutoShuffle(): void
    {
        $this->autoShuffle = true;
    }
    
    public function getSizeClasses($size): string
    {
        return match($size) {
            'small' => 'bento-small',
            'medium' => 'bento-medium', 
            'large' => 'bento-large',
            default => 'bento-small'
        };
    }
}; ?>

<div>
    <section class="fly-section section-padding py-16 bg-base-200">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">

                    {{-- Tab Navigation --}}
                    <nav class="flex justify-center">
                        <div class="nav nav-tabs flex items-center justify-center p-4 rounded-full bg-light shadow text-center" 
                            id="nav-tab" role="tablist">
                            <h2 class="mb-4 w-full text-3xl font-bold">Flight Operations</h2>

                            <div class="flex gap-3">
                                <button 
                                    wire:click="setActiveTab('Tours')"
                                    class="nav-link rounded-full px-4 py-2 {{ $activeTab === 'Tours' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} transition-colors">
                                    <h5 class="mb-0">Tours</h5>
                                </button>

                                <button 
                                    wire:click="setActiveTab('VirtualAirlines')"
                                    class="nav-link rounded-full px-4 py-2 {{ $activeTab === 'VirtualAirlines' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} transition-colors">
                                    <h5 class="mb-0">Virtual Airlines</h5>
                                </button>
                            </div>
                        </div>
                    </nav>

                    {{-- Tab Content --}}
                    <div class="tab-content shadow-lg mt-5" id="nav-tabContent">
                        
                        {{-- Tours Tab --}}
                        @if($activeTab === 'Tours')
                            <div class="tab-pane fade show active">
                                <h3 class="text-center text-2xl font-bold mb-6">Fly our Tours</h3>
                                
                                {{-- Bento Container --}}
                                <div class="bento-container" 
                                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); grid-auto-rows: 150px; gap: 12px; padding: 20px; grid-auto-flow: dense;">
                                    @foreach($shuffledImages as $index => $image)
                                        <div class="bento-item {{ $this->getSizeClasses($image['size']) }} relative overflow-hidden rounded-lg shadow-lg transition-transform hover:scale-105 flex items-center justify-center"
                                            wire:mouseenter="stopAutoShuffle" 
                                            wire:mouseleave="startAutoShuffle"
                                            style="
                                                @if($image['size'] === 'small') grid-row: span 1; grid-column: span 1; @endif
                                                @if($image['size'] === 'medium') grid-row: span 2; grid-column: span 2; @endif  
                                                @if($image['size'] === 'large') grid-row: span 2; grid-column: span 3; @endif
                                            ">
                                            <a href="{{ $image['href'] }}" 
                                            target="_blank" 
                                            class="block w-full h-full">
                                                <img src="{{ $image['url'] }}" 
                                                    alt="Bento Image" 
                                                    class="w-full h-full object-cover rounded-lg">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Auto-shuffle timer (simulated with periodic refresh) --}}
                                @if($autoShuffle)
                                    <div wire:poll.5s="shuffleImages"></div>
                                @endif
                            </div>
                        @endif

                        {{-- Virtual Airlines Tab --}}
                        @if($activeTab === 'VirtualAirlines')
                            <div class="tab-pane fade show active">
                                <h3 class="text-center text-2xl font-bold mb-6">US Certified VAs</h3>
                                <div class="text-center text-gray-600 py-8">
                                    <p>Virtual Airlines content coming soon...</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    /* Bento Grid Styles */
    .bento-item {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bento-item img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
    }

    .bento-item a {
        display: block;
        width: 100%;
        height: 100%;
    }

    .bento-item:hover {
        transform: scale(1.05);
    }

    .bento-small  { grid-row: span 1; grid-column: span 1; }
    .bento-medium { grid-row: span 2; grid-column: span 2; }
    .bento-large  { grid-row: span 2; grid-column: span 3; }
    </style>
</div>