@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'All registered users')

@section('content')
<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-3 font-medium text-slate-600">ID</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Name</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Email</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Admin</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Location</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $u->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                    <td class="px-4 py-3">{{ $u->isAdmin() ? 'Yes' : '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $u->profile?->location ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $u->created_at->format('M j, Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No users yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $users->links() }}
    </div>
</div>
@endsection
