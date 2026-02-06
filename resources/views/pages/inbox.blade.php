@extends('layouts.app')

@section('content')
<div class="content-column" data-page="inbox">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Mesajlar</h1>
        <p class="text-muted" style="font-size:0.875rem;">Creator'larla ve fan'larla iletişim kur.</p>
    </div>

    {{-- Conversation List --}}
    <div id="inbox-list" data-testid="inbox-threads">
        <div id="inbox-skeleton">
            @for ($i = 0; $i < 3; $i++)
                <div class="skeleton-card mb-2" style="height:60px;"></div>
            @endfor
        </div>
    </div>

    {{-- Empty State --}}
    <div id="inbox-empty" class="hidden">
        <div class="post-box p-6 text-center" data-testid="inbox-empty">
            <p style="font-size:2rem;">💬</p>
            <h2 class="mt-2 text-bold" style="font-size:1rem;">Henüz mesajınız yok</h2>
            <p class="mt-1 text-muted" style="font-size:0.875rem;">Bir creator'a mesaj göndererek sohbet başlatın.</p>
        </div>
    </div>

    {{-- Conversation Detail (hidden by default) --}}
    <div id="inbox-detail" class="hidden">
        <div class="d-flex align-items-center gap-3 mb-3">
            <button id="inbox-back" class="btn-ghost" style="font-size:1.25rem;">←</button>
            <div class="d-flex align-items-center gap-2">
                <div id="detail-avatar" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(217,70,239,0.8),rgba(34,211,238,0.8));display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;flex-shrink:0;"></div>
                <div>
                    <p id="detail-name" class="text-bold" style="font-size:0.875rem;"></p>
                    <p id="detail-username" class="text-muted" style="font-size:0.75rem;"></p>
                </div>
            </div>
        </div>

        <div id="messages-list" class="post-box" style="min-height:300px;max-height:500px;overflow-y:auto;padding:1rem;">
        </div>

        <div class="mt-3">
            <form id="send-form" class="d-flex gap-2">
                <input type="text" id="message-input" class="sf-input" placeholder="Mesajınızı yazın…" style="flex:1;" autocomplete="off" />
                <button type="submit" class="btn btn-primary" style="padding:0.5rem 1.25rem;">Gönder</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const skeleton = document.getElementById('inbox-skeleton');
    const listContainer = document.getElementById('inbox-list');
    const emptyState = document.getElementById('inbox-empty');
    const detailView = document.getElementById('inbox-detail');
    const messagesList = document.getElementById('messages-list');
    const sendForm = document.getElementById('send-form');
    const messageInput = document.getElementById('message-input');
    const backBtn = document.getElementById('inbox-back');

    let currentConversationId = null;
    let currentOtherUserId = null;

    async function loadConversations() {
        try {
            const res = await fetch('/api/inbox', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to load');
            const json = await res.json();
            const conversations = json.data || [];

            skeleton.classList.add('hidden');

            if (conversations.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            let html = '';
            conversations.forEach(conv => {
                const initial = (conv.other_user.name || '?')[0].toUpperCase();
                const lastMsg = conv.last_message ? conv.last_message.body : 'Henüz mesaj yok';
                const time = conv.last_message_at ? timeAgo(new Date(conv.last_message_at)) : '';
                const unread = conv.unread_count > 0;

                html += `
                <div class="thread-item" data-conv-id="${conv.id}" data-other-id="${conv.other_user.id}" data-other-name="${conv.other_user.name}" data-other-username="${conv.other_user.username}" style="cursor:pointer;">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,rgba(217,70,239,0.8),rgba(34,211,238,0.8));display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;flex-shrink:0;">${initial}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="text-bold" style="font-size:0.875rem;${unread ? 'color:#f1f5f9;' : 'color:#cbd5e1;'}">${conv.other_user.name}</p>
                            <span class="text-muted" style="font-size:0.75rem;flex-shrink:0;">${time}</span>
                        </div>
                        <p class="mt-1 text-muted text-truncate" style="font-size:0.75rem;">${truncate(lastMsg, 50)}</p>
                    </div>
                    ${unread ? '<div style="width:8px;height:8px;border-radius:50%;background:#d946ef;flex-shrink:0;"></div>' : ''}
                </div>`;
            });
            listContainer.innerHTML = `<div class="post-box overflow-hidden">${html}</div>`;

            listContainer.querySelectorAll('.thread-item').forEach(el => {
                el.addEventListener('click', () => openConversation(
                    parseInt(el.dataset.convId),
                    parseInt(el.dataset.otherId),
                    el.dataset.otherName,
                    el.dataset.otherUsername
                ));
            });
        } catch (e) {
            skeleton.classList.add('hidden');
            listContainer.innerHTML = '<div class="post-box p-4"><p class="text-muted">Mesajlar yüklenemedi.</p></div>';
        }
    }

    async function openConversation(convId, otherId, name, username) {
        currentConversationId = convId;
        currentOtherUserId = otherId;
        listContainer.classList.add('hidden');
        emptyState.classList.add('hidden');
        detailView.classList.remove('hidden');

        document.getElementById('detail-avatar').textContent = (name || '?')[0].toUpperCase();
        document.getElementById('detail-name').textContent = name;
        document.getElementById('detail-username').textContent = '@' + username;

        try {
            const res = await fetch(`/api/inbox/${convId}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Forbidden');
            const json = await res.json();
            const messages = json.messages?.data || [];

            renderMessages(messages);
        } catch (e) {
            messagesList.innerHTML = '<p class="text-muted p-3">Mesajlar yüklenemedi.</p>';
        }
    }

    function renderMessages(messages) {
        if (messages.length === 0) {
            messagesList.innerHTML = '<p class="text-muted text-center p-3" style="font-size:0.8rem;">Henüz mesaj yok. İlk mesajı gönderin!</p>';
            return;
        }
        let html = '';
        messages.forEach(msg => {
            const isMine = msg.sender_id !== currentOtherUserId;
            const time = new Date(msg.created_at).toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
            html += `
            <div style="display:flex;justify-content:${isMine ? 'flex-end' : 'flex-start'};margin-bottom:0.5rem;">
                <div style="max-width:75%;padding:0.5rem 0.75rem;border-radius:12px;font-size:0.8125rem;${isMine ? 'background:rgba(217,70,239,0.2);color:#f1f5f9;' : 'background:rgba(255,255,255,0.08);color:#cbd5e1;'}">
                    <p>${escapeHtml(msg.body)}</p>
                    <p style="font-size:0.625rem;opacity:0.6;margin-top:0.25rem;text-align:${isMine ? 'right' : 'left'};">${time}</p>
                </div>
            </div>`;
        });
        messagesList.innerHTML = html;
        messagesList.scrollTop = messagesList.scrollHeight;
    }

    backBtn?.addEventListener('click', () => {
        detailView.classList.add('hidden');
        listContainer.classList.remove('hidden');
        currentConversationId = null;
        currentOtherUserId = null;
        loadConversations();
    });

    sendForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = messageInput.value.trim();
        if (!body || !currentOtherUserId) return;

        try {
            const res = await fetch(`/api/inbox/${currentOtherUserId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ body }),
            });

            if (!res.ok) throw new Error('Send failed');
            const json = await res.json();
            messageInput.value = '';

            const msg = json.message;
            const time = new Date(msg.created_at).toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
            const noMsg = messagesList.querySelector('.text-muted.text-center');
            if (noMsg) noMsg.remove();
            messagesList.insertAdjacentHTML('beforeend', `
            <div style="display:flex;justify-content:flex-end;margin-bottom:0.5rem;">
                <div style="max-width:75%;padding:0.5rem 0.75rem;border-radius:12px;font-size:0.8125rem;background:rgba(217,70,239,0.2);color:#f1f5f9;">
                    <p>${escapeHtml(msg.body)}</p>
                    <p style="font-size:0.625rem;opacity:0.6;margin-top:0.25rem;text-align:right;">${time}</p>
                </div>
            </div>`);
            messagesList.scrollTop = messagesList.scrollHeight;
        } catch (err) {
            console.error('Send error:', err);
        }
    });

    function timeAgo(date) {
        const diff = Math.floor((Date.now() - date.getTime()) / 1000);
        if (diff < 60) return 'şimdi';
        if (diff < 3600) return Math.floor(diff / 60) + 'dk';
        if (diff < 86400) return Math.floor(diff / 3600) + 's';
        return Math.floor(diff / 86400) + 'g';
    }

    function truncate(str, len) {
        return str.length > len ? str.substring(0, len) + '…' : str;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    loadConversations();
});
</script>
@endsection
