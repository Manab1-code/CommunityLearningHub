@extends('layouts.admin')

@section('title', 'Challenges')
@section('heading', 'Weekly & Community Challenges')
@section('subheading', 'Add and manage challenges that users can participate in')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.challenges.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm">+ Add Challenge</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
            <h2 class="font-semibold text-slate-800">Weekly Challenges</h2>
            <p class="text-xs text-slate-500 mt-0.5">Shown for the current week; require start & end dates.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-2 font-medium text-slate-600">Title</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Target</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Points</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Week</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Active</th>
                        <th class="px-4 py-2 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($weeklyChallenges as $c)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2">
                            <span class="mr-1">{{ $c->icon ?? '🎯' }}</span>
                            {{ Str::limit($c->title, 30) }}
                        </td>
                        <td class="px-4 py-2 text-slate-600">{{ $c->target_count }} {{ str_replace('_', ' ', $c->target_type) }}</td>
                        <td class="px-4 py-2 font-medium">{{ $c->points }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $c->start_at ? $c->start_at->format('M j') : '—' }} – {{ $c->end_at ? $c->end_at->format('M j, Y') : '—' }}</td>
                        <td class="px-4 py-2">{{ $c->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2"><a href="{{ route('admin.challenges.edit', $c->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No weekly challenges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-slate-200">{{ $weeklyChallenges->withQueryString()->links() }}</div>
    </div>

    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
            <h2 class="font-semibold text-slate-800">Community Challenges</h2>
            <p class="text-xs text-slate-500 mt-0.5">Ongoing; no end date. Users participate over time.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-2 font-medium text-slate-600">Title</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Target</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Points</th>
                        <th class="px-4 py-2 font-medium text-slate-600">Active</th>
                        <th class="px-4 py-2 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($communityChallenges as $c)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2">
                            <span class="mr-1">{{ $c->icon ?? '🌍' }}</span>
                            {{ Str::limit($c->title, 30) }}
                        </td>
                        <td class="px-4 py-2 text-slate-600">{{ $c->target_count }} {{ str_replace('_', ' ', $c->target_type) }}</td>
                        <td class="px-4 py-2 font-medium">{{ $c->points }}</td>
                        <td class="px-4 py-2">{{ $c->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2"><a href="{{ route('admin.challenges.edit', $c->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No community challenges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-slate-200">{{ $communityChallenges->withQueryString()->links() }}</div>
    </div>
</div>

<p class="mt-6 text-sm text-slate-500">Users see active challenges on the <a href="{{ url('/challenges') }}" class="text-indigo-600 hover:underline">Challenges</a> page and can join to earn points as they complete the targets.</p>
@endsection
