<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreatorApplicationController;
use App\Http\Controllers\CreatorContentController;
use App\Http\Controllers\CreatorContentsController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\CreatorApplicationController as AdminCreatorApplicationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
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

Route::middleware(['feature:feed'])->group(function () {
    Route::get('/feed', [FeedController::class, 'index']);
    Route::get('/creators/{user}/contents', [CreatorContentsController::class, 'index']);
});

Route::middleware(['auth', 'feature:payments_core'])->group(function () {
    Route::post('/payments/subscription/invoice', [PaymentController::class, 'createSubscriptionInvoice']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
});
