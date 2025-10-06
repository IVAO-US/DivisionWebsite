<?php

/**
 * Simplified Navbar Menu Items Component
 * 
 * A clean, responsive navigation menu component using pure MaryUI components.
 * No JavaScript complexity, just clean PHP/Livewire logic.
 * 
 * Features:
 * - Pure MaryUI components for consistency
 * - Simple external link detection
 * - Clean desktop hover (CSS only) and mobile expand
 * - Security attributes for external links
 * 
 * @property string $className - CSS classes to apply to the root container
 * @property bool $showLinkIcons - Whether to show external link icons
 * @property array $menuItems - Menu structure array
 */

use Livewire\Volt\Component;

new class extends Component
{
    public string $className = '';
    public bool $showLinkIcons = true;
    public array $menuItems = [];
    
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
                    ['title' => 'Awards System', 'link' => 'https://awards.us.ivao.aero/'],
                    ['title' => 'Support', 'route' => 'members.support'],
                    ['title' => 'Discord', 'link' => 'https://discord.us.ivao.aero/'],
                    ['title' => 'Forum', 'link' => 'https://us.forum.ivao.aero/'],
                ]
            ],
            [
                'title' => 'ATCs',
                'submenus' => [
                    ['title' => 'Become an ATC', 'route' => 'atcs.become-atc'],
                    ['title' => 'Facility SOPs', 'link' => 'https://wiki.us.ivao.aero/en/atc/sop'],
                    ['title' => 'Software', 'link' => 'https://ivao.aero/softdev/software/aurora.asp'],
                    ['title' => 'Facility Ratings', 'link' => 'https://atc.ivao.aero/fras?division=US'],
                    ['title' => 'Scheduling', 'link' => 'https://atc.ivao.aero/schedule'],
                ]
            ],
            [
                'title' => 'Pilots',
                'submenus' => [
                    ['title' => 'Become a Pilot', 'link' => 'https://wiki.us.ivao.aero/en/pilots/training'],
                    ['title' => 'Software', 'link' => 'https://ivao.aero/softdev/software/altitude.asp'],
                    ['title' => 'IVAO Tracker', 'link' => 'https://tracker.ivao.aero/'],
                    ['title' => 'Tours System', 'link' => 'https://tours.th.ivao.aero/index.php?div=US'],
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
     * Get the URL for a menu item
     */
    public function getUrl(array $item): string
    {
        if (isset($item['route'])) {
            return route($item['route']);
        }
        
        return $item['link'] ?? '#';
    }
    
    /**
     * Check if a menu item is external
     */
    public function isExternal(array $item): bool
    {
        return isset($item['link']);
    }
    
    /**
     * Get target attribute for external links
     */
    public function getTarget(array $item): string
    {
        return $this->isExternal($item) ? '_blank' : '';
    }
    
    /**
     * Get rel attribute for external links
     */
    public function getRel(array $item): string
    {
        return $this->isExternal($item) ? 'noopener noreferrer' : '';
    }
    
    /**
     * Should use wire:navigate for internal links
     */
    public function shouldNavigate(array $item): bool
    {
        return !$this->isExternal($item);
    }

    /**
    * Get the active dropdown based on current route
    */
    public function getActiveDropdown(): ?string 
    {
        $currentRoute = request()->route()?->getName();
        
        if (!$currentRoute) {
            return null;
        }
        
        foreach ($this->menuItems as $item) {
            if (!empty($item['submenus'])) {
                foreach ($item['submenus'] as $submenu) {
                    if (isset($submenu['route']) && $submenu['route'] === $currentRoute) {
                        return $item['title'];
                    }
                }
            }
        }
        
        return null;
    }
};
?>

<div class="{{ $this->className }}">
    @foreach($this->menuItems as $item)
        @if(empty($item['submenus']))
            {{-- Simple menu item without submenu --}}
            @if(isset($item['exact']) && $item['exact'])
                {{-- Home item with exact matching --}}
                <x-menu-item 
                    title="{{ $item['title'] }}" 
                    link="{{ $this->getUrl($item) }}" 
                    class="navbar-item-custom"
                    exact="true"
                    target="{{ $this->getTarget($item) }}"
                    rel="{{ $this->getRel($item) }}"
                >
                    @if($this->isExternal($item) && $this->showLinkIcons)
                        <x-slot:actions>
                            <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                        </x-slot:actions>
                    @endif
                </x-menu-item>
            @else
                {{-- Regular menu item --}}
                <x-menu-item 
                    title="{{ $item['title'] }}" 
                    link="{{ $this->getUrl($item) }}" 
                    class="navbar-item-custom"
                    target="{{ $this->getTarget($item) }}"
                    rel="{{ $this->getRel($item) }}"
                >
                    @if($this->isExternal($item) && $this->showLinkIcons)
                        <x-slot:actions>
                            <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 ml-1 opacity-70" />
                        </x-slot:actions>
                    @endif
                </x-menu-item>
            @endif
        @else
            {{-- Desktop: Simple dropdown using CSS hover --}}
            <div class="dropdown dropdown-hover hidden lg:block">
                <div tabindex="0" role="button" class="btn btn-outline navbar-item-custom {{ $this->getActiveDropdown() === $item['title'] ? 'active' : '' }}">
                    {{ $item['title'] }}
                </div>
                
                <ul tabindex="0" class="dropdown-content menu bg-base-100 shadow-xl rounded-box w-64 p-2">
                    @foreach($item['submenus'] as $submenu)
                        <li>
                            <a href="{{ $this->getUrl($submenu) }}"
                               class="text-base-content hover:!bg-secondary hover:!text-secondary transition-colors"
                               target="{{ $this->getTarget($submenu) }}"
                               rel="{{ $this->getRel($submenu) }}"
                            >
                                <span>{{ $submenu['title'] }}</span>
                                @if($this->isExternal($submenu) && $this->showLinkIcons)
                                    <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 opacity-70" />
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            {{-- Mobile: Expandable menu --}}
            <div class="lg:hidden">              
                <x-menu-sub title="{{ $item['title'] }}">
                    @foreach($item['submenus'] as $submenu)
                        <a href="{{ $this->getUrl($submenu) }}"
                           class="my-0.5 py-1.5 px-4 hover:text-inherit whitespace-nowrap text-lg pl-2 flex items-center w-full"
                           target="{{ $this->getTarget($submenu) }}"
                           rel="{{ $this->getRel($submenu) }}"
                        >
                            <span>{{ $submenu['title'] }}</span>
                            @if($this->isExternal($submenu) && $this->showLinkIcons)
                                <x-icon name="phosphor.arrow-square-out" class="w-4 h-4 opacity-70 ml-1" />
                            @endif
                        </a>
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