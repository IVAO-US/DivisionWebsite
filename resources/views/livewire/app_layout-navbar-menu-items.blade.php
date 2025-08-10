<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    /* Because of the way Blade handles component, we need to report the x-menu classes onto the root <div> */
    public string $className = ''; /* :className */
    
    /* Menu structure with submenus - simplified version */
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
                'route' => 'users',
                'exact' => false,
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
                'route' => 'hello',
                'exact' => false,
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
};
?>

<div class="{{ $this->className }}">
    @foreach($this->menuItems as $item)
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
            {{-- Desktop: Menu item with hover submenu --}}
            <div class="relative group hidden lg:block">
                @if($item['exact'] ?? false)
                    <x-menu-item 
                        title="{{ $item['title'] }}" 
                        link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                        class="btn-outline navbar-item-custom group-hover:bg-primary/10"
                        exact
                    >
                        <x-slot:actions>
                            <x-icon name="lucide.chevron-down" class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" />
                        </x-slot:actions>
                    </x-menu-item>
                @else
                    <x-menu-item 
                        title="{{ $item['title'] }}" 
                        link="{{ $item['route'] ? route($item['route']) : '#' }}" 
                        class="btn-outline navbar-item-custom group-hover:bg-primary/10"
                    >
                        <x-slot:actions>
                            <x-icon name="lucide.chevron-down" class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" />
                        </x-slot:actions>
                    </x-menu-item>
                @endif
                
                {{-- Submenu dropdown --}}
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