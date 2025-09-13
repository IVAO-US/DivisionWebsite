<?php

use Livewire\Volt\Component;

new class extends Component
{
    /* Customisation: refer to app.css for theme names */
    private const LIGHT_THEME   = "ivao";
    private const DARK_THEME    = "ivao-dark";


    /* Class property */
    public $isDarkMode = false;
    
    public function mount(): void
    {
        $this->isDarkMode = session('theme', self::LIGHT_THEME) === self::DARK_THEME;
    }
    
    /* Adjust the toggle icon based on current theme */
    public function getThemeIconProperty(): string
    {
        return $this->isDarkMode ? 'phosphor.moon' : 'phosphor.sun';
    }
    
    /* Theme toggler wired with wire:click */
    public function toggleTheme(): void
    {
        $this->isDarkMode = !$this->isDarkMode;
        session(['theme' => $this->isDarkMode ? self::DARK_THEME : self::LIGHT_THEME]);
        $this->dispatch('mary-toggle-theme');
    }
    
    /* Getters for accessing constants in the template */
    public function getLightThemeNameProperty(): string
    {
        return self::LIGHT_THEME;
    }
    
    public function getDarkThemeNameProperty(): string
    {
        return self::DARK_THEME;
    }
};
?>

<div>
    <x-button :icon="$this->themeIcon" wire:click="toggleTheme" wire:loading.class="opacity-50" class="btn-secondary btn-circle" tooltipBottom="Theme" spinner />
    <x-theme-toggle lightTheme="{{ $this->lightThemeName }}" darkTheme="{{ $this->darkThemeName }}" class="hidden" />
</div>