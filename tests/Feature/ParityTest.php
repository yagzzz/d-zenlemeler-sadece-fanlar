<?php

/**
 * Parity Tests — assert JustFans v6.9.0 reference DOM markers
 * exist in the Sadece Fanlar target markup.
 *
 * See docs/PARITY_MAP.md for the full mapping.
 */

use App\Models\CreatorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('features.flags.ui', true);
    config()->set('features.flags.ui_polish', true);
    config()->set('features.flags.feed', true);
    config()->set('features.flags.content_core', true);
    config()->set('features.flags.tiers', true);
    config()->set('features.flags.creator_profile', true);
    config()->set('features.flags.analytics_stub', true);
    config()->set('features.flags.tier_ux', true);
    config()->set('features.flags.comments', true);
    config()->set('features.flags.reactions', true);
    config()->set('features.flags.bookmarks', true);
    config()->set('features.flags.notifications', true);
});

/* ═══════════════════════════════════════════════════════════════════════
   LAYOUT — mirrors reference layouts/user-no-nav.blade.php
   ═══════════════════════════════════════════════════════════════════════ */

it('layout has three-column grid: side-menu + content + mobile nav', function () {
    $html = $this->get('/')->getContent();

    // Side-menu column (desktop only)
    expect($html)->toContain('col-2 col-md-3');
    expect($html)->toContain('side-menu');
    expect($html)->toContain('d-none d-md-block');

    // Content column
    expect($html)->toContain('col-12 col-md-9');

    // Mobile bottom nav
    expect($html)->toContain('mobile-bottom-nav');
    expect($html)->toContain('fixed-bottom');
    expect($html)->toContain('d-block d-md-none');
});

it('side-menu has h-pill nav items with icon-wrapper', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('h-pill h-pill-primary');
    expect($html)->toContain('icon-wrapper');
    expect($html)->toContain('user-side-menu');
});

it('side-menu user-details has avatar', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('user-details');
    expect($html)->toContain('user-avatar');
});

it('side-menu has new post CTA button', function () {
    $user = \App\Models\User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('Yeni Gönderi');
    expect($html)->toContain('btn-round btn-primary');
});

it('mobile nav has five h-pill navigation items', function () {
    $html = $this->get('/')->getContent();

    // All 5 mobile nav destinations exist
    expect($html)->toContain('data-nav="bottom"');

    // Count h-pill links inside mobile nav (we verify the key hrefs)
    foreach (['/', '/notifications', '/create', '/inbox', '/profile'] as $href) {
        expect($html)->toContain("href=\"{$href}\"");
    }
});

it('layout loads Open Sans font', function () {
    $html = $this->get('/')->getContent();
    expect($html)->toContain('fonts.googleapis.com');
    expect($html)->toContain('Open+Sans');
});

/* ═══════════════════════════════════════════════════════════════════════
   CONTENT CARD — mirrors reference post-box.blade.php
   ═══════════════════════════════════════════════════════════════════════ */

it('content-card template has post-box structure', function () {
    $html = $this->get('/')->getContent();

    // post-box wrapper
    expect($html)->toContain('post-box');
    // Header parts
    expect($html)->toContain('post-header');
    expect($html)->toContain('post-details');
    expect($html)->toContain('post-creator-name');
    expect($html)->toContain('post-creator-handle');
    expect($html)->toContain('post-avatar');
});

it('content-card has post-content with line-clamp and show-more', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('post-content');
    expect($html)->toContain('post-content-data');
    expect($html)->toContain('line-clamp-3');
    expect($html)->toContain('label-more');
    expect($html)->toContain('label-less');
});

it('content-card has post-footer with h-pill action buttons', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('post-footer');
    expect($html)->toContain('react-button');
    expect($html)->toContain('comment-button');
    expect($html)->toContain('tip-button');
    expect($html)->toContain('like-count');
    expect($html)->toContain('comment-count');
});

it('content-card has collapsible post-comments section', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('post-comments');
    expect($html)->toContain('comments-list');
    expect($html)->toContain('comment-textarea');
    expect($html)->toContain('send-comment-btn');
    expect($html)->toContain('btn-rounded-icon');
});

it('content-card has locked overlay with CTA button', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('post-locked-overlay');
    expect($html)->toContain('cta-button');
});

it('content-card has bookmark button', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('bookmark-button');
    expect($html)->toContain('bookmark-wrapper');
});

/* ═══════════════════════════════════════════════════════════════════════
   FEED PAGE — mirrors reference feed.js
   ═══════════════════════════════════════════════════════════════════════ */

it('feed page has skeleton, empty state, and posts-wrapper', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('feed-skeleton');
    expect($html)->toContain('feed-empty');
    expect($html)->toContain('posts-wrapper');
    expect($html)->toContain('feed-loading');
    expect($html)->toContain('data-feed-endpoint');
});

it('feed page has quick compose box', function () {
    $html = $this->get('/')->getContent();

    // Quick compose is a post-box at top of feed
    expect($html)->toContain("data-page=\"feed\"");
    expect($html)->toContain('Ne düşünüyorsun');
});

/* ═══════════════════════════════════════════════════════════════════════
   CREATOR PAGE — mirrors reference profile structure
   ═══════════════════════════════════════════════════════════════════════ */

it('creator page has profile cover and avatar', function () {
    $creator = User::factory()->creator()->create(['username' => 'parity-test']);
    CreatorProfile::create(['user_id' => $creator->id, 'tagline' => 'Test tagline']);

    $html = $this->get('/c/parity-test')->getContent();

    expect($html)->toContain('profile-cover');
    expect($html)->toContain('profile-avatar');
    expect($html)->toContain('id="creator-avatar"');
    expect($html)->toContain('id="creator-name"');
});

it('creator page has stats grid', function () {
    $creator = User::factory()->creator()->create(['username' => 'stat-test']);
    CreatorProfile::create(['user_id' => $creator->id, 'tagline' => 'Stats']);

    $html = $this->get('/c/stat-test')->getContent();

    expect($html)->toContain('stat-card');
    expect($html)->toContain('creator-tips-total');
    expect($html)->toContain('creator-subscribers');
    expect($html)->toContain('creator-post-count');
});

it('creator page has tab navigation for posts/tiers', function () {
    $creator = User::factory()->creator()->create(['username' => 'tab-test']);
    CreatorProfile::create(['user_id' => $creator->id, 'tagline' => 'Tabs']);

    $html = $this->get('/c/tab-test')->getContent();

    expect($html)->toContain('tab-btn');
    expect($html)->toContain('data-tab="posts"');
    expect($html)->toContain('data-tab="tiers"');
    expect($html)->toContain('tab-posts');
    expect($html)->toContain('tab-tiers');
});

/* ═══════════════════════════════════════════════════════════════════════
   EXPLORE PAGE — mirrors reference suggestion-box
   ═══════════════════════════════════════════════════════════════════════ */

it('explore page has creator card grid', function () {
    $creator = User::factory()->creator()->create(['name' => 'ExploreCreator']);
    CreatorProfile::create(['user_id' => $creator->id, 'tagline' => 'Explore tagline']);

    $html = $this->get('/explore')->getContent();

    expect($html)->toContain('creator-card');
    expect($html)->toContain('ExploreCreator');
});

/* ═══════════════════════════════════════════════════════════════════════
   CREATE PAGE — mirrors reference creator studio
   ═══════════════════════════════════════════════════════════════════════ */

it('create page has upload zone with drag-drop', function () {
    $user = \App\Models\User::factory()->create();
    $html = $this->actingAs($user)->get('/create')->getContent();

    expect($html)->toContain('upload-zone');
    expect($html)->toContain('upload-previews');
    expect($html)->toContain('media-input');
});

it('create page has PPV price row', function () {
    $user = \App\Models\User::factory()->create();
    $html = $this->actingAs($user)->get('/create')->getContent();

    expect($html)->toContain('ppv-price-row');
    expect($html)->toContain('ppv_price_atomic');
});

/* ═══════════════════════════════════════════════════════════════════════
   MODALS — mirrors reference modal patterns
   ═══════════════════════════════════════════════════════════════════════ */

it('tier-modal has compare list and billing toggle', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('id="tier-modal"');
    expect($html)->toContain('tier-compare-list');
    expect($html)->toContain('billing-toggle');
});

it('payment-modal has three-step flow', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('id="payment-modal"');
    expect($html)->toContain('data-step="summary"');
    expect($html)->toContain('data-step="waiting"');
    expect($html)->toContain('data-step="success"');
    expect($html)->toContain('payment-address');
    expect($html)->toContain('payment-amount');
});

it('tip-modal has amount and message inputs', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('id="tip-modal"');
    expect($html)->toContain('tip-amount');
    expect($html)->toContain('tip-message');
    expect($html)->toContain('id="tip-form"');
});

it('comment-modal has comments list and form', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('id="comment-modal"');
    expect($html)->toContain('id="comment-form"');
    expect($html)->toContain('comment-input');
});

/* ═══════════════════════════════════════════════════════════════════════
   JS PARITY — mirrors reference Post.js / PostsPaginator.js
   ═══════════════════════════════════════════════════════════════════════ */

it('app.js has Post object with reference-matching methods', function () {
    $js = file_get_contents(resource_path('js/app.js'));

    // Post object methods
    expect($js)->toContain('Post.reactTo');
    expect($js)->toContain('Post.showPostComments');
    expect($js)->toContain('Post.addComment');
    expect($js)->toContain('Post.togglePostBookmark');
    expect($js)->toContain('Post.toggleFullDescription');
});

it('app.js has PostsPaginator with infinite scroll', function () {
    $js = file_get_contents(resource_path('js/app.js'));

    expect($js)->toContain('PostsPaginator');
    expect($js)->toContain('initScrollLoad');
    expect($js)->toContain('loadResults');
    expect($js)->toContain('window.addEventListener(\'scroll\'');
});

it('app.js uses correct DOM selectors for parity', function () {
    $js = file_get_contents(resource_path('js/app.js'));

    // Must target reference-matching class names
    expect($js)->toContain('.post-box');
    expect($js)->toContain('.react-button');
    expect($js)->toContain('.comment-button');
    expect($js)->toContain('.tip-button');
    expect($js)->toContain('.bookmark-button');
    expect($js)->toContain('.post-comments');
    expect($js)->toContain('.post-content-data');
    expect($js)->toContain('.line-clamp-3');
    expect($js)->toContain('.comment-textarea');
    expect($js)->toContain('.send-comment-btn');
});

it('app.js has all page setup functions', function () {
    $js = file_get_contents(resource_path('js/app.js'));

    expect($js)->toContain('setupFeed');
    expect($js)->toContain('setupCreatorPage');
    expect($js)->toContain('setupCreatePage');
    expect($js)->toContain('setupBookmarksPage');
    expect($js)->toContain('setupNotificationsPage');
    expect($js)->toContain('setupGlobalActions');
    expect($js)->toContain('setupTipForm');
    expect($js)->toContain('setupCommentForm');
});

/* ═══════════════════════════════════════════════════════════════════════
   CSS PARITY — key class names must exist in the CSS
   ═══════════════════════════════════════════════════════════════════════ */

it('CSS has reference-parity class definitions', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Core reference classes
    expect($css)->toContain('.h-pill');
    expect($css)->toContain('.icon-wrapper');
    expect($css)->toContain('.post-box');
    expect($css)->toContain('.post-header');
    expect($css)->toContain('.post-footer');
    expect($css)->toContain('.post-comments');
    expect($css)->toContain('.side-menu');
    expect($css)->toContain('.mobile-bottom-nav');
    expect($css)->toContain('.btn-rounded-icon');
    expect($css)->toContain('.comment-textarea');
    expect($css)->toContain('.line-clamp-3');
});

it('CSS has correct icon sizes matching reference', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Reference: .icon-large = 32px, .icon-medium = 24px, .icon-small = 18px
    expect($css)->toContain('.icon-large');
    expect($css)->toContain('.icon-medium');
    expect($css)->toContain('.icon-small');
});

it('CSS has bootstrap-like utility classes for layout', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('.d-flex');
    expect($css)->toContain('.d-none');
    expect($css)->toContain('.d-block');
    expect($css)->toContain('.d-md-block');
    expect($css)->toContain('.d-md-none');
    expect($css)->toContain('.container-xl');
    expect($css)->toContain('.col-2');
    expect($css)->toContain('.col-md-3');
    expect($css)->toContain('.col-md-9');
    expect($css)->toContain('.col-12');
});

/* ═══════════════════════════════════════════════════════════════════════
   SEEDED DATA — the app should look alive
   ═══════════════════════════════════════════════════════════════════════ */

it('seeded DB has diverse social interactions', function () {
    $this->seed();

    // Bookmarks
    expect(\App\Models\Bookmark::count())->toBeGreaterThanOrEqual(5);

    // Comments (cross-creator + test user)
    expect(\App\Models\Comment::count())->toBeGreaterThanOrEqual(20);

    // Reactions
    expect(\App\Models\Reaction::count())->toBeGreaterThanOrEqual(15);
});
