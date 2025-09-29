<?php
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public string $activeTab = 'calendar';
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    // Events data
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
}; ?>

<div class="w-full">
    {{-- Mobile Tabs (below lg) --}}
    <div class="lg:hidden">
        <x-card class="shadow-lg">
            <h3 class="!text-center font-bold text-primary !mb-5">Division Highlights</h3>
            <div class="justify-center">
                <x-tabs wire:model="activeTab"
                        class="w-full"
                        label-div-class="bg-base-100 !p-3 !mb-4 rounded-lg font-semibold whitespace-nowrap overflow-x-auto w-fit mx-auto" 
                        active-class="bg-primary p-3 rounded-lg !text-white font-semibold" 
                        label-class="p-3 font-semibold" 
                        >
                    <x-tab name="calendar" label="Calendar" icon="phosphor.calendar">
                        <livewire:homepage_components-division-calendar :display-weekly="true" use-today-btn="false" wire:key="mobile-calendar" />
                    </x-tab>

                    <x-tab name="events" label="Events" icon="phosphor.calendar-star">
                        {{-- Events Carousel Component --}}
                        <livewire:app_component-carousel 
                            :items="$upcomingEvents"
                            image-key="image"
                            title-key="title"
                            subtitle-key="date"
                            description-key="description"
                            link-key="link"
                            link-label="More information"
                            link-icon="phosphor.link"
                            :auto-advance-interval="4"
                            :interaction-delay="6"
                            wire:key="events-mobile-carousel" 
                            banner-height="h-96 md:h-134" />
                    </x-tab>
                </x-tabs>
            </div>
        </x-card>
    </div>

    {{-- Desktop Layout (lg and above) --}}
    <div class="hidden lg:block">
        <div class="grid lg:grid-cols-3 gap-8 h-fit">
            {{-- Calendar Section --}}
            <div class="lg:col-span-2">
                <x-card title="Division Calendar" subtitle="Scheduled Events & Training Sessions" class="h-full shadow-lg">                    
                    <div class="flex-1">
                        <livewire:homepage_components-division-calendar :display-weekly="false" use-today-btn="false" wire:key="desktop-calendar" />
                    </div>
                </x-card>
            </div>

            {{-- Events Section --}}
            <div class="lg:col-span-1">
                <x-card title="Upcoming Events" subtitle="Fly & Control Together" class="h-full shadow-lg">
                    {{-- Events Carousel Component --}}
                    <livewire:app_component-carousel 
                        :items="$upcomingEvents"
                        image-key="image"
                        title-key="title"
                        subtitle-key="date"
                        description-key="description"
                        link-key="link"
                        link-label="More information"
                        link-icon="phosphor.link"
                        :auto-advance-interval="4"
                        :interaction-delay="6"
                        wire:key="events-desktop-carousel"
                        banner-height="lg:h-52 xl:h-124" />
                </x-card>
            </div>
        </div>
    </div>
</div>