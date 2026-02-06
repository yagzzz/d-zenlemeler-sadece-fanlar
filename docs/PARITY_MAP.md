# PARITY_MAP.md — JustFans v6.9.0 → Sadece-Fanlar UI Parity

> Generated from full audit of `/home/yagobaba/İndirilenler/justfans_v6.9.0/Script/`

## Layout Structure

| Reference File | Target File | Parity Pattern |
|---|---|---|
| `layouts/user-no-nav.blade.php` | `layouts/app.blade.php` | 3-part: side-menu (col-2/col-md-3) \| content (col-12/col-md-9) \| mobile bottom nav (fixed-bottom, d-block d-md-none) |
| `template/side-menu.blade.php` | (inline in `layouts/app.blade.php`) | Desktop sidebar: avatar+name → nav items (h-pill h-pill-primary) → "New post" CTA |
| `elements/mobile-navbar.blade.php` | (inline in `layouts/app.blade.php`) | Fixed-bottom mobile nav: 5 h-pill items: home, notifications, create, messages, user avatar |
| `template/user-side-menu.blade.php` | (future: sliding overlay sidebar) | Sliding overlay 250px — not MVP-critical, skipped |

## Page Mapping

| Reference Page | Target Page | Key DOM Markers |
|---|---|---|
| `pages/feed.blade.php` | `feed/index.blade.php` | `data-page="feed"`, `.posts-wrapper`, `.feed-widgets`, `.suggestions-wrapper` |
| `pages/profile.blade.php` | `creator/show.blade.php` | `data-page="creator"`, `.profile-cover`, `.post-box[data-postID]` |
| `pages/search.blade.php` | `pages/explore.blade.php` | `data-page="explore"`, search input, filter tabs, creator grid |
| `pages/create.blade.php` | `pages/create.blade.php` | `data-page="create"`, `.dropzone`, `.post-create-actions`, price setup |
| `pages/messenger.blade.php` | `pages/inbox.blade.php` | `data-page="inbox"`, contacts list, message thread |
| `pages/notifications.blade.php` | `pages/notifications.blade.php` | `data-page="notifications"`, notification items |
| `pages/bookmarks.blade.php` | `pages/bookmarks.blade.php` | `data-page="bookmarks"`, bookmarked post list |
| `pages/profile.blade.php` (self) | `pages/profile.blade.php` | `data-page="profile"`, profile card, settings |

## Component Mapping

| Reference Component | Target Component | DOM Structure |
|---|---|---|
| `elements/feed/post-box.blade.php` | `<template id="content-card-template">` | `.post-box[data-postID]` → `.post-header` (avatar 48px + post-details + dropdown) → `.post-content` (line-clamp-3 + Show more) → `.post-media` (swiper or locked) → `.post-footer` (h-pill react/comment/tip buttons + counts) → `.post-comments` (collapsible) |
| `elements/feed/post-locked.blade.php` | (inline in template) | Lock SVG + attachment counts + unlock button |
| `elements/feed/post-new-comment.blade.php` | (inline in comments section) | Avatar + `.comment-textarea` (border-radius:20px, h:45px) + send btn-rounded-icon |
| `elements/feed/suggestions-box.blade.php` | (inline in feed sidebar) | `.suggestion-box` cards with 96px avatar, name, follow btn |
| `elements/feed/posts-wrapper.blade.php` | `#feed-list` | Posts loop with `<hr>` separators |
| `elements/feed/posts-loading-spinner.blade.php` | `#feed-skeleton` | Spinner with d-none toggle |
| `elements/checkout/checkout-box.blade.php` | `#payment-modal` | Checkout/payment modal |

## CSS Token Mapping

| Reference Class | Target Implementation | Values |
|---|---|---|
| `h-pill` | `.h-pill` | Pill-shaped clickable element, `border-radius: 25px; padding: .375rem .75rem; transition: .15s` |
| `h-pill-primary` | `.h-pill-primary` | Primary color variant, hover/active states |
| `btn-round` | `.btn-round` | `border-radius: 25px; padding: .5rem 1rem; display: block; text-align: center` |
| `btn-rounded-icon` | `.btn-rounded-icon` | `width: 45px; height: 45px; border-radius: 50%; padding: 0` |
| `neutral-bg` | `.neutral-bg` | Theme-aware neutral background |
| `post-box` | `.post-box` | Card container, no box-shadow in dark, border |
| `icon-large` | `.icon-large` | `font-size: 32px` (1.5rem) |
| `icon-medium` | `.icon-medium` | `font-size: 24px` (1.25rem) |
| `icon-small` | `.icon-small` | `font-size: 18px` |
| `pointer-cursor` | `.pointer-cursor` | `cursor: pointer` |
| `line-clamp-3` | `.line-clamp-3` | `-webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden` |
| `sticky` | `.sticky` | `position: sticky; top: 1.5rem` |
| `suggestion-box .avatar` | `.suggestion-box .avatar` | `96px × 96px` |
| `post-box .avatar` | `.post-box .avatar` | `48px × 48px` |
| `side-menu .user-avatar` | `.side-menu .user-avatar` | `50px × 50px` |
| `side-menu .icon-wrapper` | `.side-menu .icon-wrapper` | `width: 2.6rem; display: flex; justify-content: center; align-items: center` |
| `comment-textarea` | `.comment-textarea` | `height: 45px; border-radius: 20px` |
| `w-16 / w-24 / w-32` | `.w-16 / .w-24 / .w-32` | width/height utility classes |
| `Open Sans` font | `Open Sans` (via Google Fonts CDN) | Body font-family |

## JS Behavior Mapping

| Reference JS | Target JS | Pattern |
|---|---|---|
| `Post.reactTo(type, id)` | `Post.reactTo(type, id)` | Toggle `.active` on `.react-button`, AJAX POST, update count |
| `Post.addComment(postId)` | `Post.addComment(postId)` | AJAX POST, append comment HTML, clear textarea |
| `Post.showPostComments(postId, limit)` | `Post.showPostComments(postId, limit)` | Toggle d-none on `.post-comments`, load via AJAX |
| `Post.togglePostBookmark(postId)` | `Post.togglePostBookmark(postId)` | Toggle `.is-active` on `.bookmark-button`, AJAX POST |
| `Post.toggleFullDescription(postId)` | `Post.toggleFullDescription(postId)` | Toggle `.line-clamp-3` on `.post-content-data` |
| `PostsPaginator.initScrollLoad()` | `PostsPaginator.initScrollLoad()` | `window.onscroll` → when near bottom → `loadResults()` AJAX GET append |
| `PostsPaginator.loadResults(endpoint)` | `PostsPaginator.loadResults()` | Append paginated HTML/cards to `.posts-wrapper` |
| `SuggestionsSlider.init()` | Swiper-like init | Suggestions horizontal scroll |

## Required DOM Markers for Tests

```
[data-page="feed"]
[data-page="creator"]
[data-page="explore"]
[data-page="create"]
[data-page="inbox"]
[data-page="notifications"]
[data-page="bookmarks"]
[data-page="profile"]
[data-postID] or [data-content-id]
.post-box
.post-header
.post-content
.post-footer
.post-comments
.h-pill
.react-button
.bookmark-button
.comment-textarea
.side-menu
.mobile-bottom-nav
.post-locked-overlay
.suggestion-box
#content-card-template
```
