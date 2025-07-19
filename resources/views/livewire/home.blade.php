<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Volt\Component;

use App\Models\User;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.app')]
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
                'icon' => $pendingToast['icon'] ?? 'lucide.info',
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
    <x-header title="Welcome!" size="h1" subtitle="Vote for the HQ ATC of the year" class="!mb-5" />

	<x-card title="Who's the next HQ ATC of the year?" subtitle="Vote for the member of your choice" shadow separator>
		@auth
			You are logged in!
		@else
			Log in to cast your vote!
		@endauth
	</x-card>
</div>