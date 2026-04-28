<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
