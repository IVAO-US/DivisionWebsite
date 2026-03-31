// Catch Alpine x-anchor errors thrown during Livewire SPA navigation.
// These occur when x-anchor tries to reference a DOM element that was
// removed during the wire:navigate page swap.
window.addEventListener('unhandledrejection', (event) => {
    const reason = event.reason;
    const reasonStr = (typeof reason === 'string') ? reason : reason?.message || '';

    if (reasonStr.includes('x-anchor')) {
        console.warn('Alpine x-anchor error suppressed during navigation');
        event.preventDefault();
    }
});
