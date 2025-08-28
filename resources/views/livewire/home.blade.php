<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Volt\Component;

use App\Models\User;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.homepage')]
#[Title('Welcome')]
class extends Component {
    use Toast;

    /* Pending toast following authentication attempt */
    public function pendingToast(): void
    {
        $pendingToast = Session::pull('session_toast');
        if ($pendingToast) 
        {
            $toastParams = [
                'title' => $pendingToast['title'] ?? 'Notification',
                'description' => $pendingToast['description'] ?? '',
                'position' => $pendingToast['position'] ?? 'toast-top toast-end',
                'icon' => $pendingToast['icon'] ?? 'phosphor.info',
                'css' => $pendingToast['css'] ?? 'alert-info',
                'timeout' => $pendingToast['timeout'] ?? 3000,
                'redirectTo' => $pendingToast['redirectTo'] ?? null
            ];

            match ($pendingToast['type'] ?? 'info') {
                'success' => $this->success(...$toastParams),
                'error' => $this->error(...$toastParams),
                'warning' => $this->warning(...$toastParams),
                'info' => $this->info(...$toastParams),
                default => $this->info(...$toastParams)
            };
        }
    }
}; ?>

<div x-data x-init="$wire.pendingToast()">
    
    {{-- Hero Section with Video Background --}}
    <livewire:homepage_components-hero-video />
    
    {{-- What We Offer Section with American Flag Background --}}
    <livewire:homepage_components-join-us />
    
    {{-- Division Highlights Section --}}
    <section class="mx-auto px-5 md:px-15 py-20 bg-base-200">
        <livewire:homepage_components-division-highlights />
    </section>
    
    {{-- Tours & VAs Section --}}
    <section 
        class="mx-auto px-5 md:px-15 py-20 bg-base-300"
        style=" background-image: url('/assets/img/flightdeck-777.png');
                background-size: cover;
                background-position: center center;
                background-repeat: no-repeat;
                background-attachment: fixed;">
        <livewire:homepage_components-flight-ops />
    </section>
    
    {{-- Free Education Section --}}
    <section class="py-16 bg-base-200">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center">Free Education</h2>
            <livewire:homepage_components-training />
        </div>
    </section>
</div>