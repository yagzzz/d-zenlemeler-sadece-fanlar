@extends('layouts.app')

@section('content')
<div class="content-column" data-page="billing">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Ödeme & Faturalama</h1>
        <p class="text-muted" style="font-size:0.875rem;">Ödeme yöntemlerini ve fatura geçmişini yönet.</p>
    </div>

    <div class="post-box p-4 mb-3">
        <h3 class="text-bold mb-2" style="font-size:0.875rem;">Monero Cüzdan</h3>
        <p class="text-muted" style="font-size:0.875rem;">Monero (XMR) ile ödeme yapabilir ve alabilirsiniz.</p>
        <div class="empty-state mt-3">
            <p class="text-muted" style="font-size:0.875rem;">Henüz bir ödeme yöntemi eklenmemiş.</p>
            <p class="text-muted mt-1" style="font-size:0.75rem;">Bu özellik yakında aktif olacak.</p>
        </div>
    </div>

    <div class="post-box p-4">
        <h3 class="text-bold mb-2" style="font-size:0.875rem;">Fatura Geçmişi</h3>
        <div class="empty-state mt-3">
            <p class="text-muted" style="font-size:0.875rem;">Henüz fatura bulunmuyor.</p>
        </div>
    </div>
</div>
@endsection
