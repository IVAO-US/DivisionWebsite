<?php
// resources/views/livewire/social-links.blade.php

use Livewire\Volt\Component;

new class extends Component {
    public bool $singleLine = false; 
    
    public array $socialLinks = [
        ['platform' => 'discord', 'url' => 'https://discord.us.ivao.aero/', 'icon' => 'phosphor.discord-logo-fill'],
        ['platform' => 'youtube', 'url' => 'https://www.youtube.com/@IVAO_US', 'icon' => 'phosphor.youtube-logo-fill'],
        ['platform' => 'twitch', 'url' => 'https://www.twitch.tv/ivaousa', 'icon' => 'phosphor.twitch-logo-fill'],
        ['platform' => 'facebook', 'url' => 'https://www.facebook.com/ivaousa', 'icon' => 'phosphor.facebook-logo-fill'],
        ['platform' => 'instagram', 'url' => 'https://www.instagram.com/ivaousa/', 'icon' => 'phosphor.instagram-logo-fill'],
        ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@ivao_usa', 'icon' => 'phosphor.tiktok-logo-fill'],
        ['platform' => 'email', 'url' => 'mailto:us-hq@ivao.aero', 'icon' => 'phosphor.envelope-open-fill']
    ];

    /**
     * Get dynamic classes for mobile single line mode
     */
    public function getMobileSingleLineClasses(): string
    {
        $totalIcons = count($this->socialLinks);
        
        // Adjust container size based on screen width and number of icons
        if ($totalIcons <= 5) {
            return 'w-12 h-12'; // Standard size for 5 or fewer icons
        } elseif ($totalIcons <= 7) {
            return 'w-10 h-10 xs:w-11 xs:h-11'; // Smaller for 6-7 icons
        } else {
            return 'w-8 h-8 xs:w-9 xs:h-9'; // Even smaller for 8+ icons
        }
    }

    /**
     * Get dynamic icon size classes for mobile single line mode
     */
    public function getMobileIconSizeClasses(): string
    {
        $totalIcons = count($this->socialLinks);
        
        if ($totalIcons <= 5) {
            return 'w-5 h-5'; // Standard icon size
        } elseif ($totalIcons <= 7) {
            return 'w-4 h-4 xs:w-5 xs:h-5'; // Smaller icons for 6-7 total
        } else {
            return 'w-3 h-3 xs:w-4 xs:h-4'; // Even smaller for 8+ icons
        }
    }

    /**
     * Get gap classes based on number of icons
     */
    public function getMobileGapClasses(): string
    {
        $totalIcons = count($this->socialLinks);
        
        if ($totalIcons <= 5) {
            return 'gap-4';
        } elseif ($totalIcons <= 7) {
            return 'gap-2 xs:gap-3';
        } else {
            return 'gap-1 xs:gap-2';
        }
    }
}; ?>

<div>
    {{-- Mobile --}}
    @if($singleLine)
        {{-- Mobile Single Line Mode with Dynamic Sizing --}}
        <div class="flex {{ $this->getMobileGapClasses() }} justify-center items-center md:hidden px-2">
            @foreach($socialLinks as $social)
                <a href="{{ $social['url'] }}" 
                   class="{{ $this->getMobileSingleLineClasses() }} bg-white text-primary flex items-center justify-center rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg hover:bg-primary hover:text-white flex-shrink-0"
                   target="_blank" 
                   rel="noopener noreferrer"
                   aria-label="{{ ucfirst($social['platform']) }}">
                    <x-icon name="{{ $social['icon'] }}" class="{{ $this->getMobileIconSizeClasses() }}" />
                </a>
            @endforeach
        </div>
    @else
        {{-- Mobile Two Line Mode --}}
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
    @endif

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