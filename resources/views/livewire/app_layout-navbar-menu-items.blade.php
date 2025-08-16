<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    /* Because of the way Blade handles component, we need to report the x-menu classes onto the root <div> */
    public string $className = ''; /* :className */
    
    /* Menu structure with submenus - simplified version */
    public array $menuItems = [];
    
    /* Touch device detection and submenu state management */
    public bool $isTouchDevice = false;
    public array $openSubmenus = [];
    
    public function mount(): void
    {
        $this->menuItems = [
            [
                'title' => 'Home',
                'route' => 'home',
                'exact' => true
            ],
            [
                'title' => 'Division',
                'route' => null,
                'submenus' => [
                    ['title' => 'Our History', 'link' => '#'],
                    ['title' => 'Staff', 'link' => '#'],
                    ['title' => 'LiveTrack', 'link' => '#'],
                    ['title' => 'Division Transfer', 'link' => '#'],
                    ['title' => 'Rating Transfer', 'link' => '#'],
                ]
            ],
            [
                'title' => 'Training',
                'route' => null,
                'submenus' => [
                    ['title' => 'Moodle', 'link' => '#'],
                    ['title' => 'Training Request', 'link' => '#'],
                    ['title' => 'Exam', 'link' => '#'],
                    ['title' => 'Wiki', 'link' => '#'],
                    ['title' => 'Guest Controller Approval', 'link' => '#'],
                ]
            ],
            [
                'title' => 'ATCs',
                'route' => null,
                'submenus' => [
                    ['title' => 'Become an ATC', 'link' => '#'],
                    ['title' => 'Software', 'link' => '#'],
                    ['title' => 'Scheduling', 'link' => '#'],
                    ['title' => 'Facility SOPs', 'link' => '#'],
                    ['title' => 'Facility Ratings', 'link' => '#'],
                ]
            ],
            [
                'title' => 'Pilots',
                'route' => null,
                'submenus' => [
                    ['title' => 'Become a Pilot', 'link' => '#'],
                    ['title' => 'Software', 'link' => '#'],
                    ['title' => 'Tracker', 'link' => '#'],
                    ['title' => 'Tours', 'link' => '#'],
                    ['title' => 'Virtual Airlines', 'link' => '#'],
                ]
            ],
            [
                'title' => 'Community',
                'route' => null,
                'submenus' => [
                    ['title' => 'Webeye', 'link' => '#'],
                    ['title' => 'Awards', 'link' => '#'],
                    ['title' => 'Support', 'link' => '#'],
                    ['title' => 'Discord', 'link' => '#'],
                    ['title' => 'Forum', 'link' => '#'],
                ]
            ]
        ];
    }
    
    /**
     * Toggle submenu visibility for touch devices
     */
    public function toggleSubmenu(int $menuIndex): void
    {
        if (in_array($menuIndex, $this->openSubmenus)) {
            $this->openSubmenus = array_filter($this->openSubmenus, fn($index) => $index !== $menuIndex);
        } else {
            // Close other submenus and open the clicked one
            $this->openSubmenus = [$menuIndex];
        }
    }
    
    /**
     * Close all submenus
     */
    public function closeAllSubmenus(): void
    {
        $this->openSubmenus = [];
    }
    
    /**
     * Check if a submenu is open
     */
    public function isSubmenuOpen(int $menuIndex): bool
    {
        return in_array($menuIndex, $this->openSubmenus);
    }
    
    /**
     * Handle touch device detection from JavaScript
     */
    public function setTouchDevice(bool $isTouchDevice): void
    {
        $this->isTouchDevice = $isTouchDevice;
    }
    
    /**
     * Handle menu item click for touch devices
     */
    public function handleMenuClick(int $menuIndex): void
    {
        // For touch devices, always toggle submenu
        if ($this->isTouchDevice) {
            $this->toggleSubmenu($menuIndex);
        }
    }
};
?>

<div class="{{ $this->className }}" 
     x-data="{ 
        init() {
            const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            $wire.setTouchDevice(isTouchDevice);
        }
     }"
     @click.away="$wire.closeAllSubmenus()">
     
    @foreach($this->menuItems as $index => $item)
        @if(empty($item['submenus']))
            {{-- Simple menu item without submenu --}}
            @if($item['exact'] ?? false)
                <x-menu-item 
                    title="{{ $item['title'] }}" 
                    link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                    class="btn-outline navbar-item-custom"
                    exact
                />
            @else
                <x-menu-item 
                    title="{{ $item['title'] }}" 
                    link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                    class="btn-outline navbar-item-custom"
                />
            @endif
        @else
            {{-- Desktop: Menu item with hover/click submenu --}}
            <div class="relative hidden lg:block {{ !$this->isTouchDevice ? 'group' : '' }}">
                
                {{-- Menu item wrapper for touch handling --}}
                @if($this->isTouchDevice && !$item['route'])
                    {{-- Touch device without route: Use non-link element --}}
                    <div wire:click="handleMenuClick({{ $index }})" class="cursor-pointer">
                        @if($item['exact'] ?? false)
                            <div class="btn btn-outline navbar-item-custom flex items-center justify-between">
                                <span>{{ $item['title'] }}</span>
                            </div>
                        @else
                            <div class="btn btn-outline navbar-item-custom flex items-center justify-between">
                                <span>{{ $item['title'] }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Normal menu item with link --}}
                    <div @if($this->isTouchDevice && $item['route']) wire:click="handleMenuClick({{ $index }})" @endif>
                        @if($item['exact'] ?? false)
                            <x-menu-item 
                                title="{{ $item['title'] }}" 
                                link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                                class="btn-outline navbar-item-custom {{ !$this->isTouchDevice ? 'group-hover:bg-primary/10' : '' }}"
                                exact="{{ $item['route'] ? 'true' : 'false' }}"
                            >
                                <x-slot:actions>
                                    <x-icon name="phosphor.caret-down" 
                                            class="w-4 h-4 transition-transform duration-200 {{ $this->isSubmenuOpen($index) ? 'rotate-180' : '' }} {{ !$this->isTouchDevice ? 'group-hover:rotate-180' : '' }}" />
                                </x-slot:actions>
                            </x-menu-item>
                        @else
                            <x-menu-item 
                                title="{{ $item['title'] }}" 
                                link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                                class="btn-outline navbar-item-custom {{ !$this->isTouchDevice ? 'group-hover:bg-primary/10' : '' }}"
                            >
                                <x-slot:actions>
                                    <x-icon name="phosphor.caret-down" 
                                            class="w-4 h-4 transition-transform duration-200 {{ $this->isSubmenuOpen($index) ? 'rotate-180' : '' }} {{ !$this->isTouchDevice ? 'group-hover:rotate-180' : '' }}" />
                                </x-slot:actions>
                            </x-menu-item>
                        @endif
                    </div>
                @endif
                
                {{-- Submenu dropdown for touch devices --}}
                @if($this->isTouchDevice && $this->isSubmenuOpen($index))
                    <div class="absolute top-full left-0 w-64 bg-primary shadow-xl rounded-lg z-50
                               opacity-100 visible translate-y-0 transition-all duration-200 transform
                               before:content-[''] before:absolute before:-top-2 before:left-0 before:right-0 before:h-2">
                        <div class="mt-[10px] py-2">
                            @foreach($item['submenus'] as $submenu)
                                <a href="{{ $submenu['link'] }}" 
                                   class="block px-4 py-2 text-base xl:text-lg text-primary-content hover:bg-secondary transition-colors duration-150"
                                   wire:click="closeAllSubmenus()">
                                    {{ $submenu['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                {{-- Submenu dropdown for non-touch devices (hover) --}}
                @if(!$this->isTouchDevice)
                    <div class="absolute top-full left-0 w-64 bg-primary shadow-xl rounded-lg 
                               opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                               transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50
                               before:content-[''] before:absolute before:-top-2 before:left-0 before:right-0 before:h-2">
                        <div class="mt-[10px] py-2">
                            @foreach($item['submenus'] as $submenu)
                                <a href="{{ $submenu['link'] }}" 
                                   class="block px-4 py-2 text-base xl:text-lg text-primary-content hover:bg-secondary transition-colors duration-150">
                                    {{ $submenu['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Mobile: Expandable menu section --}}
            <div class="lg:hidden">              
                <x-menu-sub title="{{ $item['title'] }}">
                    @foreach($item['submenus'] as $submenu)
                        <x-menu-item 
                            title="{{ $submenu['title'] }}" 
                            link="{{ $submenu['link'] }}" 
                            class="text-lg pl-6"
                        />
                    @endforeach
                </x-menu-sub>
            </div>
        @endif
    @endforeach

    {{-- User details on mobile --}}
    <div class="lg:hidden">
        <x-menu-separator class="!border-white" />        
        <livewire:app_layout-auth-button />
    </div>
</div>