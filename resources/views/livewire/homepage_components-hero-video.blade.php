<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $logoPath = './assets/img/ivao-branding-transparent.svg';
    public string $videoPath = './assets/video/hero-background.mp4';
    public string $title = 'United States Division';
    public string $joinUrl = 'https://ivao.aero/members/person/ADJregister3.asp';
    
    public array $socialLinks = [
        ['platform' => 'facebook', 'url' => 'https://www.facebook.com/ivaousa', 'icon' => 'lucide.facebook'],
        ['platform' => 'instagram', 'url' => 'https://www.instagram.com/ivaousa/', 'icon' => 'lucide.instagram'],
        ['platform' => 'twitch', 'url' => 'https://www.twitch.tv/ivao_usa', 'icon' => 'lucide.twitch'],
        ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@ivao_usa', 'icon' => 'lucide.video'],
        ['platform' => 'youtube', 'url' => 'https://www.youtube.com/@IVAO_US', 'icon' => 'lucide.youtube'],
        ['platform' => 'discord', 'url' => 'https://discord.us.ivao.aero/', 'icon' => 'lucide.message-circle'],
        ['platform' => 'email', 'url' => 'mailto:us-hq@ivao.aero', 'icon' => 'phosphor.discord-logo-fill']
    ];
}; ?>

<div>
    <section class="hero-section relative w-full h-[calc(100vh-144px)] flex items-center justify-center overflow-hidden">
        {{-- Video Background --}}
        <div class="video-wrap absolute inset-0 w-full h-full z-0">
            <video autoplay loop muted playsinline class="custom-video w-full h-full object-cover object-center">
                <source src="{{ $videoPath }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        {{-- Dark Overlay --}}
        <div class="section-overlay absolute inset-0 bg-black/40 z-10"></div>

        {{-- Content Container --}}
        <div class="relative z-20 h-full w-full max-w-4xl mx-auto px-6 flex flex-col items-center justify-center md:justify-between text-center">
            
            <div class="flex flex-col items-center space-y-6">
                {{-- Logo --}}
                <div class="logo-container">
                    <object type="image/svg+xml" 
                            data="{{ $logoPath }}" 
                            class="homepage-logo w-80 md:w-100 lg:w-120 xl:w-160 h-auto mx-auto block">
                    </object>
                </div>

                {{-- Title --}}
                <p class="text-white text-center mb-16 h3 md:text-[2rem] lg:text-[4.00rem] xl:text-[5rem] font-bold leading-tight">
                    {{ $title }}
                </p>
            </div>

            <div class="flex flex-col items-center space-y-6 mb-16">
                {{-- Call to Action Button --}}
                <div class="cta-container">
                    <x-button 
                        link="{{ $joinUrl }}"
                        external
                        class="btn btn-accent text-white px-4 py-6 md:py-8 mb-4 md:mb-10 font-bold"
                    >
                        <span class="text-[1.00rem] md:text-[1.25rem] lg:text-[1.50rem]">Join Today!</span>
                    </x-button>
                </div>

                {{-- Social Links --}}
                {{-- Mobile --}}
                <div class="flex flex-col items-center space-y-3 md:hidden">
                    <div class="flex gap-4 justify-center">
                        @foreach(array_slice($socialLinks, 0, 4) as $social)
                            <a href="{{ $social['url'] }}" 
                            class="w-12 h-12 bg-white/90 text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
                            target="_blank" 
                            rel="noopener noreferrer"
                            aria-label="{{ ucfirst($social['platform']) }}">
                                <x-icon name="{{ $social['icon'] }}" class="w-5 h-5" />
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="flex gap-4 justify-center">
                        @foreach(array_slice($socialLinks, 4) as $social)
                            <a href="{{ $social['url'] }}" 
                            class="w-12 h-12 bg-white/90 text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
                            target="_blank" 
                            rel="noopener noreferrer"
                            aria-label="{{ ucfirst($social['platform']) }}">
                                <x-icon name="{{ $social['icon'] }}" class="w-5 h-5" />
                            </a>
                        @endforeach
                    </div>
                </div>
                
                {{-- Desktop --}}
                <div class="hidden md:flex gap-4 justify-center">
                    @foreach($socialLinks as $social)
                        <a href="{{ $social['url'] }}" 
                        class="w-12 h-12 bg-white/90 text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
                        target="_blank" 
                        rel="noopener noreferrer"
                        aria-label="{{ ucfirst($social['platform']) }}">
                            <x-icon name="{{ $social['icon'] }}" class="w-5 h-5" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>