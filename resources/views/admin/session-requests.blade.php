@extends('layouts.admin')

@section('title', 'Session Requests')
@section('heading', 'Session Requests')
@section('subheading', 'All learning session requests')

@section('content')
<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-3 font-medium text-slate-600">ID</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Skill</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Level</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Learner</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Teacher</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Status</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Proposed</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessionRequests as $sr)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $sr->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $sr->skill_name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $sr->skill_level ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $sr->learner->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $sr->teacher->name ?? '—' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sr->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($sr->status === 'accepted' ? 'bg-blue-100 text-blue-800' : ($sr->status === 'completed' ? 'bg-green-100 text-green-800' : ($sr->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600'))) }}">{{ $sr->status }}</span></td>
                    <td class="px-4 py-3 text-slate-500">{{ $sr->proposed_date ? $sr->proposed_date->format('M j, Y') : '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $sr->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">No session requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $sessionRequests->links() }}
    </div>
</div>
@endsection
