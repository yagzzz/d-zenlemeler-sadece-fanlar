@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Admin</p>
            <h1 class="text-3xl font-semibold">Creator Applications</h1>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-400">
                <tr>
                    <th class="pb-3">User</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Submitted</th>
                    <th class="pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="text-slate-200">
                @forelse ($applications as $application)
                    <tr class="border-t border-white/10">
                        <td class="py-3">
                            <div class="font-medium">{{ $application->user?->name ?? 'User #'.$application->user_id }}</div>
                            <div class="text-xs text-slate-500">{{ $application->user?->email ?? '' }}</div>
                        </td>
                        <td class="py-3">{{ $application->status }}</td>
                        <td class="py-3">{{ optional($application->created_at)->toDateTimeString() ?? '-' }}</td>
                        <td class="py-3">
                            <form method="POST" action="/admin/creator-applications/{{ $application->id }}/approve" class="inline-block">
                                @csrf
                                <button class="rounded-full bg-emerald-400 px-3 py-1 text-xs font-semibold text-slate-900">Approve</button>
                            </form>
                            <form method="POST" action="/admin/creator-applications/{{ $application->id }}/reject" class="inline-block ml-2">
                                @csrf
                                <input type="hidden" name="rejection_reason" value="Incomplete application">
                                <button class="rounded-full bg-rose-400 px-3 py-1 text-xs font-semibold text-slate-900">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="py-4 text-slate-400" colspan="4">No pending applications.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
