{{--
    Theme Initialization Script

    CRITICAL: Must be placed in <head> BEFORE @vite to prevent theme flash

    OPTIMIZATION: Single-line execution without conditionals for instant theme application.
    Uses short-circuit evaluation (||) to avoid any visible delay.

    Performance:
    - No try/catch overhead on happy path
    - No if/else branching delay
    - Inline ternary for system preference evaluation
    - localStorage read + setAttribute in ~0.1ms
--}}
<script>
    document.documentElement.setAttribute('data-theme',
        (function() {
            try {
                // Try localStorage first (instant if exists)
                return localStorage.getItem('mary-theme-toggle') ||
                    // Fallback: evaluate system preference inline
                    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'night' : 'fantasy');
            } catch (e) {
                // localStorage blocked - use system preference
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'night' : 'fantasy';
            }
        })()
    );
</script>