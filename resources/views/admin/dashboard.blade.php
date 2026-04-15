@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of the platform')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow border border-slate-200">
        <p class="text-sm text-slate-500">Total Users</p>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['users'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow border border-slate-200">
        <p class="text-sm text-slate-500">Session Requests</p>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['session_requests'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Pending: {{ $stats['session_requests_pending'] }} · Accepted: {{ $stats['session_requests_accepted'] }} · Completed: {{ $stats['session_requests_completed'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow border border-slate-200">
        <p class="text-sm text-slate-500">Learning Materials</p>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['learning_materials'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow border border-slate-200">
        <p class="text-sm text-slate-500">Challenges</p>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['challenges'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:text-indigo-700">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-2 font-medium text-slate-600">Name</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Email</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Admin</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $u)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2">{{ $u->name }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-2">{{ $u->isAdmin() ? 'Yes' : '—' }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $u->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Recent Session Requests</h2>
            <a href="{{ route('admin.session-requests') }}" class="text-sm text-indigo-600 hover:text-indigo-700">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-2 font-medium text-slate-600">Skill</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Learner</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Teacher</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Status</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSessionRequests as $sr)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2">{{ $sr->skill_name }}</td>
                        <td class="px-4 py-2">{{ $sr->learner->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $sr->teacher->name ?? '—' }}</td>
                        <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sr->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($sr->status === 'accepted' ? 'bg-blue-100 text-blue-800' : ($sr->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600')) }}">{{ $sr->status }}</span></td>
                        <td class="px-4 py-2 text-slate-500">{{ $sr->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
