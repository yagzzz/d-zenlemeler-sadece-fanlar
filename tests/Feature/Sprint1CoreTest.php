<?php

/**
 * Sprint 1 — Core Functionality Tests
 *
 * Tests: Profile save, Notification preferences, Account delete,
 *        "Daha Fazla" menu, Placeholder pages, Auth flow.
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('features.flags.ui', true);
    config()->set('features.flags.ui_polish', true);
    config()->set('features.flags.creator_applications', true);
});

/* ═══════════════════════════════════════════════════════════════════════
   A) Auth Flow
   ═══════════════════════════════════════════════════════════════════════ */

it('login page renders for guest', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Giriş Yap');
});

it('register page renders for guest', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('Kayıt Ol');
});

it('authenticated user is redirected from login page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/login')
        ->assertRedirect('/');
});

it('authenticated user is redirected from register page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/register')
        ->assertRedirect('/');
});

it('login with valid credentials succeeds', function () {
    $user = User::factory()->create(['password' => bcrypt('testpass123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'testpass123',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('login with wrong password fails', function () {
    $user = User::factory()->create(['password' => bcrypt('testpass123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ])->assertSessionHasErrors('email');
});

it('register creates new user and logs in', function () {
    $this->post('/register', [
        'name' => 'Yeni Kullanıcı',
        'username' => 'yenikullanici',
        'email' => 'yeni@test.com',
        'password' => 'sifre12345',
        'password_confirmation' => 'sifre12345',
    ])->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'yeni@test.com',
        'username' => 'yenikullanici',
        'role' => 'user',
    ]);
});

it('logout works and redirects to login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('guest cannot access profile page', function () {
    $this->get('/profile')
        ->assertRedirect('/login');
});

/* ═══════════════════════════════════════════════════════════════════════
   B) Profile Settings — PATCH /settings/profile
   ═══════════════════════════════════════════════════════════════════════ */

it('can update profile name and username', function () {
    $user = User::factory()->create([
        'name' => 'Eski İsim',
        'username' => 'eskikullanici',
    ]);

    $this->actingAs($user)
        ->patchJson('/settings/profile', [
            'name' => 'Yeni İsim',
            'username' => 'yenikullanici',
        ])
        ->assertOk()
        ->assertJson([
            'message' => 'Ayarlar güncellendi.',
            'user' => [
                'name' => 'Yeni İsim',
                'username' => 'yenikullanici',
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe('Yeni İsim');
    expect($user->username)->toBe('yenikullanici');
});

it('profile update requires name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/profile', [
            'name' => '',
            'username' => 'validuser',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('profile update requires username', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/profile', [
            'name' => 'Valid Name',
            'username' => '',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('username must be unique', function () {
    $existing = User::factory()->create(['username' => 'takenname']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/profile', [
            'name' => 'Test',
            'username' => 'takenname',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('user can keep their own username', function () {
    $user = User::factory()->create(['username' => 'myname']);

    $this->actingAs($user)
        ->patchJson('/settings/profile', [
            'name' => 'Updated Name',
            'username' => 'myname',
        ])
        ->assertOk();
});

it('guest cannot update profile', function () {
    $this->patchJson('/settings/profile', [
        'name' => 'Test',
        'username' => 'test',
    ])->assertUnauthorized();
});

/* Legacy route still works */
it('legacy PUT /api/user/settings still works', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson('/api/user/settings', [
            'name' => 'Legacy Name',
            'username' => 'legacyuser',
        ])
        ->assertOk()
        ->assertJsonPath('user.name', 'Legacy Name');
});

/* ═══════════════════════════════════════════════════════════════════════
   C) Notification Preferences — PATCH /settings/preferences
   ═══════════════════════════════════════════════════════════════════════ */

it('can update notification preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/preferences', [
            'new_subscription' => false,
        ])
        ->assertOk()
        ->assertJson([
            'message' => 'Bildirim tercihleri güncellendi.',
            'preferences' => [
                'new_subscription' => false,
                'tip_notification' => true,
                'new_message' => true,
            ],
        ]);

    $user->refresh();
    $prefs = $user->getNotificationPreferencesWithDefaults();
    expect($prefs['new_subscription'])->toBeFalse();
    expect($prefs['tip_notification'])->toBeTrue();
    expect($prefs['new_message'])->toBeTrue();
});

it('can update multiple preferences at once', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/preferences', [
            'new_subscription' => false,
            'new_message' => false,
        ])
        ->assertOk()
        ->assertJsonPath('preferences.new_subscription', false)
        ->assertJsonPath('preferences.new_message', false)
        ->assertJsonPath('preferences.tip_notification', true);
});

it('preferences default to true for new user', function () {
    $user = User::factory()->create();
    $prefs = $user->getNotificationPreferencesWithDefaults();

    expect($prefs)->toBe([
        'new_subscription' => true,
        'tip_notification' => true,
        'new_message' => true,
    ]);
});

it('guest cannot update preferences', function () {
    $this->patchJson('/settings/preferences', [
        'new_subscription' => false,
    ])->assertUnauthorized();
});

/* ═══════════════════════════════════════════════════════════════════════
   D) Account Delete — DELETE /settings/account
   ═══════════════════════════════════════════════════════════════════════ */

it('can delete account with correct password', function () {
    $user = User::factory()->create(['password' => bcrypt('mypassword')]);
    $userId = $user->id;

    $this->actingAs($user)
        ->deleteJson('/settings/account', [
            'current_password' => 'mypassword',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Hesap silindi.']);

    $this->assertDatabaseMissing('users', ['id' => $userId]);
    $this->assertGuest();
});

it('cannot delete account with wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('mypassword')]);

    $this->actingAs($user)
        ->deleteJson('/settings/account', [
            'current_password' => 'wrongpassword',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('current_password');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

it('cannot delete account without password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->deleteJson('/settings/account', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('current_password');
});

it('guest cannot delete account', function () {
    $this->deleteJson('/settings/account', [
        'current_password' => 'test',
    ])->assertUnauthorized();
});

/* Legacy route still works */
it('legacy DELETE /api/account still works', function () {
    $user = User::factory()->create(['password' => bcrypt('testpass')]);
    $userId = $user->id;

    $this->actingAs($user)
        ->deleteJson('/api/account', [
            'current_password' => 'testpass',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $userId]);
});

/* ═══════════════════════════════════════════════════════════════════════
   E) "Daha Fazla" Menu & Placeholder Pages
   ═══════════════════════════════════════════════════════════════════════ */

it('layout has "Daha Fazla" menu with dropdown', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('more-menu-toggle');
    expect($html)->toContain('more-menu-dropdown');
    expect($html)->toContain('Daha Fazla');
});

it('dropdown contains Ayarlar link', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('Ayarlar');
    expect($html)->toContain('href="/profile"');
});

it('dropdown contains Ödeme & Faturalama link', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('Ödeme & Faturalama');
    expect($html)->toContain('href="/billing"');
});

it('dropdown contains Yardım link', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('Yardım');
    expect($html)->toContain('href="/help"');
});

it('dropdown shows Admin Panel for admin user', function () {
    $admin = User::factory()->admin()->create();
    $html = $this->actingAs($admin)->get('/')->getContent();

    expect($html)->toContain('Admin Panel');
    expect($html)->toContain('href="/admin"');
});

it('dropdown hides Admin Panel for regular user', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->not->toContain('Admin Panel');
});

it('dropdown shows Creator Başvurusu for regular user', function () {
    $user = User::factory()->create(['role' => 'user']);
    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain('Creator Başvurusu');
});

it('billing page renders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/billing')
        ->assertOk()
        ->assertSee('Ödeme', false);
});

it('help page renders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/help')
        ->assertOk()
        ->assertSee('Yardım');
});

it('guest cannot access billing page', function () {
    $this->get('/billing')
        ->assertRedirect('/login');
});

it('guest cannot access help page', function () {
    $this->get('/help')
        ->assertRedirect('/login');
});

/* ═══════════════════════════════════════════════════════════════════════
   F) Profile Page UI Elements
   ═══════════════════════════════════════════════════════════════════════ */

it('profile page has settings form', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->toContain('id="settings-name"');
    expect($html)->toContain('id="settings-username"');
    expect($html)->toContain('id="save-settings-btn"');
});

it('profile page has notification toggles with data-pref-key', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->toContain('data-pref-key="new_subscription"');
    expect($html)->toContain('data-pref-key="tip_notification"');
    expect($html)->toContain('data-pref-key="new_message"');
    expect($html)->toContain('toggle-switch');
    expect($html)->toContain('toggle-input');
});

it('profile page has delete account button and modal', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->toContain('id="delete-account-btn"');
    expect($html)->toContain('id="delete-account-modal"');
    expect($html)->toContain('id="delete-confirm-password"');
    expect($html)->toContain('id="delete-confirm-btn"');
    expect($html)->toContain('id="delete-cancel-btn"');
});

it('profile page has creator apply button for regular user', function () {
    $user = User::factory()->create(['role' => 'user']);
    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->toContain('id="creator-apply-btn"');
    expect($html)->toContain('Creator Ol');
});

it('profile page hides creator apply for creator user', function () {
    $user = User::factory()->creator()->create();
    $html = $this->actingAs($user)->get('/profile')->getContent();

    expect($html)->not->toContain('id="creator-apply-btn"');
});

/* ═══════════════════════════════════════════════════════════════════════
   G) Branding
   ═══════════════════════════════════════════════════════════════════════ */

it('page title is Sadece Fanlar', function () {
    config()->set('app.name', 'Sadece Fanlar');
    $html = $this->get('/')->getContent();
    expect($html)->toContain('<title>Sadece Fanlar</title>');
});

it('guest sidebar shows Sadece Fanlar branding', function () {
    $html = $this->get('/')->getContent();
    expect($html)->toContain('Sadece Fanlar');
});
