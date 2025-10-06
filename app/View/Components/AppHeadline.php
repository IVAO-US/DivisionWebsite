<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Services\HeadlineService;

class AppHeadline extends Component
{
    public ?array $headline = null;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->headline = HeadlineService::getCurrentHeadline();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // If no headline, return empty
        if (!$this->headline) {
            return '';
        }

        $icon = $this->headline['icon'];
        $title = $this->headline['title'];
        $message = $this->headline['message'];

        return <<<HTML
            <div class="bg-accent">
                <div class="text-white text-center font-semibold max-w-7xl mx-auto px-3 py-3">
                    <div class="flex items-center justify-center gap-2">
                        <x-icon name="{$icon}" class="w-5 h-5 font-bold xl:text-lg -scale-x-100" />
                        <span class="font-bold xl:text-lg">{$title}</span>
                        <span>{$message}</span>
                    </div>
                </div>
            </div>
        HTML;
    }
}