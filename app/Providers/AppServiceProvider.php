<?php

namespace App\Providers;

use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\DefaultAccessEngine;
use App\Services\Payments\MockMoneroGateway;
use App\Services\Payments\MockPaymentSimulator;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccessEngine::class, DefaultAccessEngine::class);
        $this->app->singleton(MockPaymentSimulator::class);
        $this->app->bind(PaymentGateway::class, MockMoneroGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
