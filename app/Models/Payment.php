<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'purchase_id',
        'plan_slug',
        'amount_paise',
        'currency',
        'status',
        'razorpay_payment_id',
        'webhook_event_id',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}
