<?php

use Livewire\Volt\Component;

new class extends Component
{	
    /* To handle the menu on mobile */
    public bool $mobileMenuOpen = false;
    public function toggleMenu(): void
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
        $this->dispatch('logMobileMenuState', ['state' => $this->mobileMenuOpen]);
    }
};
?>

<div>
    {{-- NAVBAR --}}
    <x-nav full-width class="!px-0 !py-0 !mb-0 lg:!mb-3 !bg-primary !border-b-0">
        <x-slot:brand>
            <div class="w-full h-16 hidden lg:grid grid-cols-3 items-center">
                
                {{-- Left section AppBranding --}}
                <div class="flex justify-start">
                    <x-app-brand />
                </div>
                
                {{-- Center section with menu --}}
                <div class="flex justify-center">
                    <x-menu activate-by-route class="menu-horizontal justify-center items-center">
                        <livewire:app_layout-navbar-menu-items :className="'menu-horizontal justify-center items-center gap-1'" />
                    </x-menu>
                </div>
                
                {{-- Right section with actions --}}
                <div class="flex justify-end items-center">
                    <livewire:app_layout-auth-button />
                    <x-button icon="lucide.wrench" class="btn-white btn-circle btn-outline ml-2" tooltipBottom="Admin Panel" spinner />
                    <livewire:app_layout-theme-toggle />
                </div>
            </div>

            {{-- Mobile layout --}}
            <div class="w-full flex justify-between items-center h-16 lg:hidden">
                {{-- Mobile burger button --}}
                <button wire:click="toggleMenu" class="btn btn-accent btn-soft">
                    <x-icon name="lucide.menu" />
                </button>
                
                {{-- Mobile actions --}}
                <div class="flex items-center">
                    <x-button icon="lucide.wrench" class="btn-white btn-circle btn-outline mr-2" tooltipBottom="Admin Panel" spinner />
                    <livewire:app_layout-theme-toggle />
                </div>
            </div>
        </x-slot:brand>
    </x-nav>

    {{-- Mobile menu --}}
    @if ($this->mobileMenuOpen)
        <div class="lg:hidden absolute left-0 right-0 w-full bg-secondary shadow-md z-50">
            <x-menu activate-by-route class="menu-vertical w-full">
                <livewire:app_layout-navbar-menu-items :className="'menu-vertical w-full px-6'" />
            </x-menu>
        </div>
    @endif
</div>