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

    /**
     * Get approved ATC positions that can be reported
     */
    public static function approvedPositions(): array
    {
        return ['DEL', 'GND', 'TWR', 'DEP', 'APP', 'CTR', 'FSS'];
    }

    /**
     * Create from string value (for parsing cms_logs data)
     * Maps string ratings like "ASx", "ADC", etc. to enum cases
     */
    public static function fromString(?string $value): ?self
    {
        if (!$value) {
            return null;
        }

        // Normalize the input
        $normalized = strtoupper(trim($value));

        // Map string representations to enum cases
        return match($normalized) {
            'AS0' => self::AS0,
            'AS1' => self::AS1,
            'AS2' => self::AS2,
            'AS3' => self::AS3,
            'ASX' => self::AS3, // ASx is often used for AS3
            'ADC' => self::ADC,
            'APC' => self::APC,
            'ACC' => self::ACC,
            'SEC' => self::SEC,
            'SAI' => self::SAI,
            'CAI' => self::CAI,
            default => null,
        };
    }

    /**
     * Get short code for display (e.g., "AS3", "ADC")
     */
    public function shortCode(): string
    {
        return $this->name;
    }

    /**
     * Get all ratings as array of short codes
     */
    public static function shortCodes(): array
    {
        return array_map(fn($case) => $case->name, self::cases());
    }
}
?>