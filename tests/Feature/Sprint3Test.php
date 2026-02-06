<?php

use App\Models\Conversation;
use App\Models\Content;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Setting;
use App\Models\Tip;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/* ════════════════════════════════════════════════════════════════════
   A) Admin Seeder
   ════════════════════════════════════════════════════════════════════ */

it('AdminSeeder creates admin user', function () {
    $this->seed(\Database\Seeders\AdminSeeder::class);

    $admin = User::where('email', 'admin@sadecefanlar.local')->first();
    expect($admin)->not->toBeNull();
    expect($admin->role)->toBe('admin');
    expect($admin->username)->toBe('admin');
    expect($admin->isAdmin())->toBeTrue();
});

it('AdminSeeder is idempotent', function () {
    $this->seed(\Database\Seeders\AdminSeeder::class);
    $this->seed(\Database\Seeders\AdminSeeder::class);

    expect(User::where('email', 'admin@sadecefanlar.local')->count())->toBe(1);
});

/* ════════════════════════════════════════════════════════════════════
   B) Messaging System — API
   ════════════════════════════════════════════════════════════════════ */

it('guests cannot access inbox API', function () {
    $this->getJson('/api/inbox')->assertUnauthorized();
});

it('authenticated user can list conversations (empty)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/inbox')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('user can send a message to another user', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $response = $this->actingAs($sender)
        ->postJson("/api/inbox/{$receiver->id}/send", ['body' => 'Merhaba!'])
        ->assertCreated()
        ->assertJsonPath('message.body', 'Merhaba!');

    $this->assertDatabaseHas('messages', [
        'sender_id' => $sender->id,
        'body' => 'Merhaba!',
    ]);

    $this->assertDatabaseHas('conversations', [
        'user_one_id' => min($sender->id, $receiver->id),
        'user_two_id' => max($sender->id, $receiver->id),
    ]);
});

it('user cannot send message to self', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson("/api/inbox/{$user->id}/send", ['body' => 'Hello'])
        ->assertUnprocessable();
});

it('user can view conversation messages', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conv = Conversation::findOrCreateBetween($userA->id, $userB->id);
    Message::create([
        'conversation_id' => $conv->id,
        'sender_id' => $userB->id,
        'body' => 'Test mesaj',
    ]);

    $response = $this->actingAs($userA)
        ->getJson("/api/inbox/{$conv->id}")
        ->assertOk()
        ->assertJsonPath('conversation_id', $conv->id);
});

it('user cannot view others conversation', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $conv = Conversation::findOrCreateBetween($userA->id, $userB->id);

    $this->actingAs($userC)
        ->getJson("/api/inbox/{$conv->id}")
        ->assertForbidden();
});

it('viewing conversation marks messages as read', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conv = Conversation::findOrCreateBetween($userA->id, $userB->id);
    $message = Message::create([
        'conversation_id' => $conv->id,
        'sender_id' => $userB->id,
        'body' => 'Okunmamış mesaj',
    ]);

    expect($message->read_at)->toBeNull();

    $this->actingAs($userA)->getJson("/api/inbox/{$conv->id}")->assertOk();

    $message->refresh();
    expect($message->read_at)->not->toBeNull();
});

it('conversations list shows unread count', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conv = Conversation::findOrCreateBetween($userA->id, $userB->id);
    $conv->update(['last_message_at' => now()]);

    Message::create([
        'conversation_id' => $conv->id,
        'sender_id' => $userB->id,
        'body' => 'Unread 1',
    ]);
    Message::create([
        'conversation_id' => $conv->id,
        'sender_id' => $userB->id,
        'body' => 'Unread 2',
    ]);

    $response = $this->actingAs($userA)
        ->getJson('/api/inbox')
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['unread_count'])->toBe(2);
});

it('message body is required', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $this->actingAs($sender)
        ->postJson("/api/inbox/{$receiver->id}/send", ['body' => ''])
        ->assertUnprocessable();
});

/* ════════════════════════════════════════════════════════════════════
   C) Conversation Model
   ════════════════════════════════════════════════════════════════════ */

it('findOrCreateBetween normalizes user order', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conv1 = Conversation::findOrCreateBetween($a->id, $b->id);
    $conv2 = Conversation::findOrCreateBetween($b->id, $a->id);

    expect($conv1->id)->toBe($conv2->id);
    expect($conv1->user_one_id)->toBe(min($a->id, $b->id));
    expect($conv1->user_two_id)->toBe(max($a->id, $b->id));
});

it('hasParticipant returns correct results', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $c = User::factory()->create();

    $conv = Conversation::findOrCreateBetween($a->id, $b->id);

    expect($conv->hasParticipant($a->id))->toBeTrue();
    expect($conv->hasParticipant($b->id))->toBeTrue();
    expect($conv->hasParticipant($c->id))->toBeFalse();
});

/* ════════════════════════════════════════════════════════════════════
   D) Payment Demo Mode
   ════════════════════════════════════════════════════════════════════ */

it('payments_demo_mode setting exists in seeder', function () {
    $this->seed(\Database\Seeders\SettingsSeeder::class);

    expect(Setting::where('key', 'payments_demo_mode')->exists())->toBeTrue();
});

it('footer_text setting exists in seeder', function () {
    $this->seed(\Database\Seeders\SettingsSeeder::class);

    expect(Setting::where('key', 'footer_text')->exists())->toBeTrue();
});

/* ════════════════════════════════════════════════════════════════════
   E) Admin Invoice Management
   ════════════════════════════════════════════════════════════════════ */

it('admin can view invoices page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/invoices')->assertOk();
});

it('non-admin cannot access invoices page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/invoices')->assertForbidden();
});

it('admin can mark invoice as paid', function () {
    $admin = User::factory()->admin()->create();
    $payer = User::factory()->create();
    $payee = User::factory()->creator()->create();

    $invoice = Invoice::create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'invoice_type' => 'tip',
        'status' => 'pending',
        'amount_atomic' => 5000,
        'currency' => 'XMR',
        'address' => 'test_addr',
        'payment_reference' => 'test_ref_' . uniqid(),
        'expires_at' => now()->addHour(),
    ]);

    $this->actingAs($admin)
        ->post("/admin/invoices/{$invoice->id}/mark-paid")
        ->assertRedirect('/admin/invoices');

    $invoice->refresh();
    expect($invoice->status)->toBe('paid');
    expect($invoice->paid_at)->not->toBeNull();
});

it('admin can mark invoice as failed', function () {
    $admin = User::factory()->admin()->create();
    $payer = User::factory()->create();
    $payee = User::factory()->creator()->create();

    $invoice = Invoice::create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'invoice_type' => 'subscription',
        'status' => 'pending',
        'amount_atomic' => 10000,
        'currency' => 'XMR',
        'address' => 'test_addr',
        'payment_reference' => 'test_ref_' . uniqid(),
        'expires_at' => now()->addHour(),
    ]);

    $this->actingAs($admin)
        ->post("/admin/invoices/{$invoice->id}/mark-failed")
        ->assertRedirect('/admin/invoices');

    $invoice->refresh();
    expect($invoice->status)->toBe('cancelled');
});

it('admin cannot mark already paid invoice as paid again', function () {
    $admin = User::factory()->admin()->create();
    $payer = User::factory()->create();
    $payee = User::factory()->creator()->create();

    $invoice = Invoice::create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'invoice_type' => 'tip',
        'status' => 'paid',
        'amount_atomic' => 5000,
        'currency' => 'XMR',
        'address' => 'test_addr',
        'payment_reference' => 'test_ref_' . uniqid(),
        'expires_at' => now()->addHour(),
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->postJson("/admin/invoices/{$invoice->id}/mark-paid")
        ->assertStatus(422);
});

/* ════════════════════════════════════════════════════════════════════
   F) Admin Dashboard Finance Stats
   ════════════════════════════════════════════════════════════════════ */

it('admin dashboard includes finance stats', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/admin')
        ->assertOk()
        ->assertJsonStructure([
            'stats' => [
                'total_users',
                'total_creators',
                'total_contents',
                'pending_reports',
                'pending_applications',
                'total_tips',
                'total_tips_amount',
                'total_subscriptions',
                'total_invoices',
                'paid_invoices',
            ],
            'recent_invoices',
        ]);
});

/* ════════════════════════════════════════════════════════════════════
   G) Profile Preferences
   ════════════════════════════════════════════════════════════════════ */

it('user can save notification preferences', function () {
    $user = User::factory()->create([
        'notification_preferences' => [
            'new_subscription' => true,
            'tip_notification' => true,
            'new_message' => true,
        ],
    ]);

    $this->actingAs($user)
        ->patchJson('/settings/preferences', [
            'new_subscription' => false,
            'tip_notification' => true,
            'new_message' => false,
        ])
        ->assertOk()
        ->assertJsonPath('preferences.new_subscription', false)
        ->assertJsonPath('preferences.tip_notification', true)
        ->assertJsonPath('preferences.new_message', false);

    $user->refresh();
    expect($user->notification_preferences['new_subscription'])->toBeFalse();
    expect($user->notification_preferences['tip_notification'])->toBeTrue();
    expect($user->notification_preferences['new_message'])->toBeFalse();
});

it('preferences persist after refresh', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/settings/preferences', [
            'new_subscription' => false,
            'tip_notification' => false,
            'new_message' => true,
        ])
        ->assertOk();

    // Re-fetch user from DB
    $freshUser = User::find($user->id);
    $prefs = $freshUser->getNotificationPreferencesWithDefaults();

    expect($prefs['new_subscription'])->toBeFalse();
    expect($prefs['tip_notification'])->toBeFalse();
    expect($prefs['new_message'])->toBeTrue();
});

it('unchecked preferences default to false', function () {
    $user = User::factory()->create([
        'notification_preferences' => [
            'new_subscription' => true,
            'tip_notification' => true,
            'new_message' => true,
        ],
    ]);

    // Send empty form request — all toggles unchecked (form submissions omit unchecked fields)
    $this->actingAs($user)
        ->patch('/settings/preferences', [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('preferences.new_subscription', false)
        ->assertJsonPath('preferences.tip_notification', false)
        ->assertJsonPath('preferences.new_message', false);
});

/* ════════════════════════════════════════════════════════════════════
   H) Explore / Trending
   ════════════════════════════════════════════════════════════════════ */

it('explore page renders with trending filter', function () {
    $this->get('/explore')->assertOk();
    $this->get('/explore?filter=trending')->assertOk();
    $this->get('/explore?filter=latest')->assertOk();
    $this->get('/explore?filter=creators')->assertOk();
});

it('explore API returns trending scored contents', function () {
    $creator = User::factory()->creator()->create();

    Content::factory()->published()->create([
        'creator_id' => $creator->id,
        'visibility' => 'public',
        'title' => 'Test Content',
    ]);

    $this->getJson('/explore?filter=trending')
        ->assertOk()
        ->assertJsonStructure([
            'creators',
            'contents' => [
                '*' => ['id', 'title', 'reactions_count', 'comments_count', 'bookmarks_count', 'trending_score'],
            ],
            'filter',
        ]);
});

it('explore search works', function () {
    $creator = User::factory()->creator()->create(['name' => 'Zeynep Test']);

    $this->getJson('/explore?q=Zeynep')
        ->assertOk()
        ->assertJsonCount(1, 'creators');
});

/* ════════════════════════════════════════════════════════════════════
   I) Inbox Page
   ════════════════════════════════════════════════════════════════════ */

it('inbox page requires authentication', function () {
    $this->get('/inbox')->assertRedirect('/login');
});

it('inbox page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/inbox')
        ->assertOk()
        ->assertSee('Mesajlar');
});

/* ════════════════════════════════════════════════════════════════════
   J) Settings Seeder Additions
   ════════════════════════════════════════════════════════════════════ */

it('settings seeder creates all expected settings', function () {
    $this->seed(\Database\Seeders\SettingsSeeder::class);

    $expectedKeys = [
        'site_name', 'site_logo_emoji', 'site_tagline', 'primary_color', 'accent_color',
        'home_hero_text', 'maintenance_mode', 'ads_enabled', 'ad_slot_sidebar',
        'ad_slot_feed_top', 'ad_slot_profile', 'feature_registration',
        'feature_creator_applications', 'feature_tips', 'feature_comments',
        'payments_demo_mode', 'footer_text',
    ];

    foreach ($expectedKeys as $key) {
        expect(Setting::where('key', $key)->exists())->toBeTrue("Setting {$key} should exist");
    }
});

/* ════════════════════════════════════════════════════════════════════
   K) Admin Invoice Filtering
   ════════════════════════════════════════════════════════════════════ */

it('admin can filter invoices by status', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/admin/invoices?status=pending')
        ->assertOk();
});

it('admin can filter invoices by type', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/admin/invoices?type=tip')
        ->assertOk();
});

/* ════════════════════════════════════════════════════════════════════
   L) Creator Show Button Centering
   ════════════════════════════════════════════════════════════════════ */

it('creator show page renders with centered buttons', function () {
    $this->get('/c/testuser')
        ->assertOk()
        ->assertSee('justify-content:center');
});

/* ════════════════════════════════════════════════════════════════════
   M) Feature Flag — Messaging
   ════════════════════════════════════════════════════════════════════ */

it('messaging feature flag exists in config', function () {
    expect(config('features.flags.messaging'))->toBeTrue();
});
