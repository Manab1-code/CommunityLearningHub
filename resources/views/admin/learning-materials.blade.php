@extends('layouts.admin')

@section('title', 'Learning Materials')
@section('heading', 'Learning Materials')
@section('subheading', 'Resources shared by the community')

@section('content')
<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-3 font-medium text-slate-600">ID</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Title</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Type</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Skill</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Shared by</th>
                    <th class="px-4 py-3 font-medium text-slate-600">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $m)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $m->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ Str::limit($m->title, 40) }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $m->type }}</span></td>
                    <td class="px-4 py-3 text-slate-600">{{ $m->skill_name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $m->user->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $m->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No learning materials yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $materials->links() }}
    </div>
</div>
@endsection
