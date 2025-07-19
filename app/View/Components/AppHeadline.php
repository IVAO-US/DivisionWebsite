<?php

namespace App\View\Components;

use Closure;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppHeadline extends Component
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
                    <div class="bg-info">
                        <div class="text-white text-center font-semibold max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                            Cast your vote onwards December 1st 2025.
                        </div>
                    </div>
            HTML;
    }
}
