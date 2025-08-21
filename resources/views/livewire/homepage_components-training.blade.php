<?php
use Livewire\Volt\Component;

new class extends Component {
    public array $atcReasons = [
        'Professional ATC training program',
        'Real-world procedures and protocols', 
        'Active community and mentorship',
        'Advanced simulation technology'
    ];
    
    public array $pilotReasons = [
        'Realistic flight experiences',
        'Professional air traffic control',
        'Global network of airports', 
        'Educational flight training'
    ];
}; ?>

<section class="relative min-h-[80vh] flex items-center justify-center bg-cover bg-center py-20">
    
    {{-- Content Container --}}
    <div class="container mx-auto px-4 relative z-10">
        
        {{-- Main Heading --}}
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                We are now boarding!
            </h2>
            <p class="text-xl text-white max-w-2xl mx-auto">
                Join our global community and discover the thrill of virtual aviation
            </p>
        </div>

        {{-- Cards Grid --}}
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-8">
            
            {{-- Air Traffic Controllers Card --}}
            <x-card 
                class="group h-full transform transition-all duration-500 hover:scale-105 hover:shadow-2xl bg-white/95 backdrop-blur-sm border-2 border-transparent hover:border-accent/50"
                shadow="shadow-xl"
            >
                {{-- Card Header --}}
                <div class="text-center mb-8">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-accent/10 rounded-full group-hover:bg-accent/20 transition-colors duration-300">
                            <x-icon name="phosphor.radio" class="w-12 h-12 text-accent" />
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-base-content mb-3">
                        Air Traffic Controllers
                    </h3>
                    <p class="text-lg text-base-content/70">
                        Why join as ATC?
                    </p>
                </div>

                {{-- Features List --}}
                <div class="space-y-4 mb-8">
                    @foreach($atcReasons as $reason)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <x-icon name="phosphor.check-circle-fill" class="w-5 h-5 text-success" />
                            </div>
                            <span class="text-base-content/80 text-lg leading-relaxed">{{ $reason }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- CTA Button --}}
                <div class="text-center">
                    <x-button 
                        label="Get Started" 
                        icon="phosphor.airplane-takeoff"
                        class="btn-accent btn-lg text-white font-semibold px-8 py-3 transform transition-all duration-300 hover:scale-105"
                        link="ticket.html"
                    />
                </div>
            </x-card>

            {{-- Pilots Card --}}
            <x-card 
                class="group h-full transform transition-all duration-500 hover:scale-105 hover:shadow-2xl bg-white/95 backdrop-blur-sm border-2 border-transparent hover:border-primary/50"
                shadow="shadow-xl"
            >
                {{-- Card Header --}}
                <div class="text-center mb-8">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-primary/10 rounded-full group-hover:bg-primary/20 transition-colors duration-300">
                            <x-icon name="phosphor.airplane" class="w-12 h-12 text-primary" />
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-base-content mb-3">
                        Pilots
                    </h3>
                    <p class="text-lg text-base-content/70">
                        What makes it so nice to fly on IVAO?
                    </p>
                </div>

                {{-- Features List --}}
                <div class="space-y-4 mb-8">
                    @foreach($pilotReasons as $reason)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <x-icon name="phosphor.check-circle-fill" class="w-5 h-5 text-success" />
                            </div>
                            <span class="text-base-content/80 text-lg leading-relaxed">{{ $reason }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- CTA Button --}}
                <div class="text-center">
                    <x-button 
                        label="Discover More" 
                        icon="phosphor.compass"
                        class="btn-primary btn-lg text-white font-semibold px-8 py-3 transform transition-all duration-300 hover:scale-105"
                        link="ticket.html"
                    />
                </div>
            </x-card>

        </div>

        {{-- Additional Info Section --}}
        <div class="text-center mt-16">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                <h4 class="text-2xl font-bold text-white mb-4">
                    Ready to join thousands of aviation enthusiasts?
                </h4>
                <p class="text-white/90 text-lg mb-6 max-w-3xl mx-auto">
                    Experience realistic aviation simulation with professional training, 
                    active community support, and cutting-edge technology - all completely free.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <x-button 
                        label="Join Community" 
                        icon="phosphor.users"
                        class="btn-secondary btn-lg text-white font-semibold px-8"
                        link="#"
                    />
                    <x-button 
                        label="Learn More" 
                        icon="phosphor.book-open"
                        class="btn-outline btn-lg border-white text-white hover:bg-white hover:text-base-content font-semibold px-8"
                        link="#"
                    />
                </div>
            </div>
        </div>

    </div>
</section>