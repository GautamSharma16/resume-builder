<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = [
        'visitor_id',
        'visitor_hash',
        'session_id',
        'ip_address',
        'ip_hash',
        'path',
        'user_agent',
        'user_agent_hash',
        'device_hash',
        'first_visited_at',
        'last_visited_at',
    ];

    protected $casts = [
        'first_visited_at' => 'datetime',
        'last_visited_at' => 'datetime',
    ];
}
