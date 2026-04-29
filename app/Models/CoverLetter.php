<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverLetter extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'resume_id',
        'session_id',
        'job_role',
        'company',
        'data',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_paid' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
