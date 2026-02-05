@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="inbox">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Inbox</p>
        <h1 class="text-3xl font-semibold">Mesajlar</h1>
        <p class="mt-2 text-sm text-slate-400">Creator'larla ve fan'larla iletişim kur.</p>
    </div>

    <div class="rounded-3xl border border-dashed border-white/20 bg-white/5 p-8 text-center">
        <p class="text-4xl">💬</p>
        <h2 class="mt-4 text-lg font-semibold">Yakında</h2>
        <p class="mt-2 text-sm text-slate-400">Mesajlaşma özelliği çok yakında aktif olacak.</p>
    </div>
</div>
@endsection
