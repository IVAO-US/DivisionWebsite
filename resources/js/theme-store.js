/**
 * Theme Store - Alpine.js Global Store
 *
 * Manages theme state across all theme-toggle component instances.
 * Properly handles:
 * - System preferences (prefers-color-scheme)
 * - Persistent storage (localStorage)
 * - Synchronization between multiple toggle buttons
 * - Private browsing mode (localStorage may not work)
 *
 * ⚠️ DRY PRINCIPLE: Theme names are ONLY defined here.
 * To change themes, modify LIGHT_THEME and DARK_THEME constants below.
 * These must match the theme names in app.css (@plugin "daisyui" { themes: ... })
 */

// ============================================
// THEME CONFIGURATION (Single Source of Truth)
// ============================================
const LIGHT_THEME = 'ivao';
const DARK_THEME = 'ivao-dark';
// ============================================

// Apply initial theme immediately to avoid flash (before Alpine.js loads)
(function initThemeBeforeAlpine() {
    try {
        const stored = localStorage.getItem('mary-theme-toggle');
        if (stored) {
            document.documentElement.setAttribute('data-theme', stored);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const initialTheme = prefersDark ? DARK_THEME : LIGHT_THEME;
            document.documentElement.setAttribute('data-theme', initialTheme);
        }
    } catch (e) {
        // localStorage blocked - use system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', prefersDark ? DARK_THEME : LIGHT_THEME);
    }
})();

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        // Theme configuration (exposed for Blade components)
        lightTheme: LIGHT_THEME,
        darkTheme: DARK_THEME,

        // Current theme state (will be set in init)
        current: LIGHT_THEME,

        // Initialization flag to prevent re-evaluation on Livewire navigation
        _initialized: false,

        /**
         * Initialize theme from localStorage or system preferences
         * ⚠️ IMPORTANT: Only runs ONCE per session to prevent theme reset on navigation
         */
        init() {
            // ===================================================================
            // CRITICAL: Prevent re-initialization on Livewire SPA navigation
            // ===================================================================
            if (this._initialized) {
                return;
            }
            this._initialized = true;

            let initialTheme = null;

            try {
                // Try to read from localStorage (persistent user choice)
                const stored = localStorage.getItem('mary-theme-toggle');

                if (stored) {
                    // User has already chosen a theme - USE IT (don't re-evaluate)
                    initialTheme = stored;
                } else {
                    // First visit - use system preference and save it
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    initialTheme = prefersDark ? DARK_THEME : LIGHT_THEME;                    
                    localStorage.setItem('mary-theme-toggle', initialTheme);
                }
            } catch (e) {
                // localStorage completely blocked - use system preference only
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                initialTheme = prefersDark ? DARK_THEME : LIGHT_THEME;
            }

            // Set current theme
            this.current = initialTheme;

            // Apply theme immediately to avoid flash
            this.applyTheme();

            // Listen for system preference changes (ONLY if no manual preference exists)
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually chosen a theme
                try {
                    const hasManualChoice = localStorage.getItem('mary-theme-toggle');
                    if (!hasManualChoice) {
                        this.current = e.matches ? DARK_THEME : LIGHT_THEME;
                        this.applyTheme();
                    }
                } catch (err) {
                    // In private browsing, always follow system preference
                    this.current = e.matches ? DARK_THEME : LIGHT_THEME;
                    this.applyTheme();
                }
            });
        },

        /**
         * Apply theme to document
         */
        applyTheme() {
            document.documentElement.setAttribute('data-theme', this.current);

            // Dispatch event for other components that may listen
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: this.current }
            }));
        },

        /**
         * Toggle between light and dark theme
         */
        toggle() {
            const wasLight = this.current === this.lightTheme;
            this.current = wasLight ? this.darkTheme : this.lightTheme;

            // Save to localStorage (if available)
            try {
                localStorage.setItem('mary-theme-toggle', this.current);
            } catch (e) {
                // Silently fail in private browsing
            }

            // Apply new theme
            this.applyTheme();
        },

        /**
         * Check if current theme is dark
         */
        get isDark() {
            return this.current === this.darkTheme;
        },

        /**
         * Check if current theme is light
         */
        get isLight() {
            return this.current === this.lightTheme;
        }
    });

    // Initialize the store immediately
    Alpine.store('theme').init();
});

// ============================================
// LIVEWIRE SPA NAVIGATION SUPPORT
// ============================================
// Reapply theme after Livewire navigation (wire:navigate)
// The DOM is replaced during SPA navigation, so data-theme attribute is lost
document.addEventListener('livewire:navigated', () => {
    // Get current theme from Alpine.js store (persists during navigation)
    const currentTheme = Alpine.store('theme')?.current;

    if (currentTheme) {
        // Reapply theme from Alpine.js store
        document.documentElement.setAttribute('data-theme', currentTheme);
    } else {
        // Fallback: read from localStorage if Alpine store not ready
        const stored = localStorage.getItem('mary-theme-toggle');
        if (stored) {
            document.documentElement.setAttribute('data-theme', stored);
        } else {
            // Ultimate fallback: system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = prefersDark ? DARK_THEME : LIGHT_THEME;
            document.documentElement.setAttribute('data-theme', theme);
        }
    }
});