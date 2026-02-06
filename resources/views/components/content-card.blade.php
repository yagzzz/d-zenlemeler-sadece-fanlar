<template id="content-card-template">
    <article class="post-box" data-content-id="">
        {{-- Header --}}
        <div class="post-header">
            <a class="post-avatar creator-link" href="#"></a>
            <div class="post-meta">
                <a class="post-creator creator-link" href="#"></a>
                <span class="post-time"></span>
            </div>
            <span class="content-badge hidden rounded-full bg-white/10 px-3 py-1 text-xs text-slate-400">🔒</span>
        </div>

        {{-- Body --}}
        <div class="post-body">
            <div class="post-title"></div>
            <div class="post-text"></div>
        </div>

        {{-- Media / Locked Overlay --}}
        <div class="post-media hidden">
            <div class="post-locked-overlay hidden">
                <span class="text-3xl">🔒</span>
                <p class="text-sm text-slate-300 font-medium locked-message">Bu içerik kilitli</p>
                <button class="cta-button btn-primary text-xs">Kilidi Aç</button>
            </div>
        </div>

        {{-- Actions --}}
        <div class="post-actions">
            <button class="action-btn like-btn" data-action="like"><span>❤️</span><span class="like-count">0</span></button>
            <button class="action-btn comment-btn" data-action="comment"><span>💬</span><span class="comment-count">0</span></button>
            <button class="action-btn tip-btn" data-action="tip"><span>💎</span><span>Tip</span></button>
            <div class="action-spacer"></div>
            <button class="action-btn bookmark-btn" data-action="bookmark"><span class="bookmark-icon">🔖</span></button>
        </div>
    </article>
</template>
