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

<section class="about-section section-padding py-16 bg-cover bg-center relative" 
         style="background-image: url('./assets/img/american-flag.jpg'); background-color: #0059C9; background-position-y: 60%;">
    
    {{-- Background overlay --}}
    <div class="absolute inset-0 bg-blue-900 bg-opacity-50"></div>
    
    <div class="container relative z-10">
        <div class="row choose-us bg-white rounded-lg p-8 opacity-85">
            
            <div class="col-lg-8 col-12 mx-auto">
                <h2 class="text-center mb-4 text-3xl font-bold">We are now boarding!</h2>
            </div>

            {{-- Air Traffic Controllers Card --}}
            <div class="col-lg-6 col-12">
                <div class="pricing-thumb">
                    <div class="d-flex">
                        <div class="text-center w-full">
                            <h3 class="text-2xl font-bold mb-2">
                                <i class="bi-radar text-red-600 mr-2"></i> 
                                Air Traffic Controllers
                            </h3>
                            <p class="ml-3 text-gray-600">Why joining as ATC?</p>
                        </div>
                    </div>

                    <ul class="pricing-list mt-3 pl-5 space-y-2">
                        @foreach($atcReasons as $reason)
                            <li class="pricing-list-item">{{ $reason }}</li>
                        @endforeach
                    </ul>

                    <div class="text-center">
                        <a class="link-fx-1 inline-flex items-center mt-4 text-red-600 hover:text-red-700 font-semibold" 
                           href="ticket.html">
                            <span>Get Started</span>
                            <svg class="icon w-4 h-4 ml-2" viewBox="0 0 32 32" aria-hidden="true">
                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="16" cy="16" r="15.5"></circle>
                                    <line x1="10" y1="18" x2="16" y2="12"></line>
                                    <line x1="16" y1="12" x2="22" y2="18"></line>
                                </g>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Pilots Card --}}
            <div class="col-lg-6 col-12 mt-4 mt-lg-0">
                <div class="pricing-thumb">
                    <div class="d-flex">
                        <div class="text-center w-full">
                            <h3 class="text-2xl font-bold mb-2">
                                <i class="bi-airplane-fill text-red-600 mr-2"></i> 
                                Pilots
                            </h3>
                            <p class="ml-3 text-gray-600">What makes it so nice to fly on IVAO?</p>
                        </div>
                    </div>

                    <ul class="pricing-list mt-3 pl-5 space-y-2">
                        @foreach($pilotReasons as $reason)
                            <li class="pricing-list-item">{{ $reason }}</li>
                        @endforeach
                    </ul>

                    <div class="text-center">
                        <a class="link-fx-1 inline-flex items-center mt-4 text-red-600 hover:text-red-700 font-semibold" 
                           href="ticket.html">
                            <span>Discover More</span>
                            <svg class="icon w-4 h-4 ml-2" viewBox="0 0 32 32" aria-hidden="true">
                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="16" cy="16" r="15.5"></circle>
                                    <line x1="10" y1="18" x2="16" y2="12"></line>
                                    <line x1="16" y1="12" x2="22" y2="18"></line>
                                </g>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>