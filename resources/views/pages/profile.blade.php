@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="profile">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Profile</p>
        <h1 class="text-3xl font-semibold">Profil</h1>
        <p class="mt-2 text-sm text-slate-400">Hesap ayarlarını yönet.</p>
    </div>

    {{-- Profile Card --}}
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6" data-testid="profile-card">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-xl font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="text-lg font-semibold">{{ auth()->user()->name ?? 'Kullanıcı' }}</p>
                <p class="text-sm text-slate-400">{{ auth()->user()->email ?? '—' }}</p>
                <span class="mt-1 inline-block rounded-full bg-white/10 px-3 py-0.5 text-xs text-slate-300">{{ auth()->user()->role ?? 'user' }}</span>
            </div>
        </div>
    </div>

    {{-- Settings Sections --}}
    <div class="mt-6 space-y-4" data-testid="profile-settings">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h3 class="text-sm font-semibold">Hesap Bilgileri</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">İsim</label>
                    <input type="text" value="{{ auth()->user()->name ?? '' }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-fuchsia-400/50 focus:outline-none transition-colors" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">Kullanıcı Adı</label>
                    <input type="text" value="{{ auth()->user()->username ?? '' }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-fuchsia-400/50 focus:outline-none transition-colors" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-slate-400">E-posta</label>
                    <input type="email" value="{{ auth()->user()->email ?? '' }}" class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-slate-400" disabled />
                </div>
            </div>
            <button class="mt-4 rounded-xl bg-white/10 hover:bg-white/20 px-6 py-2.5 text-xs font-semibold text-white transition-colors">Kaydet</button>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h3 class="text-sm font-semibold">Bildirimler</h3>
            <div class="mt-4 space-y-3">
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
            <div class="rounded-2xl border border-fuchsia-400/20 bg-fuchsia-500/5 p-5">
                <h3 class="text-sm font-semibold">Creator Ol</h3>
                <p class="mt-2 text-xs text-slate-400">Creator başvurusu yaparak kendi içeriklerini paylaşmaya başla.</p>
                <button class="mt-3 rounded-xl bg-fuchsia-500 hover:bg-fuchsia-600 px-6 py-2.5 text-xs font-semibold text-white transition-colors">Başvur</button>
            </div>
        @endif

        <div class="rounded-2xl border border-rose-400/20 bg-rose-500/5 p-5">
            <h3 class="text-sm font-semibold text-rose-300">Tehlikeli Bölge</h3>
            <p class="mt-2 text-xs text-slate-400">Hesabını kalıcı olarak sil. Bu işlem geri alınamaz.</p>
            <button class="mt-3 rounded-xl border border-rose-400/30 px-6 py-2.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10 transition-colors">Hesabı Sil</button>
        </div>
    </div>
</div>
@endsection
