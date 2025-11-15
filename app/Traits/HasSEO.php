<?php

namespace App\Traits;

use App\Services\SeoService;
use App\Traits\BreadcrumbsTrait;

trait HasSEO
{
    use BreadcrumbsTrait;

    protected ?SeoService $seoService = null;

    /**
     * Get SEO Service instance
     */
    protected function seo(): SeoService
    {
        if (!$this->seoService) {
            $this->seoService = app(SeoService::class);
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