<?php
use Livewire\Volt\Component;

new class extends Component {
    // Carousel configuration
    public array $items = [];
    public string $imageKey = 'image';
    public string $titleKey = 'title';
    public string $subtitleKey = 'subtitle';
    public string $descriptionKey = 'description';
    public string $linkKey = 'link';
    public string $linkLabel = 'More information';
    public string $linkIcon = 'phosphor.link';
    public int $autoAdvanceInterval = 4; // seconds
    public int $interactionDelay = 6; // seconds before auto-advance resumes
    
    // New image dimension properties
    public string $imageHeight = '30vh';
    public string $imageWidth = '45vw';
    
    // New shuffle property
    public bool $shuffle = false;
    
    // Carousel state
    public int $currentIndex = 0;
    public bool $isPaused = false;
    public int $lastInteractionTime = 0;
    
    // Store original items order for potential reset
    private array $originalItems = [];
    
    public function mount(): void
    {
        $this->lastInteractionTime = time();
        
        // Store original items order
        $this->originalItems = $this->items;
        
        // Shuffle items if shuffle option is enabled
        if ($this->shuffle && !empty($this->items)) {
            $this->shuffleItems();
        }
    }
    
    /**
     * Shuffle the items array while maintaining keys structure
     */
    private function shuffleItems(): void
    {
        $keys = array_keys($this->items);
        shuffle($keys);
        $shuffledItems = [];
        
        foreach ($keys as $key) {
            $shuffledItems[] = $this->items[$key];
        }
        
        $this->items = $shuffledItems;
    }
    
    /**
     * Reset items to original order (useful for debugging or manual control)
     */
    public function resetOrder(): void
    {
        $this->items = $this->originalItems;
        $this->currentIndex = 0;
    }
    
    /**
     * Re-shuffle items manually
     */
    public function reshuffleItems(): void
    {
        if (!empty($this->items)) {
            $this->shuffleItems();
            $this->currentIndex = 0;
        }
    }
    
    public function next(): void
    {
        $this->currentIndex = ($this->currentIndex + 1) % count($this->items);
        $this->lastInteractionTime = time();
    }
    
    public function previous(): void
    {
        $this->currentIndex = ($this->currentIndex - 1 + count($this->items)) % count($this->items);
        $this->lastInteractionTime = time();
    }
    
    public function setIndex($index): void
    {
        $this->currentIndex = $index;
        $this->lastInteractionTime = time();
    }
    
    public function autoAdvance(): void
    {
        // Only auto-advance if not paused and if enough time has passed since last interaction
        if (!$this->isPaused && (time() - $this->lastInteractionTime) > $this->interactionDelay) {
            $this->currentIndex = ($this->currentIndex + 1) % count($this->items);
        }
    }
    
    public function getCurrentItem(): array
    {
        return $this->items[$this->currentIndex] ?? [];
    }
    
    /**
     * Get dynamic image styles based on configured dimensions
     */
    public function getImageStyles(): string
    {
        return "width: {$this->imageWidth}; height: {$this->imageHeight};";
    }
}; ?>

<div wire:poll.{{ $autoAdvanceInterval }}s="autoAdvance" 
     wire:mouseenter="$set('isPaused', true)" 
     wire:mouseleave="$set('isPaused', false)"
     class="h-full flex flex-col">
    
    {{-- Current Item Display --}}
    <div class="flex-1 flex flex-col">
        @if(!empty($items) && isset($items[$currentIndex]))
            @php $item = $items[$currentIndex]; @endphp
            
            {{-- Image --}}
            @if(isset($item[$imageKey]))
                <div class="flex-shrink-0 mx-auto lg:mx-0">
                    <img src="{{ $item[$imageKey] }}" 
                         alt="{{ $item[$titleKey] ?? 'Carousel item' }}"
                         style="{{ $this->getImageStyles() }}"
                         class="object-cover transition-transform duration-700 ease-out rounded-lg !w-full lg:w-auto" />
                </div>
            @endif
            
            {{-- Content --}}
            <div class="flex-1 w-full py-4 relative bg-base-100 flex flex-col">
                <div class="transition-all duration-500 delay-200 transform flex-1">
                    {{-- Title --}}
                    @if(isset($item[$titleKey]))
                        <h4 class="text-xl font-bold mb-3 text-primary transition-all duration-300">
                            {{ $item[$titleKey] }}
                        </h4>
                    @endif
                    
                    {{-- Subtitle --}}
                    @if(isset($item[$subtitleKey]))
                        <p class="text-base text-accent font-medium mb-4 transition-all duration-300 delay-100">
                            {{ $item[$subtitleKey] }}
                        </p>
                    @endif
                    
                    {{-- Description --}}
                    @if(isset($item[$descriptionKey]))
                        <p class="text-sm text-base-content/80 mb-6 line-clamp-3 leading-relaxed transition-all duration-300 delay-150">
                            {{ $item[$descriptionKey] }}
                        </p>
                    @endif
                    
                    {{-- Link Button - pushed to bottom of content --}}
                    @if(isset($item[$linkKey]))
                        <div class="mt-auto text-center">
                            <x-button 
                                label="{{ $linkLabel }}" 
                                link="{{ $item[$linkKey] }}"
                                icon="{{ $linkIcon }}"
                                class="btn-primary btn-sm transition-all duration-300 hover:scale-105 hover:-translate-y-0.5 hover:shadow-lg"
                                external
                            />
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Navigation Controls - Always at bottom --}}
    @if(count($items) > 1)
        <div class="w-full flex-shrink-0">
            <div class="w-full flex justify-between items-center py-4 bg-base-100">
                {{-- Previous Button --}}
                <div class="flex justify-center w-12">
                    <x-button 
                        wire:click="previous"
                        icon="phosphor.caret-left"
                        class="btn-circle btn-sm btn-secondary shadow-lg pb-1 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5 hover:shadow-xl"
                        title="Previous"
                    />
                </div>
                
                {{-- Indicators --}}
                <div class="flex justify-center gap-2 flex-1">
                    @foreach($items as $index => $item)
                        <button 
                            wire:click="setIndex({{ $index }})"
                            class="h-2.5 rounded-full transition-all duration-500 ease-out transform hover:scale-125 
                                {{ $index === $currentIndex 
                                    ? 'bg-secondary w-8 shadow-lg shadow-secondary/30' 
                                    : 'bg-secondary/50 hover:bg-secondary/75 w-2.5' }}"
                            title="{{ $item[$titleKey] ?? 'Item ' . ($index + 1) }}"
                        ></button>
                    @endforeach
                </div>
                
                {{-- Next Button --}}
                <div class="flex justify-center w-12">
                    <x-button 
                        wire:click="next"
                        icon="phosphor.caret-right"
                        class="btn-circle btn-sm btn-secondary shadow-lg pb-1 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5 hover:shadow-xl"
                        title="Next"
                    />
                </div>
            </div>
        </div>
    @endif
</div>