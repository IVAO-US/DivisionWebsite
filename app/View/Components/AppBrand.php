<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
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
                <a href="/" wire:navigate>
                    <div {{ $attributes->class([]) }}>
                        <div class="flex items-center gap-2 max-h-20">
                            <img src="/assets/img/us-full-blue.png" alt="IVAO US Logo" class="h-12 sm:h-15 lg:h-18 w-32 sm:w-43 lg:w-54" />
                            {{-- <h3 class="text-white hidden sm:block">IVAO US</h3> --}}
                        </div>
                    </div>
                </a>
            HTML;
    }
}
