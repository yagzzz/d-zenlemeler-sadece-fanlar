<?php

namespace App\Providers;

use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\DefaultAccessEngine;
use App\Services\Creator\CreatorProfileService;
use App\Services\Media\LocalSignedDriver;
use App\Services\Media\S3CompatibleDriver;
use App\Services\Media\StorageDriver;
use App\Services\TierService;
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
        $this->app->bind(CreatorProfileService::class, CreatorProfileService::class);
        $this->app->bind(TierService::class, TierService::class);
        $this->app->singleton(MockPaymentSimulator::class);
        $this->app->bind(PaymentGateway::class, MockMoneroGateway::class);
        $this->app->bind(StorageDriver::class, function () {
            return match (config('media.driver')) {
                's3', 'r2' => new S3CompatibleDriver(),
                default => new LocalSignedDriver(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
