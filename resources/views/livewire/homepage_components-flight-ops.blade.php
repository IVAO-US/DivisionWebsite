<?php
use Livewire\Volt\Component;
use App\Models\Tour;
use App\Models\VirtualAirline;
use App\Models\AppSetting;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $activeTab = 'tours';
    
    /* Tours details prop */
    public string $bentoSetupId;
    public string $toursRepo = 'https://tours.th.ivao.aero/index.php?div=US';

    /**
     * Mount component
     */
    public function mount(): void
    {
        // Load bento seed from database, fallback to default if not set
        $this->bentoSetupId = AppSetting::get('homepage_tours_bento_seed');
    }
    
    /**
     * Get certified virtual airlines data formatted for carousel
     */
    #[Computed]
    public function certifiedVAs(): array
    {
        return VirtualAirline::all()
            ->map(fn($va) => $va->toCarouselFormat())
            ->toArray();
    }

    /**
     * Check if there are any virtual airlines
     */
    #[Computed]
    public function hasVirtualAirlines(): bool
    {
        return count($this->certifiedVAs) > 0;
    }

    /**
     * Set active tab for mobile view
     */
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Get tours data for bento grid component
     */
    public function getToursData(): array
    {
        return Tour::all()
            ->map(fn($tour) => $tour->toBentoFormat())
            ->toArray();
    }

    /**
     * Check if there are any tours
     */
    #[Computed]
    public function hasTours(): bool
    {
        return count($this->getToursData()) > 0;
    }
}; ?>

<div class="w-full">
    {{-- Mobile Tabs (below lg) --}}
    <div class="lg:hidden">
        <x-card class="shadow-lg">
            <h3 class="!text-center font-bold text-primary !mb-5">Flight Operations</h3>
            <x-tabs wire:model="activeTab"
                    class="w-full"
                    label-div-class="bg-base-100 !p-3 !mb-4 rounded-lg font-semibold whitespace-nowrap overflow-x-auto w-fit mx-auto" 
                    active-class="bg-primary p-3 rounded-lg !text-white font-semibold" 
                    label-class="p-3 font-semibold" 
                    >
                <x-tab name="tours" label="Tours" icon="phosphor.airplane-takeoff">
                    <x-card class="flex flex-col h-full">
                        @if($this->hasTours)
                            <div class="flex-1">
                                {{-- Bento Grid for Tours --}}
                                <livewire:app_component-bento-grid :images="$this->getToursData()" setup-id="{{ $bentoSetupId }}" wire:key="mobile-tours" />
                            </div>

                            {{-- Call to Action - Pushed to bottom --}}
                            <div class="text-center mt-auto pt-10">
                                <x-button class="btn btn-accent btn-lg" link="{{ $toursRepo }}" external>
                                    <x-icon name="phosphor.airplane-takeoff" class="w-5 h-5" />
                                    View All Tours
                                </x-button>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <x-icon name="phosphor.airplane-takeoff" class="w-16 h-16 text-base-content/30 mb-4" />
                                <h4 class="text-lg font-semibold text-base-content/70 mb-2">No Tours Available</h4>
                                <p class="text-sm text-base-content/50">Check back soon for exciting flight routes!</p>
                            </div>
                        @endif
                    </x-card>
                </x-tab>

                <x-tab name="virtual-airlines" label="VAs" icon="phosphor.buildings">
                    @if($this->hasVirtualAirlines)
                        {{-- Virtual Airlines Carousel --}}
                        <livewire:app_component-carousel 
                            :items="$this->certifiedVAs"
                            image-key="image"
                            title-key="title"
                            subtitle-key="date"
                            description-key="description"
                            link-key="link"
                            link-label="More information"
                            link-icon="phosphor.link"
                            :auto-advance-interval="4"
                            :interaction-delay="6"
                            wire:key="mobile-va-carousel"
                            image-height="30vh"
                            image-width="45vw"
                            :shuffle="true"
                        />
                    @else
                        <x-card class="flex flex-col items-center justify-center py-12 text-center">
                            <x-icon name="phosphor.buildings" class="w-16 h-16 text-base-content/30 mb-4" />
                            <h4 class="text-lg font-semibold text-base-content/70 mb-2">No Virtual Airlines</h4>
                            <p class="text-sm text-base-content/50">We're working on partnerships with virtual airlines.</p>
                        </x-card>
                    @endif
                </x-tab>
            </x-tabs>
        </x-card>
    </div>

    {{-- Desktop Layout (lg and above) --}}
    <div class="hidden lg:block">
        <div class="grid lg:grid-cols-3 gap-8 h-fit">
            {{-- Virtual Airlines Section --}}
            <div class="lg:col-span-1 flex flex-col">
                <x-card title="Virtual Airlines" subtitle="Certified Partners" shadow separator class="flex-1">
                    @if($this->hasVirtualAirlines)
                        {{-- Virtual Airlines Carousel --}}
                        <livewire:app_component-carousel 
                            :items="$this->certifiedVAs"
                            image-key="image"
                            title-key="title"
                            subtitle-key="date"
                            description-key="description"
                            link-key="link"
                            link-label="More information"
                            link-icon="phosphor.link"
                            :auto-advance-interval="4"
                            :interaction-delay="6"
                            wire:key="desktop-va-carousel"
                            image-height="20vh"
                            image-width="30vw"
                            :shuffle="true"
                        />
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center h-full">
                            <x-icon name="phosphor.buildings" class="w-16 h-16 text-base-content/30 mb-4" />
                            <h4 class="text-lg font-semibold text-base-content/70 mb-2">No Virtual Airlines</h4>
                            <p class="text-sm text-base-content/50">
                                Wanna partner up?<br>
                                Learn how to <a class="underline" href="{{ route('pilots.virtual-airlines') }}">join us</a>.
                            </p>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Tours Section --}}
            <div class="lg:col-span-2 flex flex-col">
                <x-card title="Tours" subtitle="Discover Amazing Flight Routes" shadow separator class="flex flex-col flex-1">
                    @if($this->hasTours)
                        <div class="flex-1">
                            {{-- Bento Grid for Tours --}}
                            <livewire:app_component-bento-grid :images="$this->getToursData()" wire:key="desktop-tours" setup-id="{{ $bentoSetupId }}" />
                        </div>

                        {{-- Call to Action - Pushed to bottom --}}
                        <div class="text-center mt-auto pt-20">
                            <x-button class="btn btn-accent btn-lg" link="{{ $toursRepo }}" external>
                                <x-icon name="phosphor.airplane-takeoff" class="w-5 h-5" />
                                View All Tours
                            </x-button>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center flex-1">
                            <x-icon name="phosphor.airplane-takeoff" class="w-16 h-16 text-base-content/30 mb-4" />
                            <h4 class="text-lg font-semibold text-base-content/70 mb-2">No Tours Available</h4>
                            <p class="text-sm text-base-content/50">Check back soon for exciting flight adventures!</p>
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</div>