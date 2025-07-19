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
    <x-nav full-width class="!border-b-0">
        <x-slot:brand>

			<div class="w-full flex justify-between items-center relative h-16">
				
				{{-- App Menu --}}
				<div class="hidden lg:block absolute lg:w-full lg:left-1/2 lg:transform lg:-translate-x-1/2 top-1/2 -translate-y-1/2">
					<x-menu activate-by-route class="menu-horizontal justify-center items-center lg:pr-42 xl:pr-60 2xl:pr-64">
						<livewire:app_layout-navbar-menu-items :className="'menu-horizontal justify-center items-center gap-1'" />
					</x-menu>
				</div>
				
				{{-- App Actions --}}
				<div class="flex items-center absolute right-0 sm:right-8 lg:right-4">
					{{-- Desktop: Show user information --}}
					<div class="hidden lg:block">
						<livewire:app_layout-auth-button />
					</div>
					
					{{-- Mobile: Show burger button --}}
					<button wire:click="toggleMenu" class="lg:hidden btn btn-soft">
						<x-icon name="lucide.menu" />
					</button>
					
					{{-- Admin Access button --}}
                    <x-button icon="lucide.wrench" class="btn-accent btn-circle btn-outline ml-2" tooltipBottom="Admin Panel" spinner />

                    {{-- Theme toggle --}}
                    <livewire:app_layout-theme-toggle />
				</div>
			
			</div>
        

        </x-slot:brand>
    </x-nav>

    {{-- Mobile menu --}}
    @if ($this->mobileMenuOpen)
        <div class="lg:hidden absolute left-0 right-0 w-full bg-base-200 shadow-md z-50">
            <x-menu activate-by-route class="menu-vertical w-full">
                <livewire:app_layout-navbar-menu-items :className="'menu-vertical w-full px-6'" />
            </x-menu>
        </div>
    @endif
</div>