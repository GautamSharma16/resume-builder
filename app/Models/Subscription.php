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

    public function downloadsRemaining(): ?int
    {
        if (is_null($this->downloads_allowed)) {
            return null;
        }

        return max(0, $this->downloads_allowed - $this->downloads_used);
    }

    public function hasResumeSlotsRemaining(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return is_null($this->plan->resume_limit)
            || $this->resume_count < $this->plan->resume_limit;
    }

    public function resumeRemaining(): ?int
    {
        if (is_null($this->plan->resume_limit)) {
            return null;
        }

        return max(0, $this->plan->resume_limit - $this->resume_count);
    }

    public function incrementResumeCount(): self
    {
        if ($this->isActive()) {
            $this->increment('resume_count');
            $this->refresh();
        }

        return $this;
    }

    public function hasCoverLettersRemaining(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return is_null($this->plan->cover_letter_limit)
            || $this->cover_letter_count < $this->plan->cover_letter_limit;
    }

    public function coverLettersRemaining(): ?int
    {
        if (is_null($this->plan->cover_letter_limit)) {
            return null;
        }

        return max(0, $this->plan->cover_letter_limit - $this->cover_letter_count);
    }

    public function incrementCoverLetterCount(): self
    {
        if ($this->isActive()) {
            $this->increment('cover_letter_count');
            $this->refresh();
        }

        return $this;
    }
}
