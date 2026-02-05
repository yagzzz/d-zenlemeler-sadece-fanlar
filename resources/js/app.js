import './bootstrap';

/* ═══════════════════════════════════════════════════════════════════════
   Sadece Fanlar — Client Application
   ═══════════════════════════════════════════════════════════════════════ */

/* ───────────────────── Debug (local only) ─────────────────────────────── */
const IS_LOCAL = document.body.dataset.appEnv === 'local' || document.body.dataset.appEnv === 'testing';

if (IS_LOCAL) {
	window.addEventListener('error', (e) => {
		console.error('[SF]', e.message, e.filename, e.lineno);
		debugToast(`JS: ${e.message}`);
	});
	window.addEventListener('unhandledrejection', (e) => {
		console.error('[SF rejection]', e.reason);
		debugToast(`Rejection: ${String(e.reason)}`);
	});
}

function debugToast(msg) {
	const root = document.getElementById('toast-root');
	if (!root) return;
	const el = document.createElement('div');
	el.className = 'rounded-2xl px-4 py-3 text-xs shadow-lg bg-amber-600 text-white max-w-xs truncate';
	el.textContent = msg;
	root.appendChild(el);
	setTimeout(() => el.remove(), 5000);
}

/* ───────────────────── Globals ────────────────────────────────────────── */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const toastRoot = document.getElementById('toast-root');
const uiEnabled = document.body.dataset.uiEnabled === '1';
const uiPolishEnabled = document.body.dataset.uiPolishEnabled === '1';

const ctaLabels = (() => {
	const node = document.getElementById('cta-labels');
	if (!node) return {};
	try { return JSON.parse(node.textContent || '{}'); }
	catch { return {}; }
})();

/* ───────────────────── Toast ─────────────────────────────────────────── */
function showToast(message, type = 'info') {
	if (!toastRoot) return;
	const el = document.createElement('div');
	el.className = `rounded-2xl px-4 py-3 text-sm shadow-lg transition-all
		${type === 'error' ? 'bg-rose-500' : type === 'success' ? 'bg-emerald-500' : 'bg-slate-700'} text-white`;
	el.textContent = message;
	toastRoot.appendChild(el);
	setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}

/* ───────────────────── Fetch ─────────────────────────────────────────── */
async function fetchJson(url, options = {}) {
	try {
		const response = await fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json',
				'X-CSRF-TOKEN': csrfToken || '',
				...(options.headers || {}),
			},
			credentials: 'same-origin',
			...options,
		});
		if (!response.ok) throw new Error(`HTTP ${response.status}`);
		return await response.json();
	} catch (err) {
		if (IS_LOCAL) console.warn('[SF fetch]', url, err.message);
		throw err;
	}
}

/* ═══════════════════════════════════════════════════════════════════════
   Modal Manager
   ═══════════════════════════════════════════════════════════════════════ */
const ModalManager = (() => {
	const modals = {};

	function register(id) {
		const el = document.getElementById(id);
		if (!el || modals[id]) return;
		modals[id] = el;

		// Close buttons
		el.querySelectorAll('[data-tier-close],[data-payment-close],[data-tip-close]')
			.forEach(btn => btn.addEventListener('click', () => close(id)));

		// Backdrop click
		const backdrop = el.querySelector('.modal-backdrop');
		if (backdrop) backdrop.addEventListener('click', () => close(id));

		// ESC
		el.addEventListener('keydown', e => { if (e.key === 'Escape') close(id); });
	}

	function open(id) {
		if (!modals[id]) register(id);
		const el = modals[id];
		if (!el) return null;
		el.classList.remove('hidden');
		el.setAttribute('tabindex', '-1');
		el.focus();
		document.body.style.overflow = 'hidden';
		return el;
	}

	function close(id) {
		const el = modals[id];
		if (!el) return;
		el.classList.add('hidden');
		document.body.style.overflow = '';
	}

	return { register, open, close };
})();

/* ───────────────────── Content Card ──────────────────────────────────── */
function renderContentCard(item) {
	const template = document.getElementById('content-card-template');
	if (!template) return null;
	const node = template.content.cloneNode(true);
	const card = node.querySelector('.content-card');
	const lockedOverlay = node.querySelector('.locked-overlay');
	const badge = node.querySelector('.content-badge');
	const bodyEl = node.querySelector('.content-body');
	const ctaButton = node.querySelector('.cta-button');
	const tips = node.querySelector('.tips-summary');
	const avatar = node.querySelector('.creator-avatar');
	const creatorName = node.querySelector('.creator-name');

	card.dataset.contentId = item.id;
	card.querySelector('.content-title').textContent = item.title;
	card.querySelector('.content-visibility').textContent = item.visibility.replace('_', ' ');

	if (creatorName) creatorName.textContent = item.creator_username || '';
	if (avatar) avatar.textContent = (item.creator_username || '?')[0].toUpperCase();

	if (item.locked) {
		lockedOverlay?.classList.remove('hidden');
		badge?.classList.remove('hidden');
		bodyEl.textContent = 'Bu içerik kilitli. Kilidi açmak için harekete geç.';
		const reason = item.lock_reason || 'subscription_required';
		const label = ctaLabels[reason] || "Tier'leri Gör";
		ctaButton.textContent = label;
		ctaButton.classList.remove('hidden');
		ctaButton.dataset.reason = reason;
		ctaButton.dataset.creator = item.creator_username || '';
		ctaButton.dataset.contentId = item.id;
	} else {
		bodyEl.textContent = item.body || '';
		if (item.tip_cta) {
			ctaButton.textContent = 'Tip Gönder 💎';
			ctaButton.classList.remove('hidden');
			ctaButton.dataset.reason = 'tip';
			ctaButton.dataset.creator = item.creator_username || '';
			ctaButton.dataset.contentId = item.id;
		}
	}

	tips.textContent = item.tips?.count ? `💎 ${item.tips.count} tip` : '';

	return node;
}

/* ───────────────────── Tier Modal ────────────────────────────────────── */
async function openTierModal(username) {
	const modal = ModalManager.open('tier-modal');
	if (!modal || !username) return;

	const list = modal.querySelector('#tier-compare-list');
	if (!list) return;
	list.innerHTML = '<div class="skeleton-card h-24"></div>';

	try {
		const payload = await fetchJson(`/api/creators/${username}/tiers/compare`);
		list.innerHTML = '';
		(payload.tiers || []).forEach(tier => {
			const card = document.createElement('div');
			card.className = 'rounded-2xl border border-white/10 p-4 bg-white/5 hover:bg-white/10 transition-colors';
			card.innerHTML = `
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-semibold">${tier.name}</p>
						<p class="text-xs text-slate-400 mt-1">${tier.description || ''}</p>
					</div>
					${tier.badges?.length ? `<span class="rounded-full bg-amber-400/20 text-amber-300 px-2 py-0.5 text-xs">${tier.badges[0]}</span>` : ''}
				</div>
				<div class="mt-3 flex items-center justify-between">
					<span class="text-sm font-mono">${tier.price?.monthly_atomic ?? tier.price_atomic ?? '?'} XMR/ay</span>
					<button class="rounded-xl bg-white text-slate-900 px-4 py-2 text-xs font-semibold hover:bg-slate-100 transition-colors"
						data-tier-id="${tier.id}" data-creator="${username}">Seç</button>
				</div>`;
			list.appendChild(card);
		});
	} catch {
		list.innerHTML = '<p class="text-sm text-rose-300 p-4">Tier bilgisi yüklenemedi.</p>';
	}
}

/* ───────────────────── Payment Modal ─────────────────────────────────── */
function openPaymentModal({ summary, invoiceAction }) {
	const modal = ModalManager.open('payment-modal');
	if (!modal) return;

	const summaryEl = modal.querySelector('#payment-summary');
	const stepSummary = modal.querySelector('[data-step="summary"]');
	const stepWaiting = modal.querySelector('[data-step="waiting"]');
	const stepSuccess = modal.querySelector('[data-step="success"]');

	summaryEl.textContent = summary;
	stepSummary.classList.remove('hidden');
	stepWaiting.classList.add('hidden');
	stepSuccess.classList.add('hidden');

	modal.querySelector('#payment-start')?.addEventListener('click', async () => {
		try {
			const invoice = await invoiceAction();
			stepSummary.classList.add('hidden');
			stepWaiting.classList.remove('hidden');
			modal.querySelector('#payment-address').textContent = invoice.address;
			modal.querySelector('#payment-amount').textContent = `${invoice.amount_atomic} XMR`;
			modal.querySelector('#payment-invoice').textContent = invoice.invoice_id;
			await pollVerify(invoice.invoice_id);
			stepWaiting.classList.add('hidden');
			stepSuccess.classList.remove('hidden');
		} catch {
			showToast('Ödeme oluşturulamadı', 'error');
		}
	}, { once: true });
}

async function openPaymentModalWithInvoice(invoice) {
	const modal = ModalManager.open('payment-modal');
	if (!modal) return;

	const stepSummary = modal.querySelector('[data-step="summary"]');
	const stepWaiting = modal.querySelector('[data-step="waiting"]');
	const stepSuccess = modal.querySelector('[data-step="success"]');

	stepSummary.classList.add('hidden');
	stepWaiting.classList.remove('hidden');
	stepSuccess.classList.add('hidden');

	modal.querySelector('#payment-address').textContent = invoice.address;
	modal.querySelector('#payment-amount').textContent = `${invoice.amount_atomic} XMR`;
	modal.querySelector('#payment-invoice').textContent = invoice.invoice_id;

	try {
		await pollVerify(invoice.invoice_id);
		stepWaiting.classList.add('hidden');
		stepSuccess.classList.remove('hidden');
	} catch {
		showToast('Ödeme doğrulanamadı', 'error');
	}
}

/* ───────────────────── Tip Modal ─────────────────────────────────────── */
function openTipModal({ creator, contentId }) {
	const modal = ModalManager.open('tip-modal');
	if (!modal || !creator) return;
	modal.dataset.creator = creator;
	modal.dataset.contentId = contentId || '';
}

/* ───────────────────── Poll Verify ───────────────────────────────────── */
function pollVerify(invoiceId) {
	const statusEl = document.getElementById('payment-status');
	let attempts = 0;
	return new Promise((resolve, reject) => {
		const timer = setInterval(async () => {
			attempts += 1;
			try {
				const result = await fetchJson(`/api/invoices/${invoiceId}/verify`, { method: 'POST' });
				if (statusEl) statusEl.textContent = result.status === 'paid' ? 'Başarılı ✓' : 'Bekleniyor…';
				if (result.status === 'paid') { clearInterval(timer); resolve(result); }
				if (attempts >= 20) { clearInterval(timer); reject(new Error('timeout')); }
			} catch { clearInterval(timer); reject(new Error('verify-error')); }
		}, 3000);
	});
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Feed
   ═══════════════════════════════════════════════════════════════════════ */
async function setupFeed() {
	const root = document.querySelector('[data-page="feed"]');
	if (!root) return;

	const endpoint = root.dataset.feedEndpoint;
	const skeleton = document.getElementById('feed-skeleton');
	const list = document.getElementById('feed-list');
	const emptyState = document.getElementById('feed-empty');

	try {
		const payload = await fetchJson(endpoint);
		skeleton?.remove();

		if (!payload.data?.length) {
			emptyState?.classList.remove('hidden');
			return;
		}

		payload.data.forEach(item => {
			const node = renderContentCard(item);
			if (node) list?.appendChild(node);
		});
	} catch {
		skeleton?.remove();
		emptyState?.classList.remove('hidden');
		showToast('Feed yüklenemedi', 'error');
	}
}

/* ═══════════════════════════════════════════════════════════════════════
   Page: Creator
   ═══════════════════════════════════════════════════════════════════════ */
async function setupCreatorPage() {
	const page = document.querySelector('[data-page="creator"]');
	if (!page) return;

	const username = page.dataset.username;
	const profileEndpoint = page.dataset.profileEndpoint;
	const contentsEndpoint = page.dataset.contentsEndpoint;
	const tiersEndpoint = page.dataset.tiersEndpoint;
	const analyticsEndpoint = page.dataset.analyticsEndpoint;

	try {
		const profile = await fetchJson(profileEndpoint);
		const nameEl = document.getElementById('creator-name');
		const tagEl = document.getElementById('creator-tagline');
		const avatarEl = document.getElementById('creator-avatar');
		if (nameEl) nameEl.textContent = profile.creator?.display_name || username;
		if (tagEl) tagEl.textContent = profile.profile?.tagline || 'Premium içerikler burada.';
		if (avatarEl) avatarEl.textContent = (profile.creator?.display_name || username)[0].toUpperCase();
	} catch { showToast('Profil yüklenemedi', 'error'); }

	try {
		const contents = await fetchJson(contentsEndpoint);
		const list = document.getElementById('creator-contents');
		(contents.data || []).forEach(item => {
			const node = renderContentCard(item);
			if (node) list?.appendChild(node);
		});
	} catch { showToast('İçerik yüklenemedi', 'error'); }

	if (tiersEndpoint) {
		try {
			const tiers = await fetchJson(tiersEndpoint);
			const list = document.getElementById('creator-tiers');
			(tiers.data || []).forEach(tier => {
				const card = document.createElement('div');
				card.className = 'rounded-2xl border border-white/10 bg-white/5 p-5 hover:bg-white/10 transition-colors';
				card.innerHTML = `
					<p class="text-sm font-semibold">${tier.name}</p>
					<p class="text-xs text-slate-400 mt-1">${tier.description || ''}</p>
					<p class="mt-3 text-lg font-mono font-semibold">${tier.price_atomic} <span class="text-xs text-slate-400">${tier.currency}/ay</span></p>
					<button class="mt-3 w-full rounded-xl bg-white/10 hover:bg-white/20 text-white py-2 text-xs font-semibold transition-colors"
						data-tier-id="${tier.id}" data-creator="${username}">Abone Ol</button>`;
				list?.appendChild(card);
			});
		} catch { showToast('Tier yüklenemedi', 'error'); }
	}

	if (analyticsEndpoint) {
		try {
			const a = await fetchJson(analyticsEndpoint);
			const t1 = document.getElementById('creator-tips-total');
			const t2 = document.getElementById('creator-tips-count');
			const t3 = document.getElementById('creator-subscribers');
			if (t1) t1.textContent = `${a.tips_total_atomic} XMR`;
			if (t2) t2.textContent = `${a.tips_count} tip`;
			if (t3) t3.textContent = String(a.active_subscribers);
		} catch { /* silent */ }
	}

	document.getElementById('subscribe-cta')?.addEventListener('click', () => openTierModal(username));
	document.getElementById('compare-tiers')?.addEventListener('click', () => openTierModal(username));
	document.getElementById('tip-cta')?.addEventListener('click', () => openTipModal({ creator: username }));
}

/* ═══════════════════════════════════════════════════════════════════════
   Global Click Delegation
   ═══════════════════════════════════════════════════════════════════════ */
function setupGlobalActions() {
	document.body.addEventListener('click', async (event) => {
		const target = event.target.closest('[data-action], .cta-button, [data-tier-id]');
		if (!target) return;

		const action = target.dataset.action;
		if (action === 'navigate') {
			event.preventDefault();
			window.location.href = target.dataset.href;
			return;
		}
		if (action === 'open-tier-modal') {
			openTierModal(target.dataset.creator);
			return;
		}
		if (action === 'open-tip-modal') {
			openTipModal({ creator: target.dataset.creator });
			return;
		}

		if (target.matches('.cta-button')) {
			const { reason, creator, contentId } = target.dataset;

			if (reason === 'tier_required' || reason === 'subscription_required') {
				if (!creator) return showToast('Creator bulunamadı', 'error');
				return openTierModal(creator);
			}
			if (reason === 'ppv_required') {
				return openPaymentModal({
					summary: 'PPV içerik satın al',
					invoiceAction: () => fetchJson('/payments/ppv/invoice', {
						method: 'POST',
						body: JSON.stringify({ content_id: Number(contentId) }),
					}),
				});
			}
			if (reason === 'not_logged_in' || reason === 'not_verified') {
				return showToast('Devam etmek için giriş yapın', 'error');
			}
			if (reason === 'tip') {
				if (!creator) return;
				return openTipModal({ creator, contentId: Number(contentId) });
			}
		}

		if (target.matches('[data-tier-id]')) {
			const tierId = target.getAttribute('data-tier-id');
			const creator = target.getAttribute('data-creator');
			if (!tierId || !creator) return;
			openPaymentModal({
				summary: 'Tier aboneliği başlat',
				invoiceAction: () => fetchJson(`/api/creators/${creator}/subscribe/invoice`, {
					method: 'POST',
					body: JSON.stringify({ tier_id: tierId, billing: 'monthly' }),
				}),
			});
		}
	});
}

/* ───────────────────── Tip Form ──────────────────────────────────────── */
function setupTipForm() {
	const form = document.getElementById('tip-form');
	if (!form) return;
	const modal = document.getElementById('tip-modal');

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		if (!modal) return;
		const creator = modal.dataset.creator;
		const contentId = modal.dataset.contentId;
		const amount = Number(document.getElementById('tip-amount')?.value || 0);
		const message = document.getElementById('tip-message')?.value || null;

		if (!creator || amount <= 0) { showToast('Tip bilgisi eksik', 'error'); return; }

		try {
			const invoice = await fetchJson(`/api/creators/${creator}/tips/invoice`, {
				method: 'POST',
				body: JSON.stringify({ amount_atomic: amount, message, content_id: contentId ? Number(contentId) : null }),
			});
			ModalManager.close('tip-modal');
			await openPaymentModalWithInvoice(invoice);
			showToast('Tip doğrulandı ✓', 'success');
		} catch { showToast('Tip oluşturulamadı', 'error'); }
	});
}

/* ═══════════════════════════════════════════════════════════════════════
   Boot
   ═══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
	if (!uiEnabled) {
		console.info('[SF] UI disabled');
		return;
	}

	try {
		ModalManager.register('tier-modal');
		ModalManager.register('payment-modal');
		ModalManager.register('tip-modal');

		document.getElementById('payment-close-success')?.addEventListener('click', () => {
			ModalManager.close('payment-modal');
		});

		setupFeed();
		setupCreatorPage();
		setupGlobalActions();
		setupTipForm();
	} catch (err) {
		console.error('[SF boot]', err);
		if (IS_LOCAL) debugToast(`Boot error: ${err.message}`);
	}
});
