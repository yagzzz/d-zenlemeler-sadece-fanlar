@extends('layouts.app')

@section('content')
<div class="content-column" data-page="profile">
    <div class="mb-6">
        <h1 class="text-xl font-bold">Profil</h1>
        <p class="text-sm text-slate-400">Hesap ayarlarını yönet.</p>
    </div>

    {{-- Profile Card --}}
    <div class="post-box p-5 mb-4" data-testid="profile-card">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-lg font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="text-base font-semibold">{{ auth()->user()->name ?? 'Kullanıcı' }}</p>
                <p class="text-sm text-slate-400">{{ auth()->user()->email ?? '—' }}</p>
                <span class="mt-1 inline-block rounded-full bg-white/10 px-3 py-0.5 text-xs text-slate-300">{{ auth()->user()->role ?? 'user' }}</span>
            </div>
        </div>
    </div>

    {{-- Settings --}}
    <div class="space-y-4" data-testid="profile-settings">
        <div class="post-box p-5">
            <h3 class="text-sm font-semibold mb-4">Hesap Bilgileri</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">İsim</label>
                    <input type="text" value="{{ auth()->user()->name ?? '' }}" class="sf-input mt-1" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">Kullanıcı Adı</label>
                    <input type="text" value="{{ auth()->user()->username ?? '' }}" class="sf-input mt-1" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">E-posta</label>
                    <input type="email" value="{{ auth()->user()->email ?? '' }}" class="sf-input mt-1 text-slate-400" disabled />
                </div>
            </div>
            <button class="mt-4 btn-outline text-xs">Kaydet</button>
        </div>

        <div class="post-box p-5">
            <h3 class="text-sm font-semibold mb-4">Bildirimler</h3>
            <div class="space-y-3">
                @foreach (['Yeni abonelik', 'Tip bildirimi', 'Yeni mesaj'] as $notif)
                    <label class="flex items-center justify-between cursor-pointer">
                        <span class="text-sm text-slate-300">{{ $notif }}</span>
                        <div class="relative h-6 w-11 rounded-full bg-white/10">
                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-fuchsia-400 transition-transform"></div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        @if (auth()->user()?->role === 'user')
            <div class="rounded-xl border border-fuchsia-400/20 bg-fuchsia-500/5 p-5">
                <h3 class="text-sm font-semibold">Creator Ol</h3>
                <p class="mt-2 text-xs text-slate-400">Creator başvurusu yaparak kendi içeriklerini paylaşmaya başla.</p>
                <button class="mt-3 btn-primary text-xs">Başvur</button>
            </div>
        @endif

        <div class="rounded-xl border border-rose-400/20 bg-rose-500/5 p-5">
            <h3 class="text-sm font-semibold text-rose-300">Tehlikeli Bölge</h3>
            <p class="mt-2 text-xs text-slate-400">Hesabını kalıcı olarak sil. Bu işlem geri alınamaz.</p>
            <button class="mt-3 rounded-xl border border-rose-400/30 px-5 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10 transition-colors">Hesabı Sil</button>
        </div>
    </div>
</div>
@endsection
