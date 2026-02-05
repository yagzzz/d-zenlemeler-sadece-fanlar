<?php

use App\Http\Controllers\Admin\CreatorApplicationController as AdminCreatorApplicationController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CreatorApplicationController;
use App\Http\Controllers\CreatorContentController;
use App\Http\Controllers\CreatorContentsController;
use App\Http\Controllers\CreatorMediaController;
use App\Http\Controllers\CreatorProfileController;
use App\Http\Controllers\CreatorTierController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicCreatorProfileController;
use App\Http\Controllers\PublicCreatorTierCompareController;
use App\Http\Controllers\PublicCreatorTiersController;
use App\Http\Controllers\SubscriptionTierController;
use App\Http\Controllers\TipController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('feed.index');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/c/{username}', function (string $username) {
    return view('creator.show', ['username' => $username]);
});

Route::middleware(['auth', 'feature:creator_applications'])
    ->post('/creator/apply', [CreatorApplicationController::class, 'apply']);

Route::prefix('admin')
    ->middleware(['auth', 'admin', 'feature:creator_applications'])
    ->group(function () {
        Route::get('/creator-applications', [AdminCreatorApplicationController::class, 'index']);
        Route::post('/creator-applications/{application}/approve', [AdminCreatorApplicationController::class, 'approve']);
        Route::post('/creator-applications/{application}/reject', [AdminCreatorApplicationController::class, 'reject']);
    });

Route::prefix('creator')
    ->middleware(['auth', 'feature:content_core'])
    ->group(function () {
        Route::post('/content', [CreatorContentController::class, 'store']);
        Route::patch('/content/{content}', [CreatorContentController::class, 'update']);
        Route::post('/content/{content}/publish', [CreatorContentController::class, 'publish']);
    });

Route::prefix('creator')
    ->middleware(['auth', 'feature:media_core'])
    ->group(function () {
        Route::post('/media/initiate', [CreatorMediaController::class, 'initiate']);
        Route::post('/contents/{content}/media/attach', [CreatorMediaController::class, 'attach']);
    });

Route::prefix('creator')
    ->middleware(['auth', 'feature:creator_profile'])
    ->group(function () {
        Route::get('/profile', [CreatorProfileController::class, 'show']);
        Route::put('/profile', [CreatorProfileController::class, 'update']);
    });

Route::prefix('creator')
    ->middleware(['auth', 'feature:tiers'])
    ->group(function () {
        Route::get('/tiers', [CreatorTierController::class, 'index']);
        Route::post('/tiers', [CreatorTierController::class, 'store']);
        Route::patch('/tiers/{tier}', [CreatorTierController::class, 'update']);
        Route::delete('/tiers/{tier}', [CreatorTierController::class, 'destroy']);
    });

Route::middleware(['feature:feed'])->group(function () {
    Route::get('/feed', [FeedController::class, 'index']);
    Route::get('/creators/{user}/contents', [CreatorContentsController::class, 'index']);
});

Route::middleware(['auth', 'feature:payments_core'])->group(function () {
    Route::post('/payments/subscription/invoice', [PaymentController::class, 'createSubscriptionInvoice']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
});

Route::middleware(['auth', 'feature:ppv_core'])->group(function () {
    Route::post('/payments/ppv/invoice', [PaymentController::class, 'createPpvInvoice']);
});

Route::middleware(['feature:media_core'])->group(function () {
    Route::get('/media/{mediaAsset}/url', [MediaController::class, 'viewUrl'])
        ->middleware('content.access');
});

Route::middleware(['feature:creator_profile'])->group(function () {
    Route::get('/creators/{username}/profile', [PublicCreatorProfileController::class, 'show']);
});

Route::middleware(['feature:tiers'])->group(function () {
    Route::get('/creators/{username}/tiers', [PublicCreatorTiersController::class, 'index']);
});

Route::prefix('api')
    ->middleware(['feature:tips'])
    ->group(function () {
        Route::post('/creators/{username}/tips/invoice', [TipController::class, 'createCreatorTipInvoice'])
            ->middleware('auth');
        Route::post('/invoices/{invoice}/verify', [TipController::class, 'verifyInvoice'])
            ->middleware('auth');
        Route::get('/creators/{username}/tips', [TipController::class, 'listCreatorTips'])
            ->middleware('auth');
        Route::get('/contents/{content}/tips', [TipController::class, 'contentTipAggregate']);
    });

Route::prefix('api')
    ->middleware(['feature:tier_ux'])
    ->group(function () {
        Route::get('/creators/{username}/tiers/compare', [PublicCreatorTierCompareController::class, 'show']);
        Route::post('/creators/{username}/subscribe/invoice', [SubscriptionTierController::class, 'createInvoice'])
            ->middleware('auth');
        Route::post('/creators/{username}/subscription/change-tier/invoice', [SubscriptionTierController::class, 'changeTierInvoice'])
            ->middleware('auth');
    });

Route::prefix('api')
    ->middleware(['feature:content_show'])
    ->group(function () {
        Route::get('/contents/{content}', [ContentController::class, 'show'])
            ->middleware('content.access');
    });
