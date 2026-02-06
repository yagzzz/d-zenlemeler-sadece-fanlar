# SPRINT 2 — CHANGELOG

## Admin Panel + Site Ayarları + Reklam Alanları + Yönetim

### Tarih: Sprint 2
### Branch: `feat/sprint4-access-integration`

---

## A) Admin Rol Sistemi
- Mevcut `role` enum (user/creator/admin) + `isAdmin()` korundu
- `EnsureAdmin` middleware zaten mevcut — tüm admin rotaları koruyor

## B) Admin Panel (/admin)
- **Filament yerine hafif Blade tabanlı panel** tercih edildi (Laravel 12 uyumluluğu + tutarlı mimari)
- Admin layout: `resources/views/admin/layout.blade.php` (sidebar nav + stats)
- Dashboard: `/admin` — istatistikler (kullanıcı, creator, içerik, rapor, başvuru sayıları)
- Tüm admin rotaları `['auth', 'admin']` middleware ile korunuyor

## C) Site Settings (DB-Driven)
- **Migration**: `settings` tablosu (key, value, type, group, description)
- **Model**: `App\Models\Setting` — typed_value accessor, group scope
- **Service**: `App\Services\SettingsService` — cache layer (1h TTL), get/set/group/all
- **Helper**: `settings($key, $default)` global fonksiyon
- **Seeder**: `SettingsSeeder` — 15 default setting (general/ads/features)
- **Admin UI**: `/admin/settings` — gruplu form (Genel, Reklam, Özellik)

### Default Settings:
| Key | Group | Type | Default |
|-----|-------|------|---------|
| site_name | general | string | Sadece Fanlar |
| site_logo_emoji | general | string | 😎 |
| site_tagline | general | string | İçerik üreticileri için platform |
| primary_color | general | string | #d946ef |
| accent_color | general | string | #c026d3 |
| home_hero_text | general | text | En sevdiğin içerik üreticilerini destekle |
| maintenance_mode | general | boolean | false |
| ads_enabled | ads | boolean | false |
| ad_slot_sidebar | ads | text | (boş) |
| ad_slot_feed_top | ads | text | (boş) |
| ad_slot_profile | ads | text | (boş) |
| feature_registration | features | boolean | true |
| feature_creator_applications | features | boolean | true |
| feature_tips | features | boolean | true |
| feature_comments | features | boolean | true |

## D) Yönetim Ekranları

### Kullanıcı Yönetimi (`/admin/users`)
- Liste, arama (isim/email/username), rol filtresi
- Düzenleme: isim, email, rol değişikliği
- Banlama: kullanıcı silme (self-ban koruması)

### İçerik Yönetimi (`/admin/contents`)
- Liste, arama (başlık/gövde), durum filtresi
- Takedown: yayından kaldırma
- Restore: yeniden yayına alma

### Rapor/Moderasyon (`/admin/reports`)
- **Yeni tablo**: `reports` (reporter_id, reportable_type/id, reason, status, resolved_by/at)
- **Model**: `App\Models\Report` — polymorphic morph, scopes (pending/resolved/dismissed)
- Liste, durum filtresi (pending/resolved/dismissed)
- Çöz / Reddet aksiyonları

### Creator Başvuruları (`/admin/creator-applications`)
- Mevcut akış korundu, feature flag gating eklendi

## E) Frontend Entegrasyonu
- `<title>` → `settings('site_name', ...)` dinamik
- Guest avatar → `settings('site_logo_emoji', '😎')` dinamik
- Guest site adı → `settings('site_name', 'Sadece Fanlar')` dinamik
- Mobile nav avatar → dinamik emoji
- Admin link → `/admin` (önceki `/admin/creator-applications`)
- Sidebar reklam slotu → `ads_enabled` + `ad_slot_sidebar` koşullu render

---

## Buton Mapping Tablosu

| Buton / Aksiyon | Route | Method | Controller | Validation | JSON Response | UI |
|---|---|---|---|---|---|---|
| Dashboard görüntüle | GET /admin | GET | AdminDashboardController@index | — | `{stats: {...}}` | admin/dashboard |
| Ayarları görüntüle | GET /admin/settings | GET | AdminSettingsController@index | — | `{settings: {...}}` | admin/settings/index |
| Ayarları kaydet | PUT /admin/settings | PUT | AdminSettingsController@update | `settings: present\|array` | `{message: "Ayarlar güncellendi."}` | redirect /admin/settings |
| Kullanıcı listele | GET /admin/users | GET | AdminUserController@index | search?, role? | `{users: paginated}` | admin/users/index |
| Kullanıcı düzenle | GET /admin/users/{id}/edit | GET | AdminUserController@edit | — | `{user: {...}}` | admin/users/edit |
| Kullanıcı güncelle | PUT /admin/users/{id} | PUT | AdminUserController@update | `name, email, role` | `{message, user}` | redirect /admin/users |
| Kullanıcı banla | POST /admin/users/{id}/ban | POST | AdminUserController@ban | self-ban check | `{message}` | redirect /admin/users |
| İçerik listele | GET /admin/contents | GET | AdminContentController@index | search?, status? | `{contents: paginated}` | admin/contents/index |
| İçerik kaldır | POST /admin/contents/{id}/takedown | POST | AdminContentController@takedown | — | `{message}` | redirect /admin/contents |
| İçerik yayınla | POST /admin/contents/{id}/restore | POST | AdminContentController@restore | — | `{message}` | redirect /admin/contents |
| Rapor listele | GET /admin/reports | GET | AdminReportController@index | status? | `{reports: paginated}` | admin/reports/index |
| Rapor çöz | POST /admin/reports/{id}/resolve | POST | AdminReportController@resolve | — | `{message}` | redirect /admin/reports |
| Rapor reddet | POST /admin/reports/{id}/dismiss | POST | AdminReportController@dismiss | — | `{message}` | redirect /admin/reports |

---

## Test Sonuçları
- **209 test**, **692 assertion** — tümü ✅
- **42 yeni test** (AdminPanelTest.php)
- Build: ✅ green
