<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $activeTab = 'tours';
    public string $toursRepo = 'https://tours.th.ivao.aero/index.php?div=US';
    
    // Tours source images with simplified priority system
    public array $toursImages = [
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'href' => '#',
            'title' => 'Atlanta RFE Event',
            'description' => 'Real Flight Event in Atlanta',
            'priority' => true // Optionnal
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'href' => '#',
            'title' => 'European Tour',
            'description' => 'Explore European airports'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'href' => '#',
            'title' => 'Major RFE Atlanta',
            'description' => 'Large scale aviation event',
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'href' => '#',
            'title' => 'EBUSCA Airport',
            'description' => 'Scenic European destination'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png', 
            'href' => '#',
            'title' => 'Premium Atlanta Event',
            'description' => 'High-profile aviation gathering'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/ebusca.jpg',
            'href' => '#',
            'title' => 'Quick European Hop',
            'description' => 'Short distance tour'
        ],
        [
            'url' => 'https://assets.us.ivao.aero/uploads/AtlantaRFE1200by800.png',
            'href' => '#',
            'title' => 'Regional Atlanta',
            'description' => 'Regional aviation focus'
        ]
    ];

    // VA data
    public array $certifiedVAs = [
        [
            'title' => 'Atlas Skyways',
            'date' => 'AXW | Bases: KCVG - KIND',
            'description' => 'Lorem ipsum Atlas Skyways',
            'image' => 'https://api.ivao.aero/v2/virtualAirlines/905/onlineLogo?apiKey=34a24206c5dca07d17a1',
            'link' => 'https://flyatlas-va.com/'
        ],
        [
            'title' => 'Delta Virtual',
            'date' => 'DAL | Bases: KATL - KMSP - KJFK',
            'description' => 'Lorem ipsum Delta Virtual',
            'image' => 'https://api.ivao.aero/v2/virtualAirlines/829/onlineLogo?apiKey=34a24206c5dca07d17a1',
            'link' => 'https://vflydelta.com/'
        ],
        [
            'title' => 'Crosswind Airways',
            'date' => 'XWA | Bases: KJFK',
            'description' => 'Lorem ipsum Crosswind Airways',
            'image' => 'https://api.ivao.aero/v2/virtualAirlines/952/mainLogo?apiKey=34a24206c5dca07d17a1',
            'link' => 'https://crosswind-va.com/'
        ],
        [
            'title' => 'Flight Sim Central',
            'date' => 'XFS | Bases: KFDW - KOKC',
            'description' => 'Lorem ipsum Flight Sim Central',
            'image' => 'https://api.ivao.aero/v2/virtualAirlines/968/mainLogo?apiKey=34a24206c5dca07d17a1',
            'link' => 'https://www.flightsimcentral.org/'
        ]
    ];
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Get tours data for bento grid component
     */
    public function getToursData(): array
    {
        return $this->toursImages;
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
                        <div class="flex-1">
                            {{-- Bento Grid for Tours --}}
                            <livewire:app_component-bento-grid :images="$this->getToursData()" setup-id="5B9B48E8344A" wire:key="mobile-tours" />
                        </div>

                        {{-- Call to Action - Pushed to bottom --}}
                        <div class="text-center mt-auto pt-10">
                            <x-button class="btn btn-accent btn-lg" link="{{ $toursRepo }}" external>
                                <x-icon name="phosphor.airplane-takeoff" class="w-5 h-5" />
                                View All Tours
                            </x-button>
                        </div>
                    </x-card>
                </x-tab>

                <x-tab name="virtual-airlines" label="VAs" icon="phosphor.buildings">
                    {{-- Virtual Airlines Carousel --}}
                    <livewire:app_component-carousel 
                        :items="$certifiedVAs"
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
                    {{-- Virtual Airlines Carousel --}}
                    <livewire:app_component-carousel 
                        :items="$certifiedVAs"
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
                </x-card>
            </div>

            {{-- Tours Section --}}
            <div class="lg:col-span-2 flex flex-col">
                <x-card title="Tours" subtitle="Discover Amazing Flight Routes" shadow separator class="flex flex-col flex-1">
                    <div class="flex-1">
                        {{-- Bento Grid for Tours --}}
                        <livewire:app_component-bento-grid :images="$this->getToursData()" wire:key="desktop-tours" setup-id="5B9B48E8344A" />
                    </div>

                    {{-- Call to Action - Pushed to bottom --}}
                    <div class="text-center mt-auto pt-20">
                        <x-button class="btn btn-accent btn-lg" link="{{ $toursRepo }}" external>
                            <x-icon name="phosphor.airplane-takeoff" class="w-5 h-5" />
                            View All Tours
                        </x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</div>