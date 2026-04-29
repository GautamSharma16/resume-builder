<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Template;

class PageController extends Controller
{
    public function templates()
    {
        return view('pages.templates', [
            'templates' => Template::where('is_active', true)->latest()->get(),
        ]);
    }

    public function interview()
    {
        return view('pages.interview', [
            'articles' => Article::where('is_published', true)->latest('published_at')->get(),
        ]);
    }
}
