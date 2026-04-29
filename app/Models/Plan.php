<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_paise',
        'downloads_allowed',
        'duration_days',
        'resume_limit',
        'cover_letter_limit',
        'ai_enabled',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'is_active' => 'boolean',
            'downloads_allowed' => 'integer',
            'duration_days' => 'integer',
        ];
    }

    public function getIsUnlimitedAttribute(): bool
    {
        return is_null($this->downloads_allowed);
    }
}
