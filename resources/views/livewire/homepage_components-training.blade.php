<?php
use Livewire\Volt\Component;

new class extends Component {
    public array $atcReasons = [
        'Professional ATC training program',
        'Real-world procedures and protocols', 
        'Active community and mentorship',
        'Advanced software technology'
    ];
    
    public array $pilotReasons = [
        'Realistic flight experiences',
        'Real-world procedures and regulations',
        'Teaching and mentoring', 
        'Reality-based exams'
    ];
}; ?>

<section class="relative flex items-center justify-center py-10 bg-base-200">
    
    {{-- Content Container --}}
    <div class="container mx-auto px-4 relative z-10">
        
        {{-- Cards Grid --}}
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-8">
            
            {{-- Air Traffic Controllers Card --}}
            <x-card 
                class="group h-full transform transition-all duration-500 hover:scale-105 hover:shadow-2xl bg-base-100 backdrop-blur-sm border-2 border-transparent hover:border-accent/50"
                shadow="shadow-xl"
            >
                {{-- Card Header --}}
                <div class="text-center mb-6">
                    <div class="flex justify-center items-center mb-4">
                        <x-icon name="phosphor.radio" class="w-12 h-12 text-accent" />
                    </div>
                    <h3 class="w-full text-center text-2xl font-bold text-accent mb-2">Air Traffic Controllers</h3>
                </div>

                {{-- Features List --}}
                <div class="space-y-4 mb-8">
                    @foreach($atcReasons as $reason)
                        <div class="flex justify-center items-center space-x-3">
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
                        link="https://wiki.us.ivao.aero/en/atc/training"
                    />
                </div>
            </x-card>

            {{-- Pilots Card --}}
            <x-card 
                class="group h-full transform transition-all duration-500 hover:scale-105 hover:shadow-2xl bg-base-100 backdrop-blur-sm border-2 border-transparent hover:border-primary/50"
                shadow="shadow-xl"
            >

                {{-- Card Header --}}
                <div class="text-center mb-6">
                    <div class="flex justify-center items-center mb-4">
                        <x-icon name="phosphor.paper-plane-tilt" class="w-12 h-12 text-primary" />
                    </div>
                    <h3 class="w-full text-center text-2xl font-bold text-primary mb-2">Pilots</h3>
                </div>

                {{-- Features List --}}
                <div class="space-y-4 mb-8">
                    @foreach($pilotReasons as $reason)
                        <div class="flex items-center justify-center space-x-3">
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
                        link="https://wiki.us.ivao.aero/en/pilots/training"
                    />
                </div>
            </x-card>

        </div>

        {{-- Additional Info Section --}}
        <div class="text-center mt-16">
            <div class="max-w-7xl mx-auto">
                <x-card class="bg-base-100 backdrop-blur-sm border-none" shadow="shadow-lg">
                    <h4 class="text-2xl font-bold text-base-content text-center mb-4">
                        Ready to learn all there is to know about virtual aviation?
                    </h4>
                    <p class="text-base-content/90 text-lg mb-6 max-w-3xl mx-auto">
                        Become proficient <b>as real as it gets</b> in virtual air traffic control and flying.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-10 justify-center">
                        <x-button 
                            label="Request Training" 
                            icon="phosphor.users"
                            class="btn-accent btn-lg font-semibold px-8"
                            link="https://us.ivao.aero/training-request/"
                        />
                        <x-button 
                            label="Visit our Wiki" 
                            icon="phosphor.book-open"
                            class="btn-primary btn-lg font-semibold px-8"
                            link="https://wiki.us.ivao.aero/"
                        />
                    </div>
                </x-card>
            </div>
        </div>

    </div>
</section>