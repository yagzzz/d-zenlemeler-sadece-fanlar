@extends('layouts.app')

@section('content')
<div class="content-column" data-page="admin-applications">
    <div class="mb-4">
        <p class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.3em;">Yönetim Paneli</p>
        <h1 class="text-bold" style="font-size:1.25rem;">Creator Başvuruları</h1>
    </div>

    <div class="post-box p-4">
        <table style="width:100%;font-size:0.875rem;">
            <thead>
                <tr style="color:#94a3b8;text-align:left;">
                    <th style="padding-bottom:0.75rem;">Kullanıcı</th>
                    <th style="padding-bottom:0.75rem;">Durum</th>
                    <th style="padding-bottom:0.75rem;">Tarih</th>
                    <th style="padding-bottom:0.75rem;">İşlemler</th>
                </tr>
            </thead>
            <tbody style="color:#e2e8f0;">
                @forelse ($applications as $application)
                    <tr style="border-top:1px solid rgba(255,255,255,0.1);">
                        <td style="padding:0.75rem 0;">
                            <div class="text-bold">{{ $application->user?->name ?? 'Kullanıcı #'.$application->user_id }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $application->user?->email ?? '' }}</div>
                        </td>
                        <td style="padding:0.75rem 0;">{{ $application->status }}</td>
                        <td style="padding:0.75rem 0;">{{ optional($application->created_at)->toDateTimeString() ?? '-' }}</td>
                        <td style="padding:0.75rem 0;">
                            <form method="POST" action="/admin/creator-applications/{{ $application->id }}/approve" style="display:inline-block;">
                                @csrf
                                <button class="btn btn-primary" style="font-size:0.75rem;padding:0.25rem 0.75rem;">Onayla</button>
                            </form>
                            <form method="POST" action="/admin/creator-applications/{{ $application->id }}/reject" style="display:inline-block;margin-left:0.5rem;">
                                @csrf
                                <input type="hidden" name="rejection_reason" value="Eksik başvuru">
                                <button class="btn btn-outline" style="font-size:0.75rem;padding:0.25rem 0.75rem;border-color:rgba(244,63,94,0.3);color:#fda4af;">Reddet</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding:1rem 0;color:#94a3b8;" colspan="4">Bekleyen başvuru bulunmuyor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
