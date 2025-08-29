<?php

use Livewire\Volt\Component;
use App\Traits\BreadcrumbsTrait;

new class extends Component {
    use BreadcrumbsTrait;
    
    public array    $breadcrumbs;
    public bool     $enabled;
    public ?string  $title = null;

    /**
     * Obtenir les breadcrumbs selon la route actuelle
     *
     * @return array
     */
    public function breadcrumbs()
    {
        return $this->getBreadcrumbs();
    }

    /**
     * Check if breadcrumbs should be displayed
     * 
     * @return bool
     */
    public function enableDisplay(): bool
    {
        $route = request()->route();
        
        if (!$route) {
            return false;
        }
        
        $routeName = $route->getName();
        
        // Don't display breadcrumbs on home page
        return $routeName !== 'home';
    }
    
    /**
     * Mount
     */
    public function mount(?string $title = null)
    {
        $this->title = $title;
        $this->enabled = $this->enableDisplay();
        
        if ($this->enabled) {
            $this->breadcrumbs = $this->breadcrumbs();
            
            /* The last breadcrumb is named after the title of the page if existing */
            if (!empty($this->breadcrumbs) && $this->title) {
                $lastIndex = count($this->breadcrumbs) - 1;
                $this->breadcrumbs[$lastIndex]['label'] = $this->title;
            }
        } else {
            $this->breadcrumbs = [];
        }
    }
    
    /**
     * Volt::with()
     */
    public function with(): array
    {
        return [
            'breadcrumbs' => $this->breadcrumbs,
            'enabled' => $this->enabled,
        ];
    }
}; ?>

<div>
@if($enabled)
	<x-breadcrumbs 
		:items="$breadcrumbs"
		separator="phosphor.minus"
		separator-class="text-primary mx-1"
		class="bg-base-100 p-3 rounded-lg mb-4 font-semibold"
		icon-class="text-primary"
		link-item-class="text-sm font-normal hover:underline" />
@endif
<div>