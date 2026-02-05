<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreatorApplicationController;
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
