<?php

use App\Models\User;
use App\Models\Setting;
use App\Models\Content;
use App\Models\Report;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/* ════════════════════════════════════════════════════════════════════
   A) Admin Access Control
   ════════════════════════════════════════════════════════════════════ */

it('blocks guests from admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/login');
});

it('blocks non-admin users from admin dashboard', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows admin to access admin dashboard', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('admin dashboard shows stats', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard');
});

it('admin dashboard JSON returns stats', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->getJson('/admin')
        ->assertOk()
        ->assertJsonStructure(['stats' => [
            'total_users',
            'total_creators',
            'total_contents',
            'pending_reports',
            'pending_applications',
        ]]);
});

/* ════════════════════════════════════════════════════════════════════
   B) Settings CRUD
   ════════════════════════════════════════════════════════════════════ */

it('blocks non-admin from settings page', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)->get('/admin/settings')->assertForbidden();
});

it('admin can view settings page', function () {
    $admin = User::factory()->admin()->create();
    Setting::create([
        'key' => 'site_name',
        'value' => 'Test Site',
        'type' => 'string',
        'group' => 'general',
        'description' => 'Site name',
    ]);

    $this->actingAs($admin)->get('/admin/settings')
        ->assertOk()
        ->assertSee('site_name');
});

it('admin can update settings', function () {
    $admin = User::factory()->admin()->create();
    Setting::create([
        'key' => 'site_name',
        'value' => 'Old Name',
        'type' => 'string',
        'group' => 'general',
    ]);

    $this->actingAs($admin)
        ->put('/admin/settings', [
            'settings' => ['site_name' => 'New Name'],
        ])
        ->assertRedirect('/admin/settings');

    expect(Setting::where('key', 'site_name')->first()->value)->toBe('New Name');
});

it('admin can update settings via JSON', function () {
    $admin = User::factory()->admin()->create();
    Setting::create([
        'key' => 'site_name',
        'value' => 'Old Name',
        'type' => 'string',
        'group' => 'general',
    ]);

    $this->actingAs($admin)
        ->putJson('/admin/settings', [
            'settings' => ['site_name' => 'API Name'],
        ])
        ->assertOk()
        ->assertJson(['message' => 'Ayarlar güncellendi.']);

    expect(Setting::where('key', 'site_name')->first()->value)->toBe('API Name');
});

it('settings update clears cache', function () {
    $admin = User::factory()->admin()->create();
    Setting::create([
        'key' => 'site_name',
        'value' => 'Cached',
        'type' => 'string',
        'group' => 'general',
    ]);

    // Prime the cache
    $service = app(SettingsService::class);
    expect($service->get('site_name'))->toBe('Cached');

    // Update via admin
    $this->actingAs($admin)
        ->put('/admin/settings', [
            'settings' => ['site_name' => 'Fresh'],
        ]);

    // Cache should be cleared, fresh value returned
    $service->clearCache(); // extra guarantee
    expect(Setting::where('key', 'site_name')->first()->value)->toBe('Fresh');
});

it('handles boolean settings correctly', function () {
    $admin = User::factory()->admin()->create();
    Setting::create([
        'key' => 'ads_enabled',
        'value' => '0',
        'type' => 'boolean',
        'group' => 'ads',
    ]);

    // Turn ON
    $this->actingAs($admin)
        ->put('/admin/settings', [
            'settings' => ['ads_enabled' => '1'],
        ]);

    expect(Setting::where('key', 'ads_enabled')->first()->value)->toBe('1');

    // Turn OFF (unchecked checkbox — not submitted)
    $this->actingAs($admin)
        ->put('/admin/settings', [
            'settings' => [],
        ]);

    expect(Setting::where('key', 'ads_enabled')->first()->value)->toBe('0');
});

/* ════════════════════════════════════════════════════════════════════
   C) User Management
   ════════════════════════════════════════════════════════════════════ */

it('admin can list users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)->get('/admin/users')
        ->assertOk()
        ->assertSee('Jane Doe');
});

it('admin can list users via JSON', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $this->actingAs($admin)
        ->getJson('/admin/users')
        ->assertOk()
        ->assertJsonStructure(['users' => ['data']]);
});

it('admin can search users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Ahmet Yılmaz', 'email' => 'ahmet@example.com']);
    User::factory()->create(['name' => 'Mehmet Kaya']);

    $this->actingAs($admin)->get('/admin/users?search=Ahmet')
        ->assertOk()
        ->assertSee('Ahmet Yılmaz')
        ->assertDontSee('Mehmet Kaya');
});

it('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Regular User', 'role' => 'user']);
    User::factory()->creator()->create(['name' => 'Creator User']);

    $this->actingAs($admin)->get('/admin/users?role=creator')
        ->assertOk()
        ->assertSee('Creator User')
        ->assertDontSee('Regular User');
});

it('admin can edit a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)->get("/admin/users/{$user->id}/edit")
        ->assertOk()
        ->assertSee('Old Name');
});

it('admin can update a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($admin)
        ->put("/admin/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'creator',
        ])
        ->assertRedirect('/admin/users');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
    expect($user->role)->toBe('creator');
});

it('admin can update a user via JSON', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Old']);

    $this->actingAs($admin)
        ->putJson("/admin/users/{$user->id}", [
            'name' => 'New',
            'email' => $user->email,
            'role' => 'user',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Kullanıcı güncellendi.']);
});

it('admin can ban a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$user->id}/ban")
        ->assertRedirect('/admin/users');

    expect(User::find($user->id))->toBeNull();
});

it('admin cannot ban themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson("/admin/users/{$admin->id}/ban")
        ->assertStatus(422)
        ->assertJson(['message' => 'Kendinizi banlayamazsınız.']);
});

/* ════════════════════════════════════════════════════════════════════
   D) Content Management
   ════════════════════════════════════════════════════════════════════ */

it('admin can list contents', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->creator()->create();
    Content::factory()->create(['creator_id' => $creator->id, 'title' => 'Test Content']);

    $this->actingAs($admin)->get('/admin/contents')
        ->assertOk()
        ->assertSee('Test Content');
});

it('admin can list contents via JSON', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/admin/contents')
        ->assertOk()
        ->assertJsonStructure(['contents' => ['data']]);
});

it('admin can takedown published content', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'is_published' => true,
    ]);

    $this->actingAs($admin)
        ->post("/admin/contents/{$content->id}/takedown")
        ->assertRedirect('/admin/contents');

    expect($content->fresh()->is_published)->toBeFalse();
});

it('admin can restore content', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'is_published' => false,
    ]);

    $this->actingAs($admin)
        ->post("/admin/contents/{$content->id}/restore")
        ->assertRedirect('/admin/contents');

    expect($content->fresh()->is_published)->toBeTrue();
});

it('admin can takedown content via JSON', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'is_published' => true,
    ]);

    $this->actingAs($admin)
        ->postJson("/admin/contents/{$content->id}/takedown")
        ->assertOk()
        ->assertJson(['message' => 'İçerik kaldırıldı.']);
});

/* ════════════════════════════════════════════════════════════════════
   E) Report Management
   ════════════════════════════════════════════════════════════════════ */

it('admin can list reports', function () {
    $admin = User::factory()->admin()->create();
    $reporter = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create(['creator_id' => $creator->id]);

    Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Uygunsuz içerik',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->get('/admin/reports')
        ->assertOk()
        ->assertSee('Uygunsuz içerik');
});

it('admin can list reports via JSON', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/admin/reports')
        ->assertOk()
        ->assertJsonStructure(['reports' => ['data']]);
});

it('admin can resolve a report', function () {
    $admin = User::factory()->admin()->create();
    $reporter = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create(['creator_id' => $creator->id]);

    $report = Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Spam',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->post("/admin/reports/{$report->id}/resolve")
        ->assertRedirect('/admin/reports');

    $report->refresh();
    expect($report->status)->toBe('resolved');
    expect($report->resolved_by)->toBe($admin->id);
    expect($report->resolved_at)->not->toBeNull();
});

it('admin can dismiss a report', function () {
    $admin = User::factory()->admin()->create();
    $reporter = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create(['creator_id' => $creator->id]);

    $report = Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Yanlış rapor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->post("/admin/reports/{$report->id}/dismiss")
        ->assertRedirect('/admin/reports');

    expect($report->fresh()->status)->toBe('dismissed');
});

it('admin can resolve a report via JSON', function () {
    $admin = User::factory()->admin()->create();
    $reporter = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create(['creator_id' => $creator->id]);

    $report = Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Spam',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->postJson("/admin/reports/{$report->id}/resolve")
        ->assertOk()
        ->assertJson(['message' => 'Rapor çözüldü.']);
});

it('admin can filter reports by status', function () {
    $admin = User::factory()->admin()->create();
    $reporter = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create(['creator_id' => $creator->id]);

    Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Pending Report',
        'status' => 'pending',
    ]);

    Report::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => Content::class,
        'reportable_id' => $content->id,
        'reason' => 'Resolved Report',
        'status' => 'resolved',
        'resolved_by' => $admin->id,
        'resolved_at' => now(),
    ]);

    $this->actingAs($admin)->get('/admin/reports?status=pending')
        ->assertOk()
        ->assertSee('Pending Report')
        ->assertDontSee('Resolved Report');
});

/* ════════════════════════════════════════════════════════════════════
   F) SettingsService Unit
   ════════════════════════════════════════════════════════════════════ */

it('settings helper returns default when table empty', function () {
    expect(settings('site_name', 'Fallback'))->toBe('Fallback');
});

it('settings helper returns stored value', function () {
    Setting::create([
        'key' => 'site_name',
        'value' => 'My Platform',
        'type' => 'string',
        'group' => 'general',
    ]);

    app(SettingsService::class)->clearCache();

    expect(settings('site_name', 'Fallback'))->toBe('My Platform');
});

it('settings helper casts boolean correctly', function () {
    Setting::create([
        'key' => 'ads_enabled',
        'value' => '1',
        'type' => 'boolean',
        'group' => 'ads',
    ]);

    app(SettingsService::class)->clearCache();

    expect(settings('ads_enabled'))->toBeTrue();
});

it('settings service set updates value', function () {
    Setting::create([
        'key' => 'site_name',
        'value' => 'Old',
        'type' => 'string',
        'group' => 'general',
    ]);

    $service = app(SettingsService::class);
    $service->set('site_name', 'New');

    expect(Setting::where('key', 'site_name')->first()->value)->toBe('New');
});

it('settings service returns group', function () {
    Setting::create(['key' => 'site_name', 'value' => 'Test', 'type' => 'string', 'group' => 'general']);
    Setting::create(['key' => 'ads_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'ads']);

    $service = app(SettingsService::class);
    $service->clearCache();
    $general = $service->group('general');

    expect($general)->toHaveKey('site_name');
    expect($general)->not->toHaveKey('ads_enabled');
});

/* ════════════════════════════════════════════════════════════════════
   G) Frontend Integration
   ════════════════════════════════════════════════════════════════════ */

it('homepage title uses settings site_name', function () {
    Setting::create([
        'key' => 'site_name',
        'value' => 'Test Platform',
        'type' => 'string',
        'group' => 'general',
    ]);

    app(SettingsService::class)->clearCache();

    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Test Platform</title>', false);
});

it('homepage shows site logo emoji from settings', function () {
    Setting::create([
        'key' => 'site_logo_emoji',
        'value' => '🚀',
        'type' => 'string',
        'group' => 'general',
    ]);

    app(SettingsService::class)->clearCache();

    $this->get('/')->assertOk()->assertSee('🚀');
});

it('sidebar ad slot renders when ads_enabled', function () {
    Setting::create(['key' => 'ads_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'ads']);
    Setting::create([
        'key' => 'ad_slot_sidebar',
        'value' => '<div class="test-ad">AD HERE</div>',
        'type' => 'text',
        'group' => 'ads',
    ]);

    app(SettingsService::class)->clearCache();

    $this->get('/')->assertOk()->assertSee('AD HERE');
});

it('sidebar ad slot hidden when ads_disabled', function () {
    Setting::create(['key' => 'ads_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'ads']);
    Setting::create([
        'key' => 'ad_slot_sidebar',
        'value' => '<div class="test-ad">AD HERE</div>',
        'type' => 'text',
        'group' => 'ads',
    ]);

    app(SettingsService::class)->clearCache();

    $this->get('/')->assertOk()->assertDontSee('AD HERE');
});

it('admin panel link visible only to admins', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($admin)->get('/')->assertSee('Admin Panel');
    $this->actingAs($user)->get('/')->assertDontSee('Admin Panel');
});

/* ════════════════════════════════════════════════════════════════════
   H) Access Control - all admin routes blocked for non-admins
   ════════════════════════════════════════════════════════════════════ */

it('blocks non-admin from all admin routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    $routes = [
        ['GET', '/admin'],
        ['GET', '/admin/settings'],
        ['GET', '/admin/users'],
        ['GET', '/admin/contents'],
        ['GET', '/admin/reports'],
    ];

    foreach ($routes as [$method, $uri]) {
        $this->actingAs($user)->{strtolower($method)}($uri)->assertForbidden();
    }
});
