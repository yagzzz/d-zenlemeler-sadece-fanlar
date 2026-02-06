<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Content;
use App\Models\CreatorProfile;
use App\Models\Reaction;
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
        // Run admin seeder first
        $this->call(AdminSeeder::class);

        // Run settings seeder
        $this->call(SettingsSeeder::class);

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
            [
                'name' => 'Deniz Koç',
                'username' => 'deniz',
                'tagline' => 'Yemek tarifleri ve food styling 🍳',
                'bio' => 'Şef & yemek fotoğrafçısı. Haftalık tarifler ve mutfak sırları.',
            ],
            [
                'name' => 'Zeynep Akın',
                'username' => 'zeynep',
                'tagline' => 'Yazılım ve teknoloji 💻',
                'bio' => 'Full-stack developer. Kodlama dersleri, proje incelemeleri ve tech hayatı.',
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

            // Extra public posts for variety
            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Yeni Proje Duyurusu',
                'body' => 'Heyecanlı bir proje üzerinde çalışıyorum! Yakında detaylar paylaşacağım. Takipte kalın 🔥',
                'visibility' => 'public',
            ]);

            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Soru & Cevap',
                'body' => 'Yorumlarda sormak istediğiniz her şeyi sorabilirsiniz! Bu hafta tüm sorularınızı yanıtlıyorum.',
                'visibility' => 'public',
            ]);

            // Registered-only content
            Content::factory()->published()->create([
                'creator_id' => $user->id,
                'title' => 'Kayıtlı Kullanıcılara Özel',
                'body' => 'Sadece kayıtlı kullanıcılar görebilir. Giriş yap ve keşfet!',
                'visibility' => 'registered_only',
            ]);
        }

        // ──── Social Interactions ────
        $testUser = User::where('email', 'test@example.com')->first();
        $allContents = Content::where('visibility', 'public')->get();

        // Add comments from test user on first 5 public posts
        foreach ($allContents->take(5) as $content) {
            Comment::create([
                'user_id' => $testUser->id,
                'content_id' => $content->id,
                'body' => 'Harika içerik! Devamını bekliyorum 👏',
            ]);
        }

        // Creator cross-comments
        $creatorUsers = User::where('role', 'creator')->get();
        foreach ($allContents->take(8) as $i => $content) {
            $commenter = $creatorUsers[$i % $creatorUsers->count()];
            if ($commenter->id !== $content->creator_id) {
                Comment::create([
                    'user_id' => $commenter->id,
                    'content_id' => $content->id,
                    'body' => collect(['Çok güzel olmuş! 🔥', 'Eline sağlık!', 'Süper paylaşım 💯', 'Muhteşem!', 'Bu çok ilham verici', 'Bravo! 👏', 'Bayıldım!', 'Harika iş çıkarmışsın'])->random(),
                ]);
            }
        }

        // Reactions — test user likes first 10 public contents
        foreach ($allContents->take(10) as $content) {
            Reaction::create([
                'user_id' => $testUser->id,
                'reactable_type' => Content::class,
                'reactable_id' => $content->id,
                'type' => 'like',
            ]);
        }

        // Creators like each other's posts
        foreach ($allContents->take(15) as $i => $content) {
            $liker = $creatorUsers[$i % $creatorUsers->count()];
            if ($liker->id !== $content->creator_id) {
                Reaction::firstOrCreate([
                    'user_id' => $liker->id,
                    'reactable_type' => Content::class,
                    'reactable_id' => $content->id,
                ], ['type' => 'like']);
            }
        }

        // Bookmarks — test user bookmarks 5 posts
        foreach ($allContents->take(5) as $content) {
            Bookmark::create([
                'user_id' => $testUser->id,
                'content_id' => $content->id,
            ]);
        }

        // Additional social depth — more cross-comments
        $commentBodies = [
            'Kesinlikle katılıyorum! 🙌',
            'Bu tam da aradığım şeydi, teşekkürler!',
            'Daha fazla böyle içerik lütfen 🔥',
            'Vay canına, çok detaylı anlatmışsın',
            'Bunu arkadaşlarıma da göstermem lazım',
            'Ne kadar ilham verici ✨',
            'Harikasın, eline sağlık!',
            'Böyle devam 💪',
            'Bu alanda en iyi içeriği sen üretiyorsun',
            'Yeni başlayanlar için de süper rehber olmuş',
            'Uzun süredir böyle kaliteli içerik arıyordum',
            'Bir sonraki paylaşımını sabırsızlıkla bekliyorum',
        ];

        $allPublicContents = Content::where('visibility', 'public')->get();
        foreach ($allPublicContents as $content) {
            // 2-3 extra comments per public post
            $commenters = $creatorUsers->where('id', '!=', $content->creator_id)->shuffle()->take(rand(2, 3));
            foreach ($commenters as $commenter) {
                Comment::create([
                    'user_id' => $commenter->id,
                    'content_id' => $content->id,
                    'body' => $commentBodies[array_rand($commentBodies)],
                ]);
            }
        }

        // Extra reactions — creators react to all public content
        foreach ($allPublicContents as $content) {
            foreach ($creatorUsers->where('id', '!=', $content->creator_id)->shuffle()->take(3) as $liker) {
                Reaction::firstOrCreate([
                    'user_id' => $liker->id,
                    'reactable_type' => Content::class,
                    'reactable_id' => $content->id,
                ], ['type' => 'like']);
            }
        }

        // Test user comments on more posts
        foreach ($allPublicContents->shuffle()->take(10) as $content) {
            Comment::firstOrCreate([
                'user_id' => $testUser->id,
                'content_id' => $content->id,
            ], [
                'body' => $commentBodies[array_rand($commentBodies)],
            ]);
        }
    }
}