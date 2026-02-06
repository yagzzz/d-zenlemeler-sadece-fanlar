@extends('layouts.app')

@section('content')
<div class="content-column" data-page="notifications">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Bildirimler</h1>
            <p class="text-sm text-slate-400">Aktivite ve güncellemeler.</p>
        </div>
        <button id="mark-all-read" class="btn-ghost text-xs">Tümünü Okundu İşaretle</button>
    </div>

    <div id="notifications-list" class="post-box divide-y divide-white/5 overflow-hidden">
        <div class="p-6 text-center">
            <p class="text-sm text-slate-400">Bildirimler yükleniyor…</p>
        </div>
    </div>

    <div id="notifications-empty" class="hidden post-box p-8 text-center">
        <p class="text-3xl">🔔</p>
        <h2 class="mt-3 text-base font-semibold">Bildirim yok</h2>
        <p class="mt-1 text-sm text-slate-400">Yeni bir şey olduğunda burada görünecek.</p>
    </div>
</div>
@endsection
