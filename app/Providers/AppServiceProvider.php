<?php

namespace App\Providers;

use App\Models\Plan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') && filled(config('app.url'))) {
            URL::forceRootUrl(rtrim(config('app.url'), '/'));
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $request = request();
            $routeName = optional($request->route())->getName();
            // Only evergreen public content is indexable. Workspace, auth, payment,
            // and duplicate builder URLs remain available but do not compete in search.
            $indexableRoutes = [
                'home',
                'enhance-cv',
                'cover-letter',
                'templates',
                'interview',
                'blog.show',
                'resources',
                'contact',
                'privacy',
                'terms',
                'plans',
                'resume.create',
            ];

            $shouldIndex = $routeName
                && $request->isMethod('GET')
                && collect($indexableRoutes)->contains(fn (string $pattern) => Str::is($pattern, $routeName));

            $canonical = null;

            if ($shouldIndex) {
                $canonical = rtrim(config('app.url'), '/').'/'.ltrim($request->path(), '/');
                $canonical = rtrim($canonical, '/') ?: rtrim(config('app.url'), '/');
            }

            $view->with([
                'seoShouldIndex' => $shouldIndex,
                'seoCanonicalUrl' => $canonical,
            ]);
        });

        View::composer('components.plan-download-modal', function ($view) {
            $view->with('plans', Plan::where('is_active', true)->orderBy('price_paise')->get());
        });
    }
}
