<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CreatorApplicationController as AdminCreatorApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CreatorAnalyticsController;
use App\Http\Controllers\CreatorApplicationController;
use App\Http\Controllers\CreatorContentController;
use App\Http\Controllers\CreatorContentsController;
use App\Http\Controllers\CreatorMediaController;
use App\Http\Controllers\CreatorProfileController;
use App\Http\Controllers\CreatorTierController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicCreatorProfileController;
use App\Http\Controllers\PublicCreatorTierCompareController;
use App\Http\Controllers\PublicCreatorTiersController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SubscriptionTierController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

/* ══════════════════════════════════════════════════════════════════════
   Auth Routes (guest only for login/register forms)
   ══════════════════════════════════════════════════════════════════════ */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* ══════════════════════════════════════════════════════════════════════
   Public Pages (no auth required)
   ══════════════════════════════════════════════════════════════════════ */
Route::get('/', function () {
    return view('feed.index');
});

Route::get('/explore', [ExploreController::class, 'index']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/c/{username}', function (string $username) {
    return view('creator.show', ['username' => $username]);
});

/* ══════════════════════════════════════════════════════════════════════
   Authenticated Page Routes
   ══════════════════════════════════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/create', function () {
        return view('pages.create');
    });

    Route::get('/inbox', function () {
        return view('pages.inbox');
    });

    Route::get('/profile', function () {
        return view('pages.profile');
    });

    Route::get('/notifications', function () {
        return view('pages.notifications');
    });

    Route::get('/bookmarks', function () {
        return view('pages.bookmarks');
    });
});

/* ══════════════════════════════════════════════════════════════════════
   User Settings & Account
   ══════════════════════════════════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::patch('/settings/profile', [UserSettingsController::class, 'update'])->name('settings.profile');
    Route::patch('/settings/preferences', [UserSettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::delete('/settings/account', [AccountController::class, 'destroy'])->name('settings.account.destroy');

    // Keep legacy routes for backward compatibility
    Route::put('/api/user/settings', [UserSettingsController::class, 'update']);
    Route::delete('/api/account', [AccountController::class, 'destroy']);

    // Placeholder pages
    Route::get('/billing', function () {
        return view('pages.billing');
    })->name('billing');

    Route::get('/help', function () {
        return view('pages.help');
    })->name('help');
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
        Route::delete('/content/{content}', [CreatorContentController::class, 'destroy']);
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

Route::prefix('api')
    ->middleware(['auth', 'feature:analytics_stub'])
    ->group(function () {
        Route::get('/creators/{username}/analytics', [CreatorAnalyticsController::class, 'show']);
    });

/* ── Comments ────────────────────────────────────────────────────────── */
Route::prefix('api')
    ->middleware(['feature:comments'])
    ->group(function () {
        Route::get('/contents/{content}/comments', [CommentController::class, 'index']);
        Route::post('/contents/{content}/comments', [CommentController::class, 'store'])
            ->middleware('auth');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
            ->middleware('auth');
    });

/* ── Reactions ───────────────────────────────────────────────────────── */
Route::prefix('api')
    ->middleware(['auth', 'feature:reactions'])
    ->group(function () {
        Route::post('/reactions/toggle', [ReactionController::class, 'toggle']);
    });

/* ── Bookmarks ───────────────────────────────────────────────────────── */
Route::prefix('api')
    ->middleware(['auth', 'feature:bookmarks'])
    ->group(function () {
        Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle']);
        Route::get('/bookmarks', [BookmarkController::class, 'index']);
    });

/* ── Notifications ───────────────────────────────────────────────────── */
Route::prefix('api')
    ->middleware(['auth', 'feature:notifications'])
    ->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read', [NotificationController::class, 'markRead']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    });

/* ── Drafts ──────────────────────────────────────────────────────────── */
Route::prefix('api')
    ->middleware(['auth', 'feature:content_core'])
    ->group(function () {
        Route::post('/contents/{content}/draft', [DraftController::class, 'save']);
    });
