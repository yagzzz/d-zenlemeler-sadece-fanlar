{{-- Content Card Template — mirrors JustFans post-box.blade.php structure --}}
<template id="content-card-template">
    <article class="post-box" data-content-id="">
        {{-- Post Header: avatar + details + time --}}
        <div class="post-header">
            <a class="avatar post-avatar creator-link" href="#"></a>
            <div class="post-details w-100 pl-2">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="post-creator-name"><a class="creator-link" href="#"></a></div>
                        <div class="post-creator-handle"><a class="creator-link" href="#"><span>@</span><span class="handle-text"></span></a></div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="content-badge hidden" style="border-radius:9999px;background:rgba(255,255,255,0.1);padding:0.125rem 0.75rem;font-size:0.75rem;color:#94a3b8;">🔒</span>
                        <a class="post-time-link" href="javascript:void(0)"></a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Post Content --}}
        <div class="post-content mt-2 pl-3 pr-3">
            <div class="post-title"></div>
            <div class="post-content-data line-clamp-3"></div>
            <div class="label-more hidden" style="color:#d946ef;cursor:pointer;font-size:0.8125rem;">Devamını göster</div>
            <div class="label-less hidden" style="color:#d946ef;cursor:pointer;font-size:0.8125rem;">Daha az göster</div>
        </div>

        {{-- Post Media / Locked Overlay --}}
        <div class="post-media hidden">
            <div class="post-locked-overlay hidden">
                <svg class="locked-svg" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><rect x="128" y="208" width="256" height="256" rx="48" ry="48"/><path d="M176 208v-48a80 80 0 01160 0v48" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p class="locked-message" style="font-size:0.875rem;color:#cbd5e1;font-weight:500;">Bu içerik kilitli</p>
                <button class="cta-button btn btn-primary" style="font-size:0.75rem;">Kilidi Aç</button>
            </div>
        </div>

        {{-- Post Footer: reaction, comment, tip buttons + counts --}}
        <div class="post-footer mt-2 pl-3 pr-3">
            <div class="footer-actions d-flex justify-content-between">
                <div class="d-flex">
                    {{-- Like --}}
                    <div class="h-pill h-pill-primary mr-1 rounded react-button" data-action="like" title="Beğen">
                        <svg class="icon-medium" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M352.92 80C288 80 256 144 256 144s-32-64-96.92-64c-52.76 0-94.54 44.14-95.08 96.81-1.1 109.33 86.73 187.08 183 252.42a16 16 0 0017.93 0c96.26-65.34 184.09-143.09 183-252.42-.54-52.67-42.32-96.81-95.01-96.81z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    {{-- Comment --}}
                    <div class="h-pill h-pill-primary mr-1 rounded comment-button" data-action="comment" title="Yorumlar">
                        <svg class="icon-medium" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M408 64H104a56.16 56.16 0 00-56 56v192a56.16 56.16 0 0056 56h40v80l93.72-78.14a8 8 0 015.13-1.86H408a56.16 56.16 0 0056-56V120a56.16 56.16 0 00-56-56z" stroke-linejoin="round"/></svg>
                    </div>
                    {{-- Tip --}}
                    <div class="h-pill h-pill-primary mr-1 rounded tip-button" data-action="tip" title="Tip gönder">
                        <div class="d-flex align-items-center">
                            <svg class="icon-medium" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M256 104v56h56M256 104c-54.36 64-126.65 96-200 108M256 104c54.36 64 126.65 96 200 108M296 160c-16.29 53.72-41.53 101.21-80 141.44M216.46 301.44c-38.47 40.23-63.71 87.72-80 141.44M256 360c-54.36-64-126.65-96-200-108M256 360c54.36 64 126.65 96 200 108" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="d-none d-lg-block ml-1" style="font-size:0.8125rem;">Tip gönder</span>
                        </div>
                    </div>
                </div>
                <div class="mt-0 d-flex align-items-center justify-content-center post-count-details">
                    <span class="ml-2-h"><strong class="post-reactions-label-count like-count">0</strong> <span class="post-reactions-label">beğeni</span></span>
                    <span class="ml-2-h d-none d-lg-block"><strong class="post-comments-label-count comment-count">0</strong> <span class="post-comments-label">yorum</span></span>
                    <span class="ml-2-h d-none d-lg-block"><strong class="post-tips-label-count tip-count">0</strong> <span class="post-tips-label">tip</span></span>
                </div>
            </div>
        </div>

        {{-- Collapsible Comments Section --}}
        <div class="post-comments d-none">
            <hr>
            <div class="px-3 post-comments-wrapper">
                <div class="comments-list"></div>
                <div class="no-comments-label d-none pl-3" style="font-size:0.8125rem;color:#64748b;padding:0.5rem 0;">
                    Henüz yorum yok.
                </div>
            </div>
            <hr>
            {{-- New Comment Form --}}
            <div class="d-flex align-items-center gap-2 px-3 py-2 new-comment-area">
                <div class="comment-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <textarea class="comment-textarea" placeholder="Yorum yaz…" rows="1" maxlength="2000"></textarea>
                <button class="btn-rounded-icon send-comment-btn" title="Gönder">
                    <svg class="icon-small" viewBox="0 0 512 512" fill="currentColor"><path d="M476.59 227.05l-.16-.07L49.35 49.84A23.56 23.56 0 0027.14 52 24.65 24.65 0 0016 73.56v131.19c0 13.15 10.87 24.55 24 25.37l232 15.07-232 15.07c-13.12.82-24 12.22-24 25.37v131.19a24.65 24.65 0 0011.14 21.56 23.56 23.56 0 0022.21 2.16L476.43 285.02l.16-.07a38.36 38.36 0 000-57.9z"/></svg>
                </button>
            </div>
        </div>

        {{-- Bookmark (inside footer, right side) --}}
        <div class="bookmark-wrapper" style="display:none;">
            <div class="h-pill h-pill-primary bookmark-button" data-action="bookmark" title="Kaydet">
                <svg class="icon-medium bookmark-icon" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M352 48H160a48 48 0 00-48 48v368l144-128 144 128V96a48 48 0 00-48-48z" stroke-linejoin="round"/></svg>
            </div>
        </div>
    </article>
</template>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
