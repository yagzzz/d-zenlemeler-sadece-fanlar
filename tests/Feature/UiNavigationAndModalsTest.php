<?php

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('features.flags.ui', true);
    config()->set('features.flags.ui_polish', true);
    config()->set('features.flags.feed', true);
    config()->set('features.flags.content_core', true);
});

/* ── Navigation ──────────────────────────────────────────────────────── */

it('renders bottom nav with working links on every page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('data-nav="bottom"', false);
    $response->assertSee('href="/"', false);
    $response->assertSee('href="/explore"', false);
    $response->assertSee('href="/create"', false);
    $response->assertSee('href="/inbox"', false);
    $response->assertSee('href="/profile"', false);
});

it('renders explore page', function () {
    $this->get('/explore')->assertOk()->assertSee('Keşfet');
});

it('renders create page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/create')->assertOk()->assertSee('İçerik Oluştur');
});

it('renders inbox page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/inbox')->assertOk()->assertSee('Mesajlar');
});

it('renders profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Profil');
});

/* ── Feed empty state ────────────────────────────────────────────────── */

it('shows empty state container when DB has no content', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('data-ui-enabled="1"', false);
    $response->assertSee('feed-empty', false);
    $response->assertSee('feed-skeleton', false);
});

/* ── Feed with content ───────────────────────────────────────────────── */

it('feed API returns content when published content exists', function () {
    $creator = User::factory()->creator()->create();

    Content::factory()->create([
        'creator_id' => $creator->id,
        'title' => 'Test Visible Post',
        'visibility' => 'public',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->getJson('/feed')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonFragment(['title' => 'Test Visible Post']);
});

/* ── Modal markers ───────────────────────────────────────────────────── */

it('renders all three modal containers on the page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="tier-modal"', false);
    $response->assertSee('id="payment-modal"', false);
    $response->assertSee('id="tip-modal"', false);
});

/* ── data-ui-enabled wiring ──────────────────────────────────────────── */

it('sets data-ui-enabled to 1 in local test env', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-ui-enabled="1"', false);
});

it('sets data-app-env attribute on body', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-app-env="testing"', false);
});
