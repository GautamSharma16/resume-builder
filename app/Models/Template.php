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
        'pdf_path',
        'sample_data',
        'is_active',
        'has_image',
    ];

    protected $casts = [
        'sample_data' => 'array',
        'is_active' => 'boolean',
        'has_image' => 'boolean',
    ];
}
