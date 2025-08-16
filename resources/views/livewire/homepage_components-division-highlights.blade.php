<?php
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public string $activeTab = 'calendar';
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }
}; ?>

<div class="w-full">
    {{-- Mobile Tabs (below lg) --}}
    <div class="lg:hidden">
        <h3 class="!text-center font-bold text-secondary mb-10">Division Highlights</h3>
        <x-tabs wire:model="activeTab" class="w-full">
            <x-tab name="calendar" label="Calendar" icon="phosphor.calendar">
                <livewire:homepage_components-division-calendar :compact="true" wire:key="mobile-calendar" />
            </x-tab>

            <x-tab name="events" label="Events" icon="phosphor.calendar-star">
                <livewire:homepage_components-events-carousel wire:key="mobile-carousel" />
            </x-tab>
        </x-tabs>
    </div>

    {{-- Desktop Layout (lg and above) --}}
    <div class="hidden lg:block">
        <div class="grid lg:grid-cols-3 gap-8 h-fit">
            {{-- Calendar Section --}}
            <div class="lg:col-span-2 flex flex-col">
                <div class="text-center mb-4">
                    <h3 class="!text-center font-bold text-secondary">Division Calendar</h3>
                    <p class="text-base-content/66 text-lg mt-1 ">Scheduled Events & Training Sessions</p>
                </div>
                <div class="flex-1">
                    <livewire:homepage_components-division-calendar :compact="false" wire:key="desktop-calendar" />
                </div>
            </div>

            {{-- Events Section --}}
            <div class="lg:col-span-1 flex flex-col">
                <div class="text-center mb-4">
                    <h3 class="!text-center font-bold text-secondary">Upcoming Events</h3>
                    <p class="text-base-content/66 text-lg mt-1 ">Fly & Control Together</p>
                </div>
                <div class="flex-1">
                    <livewire:homepage_components-events-carousel wire:key="desktop-carousel" />
                </div>
            </div>
        </div>
    </div>
</div>