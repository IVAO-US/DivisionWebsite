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
        <div class="relative z-20 w-full max-w-4xl mx-auto px-6 pt-16 md:pt-0 flex flex-col items-center justify-center text-center space-y-2 md:space-y-4 lg:space-y-8">
            
            {{-- Logo --}}
            <div class="logo-container">
                <object type="image/svg+xml" 
                        data="{{ $logoPath }}" 
                        class="homepage-logo w-80 md:w-120 lg:w-160 h-auto mx-auto block">
                </object>
            </div>

            {{-- Title --}}
            <h1 class="text-white text-center mb-16 !h3 md:!h4 lg:!h5 xl:!h6 font-bold leading-tight">
                {{ $title }}
            </h1>

            {{-- Call to Action Button --}}
            <div class="cta-container">
                <x-button 
                    label="Join today!" 
                    link="{{ $joinUrl }}"
                    external
                    class="bg-accent text-white px-8 py-4 text-lg font-bold rounded-lg border-none shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                />
            </div>

            {{-- Social Links --}}
            <div class="social-links flex justify-center items-center">
                <div class="flex flex-wrap gap-4 justify-center">
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