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

<section class="mx-auto px-5 md:px-15 py-20 relative bg-cover bg-center min-h-[60vh] flex items-center"
         style="background-image: url('/assets/img/american-flag.jpg');">
        
    <div class="container mx-auto px-4 relative z-10 w-full">
        
        {{-- Cards Grid --}}
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8 w-full">
            
            {{-- Air Traffic Controllers Card --}}
            <div class="relative">
                <div class="bg-base-100 backdrop-blur-sm rounded-lg p-8 border-4 border-dashed border-gray-400 hover:border-primary transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1">
                    
                    {{-- Card Header --}}
                    <div class="text-center mb-6">
                        <div class="flex justify-center items-center mb-4">
                            <x-icon name="phosphor.radio" class="w-12 h-12 text-accent" />
                        </div>
                        <h3 class="w-full text-center text-2xl font-bold text-primary mb-2">Air Traffic Controllers</h3>
                        <p class="text-base-content font-medium">Why joining as ATC?</p>
                    </div>
                    
                    {{-- Reasons List --}}
                    <div class="mb-8">
                        <ul class="space-y-3">
                            @foreach($atcReasons as $reason)
                                <li class="flex items-start">
                                    <x-icon name="phosphor.check-circle" class="w-5 h-5 text-success mr-3 mt-0.5 flex-shrink-0" />
                                    <span class="text-base-content">{{ $reason }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    {{-- Call to Action --}}
                    <div class="text-center">
                        <x-button 
                            label="Get Started" 
                            link="#" 
                            class="btn-primary px-8 py-3 font-semibold hover:scale-105 transition-transform duration-200" 
                            icon="phosphor.link" 
                        />
                    </div>
                </div>
            </div>
            
            {{-- Pilots Card --}}
            <div class="relative">
                <div class="bg-base-100 backdrop-blur-sm rounded-lg p-8 border-4 border-dashed border-gray-400 hover:border-primary transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1">
                    
                    {{-- Card Header --}}
                    <div class="text-center mb-6">
                        <div class="flex justify-center items-center mb-4">
                            <x-icon name="phosphor.paper-plane-tilt" class="w-12 h-12 text-accent" />
                        </div>
                        <h3 class="w-full text-center text-2xl font-bold text-primary mb-2">Pilots</h3>
                        <p class="text-base-content font-medium">What makes it so nice to fly on IVAO?</p>
                    </div>
                    
                    {{-- Reasons List --}}
                    <div class="mb-8">
                        <ul class="space-y-3">
                            @foreach($pilotReasons as $reason)
                                <li class="flex items-start">
                                    <x-icon name="phosphor.check-circle" class="w-5 h-5 text-success mr-3 mt-0.5 flex-shrink-0" />
                                    <span class="text-base-content">{{ $reason }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    {{-- Call to Action --}}
                    <div class="text-center">
                        <x-button 
                            label="Discover More" 
                            link="#" 
                            class="btn-primary px-8 py-3 font-semibold hover:scale-105 transition-transform duration-200" 
                            icon="phosphor.link" 
                        />
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</section>