import './bootstrap';

/* ───────────────────── Global Error Logger (local only) ───────────────── */
if (document.body.dataset.appEnv === 'local') {
	window.addEventListener('error', (e) => {
		console.error('[SF Error]', e.message, e.filename, e.lineno);
		debugToast(`JS Error: ${e.message}`);
	});
	window.addEventListener('unhandledrejection', (e) => {
		console.error('[SF Rejection]', e.reason);
		debugToast(`Unhandled: ${e.reason}`);
	});
}

function debugToast(msg) {
	const root = document.getElementById('toast-root');
	if (!root) return;
	const el = document.createElement('div');
	el.className = 'rounded-2xl px-4 py-3 text-xs shadow-lg bg-amber-600 text-white';
	el.textContent = msg;
	root.appendChild(el);
	setTimeout(() => el.remove(), 5000);
}

/* ───────────────────── Constants ──────────────────────────────────────── */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const toastRoot = document.getElementById('toast-root');

const ctaLabels = (() => {
	const node = document.getElementById('cta-labels');
	if (!node) return {};
	try {
		return JSON.parse(node.textContent || '{}');
	} catch {
		return {};
	}
})();

const uiEnabled = document.body.dataset.uiEnabled === '1';
const uiPolishEnabled = document.body.dataset.uiPolishEnabled === '1';

/* ───────────────────── Toast ─────────────────────────────────────────── */
const showToast = (message, type = 'info') => {
	if (!toastRoot) return;
	const el = document.createElement('div');
	el.className = `rounded-2xl px-4 py-3 text-sm shadow-lg ${type === 'error' ? 'bg-rose-500' : 'bg-emerald-500'} text-white`;
	el.textContent = message;
	toastRoot.appendChild(el);
	setTimeout(() => el.remove(), 3000);
};

/* ───────────────────── Fetch Helper ──────────────────────────────────── */
const fetchJson = async (url, options = {}) => {
	const response = await fetch(url, {
		headers: {
			'Content-Type': 'application/json',
			Accept: 'application/json',
			'X-CSRF-TOKEN': csrfToken || '',
			...(options.headers || {}),
		},
		credentials: 'same-origin',
		...options,
	});

	if (!response.ok) {
		throw new Error(`Request failed: ${response.status}`);
	}

	return response.json();
};

const renderContentCard = (item) => {
	const template = document.getElementById('content-card-template');
	if (!template) return null;
	const node = template.content.cloneNode(true);
	const card = node.querySelector('.content-card');
	const lockedOverlay = node.querySelector('.locked-overlay');
	const badge = node.querySelector('.content-badge');
	const body = node.querySelector('.content-body');
	const ctaButton = node.querySelector('.cta-button');
	const tips = node.querySelector('.tips-summary');

	card.dataset.contentId = item.id;
	card.querySelector('.content-title').textContent = item.title;
	card.querySelector('.content-visibility').textContent = item.visibility.replace('_', ' ');

	if (item.locked) {
		lockedOverlay?.classList.remove('hidden');
		badge?.classList.remove('hidden');
		body.textContent = 'Bu içerik kilitli. Kilidi açmak için harekete geç.';
		const reason = item.lock_reason || 'subscription_required';
		const label = ctaLabels[reason] || 'Tier’leri Gör';
		ctaButton.textContent = label;
		ctaButton.classList.remove('hidden');
		ctaButton.dataset.reason = reason;
		ctaButton.dataset.creator = item.creator_username || '';
		ctaButton.dataset.contentId = item.id;
	} else {
		body.textContent = item.body || '';
		if (item.tip_cta) {
			ctaButton.textContent = 'Tip';
			ctaButton.classList.remove('hidden');
			ctaButton.dataset.reason = 'tip';
			ctaButton.dataset.creator = item.creator_username || '';
			ctaButton.dataset.contentId = item.id;
		}
	}

	tips.textContent = `Tips ${item.tips?.count ?? 0}`;

	return node;
};

/* ═══════════════════════════════════════════════════════════════════════
   Modal Manager — single registration, ESC + backdrop + scroll lock
   ═══════════════════════════════════════════════════════════════════════ */
const ModalManager = (() => {
	const registered = {};

	const register = (id) => {
		const el = document.getElementById(id);
		if (!el || registered[id]) return;
		registered[id] = el;

		// Close buttons (data-*-close attributes)
		el.querySelectorAll('[data-tier-close],[data-payment-close],[data-tip-close]')
			.forEach((btn) => btn.addEventListener('click', () => close(id)));

		// ESC key
		el.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') close(id);
		});
	};

	const open = (id) => {
		if (!registered[id]) register(id);
		const el = registered[id];
		if (!el) return null;
		el.classList.remove('hidden');
		el.setAttribute('tabindex', '-1');
		el.focus();
		document.body.style.overflow = 'hidden';
		return el;
	};

	const close = (id) => {
		const el = registered[id];
		if (!el) return;
		el.classList.add('hidden');
		document.body.style.overflow = '';
	};

	return { register, open, close };
})();

const openTierModal = async (username) => {
	const modal = ModalManager.open('tier-modal');
	if (!modal || !username) return;

	const list = modal.querySelector('#tier-compare-list');
	if (!list) return;
	list.innerHTML = '<div class="skeleton-card"></div>';

	try {
		const payload = await fetchJson(`/api/creators/${username}/tiers/compare`);
		list.innerHTML = '';
		payload.tiers.forEach((tier) => {
			const card = document.createElement('div');
			card.className = 'rounded-2xl border border-white/10 p-4 bg-white/5';
			card.innerHTML = `
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-semibold">${tier.name}</p>
						<p class="text-xs text-slate-400">${tier.description || ''}</p>
					</div>
					${tier.badges?.length ? `<span class="text-xs text-amber-300">${tier.badges[0]}</span>` : ''}
				</div>
				<div class="mt-3 text-sm">${tier.price.monthly_atomic} XMR (aylık)</div>
				<button class="mt-3 w-full rounded-xl bg-white text-slate-900 py-2 text-xs font-semibold" data-tier-id="${tier.id}" data-creator="${username}">Seç</button>
			`;
			list.appendChild(card);
		});
	} catch (error) {
		list.innerHTML = '<p class="text-sm text-rose-300">Tier bilgisi yüklenemedi.</p>';
	}
};

const openPaymentModal = ({ summary, invoiceAction }) => {
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
		} catch (error) {
			showToast('Ödeme oluşturulamadı', 'error');
		}
	}, { once: true });
};

const openPaymentModalWithInvoice = async (invoice) => {
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
	} catch (error) {
		showToast('Ödeme doğrulanamadı', 'error');
		throw error;
	}
};

const openTipModal = ({ creator, contentId }) => {
	const modal = ModalManager.open('tip-modal');
	if (!modal || !creator) return;
	modal.dataset.creator = creator;
	modal.dataset.contentId = contentId || '';
};

const pollVerify = async (invoiceId) => {
	const statusEl = document.getElementById('payment-status');
	let attempts = 0;
	return new Promise((resolve, reject) => {
		const timer = setInterval(async () => {
			attempts += 1;
			try {
				const result = await fetchJson(`/api/invoices/${invoiceId}/verify`, { method: 'POST' });
				statusEl.textContent = result.status === 'paid' ? 'Başarılı' : 'Bekleniyor…';
				if (result.status === 'paid') {
					clearInterval(timer);
					resolve(result);
				}
				if (attempts >= 20) {
					clearInterval(timer);
					reject();
				}
			} catch {
				clearInterval(timer);
				reject();
			}
		}, 3000);
	});
};

const setupFeed = async () => {
	const feedRoot = document.querySelector('[data-page="feed"]');
	if (!feedRoot) return;
	const endpoint = feedRoot.dataset.feedEndpoint;
	const skeleton = document.getElementById('feed-skeleton');
	const list = document.getElementById('feed-list');
	const emptyState = document.getElementById('feed-empty');

	try {
		const payload = await fetchJson(endpoint);
		skeleton?.remove();

		if (!payload.data || payload.data.length === 0) {
			if (emptyState) emptyState.classList.remove('hidden');
			return;
		}

		payload.data.forEach((item) => {
			const node = renderContentCard(item);
			if (node) list?.appendChild(node);
		});
	} catch {
		skeleton?.remove();
		showToast('Feed yüklenemedi', 'error');
		if (emptyState) emptyState.classList.remove('hidden');
	}
};

const setupCreatorPage = async () => {
	const page = document.querySelector('[data-page="creator"]');
	if (!page) return;
	const profileEndpoint = page.dataset.profileEndpoint;
	const contentsEndpoint = page.dataset.contentsEndpoint;
	const tiersEndpoint = page.dataset.tiersEndpoint;
	const analyticsEndpoint = page.dataset.analyticsEndpoint;
	const username = page.dataset.username;

	try {
		const profile = await fetchJson(profileEndpoint);
		document.getElementById('creator-name').textContent = profile.creator?.display_name || username;
		document.getElementById('creator-tagline').textContent = profile.profile?.tagline || 'Premium içerikler burada.';
	} catch {
		showToast('Profil yüklenemedi', 'error');
	}

	try {
		const contents = await fetchJson(contentsEndpoint);
		const list = document.getElementById('creator-contents');
		contents.data.forEach((item) => {
			const node = renderContentCard(item);
			if (node) list?.appendChild(node);
		});
	} catch {
		showToast('İçerik yüklenemedi', 'error');
	}

	if (uiPolishEnabled && tiersEndpoint) {
		try {
			const tiers = await fetchJson(tiersEndpoint);
			const list = document.getElementById('creator-tiers');
			tiers.data.forEach((tier) => {
				const card = document.createElement('div');
				card.className = 'rounded-2xl border border-white/10 bg-white/5 p-4';
				card.innerHTML = `
					<p class="text-sm font-semibold">${tier.name}</p>
					<p class="text-xs text-slate-400">${tier.description || ''}</p>
					<p class="mt-2 text-sm">${tier.price_atomic} ${tier.currency}</p>
				`;
				list?.appendChild(card);
			});
		} catch {
			showToast('Tier listesi yüklenemedi', 'error');
		}
	}

	if (uiPolishEnabled && analyticsEndpoint) {
		try {
			const analytics = await fetchJson(analyticsEndpoint);
			document.getElementById('creator-tips-total').textContent = `${analytics.tips_total_atomic} XMR`;
			document.getElementById('creator-tips-count').textContent = `${analytics.tips_count} tip`;
			document.getElementById('creator-subscribers').textContent = `${analytics.active_subscribers}`;
		} catch {
			showToast('Analitik yüklenemedi', 'error');
		}
	}

	document.getElementById('subscribe-cta')?.addEventListener('click', () => openTierModal(username));
	document.getElementById('compare-tiers')?.addEventListener('click', () => openTierModal(username));
	document.getElementById('tip-cta')?.addEventListener('click', () => openTipModal({ creator: username }));
};

const setupGlobalActions = () => {
	document.body.addEventListener('click', async (event) => {
		const target = event.target.closest('[data-action], .cta-button, [data-tier-id]');
		if (!target) return;

		/* ── data-action delegation (nav, modals) ── */
		const action = target.dataset.action;
		if (action === 'navigate') {
			event.preventDefault();
			const href = target.dataset.href;
			if (href) window.location.href = href;
			return;
		}
		if (action === 'open-tier-modal') {
			const creator = target.dataset.creator;
			if (creator) openTierModal(creator);
			return;
		}
		if (action === 'open-tip-modal') {
			const creator = target.dataset.creator;
			if (creator) openTipModal({ creator });
			return;
		}

		/* ── CTA buttons (content cards) ── */
		if (target.matches('.cta-button')) {
			const reason = target.dataset.reason;
			const creator = target.dataset.creator;
			const contentId = target.dataset.contentId;

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

		/* ── Tier select buttons ── */
		if (target.matches('[data-tier-id]')) {
			const tierId = target.getAttribute('data-tier-id');
			const creator = target.getAttribute('data-creator');
			if (!tierId || !creator) return;
			openPaymentModal({
				summary: `Tier ${tierId} için abonelik`,
				invoiceAction: () => fetchJson(`/api/creators/${creator}/subscribe/invoice`, {
					method: 'POST',
					body: JSON.stringify({ tier_id: tierId, billing: 'monthly' }),
				}),
			});
		}
	});
};

const setupTipForm = () => {
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

		if (!creator || amount <= 0) {
			showToast('Tip bilgisi eksik', 'error');
			return;
		}

		try {
			const invoice = await fetchJson(`/api/creators/${creator}/tips/invoice`, {
				method: 'POST',
				body: JSON.stringify({
					amount_atomic: amount,
					message,
					content_id: contentId ? Number(contentId) : null,
				}),
			});
			ModalManager.close('tip-modal');
			await openPaymentModalWithInvoice(invoice);
			showToast('Tip doğrulandı');
		} catch {
			showToast('Tip oluşturulamadı', 'error');
		}
	});
};

document.addEventListener('DOMContentLoaded', () => {
	if (!uiEnabled) {
		console.info('UI disabled');
		return;
	}

	// Register modals once at boot
	ModalManager.register('tier-modal');
	ModalManager.register('payment-modal');
	ModalManager.register('tip-modal');

	// Payment success close
	document.getElementById('payment-close-success')?.addEventListener('click', () => {
		ModalManager.close('payment-modal');
	});

	setupFeed();
	setupCreatorPage();
	setupGlobalActions();
	setupTipForm();
});
