{{--
    Theme Initialization Script (Critical Path)

    ⚡ CRITICAL: Must be placed in <head> BEFORE @vite to prevent initial theme flash

    This inline script runs immediately when the page loads, BEFORE any external
    JavaScript files (including Vite bundles) are loaded. This prevents the theme
    flash on initial page load.

    For SPA navigation (wire:navigate), theme persistence is handled by:
    - MutationObserver in theme-store.js (watches for data-theme changes)
    - livewire:navigating event (applies theme before navigation)
    - livewire:navigated event (verifies theme after navigation)

    Performance: localStorage read + setAttribute in ~0.1ms
--}}
<script>
    // Immediately apply theme from localStorage or system preference
    (function() {
        try {
            const stored = localStorage.getItem('mary-theme-toggle');
            const theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'ivao-dark' : 'ivao');
            document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {
            // localStorage blocked - use system preference
            const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'ivao-dark' : 'ivao';
            document.documentElement.setAttribute('data-theme', theme);
        }
    })();
</script>