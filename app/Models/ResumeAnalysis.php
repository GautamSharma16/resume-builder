<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'job_role',
        'job_description',
        'original_filename',
        'extracted_text',
        'resume_json',
        'analysis_json',
        'improved_resume_json',
        'is_paid',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'resume_json' => 'array',
            'analysis_json' => 'array',
            'improved_resume_json' => 'array',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
