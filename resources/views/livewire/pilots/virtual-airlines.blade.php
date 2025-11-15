<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use App\Models\VirtualAirline;

use Mary\Traits\Toast;
use App\Traits\HasSEO;

new 
#[Layout('components.layouts.app')]
class extends Component {
    use Toast, HasSEO;

    public function mount(): void
	{
		$this->setSEOWithBreadcrumbs(
			title: 'Virtual Airlines',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);
	}
    
    /**
     * Get virtual airlines data from database
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    #[Computed]
    public function virtualAirlines()
    {
        return VirtualAirline::all();
    }

    /**
     * Check if there are any virtual airlines
     * 
     * @return bool
     */
    #[Computed]
    public function hasVirtualAirlines(): bool
    {
        return $this->virtualAirlines->count() > 0;
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
        @if($this->hasVirtualAirlines)
            {{-- Virtual Airlines Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 md:gap-8">
                @foreach($this->virtualAirlines as $airline)
                    <a 
                        href="{{ $airline->link }}" 
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group block"
                    >
                        <div class="relative overflow-hidden rounded-xl bg-base-200 border-2 border-base-300 p-6 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-secondary hover:-translate-y-1 h-full flex flex-col items-center justify-center min-h-[200px]">
                            
                            {{-- Image Container --}}
                            <div class="w-full h-32 flex items-center justify-center mb-4">
                                <img 
                                    src="{{ $airline->banner }}" 
                                    alt="{{ $airline->name }}"
                                    class="max-w-full max-h-full rounded-xl object-contain transition-transform duration-300 group-hover:scale-110"
                                >
                            </div>
                            
                            {{-- Airline Name --}}
                            <div class="text-center">
                                <h4 class="font-bold text-base-content group-hover:text-secondary transition-colors duration-300 whitespace-nowrap">
                                    {{ $airline->name }}
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
        @else
            {{-- Empty State Fallback --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <x-icon name="phosphor.buildings" class="w-20 h-20 text-base-content/30 mb-6" />
                <h4 class="text-2xl font-semibold text-base-content/70 mb-3">No Virtual Airlines</h4>
                <p class="text-base text-base-content/50 max-w-md mb-6">
                    We're currently working on establishing partnerships with virtual airlines. Check back soon!
                </p>
                <div class="text-sm text-base-content/40">
                    Want to partner with us? <b>Learn more below</b>
                </div>
            </div>
        @endif
    </x-card>

    {{-- Call to Action Section --}}
    <x-card title="Want to be IVAO Certified?" subtitle="Reach out and we will gladly assist you." class="mt-8" shadow separator id="certification">
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