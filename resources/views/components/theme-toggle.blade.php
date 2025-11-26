<?php

use Livewire\Component;

new class extends Component
{
    /* Now using a pure Alpine.js solution meant to be more efficient */
};
?>

{{--
    Theme Toggle Component
    Uses Alpine.js global store to sync theme across all instances
    Properly handles prefers-color-scheme in private browsing
    Customisation: refer to theme-store.js for theme names
--}}

<div x-data="{
    get isDark() {
        return $store.theme?.current === $store.theme?.darkTheme;
    }
}">
    <x-button
        @click="$store.theme?.toggle()"
        class="btn-sm btn-circle max-xl:btn-soft xl:btn-secondary ml-2 lg:mx-2"
        spinner
    >
        <!-- Sun icon for light mode -->
        <x-icon name="phosphor.sun" class="w-5 h-5" x-show="!isDark" x-cloak />
        <!-- Moon icon for dark mode -->
        <x-icon name="phosphor.moon" class="w-5 h-5" x-show="isDark" x-cloak />
    </x-button>
</div>