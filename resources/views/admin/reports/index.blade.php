@extends('admin.layout')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h1>Raporlar</h1>
</div>

<div class="admin-card">
    <form method="GET" action="/admin/reports" class="search-bar">
        <select name="status" style="width:auto;">
            <option value="">Tüm Durumlar</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Bekliyor</option>
            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Çözüldü</option>
            <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Reddedildi</option>
        </select>
        <button type="submit" class="btn-sm btn-primary">🔍 Filtrele</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Raporlayan</th>
                <th>Tür</th>
                <th>Sebep</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->reporter?->name ?? '-' }}</td>
                <td>{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}</td>
                <td>{{ Str::limit($report->reason, 50) }}</td>
                <td>
                    @switch($report->status)
                        @case('pending') <span class="badge badge-warning">Bekliyor</span> @break
                        @case('resolved') <span class="badge badge-success">Çözüldü</span> @break
                        @case('dismissed') <span class="badge badge-secondary">Reddedildi</span> @break
                    @endswitch
                </td>
                <td>{{ $report->created_at->format('d.m.Y') }}</td>
                <td>
                    @if ($report->status === 'pending')
                    <form method="POST" action="/admin/reports/{{ $report->id }}/resolve" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm btn-success">Çöz</button>
                    </form>
                    <form method="POST" action="/admin/reports/{{ $report->id }}/dismiss" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm btn-outline">Reddet</button>
                    </form>
                    @else
                        <span style="color:#888; font-size:0.85rem;">
                            {{ $report->resolver?->name ?? '-' }}
                            ({{ $report->resolved_at?->format('d.m.Y') }})
                        </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888;">Rapor bulunamadı.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($reports->hasPages())
    <div class="pagination-links">
        {{ $reports->links() }}
    </div>
    @endif
</div>
@endsection
