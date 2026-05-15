<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Plan;
use App\Models\Template;
use App\Services\TemplateRenderService;

class PageController extends Controller
{
    public function home(TemplateRenderService $renderer)
    {
        $rendered = [];
        $templates = Template::where('is_active', true)
            ->where('type', 'resume')
            ->orderBy('name')
            ->limit(12)
            ->get();

        foreach ($templates as $template) {
            $rendered[$template->id] = (string) $renderer->renderResume($template, null, false);
        }

        return view('pages.home', [
            'professionalTemplates' => $templates,
            'rendered' => $rendered,
            'plans' => Plan::where('is_active', true)->orderBy('price_paise')->get(),
        ]);
    }

    public function templates(TemplateRenderService $renderer)
    {
        $rendered = [];
        $templates = Template::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        foreach ($templates as $template) {
            if ($template->type === 'resume') {
                $rendered[$template->id] = (string) $renderer->renderResume($template, null, false);
            } else {
                $rendered[$template->id] = (string) $renderer->renderCoverLetter($template);
            }
        }

        return view('pages.templates', [
            'templates' => $templates,
            'rendered' => $rendered,
        ]);
    }

    public function interview()
    {
        $posts = Article::where('is_published', true)
            ->latest('published_at')
            ->paginate(10);

        // Get unique categories with counts
        $categories = Article::where('is_published', true)
            ->select('category', \DB::raw('count(*) as posts_count'))
            ->groupBy('category')
            ->get()
            ->map(function($item) {
                return (object)[
                    'name' => $item->category ?: 'General',
                    'slug' => \Str::slug($item->category ?: 'general'),
                    'posts_count' => $item->posts_count
                ];
            });

        $popularPosts = Article::where('is_published', true)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.interview', [
            'posts' => $posts,
            'articles' => $posts,
            'categories' => $categories,
            'popularPosts' => $popularPosts,
        ]);
    }

    public function blogShow($slug)
    {
        $post = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $popularPosts = Article::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog-show', [
            'post' => $post,
            'popularPosts' => $popularPosts,
        ]);
    }
}
