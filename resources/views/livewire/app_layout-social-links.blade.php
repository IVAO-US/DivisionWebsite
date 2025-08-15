<?php
// resources/views/livewire/social-links.blade.php

use Livewire\Volt\Component;

new class extends Component {
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