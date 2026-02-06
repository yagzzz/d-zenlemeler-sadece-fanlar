@extends('layouts.app')

@section('content')
<div class="content-column" data-page="profile">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Profil</h1>
        <p class="text-muted" style="font-size:0.875rem;">Hesap ayarlarını yönet.</p>
    </div>

    {{-- Profile Card --}}
    <div class="post-box p-4 mb-3" data-testid="profile-card">
        <div class="d-flex align-items-center gap-3">
            <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#d946ef,#22d3ee);display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;color:white;">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="text-bold" style="font-size:1rem;">{{ auth()->user()->name ?? 'Kullanıcı' }}</p>
                <p class="text-muted" style="font-size:0.875rem;">{{ auth()->user()->email ?? '—' }}</p>
                <span style="display:inline-block;border-radius:9999px;background:rgba(255,255,255,0.1);padding:0.125rem 0.75rem;font-size:0.75rem;color:#cbd5e1;margin-top:0.25rem;">{{ auth()->user()->role ?? 'user' }}</span>
            </div>
        </div>
    </div>

    {{-- Settings --}}
    <div style="display:flex;flex-direction:column;gap:1rem;" data-testid="profile-settings">
        <div class="settings-card">
            <h3 class="text-bold mb-3" style="font-size:0.875rem;">Hesap Bilgileri</h3>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <div>
                    <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">İsim</label>
                    <input type="text" id="settings-name" value="{{ auth()->user()->name ?? '' }}" class="sf-input mt-1" />
                </div>
                <div>
                    <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Kullanıcı Adı</label>
                    <input type="text" id="settings-username" value="{{ auth()->user()->username ?? '' }}" class="sf-input mt-1" />
                </div>
                <div>
                    <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">E-posta</label>
                    <input type="email" value="{{ auth()->user()->email ?? '' }}" class="sf-input mt-1" style="color:#64748b;" disabled />
                </div>
            </div>
            <button id="save-settings-btn" class="btn btn-outline mt-3" style="font-size:0.75rem;">Kaydet</button>
        </div>

        {{-- Notification Toggles --}}
        @php
            $prefs = auth()->user()->getNotificationPreferencesWithDefaults();
            $toggles = [
                'new_subscription' => 'Yeni abonelik',
                'tip_notification' => 'Tip bildirimi',
                'new_message' => 'Yeni mesaj',
            ];
        @endphp
        <div class="settings-card" data-testid="notification-settings">
            <h3 class="text-bold mb-3" style="font-size:0.875rem;">Bildirimler</h3>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                @foreach ($toggles as $key => $label)
                    <label class="d-flex justify-content-between align-items-center pointer-cursor">
                        <span style="font-size:0.875rem;color:#cbd5e1;">{{ $label }}</span>
                        <div class="toggle-switch {{ ($prefs[$key] ?? true) ? 'active' : '' }}" data-pref-key="{{ $key }}">
                            <input type="checkbox" class="toggle-input" {{ ($prefs[$key] ?? true) ? 'checked' : '' }} style="display:none;" />
                            <div class="toggle-track">
                                <div class="toggle-thumb"></div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        @if (auth()->user()?->role === 'user')
            <div class="creator-apply-zone">
                <h3 class="text-bold" style="font-size:0.875rem;">Creator Ol</h3>
                <p class="mt-2 text-muted" style="font-size:0.75rem;">Creator başvurusu yaparak kendi içeriklerini paylaşmaya başla.</p>
                <button id="creator-apply-btn" class="btn btn-primary mt-2" style="font-size:0.75rem;">Başvur</button>
            </div>
        @endif

        <div class="danger-zone">
            <h3 class="text-bold" style="font-size:0.875rem;color:#fda4af;">Tehlikeli Bölge</h3>
            <p class="mt-2 text-muted" style="font-size:0.75rem;">Hesabını kalıcı olarak sil. Bu işlem geri alınamaz.</p>
            <button id="delete-account-btn" class="btn btn-outline mt-2" style="font-size:0.75rem;border-color:rgba(244,63,94,0.3);color:#fda4af;">Hesabı Sil</button>
        </div>
    </div>
</div>

{{-- Delete Account Confirm Modal --}}
<div id="delete-account-modal" class="modal-overlay hidden" data-testid="delete-modal">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width:400px;margin:auto;padding:2rem;border-radius:1rem;background:#1e1e2e;position:relative;z-index:10;">
        <h3 class="text-bold mb-3" style="font-size:1rem;color:#fda4af;">Hesabı Sil</h3>
        <p class="text-muted mb-3" style="font-size:0.875rem;">Bu işlem geri alınamaz. Tüm verileriniz kalıcı olarak silinecek.</p>
        <div class="mb-3">
            <label class="text-muted" style="font-size:0.75rem;">Onaylamak için şifrenizi girin:</label>
            <input type="password" id="delete-confirm-password" class="sf-input mt-1" placeholder="Şifre" />
        </div>
        <div class="d-flex gap-2" style="gap:0.5rem;">
            <button id="delete-confirm-btn" class="btn btn-outline" style="font-size:0.75rem;border-color:rgba(244,63,94,0.5);color:#fda4af;flex:1;">Evet, Sil</button>
            <button id="delete-cancel-btn" class="btn btn-outline" style="font-size:0.75rem;flex:1;">İptal</button>
        </div>
        <p id="delete-error-msg" class="mt-2" style="font-size:0.75rem;color:#f87171;display:none;"></p>
    </div>
</div>
@endsection
