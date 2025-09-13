<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Enums\ATCRating;
use App\Enums\PilotRating;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vid',
        'first_name',
        'last_name',
        'email',
        'rating_atc',
        'rating_pilot',
        'gca',
        'hours_atc',
        'hours_pilot',
        'division',
        'country',
        'staff',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
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
            'rating_atc' => 'integer',
            'rating_pilot' => 'integer',
            'hours_atc' => 'integer',
            'hours_pilot' => 'integer',
        ];
    }
    

    /** Relationships */
    
    /**
     * User settings relationship
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class, 'vid', 'vid');
    }

    /**
     * Admin relationship
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'vid', 'vid');
    }

    
    /** Utility functions */

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->admin !== null;
    }

    /**
     * Check if user has admin permission by string
     */
    public function hasAdminPermissionString(string $permission): bool
    {
        return Admin::hasPermissionString($this->vid, $permission);
    }

    /**
     * Check if user accepts notifications
     */
    public function acceptsNotifications(): bool
    {
        $settings = $this->settings;
        return $settings ? $settings->allow_notifications : true;
    }

    /**
     * Get or create user settings
     */
    public function getOrCreateSettings(): UserSetting
    {
        if (!$this->settings) {
            return $this->settings()->create([
                'vid' => $this->vid,
                'allow_notifications' => true,
            ]);
        }

        return $this->settings;
    }


    /** Enums */

    /**
     * Get the ATC Rating enum instance
     *
     * @return ATCRating|null
     */
    public function getAtcRatingEnumAttribute(): ?ATCRating
    {
        return ATCRating::fromInt($this->rating_atc);
    }

    /**
     * Get the ATC Rating enum instance
     *
     * @return PilotRating|null
     */
    public function getPilotRatingEnumAttribute(): ?PilotRating
    {
        return PilotRating::fromInt($this->rating_pilot);
    }


    /** Accessors */

    /**
     * Get the fullname of the member
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the formatted Staff Positions
     *
     * @return string
     */
    public function getStaffPositionsAttribute(): string
    {
        return str_replace(',', ' ', $this->staff ?? '');
    }

    /**
     * Get the division logo URL
     *
     * @return string
     */
    public function getDivisionLogoAttribute(): string
    {
        if (empty($this->division)) {
            return '/img-ivao/IVAO-logo.png'; // Fallback
        }
        return "https://www.ivao.aero/publrelat/branding/svg_logos/{$this->division}.svg";
    }

    /**
     * Get the effective email (custom email or IVAO email)
     */
    public function getEffectiveEmailAttribute(): string
    {
        $settings = $this->settings;
        return ($settings && $settings->custom_email) ? $settings->custom_email : $this->email;
    }
}