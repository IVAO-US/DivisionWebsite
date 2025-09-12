<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vid',
        'custom_email',
        'discord',
        'allow_notifications',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vid' => 'integer',
            'allow_notifications' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vid', 'vid');
    }

    /**
     * Get the effective email for the user (custom_email or fallback to user email)
     */
    public function getEffectiveEmailAttribute(): string
    {
        return $this->custom_email ?: $this->user->email;
    }
}