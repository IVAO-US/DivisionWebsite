<?php

namespace App\Traits;

use App\Services\SEOService;
use App\Traits\BreadcrumbsTrait;

trait HasSEO
{
    use BreadcrumbsTrait;

    protected ?SEOService $seoService = null;

    /**
     * Get SEO Service instance
     */
    protected function seo(): SEOService
    {
        if (!$this->seoService) {
            $this->seoService = app(SEOService::class);
        }
        
        return $this->seoService;
    }

    /**
     * Set SEO with automatic breadcrumbs
     */
    protected function setSEOWithBreadcrumbs(
        string $title,
        string $description,
        ?string $image = null,
        ?string $canonical = null,
        array $keywords = []
    ): void {
        // Set basic SEO
        $this->seo()->setPage($title, $description, $image, $canonical, $keywords);
        
        // Add breadcrumbs schema
        $breadcrumbs = $this->getBreadcrumbs();
        $this->seo()->setBreadcrumbsFromTrait($breadcrumbs);
    }
}