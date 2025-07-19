<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ForgottenPassword extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <a class="text-sm text-base-content/60 text-right" href="{{ route('users.forgotten-password') }}">Forgotten password?</a>
            HTML;
    }
}