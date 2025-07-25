<?php
// resources/views/livewire/social-links.blade.php

use Livewire\Volt\Component;

new class extends Component {
    public array $socialLinks = [
        [
            'name' => 'Facebook',
            'url' => 'https://www.facebook.com/ivaousa',
            'icon' => 'facebook',
            'color' => 'hover:bg-blue-600'
        ],
        [
            'name' => 'Instagram', 
            'url' => 'https://www.instagram.com/ivaousa/',
            'icon' => 'instagram',
            'color' => 'hover:bg-pink-600'
        ],
        [
            'name' => 'Twitch',
            'url' => 'https://www.twitch.tv/ivao_usa',
            'icon' => 'twitch',
            'color' => 'hover:bg-purple-600'
        ],
        [
            'name' => 'TikTok',
            'url' => 'https://www.tiktok.com/@ivao_usa',
            'icon' => 'video',
            'color' => 'hover:bg-black'
        ],
        [
            'name' => 'YouTube',
            'url' => 'https://www.youtube.com/@IVAO_US',
            'icon' => 'youtube',
            'color' => 'hover:bg-red-600'
        ],
        [
            'name' => 'Discord',
            'url' => 'https://discord.us.ivao.aero/',
            'icon' => 'message-circle',
            'color' => 'hover:bg-indigo-600'
        ],
        [
            'name' => 'Email',
            'url' => 'mailto:us-hq@ivao.aero',
            'icon' => 'mail',
            'color' => 'hover:bg-gray-600'
        ]
    ];
}; ?>

<div class="flex justify-center items-center">
    <div class="flex flex-wrap gap-3 justify-center">
        @foreach($socialLinks as $social)
            <a 
                href="{{ $social['url'] }}" 
                target="_blank" 
                rel="noopener noreferrer"
                class="group relative inline-flex items-center justify-center w-12 h-12 bg-white text-primary rounded-full transition-all duration-300 {{ $social['color'] }} hover:text-white transform hover:scale-110 hover:shadow-lg"
                aria-label="{{ $social['name'] }}"
            >
                <x-icon 
                    :name="'lucide.' . $social['icon']" 
                    class="w-5 h-5 transition-transform group-hover:scale-110" 
                />
                
                {{-- Tooltip --}}
                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                    {{ $social['name'] }}
                </div>
            </a>
        @endforeach
    </div>
</div>