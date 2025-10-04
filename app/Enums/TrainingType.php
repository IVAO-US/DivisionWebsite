<?php

namespace App\Enums;

enum TrainingType: string
{
    case TRAINING = 'training';
    case EXAM = 'exam';
    case GCA = 'gca';
    case CHECKOUT = 'checkout';

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::TRAINING => 'Training',
            self::EXAM => 'Exam',
            self::GCA => 'GCA',
            self::CHECKOUT => 'Checkout',
        };
    }

    /**
     * Get all available training types as key-value array
     */
    public static function toArray(): array
    {
        return [
            'training' => 'Training',
            'exam' => 'Exam',
            'gca' => 'GCA',
            'checkout' => 'Checkout',
        ];
    }

    /**
     * Create from string value
     */
    public static function fromString(?string $value): ?self
    {
        if (!$value) {
            return null;
        }

        return self::tryFrom(strtolower($value));
    }
}