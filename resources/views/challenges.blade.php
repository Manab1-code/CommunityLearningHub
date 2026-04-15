@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-8">
    <div class="max-w-7xl mx-auto mb-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Challenges & Achievements</h1>
            <p class="text-slate-500 mt-1">Complete weekly and community challenges to encourage regular participation</p>
        </div>
        <div class="flex gap-4">
            <div class="bg-white rounded-xl px-6 py-4 shadow-sm border border-slate-200">
                <p class="text-sm text-slate-500">Total Points</p>
                <p class="text-xl font-bold text-emerald-600">{{ number_format($totalPoints ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-xl px-6 py-4 shadow-sm border border-slate-200">
                <p class="text-sm text-slate-500">Badges Earned</p>
                <p class="text-xl font-bold text-emerald-600">{{ $badgesEarned ?? 0 }}/{{ $totalChallenges ?? 0 }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="max-w-7xl mx-auto mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="max-w-7xl mx-auto mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm">{{ session('info') }}</div>
    @endif

    <section class="max-w-7xl mx-auto mb-14">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Weekly Challenges</h2>
            <span class="text-sm text-slate-400">⏳ {{ $daysRemaining ?? 0 }} days remaining</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($weeklyChallenges ?? [] as $item)
                @php
                    $c = $item['challenge'];
                    $progress = $item['progress'];
                    $part = $item['participant'] ?? null;
                    $joined = $part !== null;
                    $completed = $part && $part->completed_at;
                    $pct = $c->target_count > 0 ? min(100, (int) round(($progress / $c->target_count) * 100)) : 0;
                @endphp
                <div class="relative bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <span class="absolute top-4 right-4 text-xs font-medium bg-orange-100 text-orange-500 px-2 py-0.5 rounded-full">+{{ $c->points }} pts</span>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl bg-slate-100">{{ $c->icon ?? '🎯' }}</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $c->title }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $c->description }}</p>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500"><span>Progress</span><span class="font-medium text-slate-900">{{ $progress }}/{{ $c->target_count }}</span></div>
                    <div class="w-full h-2 bg-slate-200 rounded-full"><div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div></div>
                    @if($completed)
                        <div class="py-2.5 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 text-center">✓ Completed</div>
                    @elseif($joined)
                        <a href="{{ url('/session-requests') }}" class="block w-full py-2.5 rounded-lg text-sm font-medium bg-emerald-500 text-white hover:bg-emerald-600 transition text-center">Continue Challenge</a>
                    @else
                        <form action="{{ route('challenges.join', $c->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-medium border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition">Join Challenge</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="md:col-span-3 p-6 bg-white border border-slate-200 rounded-xl text-center text-slate-500">No weekly challenges this week. Check back soon!</div>
            @endforelse
        </div>
    </section>

    <section class="max-w-7xl mx-auto mb-14">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Community Challenges</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($communityChallenges ?? [] as $item)
                @php
                    $c = $item['challenge'];
                    $progress = $item['progress'];
                    $part = $item['participant'] ?? null;
                    $joined = $part !== null;
                    $completed = $part && $part->completed_at;
                    $pct = $c->target_count > 0 ? min(100, (int) round(($progress / $c->target_count) * 100)) : 0;
                @endphp
                <div class="relative bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <span class="absolute top-4 right-4 text-xs font-medium bg-orange-100 text-orange-500 px-2 py-0.5 rounded-full">+{{ $c->points }} pts</span>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl bg-slate-100">{{ $c->icon ?? '🌍' }}</div>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $c->title }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $c->description }}</p>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500"><span>Progress</span><span class="font-medium text-slate-900">{{ $progress }}/{{ $c->target_count }}</span></div>
                    <div class="w-full h-2 bg-slate-200 rounded-full"><div class="h-full rounded-full bg-orange-400 transition-all" style="width: {{ $pct }}%"></div></div>
                    @if($completed)
                        <div class="py-2.5 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 text-center">✓ Completed</div>
                    @elseif($joined)
                        <a href="{{ url('/session-requests') }}" class="block w-full py-2.5 rounded-lg text-sm font-medium bg-emerald-500 text-white hover:bg-emerald-600 transition text-center">Continue Challenge</a>
                    @else
                        <form action="{{ route('challenges.join', $c->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-medium border border-orange-400 text-orange-600 hover:bg-orange-50 transition">Join Challenge</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="md:col-span-3 p-6 bg-white border border-slate-200 rounded-xl text-center text-slate-500">No community challenges yet.</div>
            @endforelse
        </div>
    </section>

    <section class="bg-slate-50 py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-slate-900">Achievement Badges</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach(['First Steps' => ['desc' => 'Complete first session', 'earned' => ($badgesEarned ?? 0) > 0], 'Rising Star' => ['desc' => 'Complete 3 challenges', 'earned' => ($badgesEarned ?? 0) >= 3], 'Knowledge Seeker' => ['desc' => 'Complete 5 challenges', 'earned' => ($badgesEarned ?? 0) >= 5], 'Master Teacher' => ['desc' => 'Complete Teaching Master weekly', 'earned' => false], 'Skill Sharer' => ['desc' => 'Complete Resource Sharer weekly', 'earned' => false], 'Community Star' => ['desc' => 'Complete a community challenge', 'earned' => ($badgesEarned ?? 0) >= 4]] as $title => $data)
                <div class="text-left rounded-2xl border p-5 transition shadow-sm {{ $data['earned'] ? 'bg-white border-orange-400' : 'bg-white border-slate-200 opacity-70' }}">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full mb-4 {{ $data['earned'] ? 'bg-orange-400 text-white' : 'bg-slate-200 text-slate-400' }}">🏅</div>
                    <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
                    <p class="text-sm text-slate-500 mb-3">{{ $data['desc'] }}</p>
                    <span class="inline-block text-xs px-3 py-1 rounded-full {{ $data['earned'] ? 'bg-emerald-400 text-white' : 'bg-slate-200 text-slate-500' }}">{{ $data['earned'] ? 'Earned' : 'Locked' }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-12 bg-white rounded-2xl p-8 text-center shadow-sm">
                <div class="w-14 h-14 mx-auto mb-4 flex items-center justify-center rounded-full bg-emerald-100 text-3xl">🏆</div>
                <h3 class="text-lg font-semibold text-slate-900">Keep Participating!</h3>
                <p class="text-slate-500 text-sm mt-2">Join weekly and community challenges to build habits and earn points. Teach sessions, attend sessions, and share resources to progress.</p>
                <a href="{{ route('challenges') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition">View Challenges</a>
            </div>
        </div>
    </section>
</div>
@endsection
