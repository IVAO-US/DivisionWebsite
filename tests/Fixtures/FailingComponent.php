<?php

namespace Tests\Fixtures;

use Livewire\Component;
use RuntimeException;

/**
 * A component whose actions fail through bugs of the site's own code
 */
class FailingComponent extends Component
{
    public function fail(): void
    {
        throw new RuntimeException('A bug of the site');
    }

    /**
     * Fails once the action has run, as Livewire serializes the event
     */
    public function dispatchNowhere(): void
    {
        $this->dispatch('ping')->to('no-such-component');
    }

    public function useService(UnboundService $service): void {}

    public function render(): string
    {
        return '<div></div>';
    }
}
