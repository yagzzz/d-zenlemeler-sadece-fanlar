/**
 * Sadece Fanlar — Public fallback (Vite-less environments)
 * Provides basic feed rendering and navigation when Vite is not available.
 */
(function() {
'use strict';
var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var toastRoot = document.getElementById('toast-root');
function showToast(msg, type) {
    if (!toastRoot) return;
    var el = document.createElement('div');
    el.className = 'rounded-xl px-4 py-3 text-sm shadow-lg text-white ' + (type === 'error' ? 'bg-rose-500' : type === 'success' ? 'bg-emerald-500' : 'bg-slate-700');
    el.textContent = msg; toastRoot.appendChild(el);
    setTimeout(function() { el.remove(); }, 3000);
}
function fetchJson(url, opts) {
    opts = opts || {};
    return fetch(url, { method: opts.method || 'GET', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin', body: opts.body || undefined }).then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
}
function timeAgo(iso) {
    if (!iso) return '';
    var s = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (s < 60) return 'az önce'; if (s < 3600) return Math.floor(s/60) + 'dk';
    if (s < 86400) return Math.floor(s/3600) + 'sa'; return Math.floor(s/86400) + 'g';
}
function renderCard(item) {
    var tpl = document.getElementById('content-card-template'); if (!tpl) return null;
    var node = tpl.content.cloneNode(true); var card = node.querySelector('.post-box');
    card.dataset.contentId = item.id;
    var av = card.querySelector('.post-avatar'); if (av) av.textContent = (item.creator_username || '?')[0].toUpperCase();
    var cr = card.querySelector('.post-creator'); if (cr) cr.textContent = item.creator_name || item.creator_username || '';
    card.querySelectorAll('.creator-link').forEach(function(l) { l.href = '/c/' + (item.creator_username || ''); });
    var tm = card.querySelector('.post-time'); if (tm) tm.textContent = timeAgo(item.published_at);
    var ti = card.querySelector('.post-title'); if (ti) ti.textContent = item.title || '';
    var tx = card.querySelector('.post-text'); if (tx) tx.textContent = item.locked ? 'Bu içerik kilitli.' : (item.body || '');
    var lc = card.querySelector('.like-count'); if (lc) lc.textContent = item.reactions_count || 0;
    var cc = card.querySelector('.comment-count'); if (cc) cc.textContent = item.comments_count || 0;
    if (item.locked) { var ov = card.querySelector('.post-locked-overlay'); if (ov) ov.classList.remove('hidden'); var md = card.querySelector('.post-media'); if (md) md.classList.remove('hidden'); }
    return node;
}
document.addEventListener('DOMContentLoaded', function() {
    if (document.body.dataset.uiEnabled !== '1') return;
    var feedRoot = document.querySelector('[data-page="feed"]');
    if (feedRoot) {
        var ep = feedRoot.dataset.feedEndpoint;
        fetchJson(ep).then(function(p) {
            var sk = document.getElementById('feed-skeleton'); if (sk) sk.remove();
            var list = document.getElementById('feed-list');
            if (!p.data || !p.data.length) { var em = document.getElementById('feed-empty'); if (em) em.classList.remove('hidden'); return; }
            p.data.forEach(function(item) { var n = renderCard(item); if (n && list) list.appendChild(n); });
        }).catch(function() { var sk = document.getElementById('feed-skeleton'); if (sk) sk.remove(); showToast('Feed yüklenemedi', 'error'); });
    }
    // Global CTA
    document.body.addEventListener('click', function(e) {
        var t = e.target.closest('.cta-button'); if (!t) return;
        var reason = t.dataset.reason;
        if (reason === 'not_logged_in') showToast('Giriş yapın', 'error');
        else showToast('Bu işlem için tam sürüm gerekli', 'info');
    });
});
})();
