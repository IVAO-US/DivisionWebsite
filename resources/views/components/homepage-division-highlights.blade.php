<?php
use Livewire\Component;
use App\Models\DivisionSession;
use App\Enums\SessionType;
use App\Services\RecurringEventService;

new class extends Component {
    public string $activeTab = 'calendar';
    
    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Load upcoming events from database
     */
    public function with(): array
    {
        // Fetch upcoming events of type 'event' from the database
        $sessions = DivisionSession::upcoming()
            ->ofType(SessionType::EVENT)
            ->limit(10)
            ->get();

        // Transform database records to carousel format
        $upcomingEvents = $sessions->map(function ($session) {
            return [
                'title' => $session->title,
                'date' => $session->date->format('l, F jS'),
                'description' => $session->formatted_description ?? 'Join us for this exciting moment!',
                'image' => $session->illustration ,
                'link' => 'https://forum.ivao.aero/forums/events.1457/'
            ];
        })->toArray();
        
        // Add the next recurring events to the carousel
        $nextOnlineDay = RecurringEventService::getNextOnlineDay();
        $nextSpecOpsDay = RecurringEventService::getNextSpecOpsOnlineDay();

        // Collect all next recurring events
        $nextRecurringEvents = collect([
            $nextOnlineDay,
            $nextSpecOpsDay
        ])->filter()->sortBy('date'); // Filter null values and sort by date

        // Add recurring events to the carousel
        foreach ($nextRecurringEvents->reverse() as $recurringEvent) {
            array_unshift($upcomingEvents, [
                'title' => $recurringEvent['title'],
                'date' => $recurringEvent['date']->format('l, F jS'),
                'description' => $recurringEvent['description'],
                'image' => $recurringEvent['illustration'],
                'link' => 'https://forum.ivao.aero/forums/events.1457/'
            ]);
        }

        return [
            'upcomingEvents' => $upcomingEvents,
            'hasEvents' => count($upcomingEvents) > 0
        ];
    }
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
                        <livewire:homepage-division-calendar :display-weekly="true" use-today-btn="false" wire:key="mobile-calendar" />
                    </x-tab>

                    @if($hasEvents)
                        <x-tab name="events" label="Events" icon="phosphor.calendar-star">
                            {{-- Events Carousel Component --}}
                            <livewire:carousel 
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
                    @endif
                </x-tabs>
            </div>
        </x-card>
    </div>

    {{-- Desktop Layout (lg and above) --}}
    <div class="hidden lg:block">
        <div class="grid gap-8 h-fit {{ $hasEvents ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }}">
            {{-- Calendar Section --}}
            <div class="{{ $hasEvents ? 'lg:col-span-2' : 'lg:col-span-1' }}">
                <x-card title="Division Calendar" subtitle="Scheduled Events & Training Sessions" class="h-full shadow-lg">                    
                    <div class="flex-1">
                        <livewire:homepage-division-calendar :display-weekly="false" use-today-btn="false" wire:key="desktop-calendar" />
                    </div>
                </x-card>
            </div>

            {{-- Events Section - Only shown if there are events --}}
            @if($hasEvents)
                <div class="lg:col-span-1">
                    <x-card title="Upcoming Events" subtitle="Fly & Control Together" class="h-full shadow-lg">
                        {{-- Events Carousel Component --}}
                        <livewire:carousel 
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
            @endif
        </div>
    </div>
</div>