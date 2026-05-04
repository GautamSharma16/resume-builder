<?php

namespace App\Providers;

use App\Models\Plan;
use Illuminate\Support\ServiceProvider;
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
        View::composer('components.plan-download-modal', function ($view) {
            $view->with('plans', Plan::where('is_active', true)->orderBy('price_paise')->get());
        });
    }
}
