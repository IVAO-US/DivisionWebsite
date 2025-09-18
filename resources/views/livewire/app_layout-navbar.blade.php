<?php

use Livewire\Volt\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

new class extends Component
{	
    /* To handle the menu on mobile */
    public bool $mobileMenuOpen = false;
    public function toggleMenu(): void
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
        $this->dispatch('logMobileMenuState', ['state' => $this->mobileMenuOpen]);
    }

    /* mount() */
    public ?User $user = null;
    public bool $isAdmin = false;
    public function mount() : void 
    {
        $this->user = Auth::user();
        if(!is_null($this->user)) {
            $this->isAdmin = Admin::isAdmin($this->user->vid);
        }
    }
};
?>

<div>
    {{-- NAVBAR --}}
    <x-nav full-width class="!px-0 !py-0 !mb-0 lg:!mb-1 !bg-primary !border-b-0">
        <x-slot:brand>
            <div class="w-full h-16 hidden lg:grid grid-cols-3 items-center">
                
                {{-- Left section AppBranding --}}
                <div class="flex justify-start">
                    <x-app-brand />
                </div>
                
                {{-- Center section with menu --}}
                <div class="flex justify-center">
                    <x-menu activate-by-route class="menu-horizontal justify-center items-center">
                        <livewire:app_layout-navbar-menu-items :className="'menu-horizontal justify-center items-center gap-1'" :showLinkIcons="true" />
                    </x-menu>
                </div>
                
                {{-- Right section with actions --}}
                <div class="flex justify-end items-center gap-2">
                    <livewire:app_layout-auth-button />

                        {{-- Admin Access button --}}
                        @if($this->isAdmin)
                            <x-button icon="phosphor.wrench" class="btn-neutral btn-circle" tooltipBottom="Admin Panel" link="{{ route('admin.index') }}" spinner />
                        @endif
                        
                    <livewire:app_layout-theme-toggle />
                </div>
            </div>

            {{-- Mobile layout --}}
            <div class="w-full flex justify-between items-center h-16 lg:hidden">
                {{-- Left section AppBranding --}}
                <div class="justify-start">
                    <x-app-brand />
                </div>

                {{-- Mobile burger button --}}
                <div class="justify-end">
                    {{-- Mobile actions --}}
                    <div class="flex items-center gap-2">
                        <x-button icon="phosphor.list" wire:click="toggleMenu" class="btn btn-accent" />

                        {{-- Admin Access button --}}
                        @if($this->isAdmin)
                            <x-button icon="phosphor.wrench" class="btn-neutral btn-circle" tooltipBottom="Admin Panel" link="{{ route('admin.index') }}" spinner />
                        @endif

                        <livewire:app_layout-theme-toggle />
                    </div>
                </div>
            </div>
        </x-slot:brand>
    </x-nav>

    {{-- Mobile menu --}}
    @if ($this->mobileMenuOpen)
        <div class="lg:hidden absolute left-0 right-0 w-full bg-secondary shadow-md z-50 top-full">
            <x-menu activate-by-route class="menu-vertical w-full">
                <livewire:app_layout-navbar-menu-items :className="'menu-vertical w-full px-6'" :showLinkIcons="true" />
            </x-menu>
        </div>
    @endif
</div>