<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Format virtual airline data for carousel component
     *
     * @return array
     */
    public function toCarouselFormat(): array
    {
        return [
            'title' => $this->name,
            'date' => $this->formatted_hubs,
            'description' => $this->description,
            'image' => $this->banner,
            'link' => $this->link,
        ];
    }
}