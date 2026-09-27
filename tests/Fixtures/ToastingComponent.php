<?php

namespace Tests\Fixtures;

use Livewire\Component;
use Mary\Traits\Toast;

/**
 * A component whose own action shows a MaryUI toast
 */
class ToastingComponent extends Component
{
    use Toast;

    public function notify(): void
    {
        $this->success('Saved');
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
