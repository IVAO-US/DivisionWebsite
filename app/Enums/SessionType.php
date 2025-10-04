<?php

namespace App\Enums;

enum SessionType: string
{
    case EVENT = 'event';
    case EXAM = 'exam';
    case TRAINING = 'training';
    case GCA = 'gca';
    case ONLINE_DAY = 'online_day';

    /**
     * Get all available session types
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::EVENT => 'Event',
            self::EXAM => 'Exam',
            self::TRAINING => 'Training',
            self::GCA => 'Guest Controller Approval',
            self::ONLINE_DAY => 'Online Day',
        };
    }
}