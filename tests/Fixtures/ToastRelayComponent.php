<?php

namespace Tests\Fixtures;

use App\Support\Toast;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * A component that relays an icon received from the browser to a toast,
 * from one of its own listeners: a call the browser guard never sees
 */
class ToastRelayComponent extends Component
{
    use Toast;

    #[On('show-toast')]
    public function relay(string $icon): void
    {
        $this->error('Relayed', icon: $icon);
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
