@extends('layouts.app')

@section('content')
<div class="content-column" data-page="inbox">
    <div class="mb-6">
        <h1 class="text-xl font-bold">Mesajlar</h1>
        <p class="text-sm text-slate-400">Creator'larla ve fan'larla iletişim kur.</p>
    </div>

    <div class="post-box divide-y divide-white/5 overflow-hidden" data-testid="inbox-threads">
        @php
            $threads = [
                ['name' => 'Destek Ekibi', 'msg' => 'Hoş geldin! Yardıma ihtiyacın olursa bize yaz.', 'time' => '2dk', 'unread' => true],
                ['name' => 'Sistem', 'msg' => 'Hesabın başarıyla oluşturuldu.', 'time' => '1s', 'unread' => false],
            ];
        @endphp
        @foreach ($threads as $thread)
            <div class="flex items-center gap-3 p-4 hover:bg-white/5 transition-colors cursor-pointer {{ $thread['unread'] ? 'bg-white/[0.02]' : '' }}">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500/80 to-cyan-400/80 text-xs font-bold text-white shrink-0">{{ strtoupper(substr($thread['name'], 0, 1)) }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold {{ $thread['unread'] ? 'text-white' : 'text-slate-300' }}">{{ $thread['name'] }}</p>
                        <span class="text-xs text-slate-500 shrink-0">{{ $thread['time'] }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400 truncate">{{ $thread['msg'] }}</p>
                </div>
                @if ($thread['unread'])
                    <div class="h-2 w-2 rounded-full bg-fuchsia-400 shrink-0"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4 rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-center">
        <p class="text-xs text-slate-500">💬 Gerçek zamanlı mesajlaşma Phase B'de aktif olacak.</p>
    </div>
</div>
@endsection
