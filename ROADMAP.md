# Sadece Fanlar — Roadmap

## ✅ Phase A — MVP Foundation (Current)

Everything needed for a locally-runnable demo with working social features.

| Feature | Status |
|---|---|
| Content feed (public + gated) | ✅ Done |
| Creator profiles + tiers | ✅ Done |
| Subscriptions (monthly / yearly) | ✅ Done |
| PPV content purchases | ✅ Done |
| Tips (XMR) with invoice polling | ✅ Done |
| Feature flag system | ✅ Done |
| Access engine (public, registered, subscriber, tier, PPV) | ✅ Done |
| Comments (threaded, with delete) | ✅ Done |
| Reactions (like toggle, polymorphic) | ✅ Done |
| Bookmarks (toggle + page) | ✅ Done |
| Notifications (in-app, mark read) | ✅ Done |
| Draft save (manual + field support) | ✅ Done |
| Content creation form with upload zone | ✅ Done |
| Full UI overhaul (sidebar, post-box, mobile nav) | ✅ Done |
| Design system (CSS custom properties, component classes) | ✅ Done |
| Seeder with 5 creators, 35+ posts, comments, reactions | ✅ Done |
| 92 tests passing, Vite build clean | ✅ Done |

---

## 🔜 Phase B — Production Readiness

### B1: Messenger (Real-time DMs)
- [ ] `messages` and `conversations` tables + models
- [ ] ConversationController (list, show, send)
- [ ] Inbox page wired to real data (replace placeholder)
- [ ] Laravel Echo + Pusher/Soketi for real-time delivery
- [ ] Read receipts and typing indicators
- [ ] Media attachment support in messages

### B2: Media Pipeline
- [ ] Chunked upload endpoint (`/api/upload/chunk`)
- [ ] Server-side reassembly + hash verification
- [ ] FFmpeg integration for video transcoding (HLS/DASH)
- [ ] Image optimization (WebP conversion, thumbnails)
- [ ] S3/MinIO storage driver integration
- [ ] Blurhash placeholders for locked media
- [ ] NSFW detection stub (flag for manual review)

### B3: Lists & Collections
- [ ] `lists` table + ListController
- [ ] Creator-side: organize content into collections
- [ ] Fan-side: custom lists / playlists
- [ ] Drag-and-drop reordering

### B4: Admin Panel
- [ ] Admin middleware + dashboard route group
- [ ] Creator application review queue (approve/reject)
- [ ] Content moderation queue
- [ ] User management (ban, role change)
- [ ] Platform analytics (revenue, signups, active users)
- [ ] Feature flag management UI

### B5: Search & Discovery
- [ ] Full-text search (Laravel Scout + Meilisearch)
- [ ] Trending algorithm (time-weighted reactions + views)
- [ ] Category/tag filtering with facets
- [ ] "Suggested creators" recommendation engine

### B6: SEO & Performance
- [ ] Server-side rendering for public creator pages
- [ ] OpenGraph meta tags per creator/content
- [ ] Sitemap generation
- [ ] Image lazy loading + Intersection Observer
- [ ] Response caching (Redis) for feed + explore
- [ ] Database query optimization (eager loading audit)

### B7: Auth & Security
- [ ] Two-factor authentication (TOTP)
- [ ] Email verification enforcement
- [ ] Rate limiting on sensitive endpoints
- [ ] CSRF double-submit for AJAX
- [ ] Content Security Policy headers
- [ ] Audit log for admin actions

### B8: GDPR & Compliance
- [ ] Cookie consent banner
- [ ] Data export (user's own data as ZIP)
- [ ] Account deletion with content cascade
- [ ] Privacy policy + Terms of Service pages
- [ ] Age verification gate

### B9: Payment Infrastructure
- [ ] Real Monero wallet RPC integration (replace mock)
- [ ] Payment webhook handling (block confirmations)
- [ ] Payout system (creator withdrawal requests)
- [ ] Invoice PDF generation
- [ ] Subscription renewal automation
- [ ] Failed payment retry logic
- [ ] Revenue split configuration

---

## 🚀 Phase C — Scale & Differentiate

### C1: Live Streaming
- [ ] Nginx-RTMP or SRS media server integration
- [ ] OBS/RTMP ingest with stream key management
- [ ] HLS playback in browser (hls.js)
- [ ] Live chat overlay (WebSocket)
- [ ] Stream recording + VOD conversion
- [ ] Go-live notification to subscribers
- [ ] Tip overlay during streams

### C2: Polls & Interactive Content
- [ ] Poll model + PollController
- [ ] Multi-choice / single-choice polls
- [ ] Poll results visualization
- [ ] Quiz-style polls with correct answers
- [ ] Expiring polls (auto-close)

### C3: Referral & Affiliate System
- [ ] Referral codes + tracking table
- [ ] Revenue share for referrers
- [ ] Referral analytics dashboard
- [ ] Creator-to-creator shoutouts

### C4: Mobile App (PWA)
- [ ] Service worker for offline support
- [ ] Web push notifications
- [ ] Add-to-homescreen manifest
- [ ] App shell caching strategy
- [ ] Background sync for uploads

### C5: Creator Analytics Dashboard
- [ ] Earnings charts (daily/weekly/monthly)
- [ ] Subscriber growth trends
- [ ] Content performance metrics (views, reactions, comments)
- [ ] Audience demographics
- [ ] Best posting times analysis

### C6: Internationalization
- [ ] Laravel localization setup
- [ ] Turkish (tr) + English (en) language files
- [ ] Language switcher in UI
- [ ] RTL support preparation

### C7: API & Integrations
- [ ] Public REST API for third-party apps
- [ ] API key management for creators
- [ ] Webhook system (subscription events, tips, new content)
- [ ] Zapier/IFTTT integration stubs

---

## Priority Order

1. **B9** (Payment Infrastructure) — Required for any real transaction
2. **B2** (Media Pipeline) — Core content delivery
3. **B1** (Messenger) — High user engagement feature
4. **B4** (Admin Panel) — Platform management
5. **B7** (Auth & Security) — Production security
6. **B5** (Search & Discovery) — Growth driver
7. **C1** (Live Streaming) — Key differentiator
8. **B8** (GDPR) — Legal compliance
9. Everything else by user demand

---

*Last updated: Phase A MVP complete*
