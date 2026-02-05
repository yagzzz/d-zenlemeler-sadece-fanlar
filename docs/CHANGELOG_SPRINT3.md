# Sprint 3 Changelog (6 Şubat 2026)

## Summary
Sprint 3 delivers creator profiles, media core, tier UX, tips, and the public UI pages. It also adds contract coverage for access gating and payment flows.

## Added
- Media core with media assets, attach flow, and media URL access control.
- Creator profiles (public profile contract + creator profile CRUD for approved creators).
- Tier UX compare contract + subscription change-tier flow.
- Tips contracts and invoice flow for creators and content.
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
