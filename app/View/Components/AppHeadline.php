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
                    <div class="bg-accent">
                        <div class="text-white text-center font-semibold max-w-7xl mx-auto py-3">
                            <div class="flex items-center justify-center gap-2">
                                <x-icon name="lucide.megaphone" class="w-5 h-5 font-bold" label="Happening now!" />
                                <span>Online Day is active between 18:00 UTC and 06:00 UTC.</span>
                            </div>
                        </div>
                    </div>
            HTML;
    }
}
