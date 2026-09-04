<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            $this->url(route('home'), now(), 'daily', '1.0'),
            $this->url(route('templates'), now(), 'weekly', '0.9'),
            $this->url(route('resume.create'), now(), 'weekly', '0.9'),
            $this->url(route('resume-maker'), now(), 'weekly', '0.9'),
            $this->url(route('improve-cv'), now(), 'weekly', '0.9'),
            $this->url(route('ats-checker'), now(), 'weekly', '0.85'),
            $this->url(route('cover-letter'), now(), 'weekly', '0.85'),
            $this->url(route('interview'), now(), 'daily', '0.8'),
            $this->url(route('plans'), now(), 'weekly', '0.7'),
            $this->url(route('contact'), now(), 'monthly', '0.5'),
            $this->url(route('privacy'), now(), 'monthly', '0.3'),
            $this->url(route('terms'), now(), 'monthly', '0.3'),
        ]);

        foreach (['ats', 'fresher', 'experienced'] as $category) {
            $urls->push($this->url(route('resume-maker', ['category' => $category]), now(), 'weekly', '0.8'));
        }

        Article::where('is_published', true)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->get(['slug', 'updated_at', 'published_at'])
            ->each(function (Article $article) use ($urls) {
                $urls->push($this->url(
                    route('blog.show', $article->slug),
                    $article->updated_at ?? $article->published_at,
                    'weekly',
                    '0.7'
                ));
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /reset-password',
            'Disallow: /verify-otp',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function url(string $loc, Carbon $lastmod, string $changefreq, string $priority): array
    {
        return compact('loc', 'lastmod', 'changefreq', 'priority');
    }
}
