<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\CreatorProfile;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ──── Demo Creators ────
        $creators = [
            [
                'name' => 'Aylin Yıldız',
                'username' => 'aylin',
                'tagline' => 'Dijital sanat ve illüstrasyon ✨',
                'bio' => 'Freelance dijital sanatçı. Her hafta yeni çizimler paylaşıyorum.',
            ],
            [
                'name' => 'Kaan Demir',
                'username' => 'kaan',
                'tagline' => 'Fitness ve sağlıklı yaşam 💪',
                'bio' => 'Kişisel antrenör. Egzersiz programları ve beslenme tüyoları.',
            ],
            [
                'name' => 'Elif Seren',
                'username' => 'elif',
                'tagline' => 'Müzik prodüksiyon ve remix 🎵',
                'bio' => 'Beatmaker & prodüktör. Yeni track\'ler ve behind-the-scenes.',
            ],
        ];

        foreach ($creators as $c) {
            $user = User::factory()->creator()->create([
                'name' => $c['name'],
                'username' => $c['username'],
                'email' => $c['username'] . '@example.com',
            ]);

            CreatorProfile::create([
                'user_id' => $user->id,
                'tagline' => $c['tagline'],
                'bio' => $c['bio'],
                'wallet_xmr_address' => 'demo_xmr_' . $c['username'],
                'currency' => 'XMR',
                'allow_free_content' => true,
                'allow_registered_only' => true,
                'allow_ppv' => true,
                'allow_tips' => true,
            ]);

            // Two tiers per creator
            $basic = Tier::create([
                'creator_id' => $user->id,
                'name' => 'Fan',
                'description' => 'Temel erişim — tüm kayıtlı içerikler',
                'tier_level' => 1,
                'price_atomic' => 5000,
                'yearly_price_atomic' => 50000,
                'yearly_duration_days' => 365,
                'currency' => 'XMR',
                'duration_days' => 30,
                'is_active' => true,
                'position' => 1,
            ]);

            $premium = Tier::create([
                'creator_id' => $user->id,
                'name' => 'Süper Fan',
                'description' => 'Premium erişim — tüm içerikler + özel',
                'tier_level' => 2,
                'price_atomic' => 15000,
                'yearly_price_atomic' => 150000,
                'yearly_duration_days' => 365,
                'currency' => 'XMR',
                'duration_days' => 30,
                'is_active' => true,
                'position' => 2,
                'is_most_popular' => true,
            ]);

            // Two public contents
            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => $c['name'] . ' — Hoş Geldin!',
                'body' => 'Merhaba! Ben ' . $c['name'] . '. ' . $c['bio'] . ' Profilimde paylaştığım içerikleri keşfet!',
                'visibility' => 'public',
            ]);

            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Haftalık Güncelleme',
                'body' => 'Bu haftaki planlar ve yeni içerikler hakkında detaylar…',
                'visibility' => 'public',
            ]);

            // One subscriber-only content
            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Abonelere Özel İçerik',
                'body' => 'Bu içerik sadece abone olan fan\'lar tarafından görülebilir.',
                'visibility' => 'subscriber_only',
                'required_tier_id' => $basic->id,
            ]);

            // One PPV content
            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Premium Koleksiyon',
                'body' => 'Satın alındığında erişilebilen özel içerik.',
                'visibility' => 'ppv',
                'ppv_price_atomic' => 3000,
            ]);
        }
    }
}
