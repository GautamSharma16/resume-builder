<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'created_by',
        'type',
        'name',
        'slug',
        'category',
        'html',
        'preview_image',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
