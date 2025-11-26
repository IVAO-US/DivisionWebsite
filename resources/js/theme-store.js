/**
 * Theme Store - Alpine.js Global Store
 *
 * Manages theme state across all theme-toggle component instances.
 * Properly handles:
 * - System preferences (prefers-color-scheme)
 * - Persistent storage (localStorage)
 * - Synchronization between multiple toggle buttons
 * - Private browsing mode (localStorage may not work)
 * - Livewire SPA navigation (wire:navigate) without flash
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

/**
 * Get current theme from localStorage or system preference
 * This function is used across all initialization points
 */
function getCurrentTheme() {
    try {
        const stored = localStorage.getItem('mary-theme-toggle');
        if (stored) {
            return stored;
        }
    } catch (e) {
        // localStorage blocked - fall through to system preference
    }

    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return prefersDark ? DARK_THEME : LIGHT_THEME;
}

/**
 * Apply theme to document immediately
 */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}

// ============================================
// CRITICAL FIX: MutationObserver to prevent theme flash
// ============================================
// This observer watches for changes to the data-theme attribute
// and immediately restores it if it's removed or changed incorrectly
// during Livewire navigation (wire:navigate)
(function initThemeProtection() {
    let lastKnownTheme = getCurrentTheme();
    applyTheme(lastKnownTheme);

    // Create observer to watch for attribute changes on <html>
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const expectedTheme = getCurrentTheme();

                // If theme attribute is removed or incorrect, restore it immediately
                if (!currentTheme || (currentTheme !== expectedTheme && expectedTheme !== lastKnownTheme)) {
                    // Only update if we have a valid stored theme
                    // This prevents fighting with intentional theme changes
                    const stored = localStorage.getItem('mary-theme-toggle');
                    if (stored && currentTheme !== stored) {
                        applyTheme(stored);
                        lastKnownTheme = stored;
                    }
                }
            }
        });
    });

    // Start observing
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });

    // Update lastKnownTheme when theme is explicitly changed
    window.addEventListener('theme-changed', (e) => {
        if (e.detail?.theme) {
            lastKnownTheme = e.detail.theme;
        }
    });
})();

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        // Theme configuration (exposed for Blade components)
        lightTheme: LIGHT_THEME,
        darkTheme: DARK_THEME,

        // Current theme state (synced with getCurrentTheme())
        current: getCurrentTheme(),

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

            // Sync with current theme (already applied by initThemeProtection)
            this.current = getCurrentTheme();

            // Save to localStorage if it's the first visit
            try {
                const stored = localStorage.getItem('mary-theme-toggle');
                if (!stored) {
                    localStorage.setItem('mary-theme-toggle', this.current);
                }
            } catch (e) {
                // localStorage blocked - continue without saving
            }

            // Listen for system preference changes (ONLY if no manual preference exists)
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually chosen a theme
                try {
                    const hasManualChoice = localStorage.getItem('mary-theme-toggle');
                    if (!hasManualChoice) {
                        this.current = e.matches ? DARK_THEME : LIGHT_THEME;
                        applyTheme(this.current);
                        this.dispatchThemeChanged();
                    }
                } catch (err) {
                    // In private browsing, always follow system preference
                    this.current = e.matches ? DARK_THEME : LIGHT_THEME;
                    applyTheme(this.current);
                    this.dispatchThemeChanged();
                }
            });
        },

        /**
         * Dispatch theme-changed event
         */
        dispatchThemeChanged() {
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

            // Apply new theme (MutationObserver will handle it, but we do it anyway for immediate feedback)
            applyTheme(this.current);
            this.dispatchThemeChanged();
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
/**
 * Additional insurance: Reapply theme before and after Livewire navigation
 * The MutationObserver above should handle most cases, but these listeners
 * provide extra protection for edge cases.
 */

// Before navigation starts: ensure theme is preserved
document.addEventListener('livewire:navigating', () => {
    const theme = getCurrentTheme();
    if (theme) {
        applyTheme(theme);
    }
});

// After navigation completes: verify theme is still correct
document.addEventListener('livewire:navigated', () => {
    const theme = getCurrentTheme();
    if (theme) {
        // Only apply if different from current attribute
        const current = document.documentElement.getAttribute('data-theme');
        if (current !== theme) {
            applyTheme(theme);
        }

        // Sync Alpine store if available
        if (window.Alpine?.store && Alpine.store('theme')) {
            Alpine.store('theme').current = theme;
        }
    }
});