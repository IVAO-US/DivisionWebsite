<?php

/**
 * Navbar Menu Items Component
 * 
 * A responsive navigation menu component with support for nested submenus,
 * external link detection, and touch device optimization.
 * 
 * Features:
 * - Automatic external link detection and styling
 * - Touch-friendly submenu interactions
 * - Responsive design (desktop hover, mobile expand)
 * - Security attributes for external links (target="_blank", rel="noopener noreferrer")
 * - Optional external link icons
 * 
 * Usage:
 * <livewire:app_layout-navbar-menu-items :className="menu-class" :showLinkIcons="true" />
 * 
 * Menu Structure:
 * - Use 'route' => 'route.name' for internal Laravel routes
 * - Use 'link' => 'https://...' for external links
 * 
 * Example menu item structure:
 * [
 *     'title' => 'Home',
 *     'route' => 'home',           // Internal route
 *     'exact' => true              // Optional: exact route matching
 * ],
 * [
 *     'title' => 'External',
 *     'link' => 'https://example.com'  // External link
 * ],
 * [
 *     'title' => 'Services',
 *     'submenus' => [
 *         ['title' => 'Internal Service', 'route' => 'services.internal'],
 *         ['title' => 'External Service', 'link' => 'https://external.com']
 *     ]
 * ]
 * 
 * @property string $className - CSS classes to apply to the root container
 * @property bool $showLinkIcons - Whether to show external link icons
 * @property array $menuItems - Menu structure array
 * @property bool $isTouchDevice - Touch device detection state
 * @property array $openSubmenus - Currently open submenus for touch devices
 */

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    /* Because of the way Blade handles component, we need to report the x-menu classes onto the root <div> */
    public string $className = ''; /* :className */
    
    /* Whether to show external link icons */
    public bool $showLinkIcons = true; /* :showLinkIcons */
    
    /* Menu structure with submenus */
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
                'submenus' => [
                    ['title' => 'Our History', 'route' => 'division.our-history'],
                    ['title' => 'Staff', 'link' => 'https://www.ivao.aero/staff/division.asp?Id=US'],
                    ['title' => 'LiveTrack', 'link' => 'https://livetrack.us.ivao.aero/'],
                    ['title' => 'Division Transfer', 'route' => 'division.transfer'],
                    ['title' => 'Rating Transfer', 'link' => 'https://wiki.ivao.aero/en/home/training/main/training_procedures/rating_transfer'],
                ]
            ],
            [
                'title' => 'Members',
                'submenus' => [
                    ['title' => 'Webeye', 'link' => 'https://webeye.ivao.aero/'],
                    ['title' => 'Awards', 'link' => 'https://awards.us.ivao.aero/'],
                    ['title' => 'Support', 'route' => 'members.support'],
                    ['title' => 'Discord', 'link' => 'https://discord.us.ivao.aero/'],
                    ['title' => 'Forum', 'link' => 'https://us.forum.ivao.aero/'],
                ]
            ],
            [
                'title' => 'ATCs',
                'submenus' => [
                    ['title' => 'Become an ATC', 'route' => 'atcs.become-atc'],
                    ['title' => 'Software', 'link' => 'https://ivao.aero/softdev/software/aurora.asp'],
                    ['title' => 'Facility SOPs', 'link' => 'https://wiki.us.ivao.aero/en/atc/sop'],
                    ['title' => 'Scheduling', 'link' => 'https://atc.ivao.aero/schedule'],
                    ['title' => 'Facility Ratings', 'link' => 'https://atc.ivao.aero/fras?division=US'],
                ]
            ],
            [
                'title' => 'Pilots',
                'submenus' => [
                    ['title' => 'Become a Pilot', 'link' => 'https://wiki.us.ivao.aero/en/pilots/training'],
                    ['title' => 'Software', 'link' => 'https://ivao.aero/softdev/software/altitude.asp'],
                    ['title' => 'Tracker', 'link' => 'https://tracker.ivao.aero/'],
                    ['title' => 'Tours', 'link' => 'https://tours.th.ivao.aero/index.php?div=US'],
                    ['title' => 'Virtual Airlines', 'route' => 'pilots.virtual-airlines'],
                ]
            ],
            [
                'title' => 'Training',
                'submenus' => [
                    ['title' => 'Moodle', 'link' => 'https://moodle.us.ivao.aero/'],
                    ['title' => 'Knowledge Wiki', 'link' => 'https://wiki.us.ivao.aero/'],
                    ['title' => 'Training Request', 'route' => 'training.request'],
                    ['title' => 'Exams', 'route' => 'training.exams'],
                    ['title' => 'Guest Controller Approval', 'route' => 'training.gca'],
                ]
            ],
        ];
    }
    
    /**
     * Get the URL for a menu item (internal route or external link)
     */
    public function getMenuItemUrl(array $item): string
    {
        if (isset($item['route'])) {
            return route($item['route']);
        }
        
        if (isset($item['link'])) {
            return $item['link'];
        }
        
        return '#';
    }
    
    /**
     * Check if a menu item is external
     */
    public function isExternalMenuItem(array $item): bool
    {
        return isset($item['link']);
    }
    
    /**
     * Get target attribute for menu item
     */
    public function getMenuItemTarget(array $item): string
    {
        return $this->isExternalMenuItem($item) ? '_blank' : '';
    }
    
    /**
     * Get rel attribute for menu item
     */
    public function getMenuItemRel(array $item): string
    {
        return $this->isExternalMenuItem($item) ? 'noopener noreferrer' : '';
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
                    link="{{ $this->getMenuItemUrl($item) }}" 
                    class="btn-outline navbar-item-custom"
                    exact
                    target="{{ $this->getMenuItemTarget($item) }}"
                    rel="{{ $this->getMenuItemRel($item) }}"
                >
                    @if($this->isExternalMenuItem($item) && $this->showLinkIcons)
                        <x-slot:actions>
                            <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                        </x-slot:actions>
                    @endif
                </x-menu-item>
            @else
                <x-menu-item 
                    title="{{ $item['title'] }}" 
                    link="{{ $this->getMenuItemUrl($item) }}" 
                    class="btn-outline navbar-item-custom"
                    target="{{ $this->getMenuItemTarget($item) }}"
                    rel="{{ $this->getMenuItemRel($item) }}"
                >
                    @if($this->isExternalMenuItem($item) && $this->showLinkIcons)
                        <x-slot:actions>
                            <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                        </x-slot:actions>
                    @endif
                </x-menu-item>
            @endif
        @else
            {{-- Desktop: Menu item with hover/click submenu --}}
            <div class="relative hidden lg:block {{ !$this->isTouchDevice ? 'group' : '' }}">
                
                {{-- Menu item wrapper for touch handling --}}
                @if($this->isTouchDevice && !isset($item['route']) && !isset($item['link']))
                    {{-- Touch device without route/link: Use non-link element --}}
                    <div wire:click="handleMenuClick({{ $index }})" class="cursor-pointer">
                        <div class="btn btn-outline navbar-item-custom flex items-center justify-between">
                            <span>{{ $item['title'] }}</span>
                        </div>
                    </div>
                @else
                    {{-- Normal menu item with link --}}
                    <div @if($this->isTouchDevice && (isset($item['route']) || isset($item['link']))) wire:click="handleMenuClick({{ $index }})" @endif>
                        @if($item['exact'] ?? false)
                            <x-menu-item 
                                title="{{ $item['title'] }}" 
                                link="{{ $this->getMenuItemUrl($item) }}" 
                                class="btn-outline navbar-item-custom {{ !$this->isTouchDevice ? 'group-hover:bg-primary/10' : '' }}"
                                exact="true"
                                target="{{ $this->getMenuItemTarget($item) }}"
                                rel="{{ $this->getMenuItemRel($item) }}"
                            >
                                <x-slot:actions>
                                    @if($this->isExternalMenuItem($item) && $this->showLinkIcons)
                                        <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                                    @endif
                                    <x-icon name="phosphor.caret-down" 
                                            class="w-4 h-4 transition-transform duration-200 {{ $this->isSubmenuOpen($index) ? 'rotate-180' : '' }} {{ !$this->isTouchDevice ? 'group-hover:rotate-180' : '' }}" />
                                </x-slot:actions>
                            </x-menu-item>
                        @else
                            <x-menu-item 
                                title="{{ $item['title'] }}" 
                                link="{{ $this->getMenuItemUrl($item) }}" 
                                class="btn-outline navbar-item-custom {{ !$this->isTouchDevice ? 'group-hover:bg-primary/10' : '' }}"
                                target="{{ $this->getMenuItemTarget($item) }}"
                                rel="{{ $this->getMenuItemRel($item) }}"
                            >
                                <x-slot:actions>
                                    @if($this->isExternalMenuItem($item) && $this->showLinkIcons)
                                        <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                                    @endif
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
                                <a href="{{ $this->getMenuItemUrl($submenu) }}" 
                                   class="block px-4 py-2 text-base xl:text-lg text-primary-content hover:bg-secondary transition-colors duration-150 {{ $this->isExternalMenuItem($submenu) ? 'flex items-center justify-between' : '' }}"
                                   wire:click="closeAllSubmenus()"
                                   target="{{ $this->getMenuItemTarget($submenu) }}"
                                   rel="{{ $this->getMenuItemRel($submenu) }}">
                                    <span>{{ $submenu['title'] }}</span>
                                    @if($this->isExternalMenuItem($submenu) && $this->showLinkIcons)
                                        <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 opacity-70 ml-2" />
                                    @endif
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
                                <a href="{{ $this->getMenuItemUrl($submenu) }}" 
                                   class="block px-4 py-2 text-base xl:text-lg text-primary-content hover:bg-secondary transition-colors duration-150 {{ $this->isExternalMenuItem($submenu) ? 'flex items-center justify-between' : '' }}"
                                   target="{{ $this->getMenuItemTarget($submenu) }}"
                                   rel="{{ $this->getMenuItemRel($submenu) }}">
                                    <span>{{ $submenu['title'] }}</span>
                                    @if($this->isExternalMenuItem($submenu) && $this->showLinkIcons)
                                        <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 opacity-70 ml-2" />
                                    @endif
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
                            link="{{ $this->getMenuItemUrl($submenu) }}" 
                            class="text-lg pl-6"
                            target="{{ $this->getMenuItemTarget($submenu) }}"
                            rel="{{ $this->getMenuItemRel($submenu) }}"
                        >
                            @if($this->isExternalMenuItem($submenu) && $this->showLinkIcons)
                                <x-slot:actions>
                                    <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                                </x-slot:actions>
                            @endif
                        </x-menu-item>
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