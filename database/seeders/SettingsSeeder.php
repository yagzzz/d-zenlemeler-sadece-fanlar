<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General ─────────────────────────────────
            [
                'key'         => 'site_name',
                'value'       => 'Sadece Fanlar',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Site adı (başlık ve navigasyonda görünür)',
            ],
            [
                'key'         => 'site_logo_emoji',
                'value'       => '😎',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Site logo emojisi (sidebar avatar)',
            ],
            [
                'key'         => 'site_tagline',
                'value'       => 'İçerik üreticileri için platform',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Site sloganı',
            ],
            [
                'key'         => 'primary_color',
                'value'       => '#d946ef',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Ana renk (hex)',
            ],
            [
                'key'         => 'accent_color',
                'value'       => '#c026d3',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Vurgu rengi (hex)',
            ],
            [
                'key'         => 'home_hero_text',
                'value'       => 'En sevdiğin içerik üreticilerini destekle',
                'type'        => 'text',
                'group'       => 'general',
                'description' => 'Ana sayfa hero metni',
            ],
            [
                'key'         => 'maintenance_mode',
                'value'       => '0',
                'type'        => 'boolean',
                'group'       => 'general',
                'description' => 'Bakım modu (aktifken site erişime kapalı)',
            ],

            // ── Ads ─────────────────────────────────────
            [
                'key'         => 'ads_enabled',
                'value'       => '0',
                'type'        => 'boolean',
                'group'       => 'ads',
                'description' => 'Reklam alanlarını göster',
            ],
            [
                'key'         => 'ad_slot_sidebar',
                'value'       => '',
                'type'        => 'text',
                'group'       => 'ads',
                'description' => 'Sidebar reklam kodu (HTML)',
            ],
            [
                'key'         => 'ad_slot_feed_top',
                'value'       => '',
                'type'        => 'text',
                'group'       => 'ads',
                'description' => 'Feed üst reklam kodu (HTML)',
            ],
            [
                'key'         => 'ad_slot_profile',
                'value'       => '',
                'type'        => 'text',
                'group'       => 'ads',
                'description' => 'Profil sayfası reklam kodu (HTML)',
            ],

            // ── Features ────────────────────────────────
            [
                'key'         => 'feature_registration',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'features',
                'description' => 'Yeni kayıt olma izni',
            ],
            [
                'key'         => 'feature_creator_applications',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'features',
                'description' => 'Creator başvuru sistemi',
            ],
            [
                'key'         => 'feature_tips',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'features',
                'description' => 'Bahşiş sistemi',
            ],
            [
                'key'         => 'feature_comments',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'features',
                'description' => 'Yorum sistemi',
            ],
            [
                'key'         => 'payments_demo_mode',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'features',
                'description' => 'Ödeme demo modu (faturalar otomatik onaylanır)',
            ],
            [
                'key'         => 'footer_text',
                'value'       => '© 2025 Sadece Fanlar. Tüm hakları saklıdır.',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Footer metni',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
