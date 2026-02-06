# CHANGELOG — Sprint 3: "Her Şey Çalışsın"

## Genel Bakış
Sprint 3, UI'da görünen ama çalışmayan tüm kritik fonksiyonları gerçekten çalışır hale getirir.

---

## 1. AdminSeeder — Varsayılan Admin Kullanıcı
| Dosya | Değişiklik |
|-------|------------|
| `database/seeders/AdminSeeder.php` | **YENİ** — admin@sadecefanlar.local / Admin123! / username: admin / role: admin |
| `database/seeders/DatabaseSeeder.php` | AdminSeeder çağrısı eklendi (SettingsSeeder'dan önce) |

**Kullanım:**
```bash
php artisan db:seed --class=AdminSeeder
# veya tüm seeder'ları çalıştır:
php artisan migrate:fresh --seed
```

**Demo Admin Giriş:**
- Email: `admin@sadecefanlar.local`
- Şifre: `Admin123!`
- Admin Panel: `/admin`

---

## 2. Mesajlaşma Sistemi (Inbox)
| Dosya | Değişiklik |
|-------|------------|
| `database/migrations/2026_03_02_000001_create_conversations_table.php` | **YENİ** — user_one_id, user_two_id, last_message_at |
| `database/migrations/2026_03_02_000002_create_messages_table.php` | **YENİ** — conversation_id, sender_id, body, read_at |
| `app/Models/Conversation.php` | **YENİ** — findOrCreateBetween, hasParticipant, forUser scope |
| `app/Models/Message.php` | **YENİ** — conversation, sender ilişkileri |
| `app/Http/Controllers/InboxController.php` | **YENİ** — index, show, send |
| `resources/views/pages/inbox.blade.php` | **YENİLENDİ** — AJAX/fetch ile gerçek mesajlaşma UI |
| `routes/web.php` | Inbox API rotaları eklendi |

**API Endpoints:**
- `GET /api/inbox` — Kullanıcının konuşmalarını listeler
- `GET /api/inbox/{conversation}` — Konuşma mesajlarını gösterir
- `POST /api/inbox/{user}/send` — Kullanıcıya mesaj gönderir

---

## 3. Ödeme Demo Modu
| Dosya | Değişiklik |
|-------|------------|
| `database/seeders/SettingsSeeder.php` | `payments_demo_mode` ve `footer_text` ayarları eklendi |
| `app/Services/Payments/PaymentService.php` | Demo modda otomatik paid |

---

## 4. Admin Panel Geliştirmeleri
| Dosya | Değişiklik |
|-------|------------|
| `app/Http/Controllers/Admin/AdminDashboardController.php` | Finans istatistikleri |
| `app/Http/Controllers/Admin/AdminInvoiceController.php` | **YENİ** — Fatura yönetimi |
| `resources/views/admin/dashboard.blade.php` | Finans kartları + son faturalar |
| `resources/views/admin/invoices/index.blade.php` | **YENİ** — Fatura listesi |
| `resources/views/admin/layout.blade.php` | Faturalar sidebar linki |

---

## 5. Profil Tercihleri Düzeltmesi
- `UserSettingsController::updatePreferences` — unchecked toggle'lar false olarak kaydediliyor

## 6. Keşfet / Trend Sayfası
- Trending skor: `reactions_count + comments_count + bookmarks_count`
- Filtreler: trending, latest, creators

## 7. UI/UX
- Creator profil butonları ortalandı
- Public UI pages for feed and creator profile, gated by the ui feature flag.

## Public Routes & Contracts (new/changed)
- GET / (UI feed page)
- GET /c/{username} (UI creator page)
- GET /feed (feed contract)
- GET /creators/{user}/contents (public creator contents contract)
- GET /creators/{username}/profile (public creator profile contract)
- GET /creators/{username}/tiers (public creator tiers list)
- GET /api/creators/{username}/tiers/compare (tier compare contract)
- POST /api/creators/{username}/subscribe/invoice (auth)
- POST /api/creators/{username}/subscription/change-tier/invoice (auth)
- POST /api/creators/{username}/tips/invoice (auth)
- POST /api/invoices/{invoice}/verify (auth)
- GET /api/creators/{username}/tips (auth)
- GET /api/contents/{content}/tips
- POST /payments/ppv/invoice (auth)
- GET /media/{mediaAsset}/url

## Feature Flags (introduced/used)
- media_core
- creator_profile
- tiers
- tier_ux
- tips
- ui

## Local Development (minimum)
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- npm install
- npm run dev

## Tests
- php artisan test
- php artisan pint --test

## Integration Plan
1) Enable feature flags incrementally (media_core, creator_profile, tiers, tier_ux, tips, ui).
2) Verify contracts in staging with existing Feature tests.
3) Run Vite build for production deploys.
4) Monitor payments/tips invoice verification logs after rollout.
