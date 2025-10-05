<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'banner',
        'link',
        'bento_priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bento_priority' => 'boolean',
    ];

    /**
     * Format tour data for bento grid component
     *
     * @return array
     */
    public function toBentoFormat(): array
    {
        return [
            'url' => $this->banner,
            'href' => $this->link,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->bento_priority,
        ];
    }
}