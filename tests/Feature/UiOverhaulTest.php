<?php

use App\Models\Content;
use App\Models\CreatorProfile;
use App\Models\Tier;
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
});

/* ═══════════════════════════════════════════════════════════════════════
   Modal Backdrop Structure
   ═══════════════════════════════════════════════════════════════════════ */

it('tier-modal backdrop has modal-backdrop class', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('id="tier-modal"', false)
        ->assertSee('modal-backdrop', false);
});

it('payment-modal backdrop has modal-backdrop class', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('id="payment-modal"', false);

    $html = $this->get('/')->getContent();
    expect($html)->toContain('class="modal-backdrop absolute inset-0 bg-black/60" data-payment-close');
});

it('tip-modal backdrop has modal-backdrop class', function () {
    $html = $this->get('/')->getContent();
    expect($html)->toContain('class="modal-backdrop absolute inset-0 bg-black/60" data-tip-close');
});

/* ═══════════════════════════════════════════════════════════════════════
   Content Card Template
   ═══════════════════════════════════════════════════════════════════════ */

it('content-card template includes post-avatar element', function () {
    $html = $this->get('/')->getContent();
    expect($html)->toContain('post-avatar');
});

it('content-card template includes post-creator element', function () {
    $html = $this->get('/')->getContent();
    expect($html)->toContain('post-creator');
});

/* ═══════════════════════════════════════════════════════════════════════
   Explore Page
   ═══════════════════════════════════════════════════════════════════════ */

it('explore page has search input', function () {
    $this->get('/explore')
        ->assertOk()
        ->assertSee('Creator veya içerik ara', false);
});

it('explore page has filter buttons', function () {
    $this->get('/explore')
        ->assertOk()
        ->assertSee('explore-filters', false)
        ->assertSee('Trend');
});

it('explore page shows seeded creators', function () {
    $creator = User::factory()->creator()->create(['name' => 'Explore Test']);
    CreatorProfile::create([
        'user_id' => $creator->id,
        'tagline' => 'Test tagline',
    ]);

    $this->get('/explore')
        ->assertOk()
        ->assertSee('Explore Test');
});

it('explore page shows empty state when no creators', function () {
    $this->get('/explore')
        ->assertOk()
        ->assertSee('Henüz creator bulunmuyor');
});

/* ═══════════════════════════════════════════════════════════════════════
   Create Page (Creator Studio)
   ═══════════════════════════════════════════════════════════════════════ */

it('create page has creator studio form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/create')
        ->assertOk()
        ->assertSee('creator-studio', false)
        ->assertSee('create-form', false);
});

it('create page has visibility selector', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/create')
        ->assertOk()
        ->assertSee('visibility-btn', false)
        ->assertSee('upload-zone', false);
});

it('create page has visibility options', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/create')
        ->assertOk()
        ->assertSee('Herkese Açık', false)
        ->assertSee('Aboneler');
});

/* ═══════════════════════════════════════════════════════════════════════
   Inbox Page
   ═══════════════════════════════════════════════════════════════════════ */

it('inbox page shows thread list container', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/inbox')
        ->assertOk()
        ->assertSee('inbox-threads', false)
        ->assertSee('Mesajlar');
});

/* ═══════════════════════════════════════════════════════════════════════
   Profile Page
   ═══════════════════════════════════════════════════════════════════════ */

it('profile page shows settings when logged in', function () {
    $user = User::factory()->create(['name' => 'ProfileUser']);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('profile-settings', false)
        ->assertSee('ProfileUser');
});

it('profile page shows creator application prompt for regular users', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Creator Ol');
});

/* ═══════════════════════════════════════════════════════════════════════
   Creator Show Page
   ═══════════════════════════════════════════════════════════════════════ */

it('creator show page has avatar element with initial', function () {
    $creator = User::factory()->creator()->create(['username' => 'avatar-test', 'name' => 'Zelda']);
    CreatorProfile::create(['user_id' => $creator->id, 'tagline' => 'Test']);

    $this->get('/c/avatar-test')
        ->assertOk()
        ->assertSee('id="creator-avatar"', false);
});

/* ═══════════════════════════════════════════════════════════════════════
   Demo Seeder
   ═══════════════════════════════════════════════════════════════════════ */

it('database seeder creates creators with profiles and content', function () {
    $this->seed();

    expect(User::where('role', 'creator')->count())->toBe(5);
    expect(CreatorProfile::count())->toBe(5);
    expect(Tier::count())->toBe(10); // 2 tiers × 5 creators
    expect(Content::where('is_published', true)->count())->toBeGreaterThanOrEqual(35); // 7 contents × 5 creators
});

it('seeded feed returns content', function () {
    $this->seed();

    $this->getJson('/feed')
        ->assertOk()
        ->assertJsonPath('meta.total', fn ($v) => $v >= 6);
});

it('seeded explore page lists creators', function () {
    $this->seed();

    $this->get('/explore')
        ->assertOk()
        ->assertSee('aylin')
        ->assertSee('kaan')
        ->assertSee('elif');
});

/* ═══════════════════════════════════════════════════════════════════════
   JS Integrity
   ═══════════════════════════════════════════════════════════════════════ */

it('app.js does not contain broken single-quote string', function () {
    $js = file_get_contents(resource_path('js/app.js'));
    expect($js)->not->toContain("'Tier'leri");
});

it('app.js contains ModalManager with modal-backdrop support', function () {
    $js = file_get_contents(resource_path('js/app.js'));
    expect($js)->toContain('.modal-backdrop');
    expect($js)->toContain('ModalManager');
});

it('app.js contains DOMContentLoaded boot guard', function () {
    $js = file_get_contents(resource_path('js/app.js'));
    expect($js)->toContain('DOMContentLoaded');
    expect($js)->toContain('uiEnabled');
});
