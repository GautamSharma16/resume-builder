<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_slug',
        'status',
        'downloads_allowed',
        'downloads_used',
        'plan_started_at',
        'expiry_date',
        'resume_count',
        'cover_letter_count',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'downloads_allowed' => 'integer',
        'downloads_used' => 'integer',
        'plan_started_at' => 'datetime',
        'expiry_date' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expiry_date
            && $this->expiry_date->isFuture();
    }

    public function hasDownloadsRemaining(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return is_null($this->downloads_allowed)
            || $this->downloads_used < $this->downloads_allowed;
    }
}
