@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-emerald-100 text-2xl">📖</div>
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Learning</h1>
                    <p class="text-slate-500">Track your sessions and progress with teachers</p>
                </div>
            </div>
            <div class="relative w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input type="text" id="search-courses" placeholder="Search by skill or teacher..." class="w-full pl-9 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow border border-slate-200">
                <p class="text-2xl font-bold text-emerald-600">{{ $inProgressCount }}</p>
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

        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
            <p class="text-sm text-slate-700 mb-2">Browse videos, notes, and guides shared by the community.</p>
            <a href="{{ url('/learning-materials') }}" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium text-sm">View Learning Materials →</a>
        </div>

        @if($recentMaterials->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-3">Recent community resources</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($recentMaterials as $m)
                <a href="{{ route('learning-materials.show', $m->id) }}" class="block p-3 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 transition">
                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $m->type }}</span>
                    <p class="font-medium text-slate-900 mt-1 truncate">{{ $m->title }}</p>
                    <p class="text-xs text-slate-500">{{ $m->user->name }} · {{ $m->skill_name ?: 'General' }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mb-6 flex border-b border-slate-300" data-tabs>
            <button type="button" data-tab="ongoing" class="tab-btn px-4 py-2 -mb-px font-medium border-b-2 border-emerald-600 text-emerald-600">In Progress ({{ $inProgressCount }})</button>
            <button type="button" data-tab="completed" class="tab-btn px-4 py-2 -mb-px font-medium text-slate-500 border-b-2 border-transparent">Completed ({{ $completedCount }})</button>
        </div>

        <div class="space-y-4" data-panel="ongoing">
            @forelse($inProgress as $req)
            <div class="session-card relative border rounded-xl p-4 shadow border-slate-200 bg-white" data-search="{{ strtolower($req->skill_name . ' ' . ($req->teacher->name ?? '')) }}">
                <div class="absolute top-3 right-3 flex items-center gap-2">
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">▶ In Progress</span>
                </div>
                <h3 class="font-semibold text-lg text-slate-900 mb-2">{{ $req->skill_name }}</h3>
                <p class="text-sm text-slate-600 mb-3">Learning with <strong>{{ $req->teacher->name }}</strong>@if($req->teacher->profile && $req->teacher->profile->location) · {{ $req->teacher->profile->location }}@endif</p>
                @if($req->message)
                <p class="text-sm text-slate-500 mb-2 italic">"{{ Str::limit($req->message, 120) }}"</p>
                @endif
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 border-t border-slate-200 pt-2">
                    @if($req->proposed_date)
                    <span>📅 {{ $req->proposed_date->format('M j, Y') }}</span>
                    @endif
                    <span>Level: {{ $req->skill_level ?? '—' }}</span>
                    <a href="{{ url('/session-requests') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">View session details →</a>
                </div>
            </div>
            @empty
            <div class="border rounded-xl p-8 text-center bg-white border-slate-200">
                <p class="text-slate-500 mb-2">No sessions in progress yet.</p>
                <a href="{{ url('/explore') }}" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium">Find teachers on Explore →</a>
            </div>
            @endforelse
        </div>

        <div class="space-y-4 hidden" data-panel="completed">
            @forelse($completed as $req)
            <div class="session-card relative border rounded-xl p-4 shadow border-slate-200 bg-white" data-search="{{ strtolower($req->skill_name . ' ' . ($req->teacher->name ?? '')) }}">
                <div class="absolute top-3 right-3 flex items-center gap-2">
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">✓ Completed</span>
                </div>
                <h3 class="font-semibold text-lg text-slate-900 mb-2">{{ $req->skill_name }}</h3>
                <p class="text-sm text-slate-600 mb-3">Learned with <strong>{{ $req->teacher->name }}</strong></p>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 border-t border-slate-200 pt-2">
                    @if($req->accepted_date)
                    <span>📅 Completed {{ $req->accepted_date->format('M j, Y') }}</span>
                    @endif
                    <span>Level: {{ $req->skill_level ?? '—' }}</span>
                </div>
            </div>
            @empty
            <div class="border rounded-xl p-8 text-center bg-white border-slate-200">
                <p class="text-slate-500">No completed sessions yet. Complete a session with a teacher to see it here.</p>
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
            b.classList.remove('border-emerald-600', 'text-emerald-600');
            b.classList.add('text-slate-500', 'border-transparent');
        });
        this.classList.add('border-emerald-600', 'text-emerald-600');
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
