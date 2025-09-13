<?php

namespace App\Enums;

enum PilotRating: int
{
    case FS0 = 1;
    case FS1 = 2;
    case FS2 = 3;
    case FS3 = 4;
    case PP  = 5;
    case SPP = 6;
    case CP  = 7;
    case ATP = 8;
    case SFI = 9;
    case CFI = 10;

    /**
     * Get the full name for this rating
     */
    public function fullName(): string
    {
        return match($this) {
            self::FS0 => 'Flight Observer',
            self::FS1 => 'Basic Flight Student',
            self::FS2 => 'Flight Student',
            self::FS3 => 'Advanced Flight Student', 
            self::PP  => 'Private Pilot',
            self::SPP => 'Senior Private Pilot',
            self::CP  => 'Commercial Pilot',
            self::ATP => 'Airline Transport Pilot',
            self::SFI => 'Senior Flight Instructor',
            self::CFI => 'Chief Flight Instructor',
        };
    }

    /**
     * Get the image filename for this rating
     */
    public function imageFile(): string
    {
        return match($this) {
            self::FS0 => 'no-rating.webp',
            default => $this->name . '.webp',
        };
    }

    /**
     * Get the full image path
     */
    public function imagePath(): string
    {
        return '../assets/img-ivao/' . $this->imageFile();
    }

    /**
     * Create from integer value with fallback
     */
    public static function fromInt(?int $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
?>