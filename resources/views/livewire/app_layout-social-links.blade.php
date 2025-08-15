<?php
// resources/views/livewire/social-links.blade.php

use Livewire\Volt\Component;

new class extends Component {
    public array $socialLinks = [
        ['platform' => 'discord', 'url' => 'https://discord.us.ivao.aero/', 'icon' => 'phosphor.discord-logo-fill'],
        ['platform' => 'youtube', 'url' => 'https://www.youtube.com/@IVAO_US', 'icon' => 'phosphor.youtube-logo-fill'],
        ['platform' => 'twitch', 'url' => 'https://www.twitch.tv/ivao_usa', 'icon' => 'phosphor.twitch-logo-fill'],
        ['platform' => 'facebook', 'url' => 'https://www.facebook.com/ivaousa', 'icon' => 'phosphor.facebook-logo-fill'],
        ['platform' => 'instagram', 'url' => 'https://www.instagram.com/ivaousa/', 'icon' => 'phosphor.instagram-logo-fill'],
        ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@ivao_usa', 'icon' => 'phosphor.tiktok-logo-fill'],
        ['platform' => 'email', 'url' => 'mailto:us-hq@ivao.aero', 'icon' => 'phosphor.envelope-open-fill']
    ];
}; ?>

<div>
    {{-- Mobile --}}
    <div class="flex flex-col items-center space-y-3 md:hidden">
        <div class="flex gap-4 justify-center">
            @foreach(array_slice($socialLinks, 0, 4) as $social)
                <a href="{{ $social['url'] }}" 
                class="w-12 h-12 bg-white text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
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
                class="w-12 h-12 bg-white text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
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
            class="w-12 h-12 bg-white text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white"
            target="_blank" 
            rel="noopener noreferrer"
            aria-label="{{ ucfirst($social['platform']) }}">
                <x-icon name="{{ $social['icon'] }}" class="w-5 h-5" />
            </a>
        @endforeach
    </div>
</div>