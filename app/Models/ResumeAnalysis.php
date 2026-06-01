<?php

namespace App\Models;

use App\Support\Utf8Sanitizer;
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

    protected $casts = [
        'resume_json' => 'array',
        'analysis_json' => 'array',
        'improved_resume_json' => 'array',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saving(function (ResumeAnalysis $model): void {
            foreach ([
                'resume_json',
                'analysis_json',
                'improved_resume_json',
                'extracted_text',
                'job_role',
                'job_description',
                'original_filename',
            ] as $attribute) {
                if (! $model->isDirty($attribute)) {
                    continue;
                }

                $value = $model->{$attribute};

                if (is_array($value)) {
                    $model->{$attribute} = Utf8Sanitizer::jsonSafe($value);
                } elseif (is_string($value)) {
                    $model->{$attribute} = Utf8Sanitizer::cleanString($value);
                }
            }
        });
    }
}
