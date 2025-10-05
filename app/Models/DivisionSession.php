<?php

namespace App\Models;

use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DivisionSession extends Model
{
    protected $table = 'division_sessions';
    
    protected $fillable = [
        'title',
        'date',
        'time_start',
        'time_end',
        'type',
        'training_details',
        'illustration',
        'description',
        'last_log_id',
    ];

    protected $casts = [
        'date' => 'date',
        'training_details' => 'array',
        'type' => SessionType::class,
    ];

    /**
     * Scope to filter events by type
     */
    public function scopeOfType($query, SessionType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope to get upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('time_start');
    }

    /**
     * Scope to get past events
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now()->toDateString())
            ->orderBy('date', 'desc')
            ->orderBy('time_start', 'desc');
    }

    /**
     * Get formatted date and time
     */
    protected function formattedDateTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->format('Y-m-d') . ' ' . substr($this->time_start, 0, 5) . ' - ' . substr($this->time_end, 0, 5) . ' UTC'
        );
    }

    /**
     * Get formatted description with clickable links and line breaks
     */
    protected function formattedDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->description) {
                    return null;
                }
                
                // Convert line breaks to <br> tags
                $formatted = nl2br(e($this->description));
                
                // Convert URLs to clickable links with external attribute
                $formatted = preg_replace(
                    '/(https?:\/\/[^\s<]+)/i',
                    '<a href="$1" target="_blank" rel="noopener noreferrer external" class="underline">$1</a>',
                    $formatted
                );
                
                return $formatted;
            }
        );
    }

    /**
     * Check if event is a training session
     */
    public function isTraining(): bool
    {
        return $this->type === SessionType::TRAINING;
    }

    /**
     * Check if event is an exam
     */
    public function isExam(): bool
    {
        return $this->type === SessionType::EXAM;
    }
}