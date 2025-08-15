<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\User;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.app')]
#[Title('Hello!')]
class extends Component {
    use Toast;

    public string $name = '';
    public string $email = '';
    
    /* Mount() */
    public function mount(): void
    {
        $user = Auth::user();

        $this->fill($user);
        $this->name = $user->full_name;
    }

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
    <x-header title="Welcome!" size="h1" subtitle="This is a protected page" class="!mb-5" />

    <x-card title="Livewire/Volt Check" subtitle="Your name should be displayed" shadow separator>
        <div class="md:w-96 mx-auto">
            <x-form>
                <x-input    label="Name"                    icon="phosphor.user"   wire:model="name"  readonly />
                <x-input    label="Email"                   icon="phosphor.at"  wire:model="email" readonly />
            </x-form>
        </div>
    </x-card>
</div>