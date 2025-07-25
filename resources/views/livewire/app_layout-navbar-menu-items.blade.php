<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state};

new class extends Component
{
    /* Because of the way Blade handles component, we need to report the x-menu classes onto the root <div> */
    public string $className = ''; /* :className */
};
?>

<div class="{{ $this->className }}">
    {{-- Main navigation items matching the original design --}}
    <x-menu-item 
        title="Home" 
        link="{{ route('home') }}" 
        class="btn-outline navbar-item-custom"
        exact 
    />
    
    <x-menu-item 
        title="Division" 
        link="{{ route('users') }}" 
        class="btn-outline navbar-item-custom"
    />
    
    <x-menu-item 
        title="Training" 
        link="{{ route('hello') }}" 
        class="btn-outline navbar-item-custom"
    />
    
    <x-menu-item 
        title="Pilots" 
        link="#" 
        class="btn-outline navbar-item-custom"
    />
    
    <x-menu-item 
        title="ATCs" 
        link="#" 
        class="btn-outline navbar-item-custom"
    />
    
    <x-menu-item 
        title="Socials" 
        link="#" 
        class="btn-outline navbar-item-custom"
    />

    {{-- User details on mobile --}}
    <div class="lg:hidden">
        <x-menu-separator class="border-white/20" />
        
        {{-- Protected menu items for mobile --}}
        @auth
            <x-menu-item 
                title="Logged #1" 
                icon="lucide.book-open-text" 
                link="{{ route('hello') }}" 
                class="btn-outline navbar-item-custom"
            />
        @endauth
        
        <livewire:app_layout-auth-button />
    </div>
</div>