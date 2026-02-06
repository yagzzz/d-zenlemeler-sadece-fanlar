@extends('admin.layout')

@section('content')
<h1 style="margin-bottom:1.5rem;">Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_users'] }}</div>
        <div class="stat-label">Toplam Kullanıcı</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_creators'] }}</div>
        <div class="stat-label">Aktif Creator</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_contents'] }}</div>
        <div class="stat-label">Toplam İçerik</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending_reports'] }}</div>
        <div class="stat-label">Bekleyen Raporlar</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending_applications'] }}</div>
        <div class="stat-label">Creator Başvuruları</div>
    </div>
</div>

{{-- Finance Stats --}}
<h2 style="margin-bottom:1rem;">💰 Finans Özeti</h2>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_tips'] }}</div>
        <div class="stat-label">Toplam Tip</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_tips_amount'] / 1e12, 6) }} XMR</div>
        <div class="stat-label">Tip Hacmi</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_subscriptions'] }}</div>
        <div class="stat-label">Toplam Abonelik</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['paid_invoices'] }} / {{ $stats['total_invoices'] }}</div>
        <div class="stat-label">Ödenen / Toplam Fatura</div>
    </div>
</div>

{{-- Recent Invoices --}}
@if ($recentInvoices->count() > 0)
<div class="admin-card">
    <h3>Son 20 Fatura</h3>
    <div style="overflow-x:auto;">
        <table class="admin-table" style="margin-top:0.75rem;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tür</th>
                    <th>Ödeyen</th>
                    <th>Alıcı</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentInvoices as $inv)
                <tr>
                    <td>#{{ $inv->id }}</td>
                    <td><span class="badge badge-info">{{ $inv->invoice_type }}</span></td>
                    <td>{{ $inv->payer?->name ?? '—' }}</td>
                    <td>{{ $inv->payee?->name ?? '—' }}</td>
                    <td>{{ number_format($inv->amount_atomic / 1e12, 6) }} {{ $inv->currency }}</td>
                    <td>
                        @if ($inv->status === 'paid')
                            <span class="badge badge-success">Ödendi</span>
                        @elseif ($inv->status === 'pending')
                            <span class="badge badge-warning">Bekliyor</span>
                        @elseif ($inv->status === 'expired')
                            <span class="badge badge-secondary">Süresi Doldu</span>
                        @else
                            <span class="badge badge-danger">{{ ucfirst($inv->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $inv->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="admin-card">
    <h3>Hızlı Bağlantılar</h3>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:1rem;">
        <a href="/admin/settings" class="btn-sm btn-primary" style="text-decoration:none;">⚙️ Site Ayarları</a>
        <a href="/admin/users" class="btn-sm btn-outline" style="text-decoration:none;">👥 Kullanıcılar</a>
        <a href="/admin/contents" class="btn-sm btn-outline" style="text-decoration:none;">📝 İçerikler</a>
        <a href="/admin/reports" class="btn-sm btn-outline" style="text-decoration:none;">🚩 Raporlar</a>
        <a href="/admin/invoices" class="btn-sm btn-outline" style="text-decoration:none;">💰 Faturalar</a>
        <a href="/admin/creator-applications" class="btn-sm btn-outline" style="text-decoration:none;">🎨 Başvurular</a>
    </div>
</div>
@endsection
