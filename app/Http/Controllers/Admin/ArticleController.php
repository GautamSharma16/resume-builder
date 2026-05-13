<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        return view('admin.articles.index', ['articles' => Article::latest()->get()]);
    }

    public function create()
    {
        $categories = Article::distinct()->pluck('category')->toArray();
        if (empty($categories)) {
            $categories = ['Freshers', 'Experienced', 'Preparation'];
        }
        return view('admin.articles.create', [
            'article' => new Article(),
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(5);
        $data['author_id'] = $request->user()->id;
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }

        Article::create($data);

        return redirect()->route('admin.articles.index')->with('status', 'Article created.');
    }

    public function edit(Article $article)
    {
        $categories = Article::distinct()->pluck('category')->toArray();
        if (empty($categories)) {
            $categories = ['Freshers', 'Experienced', 'Preparation'];
        }
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request);
        $data['published_at'] = $data['is_published'] ? ($article->published_at ?? now()) : null;

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }

        $article->update($data);

        return redirect()->route('admin.articles.index')->with('status', 'Article updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ]) + ['is_published' => false];
    }
}

