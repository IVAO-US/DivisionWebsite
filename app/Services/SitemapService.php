<?php

namespace App\Services;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\Route;

class SitemapService
{
    public function generate(): Sitemap
    {
        $sitemap = Sitemap::create();
        
        // Homepage 
        $homepageConfig = config('seotools.sitemap.homepage');
        $url = route('home');
        $sitemap->add(
            Url::create($url)
                ->setLastModificationDate(now())
                ->setChangeFrequency($this->mapFrequency($homepageConfig['frequency']))
                ->setPriority($homepageConfig['priority'])
        );
        
        // Static pages from config
        $this->addStaticPages($sitemap);
        return $sitemap;
    }

    protected function addStaticPages(Sitemap $sitemap): void
    {
        $pages = config('seotools.sitemap.static_pages', []);

        foreach ($pages as $page) {
            if (!Route::has($page['route'])) {
                continue;
            }

            // Add URL 
            $url = route($page['route']);
            $sitemap->add(
                Url::create($url)
                    ->setLastModificationDate(now())
                    ->setChangeFrequency($this->mapFrequency($page['frequency']))
                    ->setPriority($page['priority'])
            );
        }
    }

    /**
     * Map string frequency to Spatie constant
     */
    protected function mapFrequency(string $frequency): string
    {
        return match (strtolower($frequency)) {
            'always' => Url::CHANGE_FREQUENCY_ALWAYS,
            'hourly' => Url::CHANGE_FREQUENCY_HOURLY,
            'daily' => Url::CHANGE_FREQUENCY_DAILY,
            'weekly' => Url::CHANGE_FREQUENCY_WEEKLY,
            'monthly' => Url::CHANGE_FREQUENCY_MONTHLY,
            'yearly' => Url::CHANGE_FREQUENCY_YEARLY,
            'never' => Url::CHANGE_FREQUENCY_NEVER,
            default => Url::CHANGE_FREQUENCY_WEEKLY,
        };
    }
}