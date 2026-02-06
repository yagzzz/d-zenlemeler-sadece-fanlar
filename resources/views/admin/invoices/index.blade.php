@extends('admin.layout')

@section('content')
<h1 style="margin-bottom:1.5rem;">💰 Fatura Yönetimi</h1>

{{-- Filters --}}
<div class="search-bar">
    <form method="GET" action="/admin/invoices" style="display:flex;gap:0.5rem;width:100%;">
        <select name="status" class="sf-input" style="max-width:180px;" onchange="this.form.submit()">
            <option value="">Tüm Durumlar</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Bekliyor</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Süresi Doldu</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Başarısız</option>
        </select>
        <select name="type" class="sf-input" style="max-width:180px;" onchange="this.form.submit()">
            <option value="">Tüm Türler</option>
            <option value="subscription" {{ request('type') === 'subscription' ? 'selected' : '' }}>Abonelik</option>
            <option value="ppv" {{ request('type') === 'ppv' ? 'selected' : '' }}>PPV</option>
            <option value="tip" {{ request('type') === 'tip' ? 'selected' : '' }}>Tip</option>
        </select>
    </form>
</div>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tür</th>
                    <th>Ödeyen</th>
                    <th>Alıcı</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                <tr>
                    <td>#{{ $invoice->id }}</td>
                    <td><span class="badge badge-info">{{ $invoice->invoice_type }}</span></td>
                    <td>{{ $invoice->payer?->name ?? '—' }} <br><small style="color:#888;">{{ $invoice->payer?->email ?? '' }}</small></td>
                    <td>{{ $invoice->payee?->name ?? '—' }} <br><small style="color:#888;">{{ $invoice->payee?->email ?? '' }}</small></td>
                    <td>{{ number_format($invoice->amount_atomic / 1e12, 6) }} {{ $invoice->currency }}</td>
                    <td>
                        @if ($invoice->status === 'paid')
                            <span class="badge badge-success">Ödendi</span>
                        @elseif ($invoice->status === 'pending')
                            <span class="badge badge-warning">Bekliyor</span>
                        @elseif ($invoice->status === 'expired')
                            <span class="badge badge-secondary">Süresi Doldu</span>
                        @else
                            <span class="badge badge-danger">{{ ucfirst($invoice->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $invoice->created_at->format('d.m.Y H:i') }}</td>
                    <td>
                        @if ($invoice->status === 'pending')
                        <div style="display:flex;gap:0.25rem;">
                            <form method="POST" action="/admin/invoices/{{ $invoice->id }}/mark-paid">
                                @csrf
                                <button type="submit" class="btn-sm btn-success" title="Ödendi İşaretle">✓</button>
                            </form>
                            <form method="POST" action="/admin/invoices/{{ $invoice->id }}/mark-failed">
                                @csrf
                                <button type="submit" class="btn-sm btn-danger" title="Başarısız İşaretle">✗</button>
                            </form>
                        </div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:#888;">Fatura bulunamadı.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($invoices->hasPages())
    <div class="pagination-links">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
