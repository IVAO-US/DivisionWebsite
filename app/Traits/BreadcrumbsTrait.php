<?php

namespace App\Traits;
use Illuminate\Support\Facades\Route;

trait BreadcrumbsTrait
{
    /**
     * Automatically generates breadcrumbs based on the current route and URL
     * 
     * @return array
     */
    public function getBreadcrumbs(): array
    {
        // Initialize with Home breadcrumb always present
        $breadcrumbs = [
            [
                'label' => '',
                'link' => route('home'),
                'icon' => 'phosphor.house',
            ]
        ];
        
        $route = request()->route();
        
        if (!$route) {
            return $breadcrumbs;
        }
        
        // Get the current URL path without query parameters
        $path = request()->path();
        $urlSegments = explode('/', $path);
        // Remove empty segments
        $urlSegments = array_filter($urlSegments);
        
        // Build breadcrumbs from URL segments
        $currentPath = '';
        $processedSegments = [];
        $numericSegmentFound = false;
        
        foreach ($urlSegments as $index => $segment) {
            $isLastSegment = $index === count($urlSegments) - 1;
            $processedSegments[] = $segment;
            
            // Check if this is a numeric segment (likely ID)
            if (is_numeric($segment)) {
                $numericSegmentFound = true;
                continue; // Skip adding this segment to breadcrumbs
            }
            
            // Build the current path for route checking
            $currentPath = $currentPath ? "$currentPath.$segment" : $segment;
            
            // Add breadcrumb for this segment
            $breadcrumbs[] = [
                'label' => $this->formatLabel($segment),
                'link' => $isLastSegment ? null : $this->getUrlForSegments($processedSegments)
            ];
        }
        
        // If the last segment was numeric, add an "Review" breadcrumb
        if ($numericSegmentFound) {
            $breadcrumbs[] = [
                'label' => 'Review',
                'link' => null
            ];
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Get proper URL for the given segments
     *
     * @param array $segments
     * @return string
     */
    protected function getUrlForSegments(array $segments): string
    {
        // Build the full path
        $path = '/' . implode('/', $segments);
        $uri = trim($path, '/');
        
        // First, try to find a direct route match
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            if ($route->uri() === $uri && $route->getName()) {
                return route($route->getName());
            }
        }
        
        // If no direct match, try to find a route with the last segment as name
        $lastSegment = end($segments);
        if (Route::has($lastSegment)) {
            return route($lastSegment);
        }
        
        // If we have a single segment, check if it's a route name
        if (count($segments) === 1) {
            if (Route::has($segments[0])) {
                return route($segments[0]);
            }
        }
        
        // Fallback to the URL helper
        return url($path);
    }
    
    /**
     * Formats a route segment into a readable label
     *
     * @param string $segment
     * @return string
     */
    protected function formatLabel(string $segment): string
    {
        // Convert hyphens and underscores to spaces
        $label = str_replace(['-', '_'], ' ', $segment);
        
        // Capitalize the first letter of each word
        return ucwords($label);
    }
}