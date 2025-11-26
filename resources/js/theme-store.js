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

        /**
         * Initialize theme from localStorage or system preferences
         */
        init() {
            let initialTheme = null;

            try {
                // Try to read from localStorage (persistent user choice)
                const stored = localStorage.getItem('mary-theme-toggle');

                if (stored) {
                    initialTheme = stored;
                } else {
                    // Fallback to system preference
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    initialTheme = prefersDark ? DARK_THEME : LIGHT_THEME;

                    // Try to save preference (may fail in private browsing)
                    try {
                        localStorage.setItem('mary-theme-toggle', initialTheme);
                    } catch (e) {
                        console.warn('localStorage not available (private browsing?). Theme will not persist.');
                    }
                }
            } catch (e) {
                // localStorage completely blocked - use system preference only
                console.warn('localStorage blocked. Using system preference only.');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                initialTheme = prefersDark ? DARK_THEME : LIGHT_THEME;
            }

            // Set current theme
            this.current = initialTheme;

            // Apply theme immediately to avoid flash
            this.applyTheme();

            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually set a preference
                try {
                    if (!localStorage.getItem('mary-theme-toggle')) {
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