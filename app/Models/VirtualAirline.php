<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class VirtualAirline extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icao_code',
        'hubs',
        'description',
        'banner',
        'link',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hubs' => 'array',
    ];

    /**
     * Get formatted hubs display for carousel
     * Format: "ICAO | Bases: HUB1 - HUB2 - HUB3"
     *
     * @return string
     */
    public function getFormattedHubsAttribute(): string
    {
        if (empty($this->hubs) || !is_array($this->hubs)) {
            return "{$this->icao_code} | Bases: No bases";
        }
            
        $hubsString = implode(' - ', $this->hubs);
        return "{$this->icao_code} | Bases: {$hubsString}";
    }

    /**
     * Get formatted description with clickable links and line breaks
     */
    protected function formattedDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->description) {
                    return null;
                }
                
                // Convert line breaks to <br> tags
                $formatted = nl2br(e($this->description));
                
                // Convert URLs to clickable links with external attribute
                $formatted = preg_replace(
                    '/(https?:\/\/[^\s<]+)/i',
                    '<a href="$1" target="_blank" rel="noopener noreferrer external" class="underline">$1</a>',
                    $formatted
                );
                
                return $formatted;
            }
        );
    }

    /**
     * Format virtual airline data for carousel component
     *
     * @return array
     */
    public function toCarouselFormat(): array
    {
        return [
            'title' => $this->name,
            'date' => $this->formatted_hubs,
            'description' => $this->formatted_description,
            'image' => $this->banner,
            'link' => $this->link,
        ];
    }
}