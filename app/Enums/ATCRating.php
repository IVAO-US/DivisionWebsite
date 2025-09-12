<?php

namespace App\Enums;

enum ATCRating: int
{
    case AS0 = 1;
    case AS1 = 2;
    case AS2 = 3;
    case AS3 = 4;
    case ADC = 5;
    case APC = 6;
    case ACC = 7;
    case SEC = 8;
    case SAI = 9;
    case CAI = 10;

    /**
     * Get the full name for this rating
     */
    public function fullName(): string
    {
        return match($this) {
            self::AS0 => 'ATC Observer',
            self::AS1 => 'ATC Applicant',
            self::AS2 => 'ATC Trainee',
            self::AS3 => 'Advanced ATC Trainee', 
            self::ADC => 'Aerodrome Controller',
            self::APC => 'Approach Controller',
            self::ACC => 'Center Controller',
            self::SEC => 'Senior Controller',
            self::SAI => 'Senior ATC Instructor',
            self::CAI => 'Chief ATC Instructor',
        };
    }

    /**
     * Get the image filename for this rating
     */
    public function imageFile(): string
    {
        return match($this) {
            self::AS0 => 'no-rating.webp',
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