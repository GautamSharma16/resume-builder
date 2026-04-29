<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_slug',
        'item_type',
        'item_id',
        'amount_paise',
        'currency',
        'status',
        'razorpay_payment_link_id',
        'razorpay_payment_link_reference_id',
        'razorpay_payment_link_url',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'webhook_event_id',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'notes' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
