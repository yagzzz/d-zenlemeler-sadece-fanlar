import './bootstrap';

/* ═══════════════════════════════════════════════════════════════════════
   Sadece Fanlar — Client Application  (Phase A MVP)
   ═══════════════════════════════════════════════════════════════════════ */

/* ───────────────────── Debug ─────────────────────────────────────────── */
const IS_LOCAL = document.body.dataset.appEnv === 'local' || document.body.dataset.appEnv === 'testing';
if (IS_LOCAL) {
window.addEventListener('error', e => { console.error('[SF]', e.message, e.filename, e.lineno); debugToast(`JS: ${e.message}`); });
window.addEventListener('unhandledrejection', e => { console.error('[SF rejection]', e.reason); debugToast(`Rejection: ${String(e.reason)}`); });
}
function debugToast(msg) {
const r = document.getElementById('toast-root'); if (!r) return;
const el = Object.assign(document.createElement('div'), { className: 'rounded-xl px-4 py-3 text-xs shadow-lg bg-amber-600 text-white max-w-xs truncate', textContent: msg });
r.appendChild(el); setTimeout(() => el.remove(), 5000);
}

/* ───────────────────── Globals ────────────────────────────────────────── */
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const toastRoot = document.getElementById('toast-root');
const uiEnabled = document.body.dataset.uiEnabled === '1';
const ctaLabels = (() => { try { return JSON.parse(document.getElementById('cta-labels')?.textContent || '{}'); } catch { return {}; } })();

/* ───────────────────── Helpers ────────────────────────────────────────── */
function showToast(message, type = 'info') {
if (!toastRoot) return;
const el = Object.assign(document.createElement('div'), {
className: `rounded-xl px-4 py-3 text-sm shadow-lg transition-all ${type === 'error' ? 'bg-rose-500' : type === 'success' ? 'bg-emerald-500' : 'bg-slate-700'} text-white`,
textContent: message,
});
toastRoot.appendChild(el);
setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}

async function fetchJson(url, opts = {}) {
const res = await fetch(url, { headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf || '', ...(opts.headers || {}) }, credentials: 'same-origin', ...opts });
if (!res.ok) throw new Error(`HTTP ${res.status}`);
return res.json();
}

function timeAgo(iso) {
if (!iso) return '';
const d = new Date(iso), s = Math.floor((Date.now() - d) / 1000);
if (s < 60) return 'az önce';
if (s < 3600) return `${Math.floor(s / 60)}dk`;
if (s < 86400) return `${Math.floor(s / 3600)}sa`;
return `${Math.floor(s / 86400)}g`;
}

/* ═══════════════════════════════════════════════════════════════════════
   Modal Manager
   ═══════════════════════════════════════════════════════════════════════ */
const ModalManager = (() => {
const modals = {};
function register(id) {
const el = document.getElementById(id); if (!el || modals[id]) return; modals[id] = el;
el.querySelectorAll('[data-tier-close],[data-payment-close],[data-tip-close],[data-comment-close]').forEach(b => b.addEventListener('click', () => close(id)));
const bg = el.querySelector('.modal-backdrop'); if (bg) bg.addEventListener('click', () => close(id));
el.addEventListener('keydown', e => { if (e.key === 'Escape') close(id); });
}
function open(id) { if (!modals[id]) register(id); const el = modals[id]; if (!el) return null; el.classList.remove('hidden'); el.setAttribute('tabindex', '-1'); el.focus(); document.body.style.overflow = 'hidden'; return el; }
function close(id) { const el = modals[id]; if (!el) return; el.classList.add('hidden'); document.body.style.overflow = ''; }
return { register, open, close };
})();

/* ═══════════════════════════════════════════════════════════════════════
   Content Card Renderer
   ═══════════════════════════════════════════════════════════════════════ */
function renderContentCard(item) {
const tpl = document.getElementById('content-card-template'); if (!tpl) return null;
const node = tpl.content.cloneNode(true);
const card = node.querySelector('.post-box');
card.dataset.contentId = item.id;

// Header
const avatar = card.querySelector('.post-avatar');
const creator = card.querySelector('.post-creator');
const links = card.querySelectorAll('.creator-link');
if (avatar) avatar.textContent = (item.creator_username || '?')[0].toUpperCase();
if (creator) creator.textContent = item.creator_name || item.creator_username || '';
links.forEach(l => l.href = `/c/${item.creator_username || ''}`);
const time = card.querySelector('.post-time');
if (time) time.textContent = timeAgo(item.published_at);

// Body
const title = card.querySelector('.post-title');
const text = card.querySelector('.post-text');
if (title) title.textContent = item.title || '';

// Locked state
const badge = card.querySelector('.content-badge');
const media = card.querySelector('.post-media');
const overlay = card.querySelector('.post-locked-overlay');
const ctaBtn = card.querySelector('.cta-button');

if (item.locked) {
if (text) text.textContent = 'Bu içerik kilitli.';
if (badge) { badge.classList.remove('hidden'); badge.textContent = '🔒 ' + (item.visibility || '').replace('_', ' '); }
if (media) media.classList.remove('hidden');
if (overlay) overlay.classList.remove('hidden');
if (ctaBtn) {
const reason = item.lock_reason || 'subscription_required';
ctaBtn.textContent = ctaLabels[reason] || "Tier'leri Gör";
ctaBtn.dataset.reason = reason;
ctaBtn.dataset.creator = item.creator_username || '';
ctaBtn.dataset.contentId = item.id;
}
} else {
if (text) text.textContent = item.body || '';
}

// Actions
const likeBtn = card.querySelector('.like-btn');
const likeCount = card.querySelector('.like-count');
const commentBtn = card.querySelector('.comment-btn');
const commentCount = card.querySelector('.comment-count');
const tipBtn = card.querySelector('.tip-btn');
const bookmarkBtn = card.querySelector('.bookmark-btn');
const bookmarkIcon = card.querySelector('.bookmark-icon');

if (likeCount) likeCount.textContent = item.reactions_count || 0;
if (commentCount) commentCount.textContent = item.comments_count || 0;
if (item.user_reacted && likeBtn) likeBtn.classList.add('active');
if (item.user_bookmarked && bookmarkBtn) { bookmarkBtn.classList.add('active'); if (bookmarkIcon) bookmarkIcon.textContent = '🔖'; }

// Like
if (likeBtn) likeBtn.addEventListener('click', async () => {
try {
const r = await fetchJson('/api/reactions/toggle', { method: 'POST', body: JSON.stringify({ reactable_type: 'content', reactable_id: item.id }) });
likeBtn.classList.toggle('active', r.active);
const cur = parseInt(likeCount?.textContent || '0');
if (likeCount) likeCount.textContent = r.active ? cur + 1 : Math.max(0, cur - 1);
} catch { showToast('Giriş yapmalısın', 'error'); }
});

// Comment
if (commentBtn) commentBtn.addEventListener('click', () => openCommentModal(item.id));

// Tip
if (tipBtn) tipBtn.addEventListener('click', () => openTipModal({ creator: item.creator_username, contentId: item.id }));

// Bookmark
if (bookmarkBtn) bookmarkBtn.addEventListener('click', async () => {
try {
const r = await fetchJson('/api/bookmarks/toggle', { method: 'POST', body: JSON.stringify({ content_id: item.id }) });
bookmarkBtn.classList.toggle('active', r.active);
if (bookmarkIcon) bookmarkIcon.textContent = r.active ? '🔖' : '🔖';
showToast(r.active ? 'Kaydedildi' : 'Kaldırıldı', 'success');
} catch { showToast('Giriş yapmalısın', 'error'); }
});

return node;
}

/* ═══════════════════════════════════════════════════════════════════════
   Comment Modal
   ═══════════════════════════════════════════════════════════════════════ */
let currentCommentContentId = null;

async function openCommentModal(contentId) {
currentCommentContentId = contentId;
const modal = ModalManager.open('comment-modal');
if (!modal) return;
const list = document.getElementById('comments-list');
const empty = document.getElementById('comments-empty');
if (list) list.innerHTML = '<p class="text-xs text-slate-500 py-4 text-center">Yükleniyor…</p>';

try {
const payload = await fetchJson(`/api/contents/${contentId}/comments`);
if (!list) return;
list.innerHTML = '';
if (!payload.data?.length) { if (empty) { list.appendChild(empty); empty.classList.remove('hidden'); } return; }
if (empty) empty.classList.add('hidden');
payload.data.forEach(c => list.appendChild(renderComment(c)));
} catch { if (list) list.innerHTML = '<p class="text-xs text-rose-400 py-4 text-center">Yorumlar yüklenemedi.</p>'; }
}

function renderComment(c) {
const div = document.createElement('div');
div.className = 'comment-item';
div.innerHTML = `<div class="comment-avatar">${(c.username || '?')[0].toUpperCase()}</div><div class="comment-body"><span class="comment-author">${c.display_name || c.username}</span><p class="comment-text">${escapeHtml(c.body)}</p><div class="comment-meta"><span>${timeAgo(c.created_at)}</span><span>❤️ ${c.reactions_count || 0}</span></div></div>`;
if (c.replies?.length) c.replies.forEach(r => { const rd = renderComment(r); rd.classList.add('ml-10'); div.parentElement?.appendChild(rd); });
return div;
}

function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

/* ═══════════════════════════════════════════════════════════════════════
   Tier Modal
   ═══════════════════════════════════════════════════════════════════════ */
async function openTierModal(username) {
const modal = ModalManager.open('tier-modal'); if (!modal || !username) return;
const list = modal.querySelector('#tier-compare-list'); if (!list) return;
list.innerHTML = '<div class="skeleton-card h-20"></div>';
try {
const p = await fetchJson(`/api/creators/${username}/tiers/compare`);
list.innerHTML = '';
(p.tiers || []).forEach(t => {
const c = document.createElement('div');
c.className = 'tier-card' + (t.is_most_popular ? ' popular' : '');
c.innerHTML = `<div class="flex items-center justify-between"><div><p class="text-sm font-semibold">${t.name}</p><p class="text-xs text-slate-400 mt-1">${t.description || ''}</p></div>${t.badges?.length ? `<span class="rounded-full bg-amber-400/20 text-amber-300 px-2 py-0.5 text-xs">${t.badges[0]}</span>` : ''}</div><div class="mt-3 flex items-center justify-between"><span class="text-sm font-mono">${t.price?.monthly_atomic ?? t.price_atomic ?? '?'} XMR/ay</span><button class="btn-primary text-xs px-4 py-2" data-tier-id="${t.id}" data-creator="${username}">Seç</button></div>`;
list.appendChild(c);
});
} catch { list.innerHTML = '<p class="text-sm text-rose-300 p-4">Tier bilgisi yüklenemedi.</p>'; }
}

/* ═══════════════════════════════════════════════════════════════════════
   Payment Modal
   ═══════════════════════════════════════════════════════════════════════ */
function openPaymentModal({ summary, invoiceAction }) {
const modal = ModalManager.open('payment-modal'); if (!modal) return;
const [stepS, stepW, stepOk] = ['summary', 'waiting', 'success'].map(s => modal.querySelector(`[data-step="${s}"]`));
modal.querySelector('#payment-summary').textContent = summary;
stepS.classList.remove('hidden'); stepW.classList.add('hidden'); stepOk.classList.add('hidden');
modal.querySelector('#payment-start')?.addEventListener('click', async () => {
try {
const inv = await invoiceAction();
stepS.classList.add('hidden'); stepW.classList.remove('hidden');
modal.querySelector('#payment-address').textContent = inv.address;
modal.querySelector('#payment-amount').textContent = `${inv.amount_atomic} XMR`;
modal.querySelector('#payment-invoice').textContent = inv.invoice_id;
await pollVerify(inv.invoice_id);
stepW.classList.add('hidden'); stepOk.classList.remove('hidden');
} catch { showToast('Ödeme oluşturulamadı', 'error'); }
}, { once: true });
}

async function openPaymentModalWithInvoice(inv) {
const modal = ModalManager.open('payment-modal'); if (!modal) return;
const [stepS, stepW, stepOk] = ['summary', 'waiting', 'success'].map(s => modal.querySelector(`[data-step="${s}"]`));
stepS.classList.add('hidden'); stepW.classList.remove('hidden'); stepOk.classList.add('hidden');
modal.querySelector('#payment-address').textContent = inv.address;
modal.querySelector('#payment-amount').textContent = `${inv.amount_atomic} XMR`;
modal.querySelector('#payment-invoice').textContent = inv.invoice_id;
try { await pollVerify(inv.invoice_id); stepW.classList.add('hidden'); stepOk.classList.remove('hidden'); }
catch { showToast('Ödeme doğrulanamadı', 'error'); }
}

/* ───────────────────── Tip Modal ─────────────────────────────────────── */
function openTipModal({ creator, contentId }) {
const modal = ModalManager.open('tip-modal'); if (!modal || !creator) return;
modal.dataset.creator = creator; modal.dataset.contentId = contentId || '';
}

/* ───────────────────── Poll Verify ───────────────────────────────────── */
function pollVerify(invoiceId) {
const statusEl = document.getElementById('payment-status');
let attempts = 0;
return new Promise((resolve, reject) => {
const timer = setInterval(async () => {
attempts++;
try {
const r = await fetchJson(`/api/invoices/${invoiceId}/verify`, { method: 'POST' });
if (statusEl) statusEl.textContent = r.status === 'paid' ? 'Başarılı ✓' : 'Bekleniyor…';
if (r.status === 'paid') { clearInterval(timer); resolve(r); }
if (attempts >= 20) { clearInterval(timer); reject(new Error('timeout')); }
} catch { clearInterval(timer); reject(new Error('verify-error')); }
}, 3000);
});
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Feed
   ═══════════════════════════════════════════════════════════════════════ */
async function setupFeed() {
const root = document.querySelector('[data-page="feed"]'); if (!root) return;
const endpoint = root.dataset.feedEndpoint;
const skeleton = document.getElementById('feed-skeleton');
const list = document.getElementById('feed-list');
const empty = document.getElementById('feed-empty');
try {
const p = await fetchJson(endpoint);
skeleton?.remove();
if (!p.data?.length) { empty?.classList.remove('hidden'); return; }
p.data.forEach(item => { const n = renderContentCard(item); if (n) list?.appendChild(n); });
} catch { skeleton?.remove(); empty?.classList.remove('hidden'); showToast('Feed yüklenemedi', 'error'); }
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Creator
   ═══════════════════════════════════════════════════════════════════════ */
async function setupCreatorPage() {
const page = document.querySelector('[data-page="creator"]'); if (!page) return;
const username = page.dataset.username;

// Profile
try {
const p = await fetchJson(page.dataset.profileEndpoint);
const name = document.getElementById('creator-name');
const tag = document.getElementById('creator-tagline');
const av = document.getElementById('creator-avatar');
if (name) name.textContent = p.creator?.display_name || username;
if (tag) tag.textContent = p.profile?.tagline || '';
if (av) av.textContent = (p.creator?.display_name || username)[0].toUpperCase();
} catch {}

// Contents
try {
const c = await fetchJson(page.dataset.contentsEndpoint);
const list = document.getElementById('creator-contents');
const postCount = document.getElementById('creator-post-count');
if (postCount) postCount.textContent = String(c.data?.length || c.meta?.total || 0);
(c.data || []).forEach(item => { const n = renderContentCard(item); if (n) list?.appendChild(n); });
} catch {}

// Tiers
if (page.dataset.tiersEndpoint) {
try {
const t = await fetchJson(page.dataset.tiersEndpoint);
const list = document.getElementById('creator-tiers');
(t.data || []).forEach(tier => {
const card = document.createElement('div');
card.className = 'tier-card' + (tier.is_most_popular ? ' popular' : '');
card.innerHTML = `<p class="text-sm font-semibold">${tier.name}</p><p class="text-xs text-slate-400 mt-1">${tier.description || ''}</p><p class="mt-3 text-lg font-mono font-semibold">${tier.price_atomic} <span class="text-xs text-slate-400">${tier.currency}/ay</span></p><button class="mt-3 w-full btn-primary text-xs py-2" data-tier-id="${tier.id}" data-creator="${username}">Abone Ol</button>`;
list?.appendChild(card);
});
} catch {}
}

// Analytics
if (page.dataset.analyticsEndpoint) {
try {
const a = await fetchJson(page.dataset.analyticsEndpoint);
const t1 = document.getElementById('creator-tips-total');
const t2 = document.getElementById('creator-tips-count');
const t3 = document.getElementById('creator-subscribers');
if (t1) t1.textContent = `${a.tips_total_atomic} XMR`;
if (t2) t2.textContent = `${a.tips_count} tip`;
if (t3) t3.textContent = String(a.active_subscribers);
} catch {}
}

// Buttons
document.getElementById('subscribe-cta')?.addEventListener('click', () => openTierModal(username));
document.getElementById('compare-tiers')?.addEventListener('click', () => openTierModal(username));
document.getElementById('tip-cta')?.addEventListener('click', () => openTipModal({ creator: username }));

// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => btn.addEventListener('click', () => {
document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('border-fuchsia-500', 'text-white'); b.classList.add('border-transparent', 'text-slate-400'); });
btn.classList.add('border-fuchsia-500', 'text-white'); btn.classList.remove('border-transparent', 'text-slate-400');
const tab = btn.dataset.tab;
document.getElementById('tab-posts')?.classList.toggle('hidden', tab !== 'posts');
document.getElementById('tab-tiers')?.classList.toggle('hidden', tab !== 'tiers');
}));
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Create
   ═══════════════════════════════════════════════════════════════════════ */
function setupCreatePage() {
const page = document.querySelector('[data-page="create"]'); if (!page) return;
const form = document.getElementById('create-form');

// Visibility selector
page.querySelectorAll('.visibility-btn').forEach(btn => btn.addEventListener('click', () => {
page.querySelectorAll('.visibility-btn').forEach(b => { b.classList.remove('pill-active'); b.classList.add('pill-default'); });
btn.classList.add('pill-active'); btn.classList.remove('pill-default');
const vis = btn.dataset.visibility;
document.getElementById('visibility-input').value = vis;
document.getElementById('ppv-price-row')?.classList.toggle('hidden', vis !== 'ppv');
}));

// Upload zone
const zone = document.getElementById('upload-zone');
const fileInput = document.getElementById('media-input');
const previews = document.getElementById('upload-previews');
if (zone && fileInput) {
zone.addEventListener('click', () => fileInput.click());
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-fuchsia-400/50'); });
zone.addEventListener('dragleave', () => zone.classList.remove('border-fuchsia-400/50'));
zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('border-fuchsia-400/50'); handleFiles(e.dataTransfer.files); });
fileInput.addEventListener('change', () => handleFiles(fileInput.files));
}

function handleFiles(files) {
if (!previews) return;
[...files].forEach(f => {
const wrap = document.createElement('div');
wrap.className = 'relative rounded-lg overflow-hidden bg-white/5 aspect-square flex items-center justify-center';
if (f.type.startsWith('image/')) {
const img = document.createElement('img');
img.src = URL.createObjectURL(f); img.className = 'object-cover w-full h-full';
wrap.appendChild(img);
} else {
wrap.innerHTML = `<span class="text-2xl">${f.type.startsWith('video/') ? '🎥' : '🎵'}</span>`;
}
const rm = document.createElement('button');
rm.className = 'absolute top-1 right-1 bg-black/60 rounded-full w-5 h-5 text-xs text-white flex items-center justify-center';
rm.textContent = '✕'; rm.addEventListener('click', () => wrap.remove());
wrap.appendChild(rm);
previews.appendChild(wrap);
});
}

// Submit
if (form) form.addEventListener('submit', async e => {
e.preventDefault();
const fd = new FormData(form);
const body = { title: fd.get('title'), body: fd.get('body'), visibility: fd.get('visibility') };
if (body.visibility === 'ppv') body.ppv_price_atomic = Number(fd.get('ppv_price_atomic') || 3000);
try {
await fetchJson('/creator/content', { method: 'POST', body: JSON.stringify(body) });
showToast('İçerik oluşturuldu!', 'success');
form.reset();
if (previews) previews.innerHTML = '';
} catch (err) { showToast('İçerik oluşturulamadı: ' + err.message, 'error'); }
});

// Draft save
document.getElementById('save-draft-btn')?.addEventListener('click', () => {
showToast('Taslak kaydedildi 💾', 'success');
const status = document.getElementById('draft-status');
if (status) { status.classList.remove('hidden'); setTimeout(() => status.classList.add('hidden'), 3000); }
});
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Bookmarks
   ═══════════════════════════════════════════════════════════════════════ */
async function setupBookmarksPage() {
const page = document.querySelector('[data-page="bookmarks"]'); if (!page) return;
const endpoint = page.dataset.bookmarksEndpoint;
const skeleton = document.getElementById('bookmarks-skeleton');
const list = document.getElementById('bookmarks-list');
const empty = document.getElementById('bookmarks-empty');
try {
const p = await fetchJson(endpoint);
skeleton?.remove();
if (!p.data?.length) { empty?.classList.remove('hidden'); return; }
p.data.forEach(item => { const n = renderContentCard(item); if (n) list?.appendChild(n); });
} catch { skeleton?.remove(); empty?.classList.remove('hidden'); }
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Notifications
   ═══════════════════════════════════════════════════════════════════════ */
async function setupNotificationsPage() {
const page = document.querySelector('[data-page="notifications"]'); if (!page) return;
const list = document.getElementById('notifications-list');
const empty = document.getElementById('notifications-empty');
try {
const p = await fetchJson('/api/notifications');
if (!list) return;
if (!p.data?.length) { list.classList.add('hidden'); empty?.classList.remove('hidden'); return; }
list.innerHTML = '';
p.data.forEach(n => {
const div = document.createElement('div');
div.className = `notification-item ${n.read ? '' : 'unread'}`;
div.innerHTML = `${n.read ? '' : '<div class="notification-dot"></div>'}<div class="flex-1"><p class="text-sm">${n.data?.message || n.type}</p><p class="text-xs text-slate-500 mt-0.5">${timeAgo(n.created_at)}</p></div>`;
list.appendChild(div);
});
} catch { if (list) list.innerHTML = '<p class="text-xs text-slate-500 p-6 text-center">Bildirimler yüklenemedi.</p>'; }

document.getElementById('mark-all-read')?.addEventListener('click', async () => {
try { await fetchJson('/api/notifications/read', { method: 'POST' }); showToast('Tümü okundu', 'success'); document.querySelectorAll('.notification-item.unread').forEach(el => { el.classList.remove('unread'); el.querySelector('.notification-dot')?.remove(); }); } catch {}
});
}

/* ═══════════════════════════════════════════════════════════════════════
   Global Click Delegation
   ═══════════════════════════════════════════════════════════════════════ */
function setupGlobalActions() {
document.body.addEventListener('click', async event => {
const target = event.target.closest('.cta-button, [data-tier-id], [data-action]');
if (!target) return;

// CTA buttons inside locked posts
if (target.matches('.cta-button')) {
const { reason, creator, contentId } = target.dataset;
if (reason === 'tier_required' || reason === 'subscription_required') return openTierModal(creator);
if (reason === 'ppv_required') return openPaymentModal({ summary: 'PPV içerik satın al', invoiceAction: () => fetchJson('/payments/ppv/invoice', { method: 'POST', body: JSON.stringify({ content_id: Number(contentId) }) }) });
if (reason === 'not_logged_in' || reason === 'not_verified') return showToast('Devam etmek için giriş yapın', 'error');
if (reason === 'tip') return openTipModal({ creator, contentId: Number(contentId) });
}

// Tier subscribe buttons
if (target.matches('[data-tier-id]')) {
const tierId = target.getAttribute('data-tier-id');
const creator = target.getAttribute('data-creator');
if (!tierId || !creator) return;
openPaymentModal({ summary: 'Tier aboneliği başlat', invoiceAction: () => fetchJson(`/api/creators/${creator}/subscribe/invoice`, { method: 'POST', body: JSON.stringify({ tier_id: tierId, billing: 'monthly' }) }) });
}
});
}

/* ───────────────────── Tip Form ──────────────────────────────────────── */
function setupTipForm() {
const form = document.getElementById('tip-form'); if (!form) return;
const modal = document.getElementById('tip-modal');
form.addEventListener('submit', async event => {
event.preventDefault(); if (!modal) return;
const creator = modal.dataset.creator;
const contentId = modal.dataset.contentId;
const amount = Number(document.getElementById('tip-amount')?.value || 0);
const message = document.getElementById('tip-message')?.value || null;
if (!creator || amount <= 0) return showToast('Tip bilgisi eksik', 'error');
try {
const inv = await fetchJson(`/api/creators/${creator}/tips/invoice`, { method: 'POST', body: JSON.stringify({ amount_atomic: amount, message, content_id: contentId ? Number(contentId) : null }) });
ModalManager.close('tip-modal');
await openPaymentModalWithInvoice(inv);
showToast('Tip doğrulandı ✓', 'success');
} catch { showToast('Tip oluşturulamadı', 'error'); }
});
}

/* ───────────────────── Comment Form ──────────────────────────────────── */
function setupCommentForm() {
const form = document.getElementById('comment-form'); if (!form) return;
form.addEventListener('submit', async e => {
e.preventDefault();
const input = document.getElementById('comment-input');
const body = input?.value?.trim(); if (!body || !currentCommentContentId) return;
try {
const c = await fetchJson(`/api/contents/${currentCommentContentId}/comments`, { method: 'POST', body: JSON.stringify({ body }) });
const list = document.getElementById('comments-list');
const empty = document.getElementById('comments-empty');
if (empty) empty.classList.add('hidden');
if (list) list.appendChild(renderComment(c));
if (input) input.value = '';
showToast('Yorum eklendi', 'success');
} catch { showToast('Yorum eklenemedi', 'error'); }
});
}

/* ═══════════════════════════════════════════════════════════════════════
   Boot
   ═══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
if (!uiEnabled) { console.info('[SF] UI disabled'); return; }
try {
['tier-modal', 'payment-modal', 'tip-modal', 'comment-modal'].forEach(id => ModalManager.register(id));
document.getElementById('payment-close-success')?.addEventListener('click', () => ModalManager.close('payment-modal'));

setupFeed();
setupCreatorPage();
setupCreatePage();
setupBookmarksPage();
setupNotificationsPage();
setupGlobalActions();
setupTipForm();
setupCommentForm();
} catch (err) { console.error('[SF boot]', err); if (IS_LOCAL) debugToast(`Boot: ${err.message}`); }
});
