@extends('layouts.app')

@section('content')
<div class="content-column" data-page="inbox">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Mesajlar</h1>
        <p class="text-muted" style="font-size:0.875rem;">Creator'larla ve fan'larla iletişim kur.</p>
    </div>

    <div class="post-box overflow-hidden" data-testid="inbox-threads">
        @php
            $threads = [
                ['name' => 'Destek Ekibi', 'msg' => 'Hoş geldin! Yardıma ihtiyacın olursa bize yaz.', 'time' => '2dk', 'unread' => true],
                ['name' => 'Sistem', 'msg' => 'Hesabın başarıyla oluşturuldu.', 'time' => '1s', 'unread' => false],
            ];
        @endphp
        @foreach ($threads as $thread)
            <div class="thread-item {{ $thread['unread'] ? '' : '' }}">
                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,rgba(217,70,239,0.8),rgba(34,211,238,0.8));display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($thread['name'], 0, 1)) }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-bold" style="font-size:0.875rem;{{ $thread['unread'] ? 'color:#f1f5f9;' : 'color:#cbd5e1;' }}">{{ $thread['name'] }}</p>
                        <span class="text-muted" style="font-size:0.75rem;flex-shrink:0;">{{ $thread['time'] }}</span>
                    </div>
                    <p class="mt-1 text-muted text-truncate" style="font-size:0.75rem;">{{ $thread['msg'] }}</p>
                </div>
                @if ($thread['unread'])
                    <div style="width:8px;height:8px;border-radius:50%;background:#d946ef;flex-shrink:0;"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-3 empty-state">
        <p class="text-muted" style="font-size:0.75rem;">💬 Gerçek zamanlı mesajlaşma Phase B'de aktif olacak.</p>
    </div>
</div>
@endsection
