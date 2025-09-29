<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Volt\Component;

new 
#[Layout('components.layouts.app')]
#[Title('Virtual Airlines')]
class extends Component {
    
    /**
     * Get virtual airlines data
     * 
     * @return array
     */
    public function virtualAirlines(): array
    {
        return [
            [
                'name' => 'vFly Delta',
                'image' => '/assets/img/virtual-airlines/deltavalogo.png',
                'url' => 'https://vflydelta.com/',
            ],
            [
                'name' => 'Flight Sim Central',
                'image' => '/assets/img/virtual-airlines/fsc.png',
                'url' => 'https://www.flightsimcentral.org/',
            ],
            [
                'name' => 'Crosswind VA',
                'image' => '/assets/img/virtual-airlines/xwa.png',
                'url' => 'https://crosswind-va.com/',
            ],
            [
                'name' => 'Atlas Skyways',
                'image' => '/assets/img/virtual-airlines/atlaslogo.png',
                'url' => 'https://flyatlas-va.com/',
            ],
        ];
    }
    
}; ?>

<div>
    <x-header 
        title="Virtual Airlines" 
        size="h2" 
        subtitle="Our certified partner virtual airlines connecting flight simulation enthusiasts worldwide" 
        class="!mb-8" 
    />

    <x-card title="Certified VAs" subtitle="Our IVAO Certified Virtual Airlines" shadow separator>
        {{-- Virtual Airlines Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 md:gap-8">
            @foreach($this->virtualAirlines() as $airline)
                <a 
                    href="{{ $airline['url'] }}" 
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group block"
                >
                    <div class="relative overflow-hidden rounded-xl bg-base-200 border-2 border-base-300 p-6 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-secondary hover:-translate-y-1 h-full flex flex-col items-center justify-center min-h-[200px]">
                        
                        {{-- Image Container --}}
                        <div class="w-full h-32 flex items-center justify-center mb-4">
                            <img 
                                src="{{ $airline['image'] }}" 
                                alt="{{ $airline['name'] }}"
                                class="max-w-full max-h-full rounded-xl object-contain transition-transform duration-300 group-hover:scale-110"
                            >
                        </div>
                        
                        {{-- Airline Name --}}
                        <div class="text-center">
                            <h4 class="font-bold text-base-content group-hover:text-secondary transition-colors duration-300 whitespace-nowrap">
                                {{ $airline['name'] }}
                            </h4>
                        </div>

                        {{-- External Link Icon --}}
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <x-icon name="phosphor.arrow-square-out" class="w-5 h-5 text-secondary" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </x-card>

    {{-- Call to Action Section --}}
    <x-card title="Want to be IVAO Certified?" subtitle="Reach out and we will gladly assist you." class="mt-8" shadow separator>
        <x-alert icon="phosphor.warning-circle" class="w-full alert-warning border-warning bg-warning mb-6 ">
            <h6 class="text-warning-content mb-4">Prior to your Request</h6>
            Make sure to review IVAO Rules & Regulations about Virtual Airlines Certification: <a target="_blank" href="https://wiki.ivao.aero/en/home/ivao/regulations#flight-operations" class="font-semibold underline">visit our IVAO Wiki</a>.<br>
            The IVAO HQ Flight Operations Department also published a guide: <a target="_blank" href="https://wiki.ivao.aero/en/home/flightoperations/FAQ_VA" class="font-semibold underline">read the article</a>.
        </x-alert>

        <div class="text-center">
            <x-button 
                label="Ready? Contact Us!" 
                icon="phosphor.envelope"
                class="btn btn-accent lg:btn-lg" 
                link="mailto:us-flightops@ivao.aero"
            />
        </div>
    </x-card>
</div>