<?php

namespace App\Providers;

use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\DefaultAccessEngine;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccessEngine::class, DefaultAccessEngine::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
