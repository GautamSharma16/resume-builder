<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'is_published',
        'published_at',
        'thumbnail',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
