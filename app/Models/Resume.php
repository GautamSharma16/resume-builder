<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'session_id',
        'title',
        'source',
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
