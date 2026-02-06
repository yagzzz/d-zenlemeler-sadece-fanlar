@extends('layouts.app')

@section('content')
<div class="content-column" data-page="notifications">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-bold" style="font-size:1.25rem;">Bildirimler</h1>
            <p class="text-muted" style="font-size:0.875rem;">Aktivite ve güncellemeler.</p>
        </div>
        <button id="mark-all-read" class="btn btn-ghost" style="font-size:0.75rem;">Tümünü Okundu İşaretle</button>
    </div>

    <div id="notifications-list" class="post-box overflow-hidden">
        <div class="p-5 text-center">
            <p class="text-muted" style="font-size:0.875rem;">Bildirimler yükleniyor…</p>
        </div>
    </div>

    <div id="notifications-empty" class="hidden post-box p-6 text-center">
        <p style="font-size:2rem;">🔔</p>
        <h2 class="mt-2 text-bold" style="font-size:1rem;">Bildirim yok</h2>
        <p class="mt-1 text-muted" style="font-size:0.875rem;">Yeni bir şey olduğunda burada görünecek.</p>
    </div>
</div>
@endsection
