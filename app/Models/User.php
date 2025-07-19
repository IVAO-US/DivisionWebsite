<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

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

    /**
     * Get the fullname of the member
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}