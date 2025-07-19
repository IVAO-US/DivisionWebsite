<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PasswordHint extends Component
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
                <ul class="text-xs text-base-content/60 list-inside space-y-1">
                    <li>Must be more than <b>8</b> characters and:</li>
                    <li class="list-[square] ms-2">a <b>mix</b> of lowercase and uppercase <b>letters</b>,</li>
                    <li class="list-[square] ms-2">at least one <b>number</b>,</li>
                    <li class="list-[square] ms-2">at least one <b>symbol</b>.</li>
                </ul>
            HTML;
    }
}