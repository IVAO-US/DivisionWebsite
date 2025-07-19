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
    <x-menu-item title="Home" icon="lucide.home" link="{{ route('home') }}" exact />
    <x-menu-item title="Item #1" icon="lucide.area-chart" link="{{ route('users') }}" />

    {{-- Protected menu items --}}
    @auth
        <x-menu-item title="Logged #1" icon="lucide.book-open-text" link="{{ route('hello') }}" />
    @endauth

    {{-- User details on mobile --}}
    <div class="lg:hidden">
        <x-menu-separator />
        <livewire:app_layout-auth-button />
    </div>
</div>