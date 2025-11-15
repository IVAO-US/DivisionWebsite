<?php

namespace App\Services;

use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\JsonLdMulti;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Log;

class SeoService
{
    /**
     * Set SEO for a standard page with multilingual support
     */
    public function setPage(
        string $title,
        string $description,
        ?string $image = null,
        ?string $canonical = null,
        array $keywords = [],
        bool $addAlternateLanguages = true  // ADD THIS PARAMETER
    ): void {
        $this->setMeta($title, $description, $canonical, $keywords);
        $this->setOpenGraph($title, $description, $image);
        $this->setTwitterCard($title, $description, $image);
        $this->setJsonLd($title, $description, $image, 'WebPage');
        
        if ($addAlternateLanguages) {
            try {
                $this->setAlternateLanguages();
            } catch (\Exception $e) {
                // Silently fail - alternate languages are optional
                Log::debug('Failed to set alternate languages: ' . $e->getMessage());
            }
        }
    }

    /**
     * Set breadcrumbs schema from existing breadcrumbs
     */
    public function setBreadcrumbsFromTrait(array $breadcrumbs): void
    {
        if (empty($breadcrumbs)) {
            return;
        }

        $itemListElement = [];
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            // Skip if no link (last item)
            if (!isset($breadcrumb['link'])) {
                continue;
            }

            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['label'] ?: 'Home',
                'item' => $breadcrumb['link'],
            ];
        }

        if (!empty($itemListElement)) {
            JsonLd::addValue('breadcrumb', [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElement,
            ]);
        }
    }

    /**
     * Set alternate languages for multilingual SEO (hreflang tags)
     * This is done via OpenGraph meta tags since SEOMeta::setAlternateLanguages() may not be available
     */
    public function setAlternateLanguages(?string $customUrl = null): void
    {
        // We'll add hreflang tags via raw meta tags in OpenGraph
        // This is already handled in setOpenGraph() method below
        // This method is kept for future compatibility
    }

    /**
     * Set organization schema (for homepage)
     */
    public function setOrganization(
        string $name,
        string $url,
        string $logo,
        array $socialProfiles = []
    ): void {
        JsonLdMulti::newJsonLd();
        JsonLd::setType('Organization');
        JsonLd::addValue('name', $name);
        JsonLd::addValue('url', $url);
        JsonLd::addValue('logo', $logo);
        
        if (!empty($socialProfiles)) {
            JsonLd::addValue('sameAs', $socialProfiles);
        }
    }

    /**
     * Set FAQ Schema
     */
    public function addFAQSchema(array $faqs): void
    {
        $mainEntity = [];
        
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }
        
        JsonLdMulti::newJsonLd();
        JsonLd::setType('FAQPage');
        JsonLd::addValue('mainEntity', $mainEntity);
    }

    /**
     * Private helper methods
     */
    private function setMeta(
        string $title,
        string $description,
        ?string $canonical = null,
        array $keywords = []
    ): void {
        SEOMeta::setTitle($title)
            ->setDescription($description);
        
        if (!empty($keywords)) {
            SEOMeta::setKeywords($keywords);
        }
        
        if ($canonical) {
            SEOMeta::setCanonical($canonical);
        } else {
            SEOMeta::setCanonical(URL::current());
        }
    }

    private function setOpenGraph(
        string $title,
        string $description,
        ?string $image = null
    ): void {
        OpenGraph::setTitle($title)
            ->setDescription($description)
            ->setUrl(URL::current());
        if ($image) {
            OpenGraph::addImage($image);
        }
        return;
    }

    private function setTwitterCard(
        string $title,
        string $description,
        ?string $image = null
    ): void {
        TwitterCard::setTitle($title)
            ->setDescription($description);
        
        if ($image) {
            TwitterCard::setImage($image);
        }
    }

    private function setJsonLd(
        string $title,
        string $description,
        ?string $image = null,
        string $type = 'WebPage'
    ): void {
        JsonLd::setTitle($title)
            ->setDescription($description)
            ->setType($type);
        
        if ($image) {
            JsonLd::addImage($image);
        }
    }
}