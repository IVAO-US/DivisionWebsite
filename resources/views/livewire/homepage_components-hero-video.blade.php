<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $logoPath = './img/ivao-branding-transparent.svg';
    public string $videoPath = './assets/video/hero-background.mp4';
    public string $title = 'United States Division';
    public string $joinUrl = 'https://ivao.aero/members/person/ADJregister3.asp';
    
    public array $socialLinks = [
        ['platform' => 'facebook', 'url' => 'https://www.facebook.com/ivaousa', 'icon' => 'bi-facebook'],
        ['platform' => 'instagram', 'url' => 'https://www.instagram.com/ivaousa/', 'icon' => 'bi-instagram'],
        ['platform' => 'twitch', 'url' => 'https://www.twitch.tv/ivao_usa', 'icon' => 'bi-twitch'],
        ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@ivao_usa', 'icon' => 'bi-tiktok'],
        ['platform' => 'youtube', 'url' => 'https://www.youtube.com/@IVAO_US', 'icon' => 'bi-youtube'],
        ['platform' => 'discord', 'url' => 'https://discord.us.ivao.aero/', 'icon' => 'bi-discord'],
        ['platform' => 'email', 'url' => 'mailto:us-hq@ivao.aero', 'icon' => 'bi-envelope-at-fill']
    ];
}; ?>

<section class="hero-section relative min-h-screen flex items-center justify-center overflow-hidden">
    {{-- Section Overlay --}}
    <div class="section-overlay absolute inset-0 bg-white opacity-15"></div>

    {{-- Container --}}
    <div class="container relative z-10 d-flex justify-content-center align-items-center h-[90vh] mb-0">
        <div class="row">
            <div class="col-12 mt-auto mb-5 text-center">
                <small>
                    <object type="image/svg+xml" data="{{ $logoPath }}" class="homepage-logo w-1/2 md:w-full h-auto mx-auto block"></object>
                </small>

                <h1 class="text-white mb-5 text-4xl md:text-6xl font-bold">
                    {{ $title }}
                </h1>

                <a class="btn btn-primary bg-red-600 hover:bg-red-700 text-white px-8 py-4 text-lg font-bold rounded" 
                   href="{{ $joinUrl }}" 
                   target="_blank">
                    Join today!
                </a>
            </div>

            {{-- Social Share --}}
            <div class="social-share homepage-social-share flex justify-center items-end">
                <ul class="social-icon flex items-center justify-center gap-3">
                    @foreach($socialLinks as $social)
                        <li class="social-icon-item">
                            <a href="{{ $social['url'] }}" 
                               class="social-icon-link w-12 h-12 bg-white text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center rounded-full transition-colors duration-300"
                               target="_blank" 
                               rel="noopener noreferrer">
                                <i class="{{ $social['icon'] }} text-lg"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Video Wrap --}}
    <div class="video-wrap absolute inset-0 w-full h-full">
        <video autoplay loop muted playsinline class="custom-video w-full h-full object-cover" poster="">
            <source src="{{ $videoPath }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</section>