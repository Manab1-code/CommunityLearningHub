@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-teal-100 text-2xl">📖</div>
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Teaching</h1>
                    <p class="text-slate-500">Track your mentorship sessions and shared resources</p>
                </div>
            </div>
            <div class="relative w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input type="text" id="search-courses" placeholder="Search by skill or learner..." class="w-full pl-9 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow border border-slate-200">
                <p class="text-2xl font-bold text-teal-600">{{ $inProgressCount }}</p>
                <p class="text-sm text-slate-500">In Progress</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow border border-slate-200">
                <p class="text-2xl font-bold text-slate-900">{{ $completedCount }}</p>
                <p class="text-sm text-slate-500">Completed</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow border border-slate-200">
                <p class="text-2xl font-bold text-slate-900">{{ $avgProgressPercent }}%</p>
                <p class="text-sm text-slate-500">Completion Rate</p>
            </div>
        </div>

        <div class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-xl">
            <p class="text-sm text-slate-700 mb-2">Share videos, notes, or guides with the community. @if($myMaterialsCount > 0) You've shared <strong>{{ $myMaterialsCount }}</strong> resource(s). @endif</p>
            <a href="{{ url('/learning-materials/create') }}" class="inline-flex items-center gap-2 text-teal-600 hover:text-teal-700 font-medium text-sm">Share a resource →</a>
            @if($myMaterialsCount > 0)
            <a href="{{ url('/learning-materials') }}" class="ml-4 inline-flex items-center gap-2 text-slate-600 hover:text-slate-700 font-medium text-sm">View all materials →</a>
            @endif
        </div>

        <div class="mb-6 flex border-b border-slate-300" data-tabs>
            <button type="button" data-tab="ongoing" class="tab-btn px-4 py-2 -mb-px font-medium border-b-2 border-teal-600 text-teal-600">In Progress ({{ $inProgressCount }})</button>
            <button type="button" data-tab="completed" class="tab-btn px-4 py-2 -mb-px font-medium text-slate-500 border-b-2 border-transparent">Completed ({{ $completedCount }})</button>
        </div>

        <div class="space-y-4" data-panel="ongoing">
            @forelse($inProgress as $req)
            <div class="session-card relative border rounded-xl p-4 shadow border-slate-200 bg-white" data-search="{{ strtolower($req->skill_name . ' ' . ($req->learner->name ?? '')) }}">
                <div class="absolute top-3 right-3 flex items-center gap-2">
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-teal-100 text-teal-700">▶ In Progress</span>
                </div>
                <h3 class="font-semibold text-lg text-slate-900 mb-2">{{ $req->skill_name }}</h3>
                <p class="text-sm text-slate-600 mb-3">Teaching <strong>{{ $req->learner->name }}</strong>@if($req->learner->profile && $req->learner->profile->location) · {{ $req->learner->profile->location }}@endif</p>
                @if($req->message)
                <p class="text-sm text-slate-500 mb-2 italic">"{{ Str::limit($req->message, 120) }}"</p>
                @endif
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 border-t border-slate-200 pt-2">
                    @if($req->proposed_date)
                    <span>📅 {{ $req->proposed_date->format('M j, Y') }}</span>
                    @endif
                    <span>Level: {{ $req->skill_level ?? '—' }}</span>
                    <a href="{{ url('/session-requests') }}" class="text-teal-600 hover:text-teal-700 font-medium">View session details →</a>
                </div>
            </div>
            @empty
            <div class="border rounded-xl p-8 text-center bg-white border-slate-200">
                <p class="text-slate-500 mb-2">No teaching sessions in progress yet.</p>
                <a href="{{ url('/matching/learners') }}" class="inline-flex items-center gap-2 text-teal-600 hover:text-teal-700 font-medium">Find learners who want your skills →</a>
            </div>
            @endforelse
        </div>

        <div class="space-y-4 hidden" data-panel="completed">
            @forelse($completed as $req)
            <div class="session-card relative border rounded-xl p-4 shadow border-slate-200 bg-white" data-search="{{ strtolower($req->skill_name . ' ' . ($req->learner->name ?? '')) }}">
                <div class="absolute top-3 right-3">
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">✓ Completed</span>
                </div>
                <h3 class="font-semibold text-lg text-slate-900 mb-2">{{ $req->skill_name }}</h3>
                <p class="text-sm text-slate-600 mb-3">Taught <strong>{{ $req->learner->name }}</strong></p>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 border-t border-slate-200 pt-2">
                    @if($req->accepted_date)
                    <span>📅 Completed {{ $req->accepted_date->format('M j, Y') }}</span>
                    @endif
                    <span>Level: {{ $req->skill_level ?? '—' }}</span>
                </div>
            </div>
            @empty
            <div class="border rounded-xl p-8 text-center bg-white border-slate-200">
                <p class="text-slate-500">No completed teaching sessions yet. When learners complete sessions with you, they'll appear here.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-tabs] .tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        document.querySelectorAll('[data-panel]').forEach(p => { p.classList.add('hidden'); });
        document.querySelector('[data-panel="' + tab + '"]').classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-teal-600', 'text-teal-600');
            b.classList.add('text-slate-500', 'border-transparent');
        });
        this.classList.add('border-teal-600', 'text-teal-600');
        this.classList.remove('text-slate-500', 'border-transparent');
    });
});
document.getElementById('search-courses')?.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.session-card').forEach(card => {
        const text = (card.getAttribute('data-search') || '');
        card.style.display = !q || text.includes(q) ? '' : 'none';
    });
});
</script>
@endsection
