<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdprDeletionLog extends Model
{
    protected $fillable = [
        'user_vid',
        'user_full_name',
        'user_email',
        'admin_vid',
        'admin_name',
        'control_key',
        'deleted_data',
        'reason',
        'executed_at'
    ];

    protected $casts = [
        'deleted_data' => 'array',
        'executed_at' => 'datetime'
    ];

    /**
     * Get the admin who executed the deletion
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_vid', 'vid');
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutedAtAttribute(): string
    {
        return $this->executed_at->format('Y-m-d H:i:s');
    }

    /**
     * Get summary of deleted data
     */
    public function getDeletedDataSummaryAttribute(): string
    {
        $summary = [];
        
        if (isset($this->deleted_data['user'])) {
            $summary[] = 'User profile';
        }
        
        if (isset($this->deleted_data['user_settings'])) {
            $summary[] = 'User settings';
        }
        
        if (isset($this->deleted_data['operative'])) {
            $summary[] = 'Operative data';
        }
        
        if (isset($this->deleted_data['admin'])) {
            $summary[] = 'Admin privileges';
        }
        
        if (isset($this->deleted_data['polls_created']) && count($this->deleted_data['polls_created']) > 0) {
            $pollCount = count($this->deleted_data['polls_created']);
            $summary[] = "{$pollCount} created poll(s)";
        }
        
        if (isset($this->deleted_data['poll_votes']) && count($this->deleted_data['poll_votes']) > 0) {
            $voteCount = count($this->deleted_data['poll_votes']);
            $summary[] = "{$voteCount} poll vote(s)";
        }
        
        if (isset($this->deleted_data['sessions']) && count($this->deleted_data['sessions']) > 0) {
            $sessionCount = count($this->deleted_data['sessions']);
            $summary[] = "{$sessionCount} session(s)";
        }
        
        return implode(', ', $summary);
    }
}