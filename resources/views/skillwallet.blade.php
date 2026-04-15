@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Skill Wallet</h1>
        <p class="text-slate-500 mb-8">Earn points when you finish teaching a session, then spend points to add a new learning skill to your profile and pursue it with tutors.</p>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
                <p class="text-sm opacity-90 mb-1">Your balance</p>
                <p class="text-4xl font-bold">{{ number_format($balance ?? 0) }}</p>
                <p class="text-sm opacity-90 mt-1">points</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="text-2xl mb-2">📤</div>
                <p class="font-semibold text-slate-900">Earn by teaching</p>
                <p class="text-sm text-slate-500">+{{ $pointsPerTeaching ?? 50 }} pts when you mark a session complete</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="text-2xl mb-2">📥</div>
                <p class="font-semibold text-slate-900">Redeem to learn</p>
                <p class="text-sm text-slate-500">{{ $redeemCost ?? 50 }} pts = add 1 learning skill to your profile</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Redeem points to learn</h2>
                <p class="text-sm text-slate-500 mb-4">Spend {{ $redeemCost ?? 50 }} points to add a <strong>learning</strong> skill to your profile (beginner). Use Explore or session requests to find someone who teaches it. You can’t redeem a skill you already track as learning.</p>
                <form action="{{ route('skillwallet.redeem') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Skill you want to learn</label>
                        <input type="text" name="skill_name" value="{{ old('skill_name') }}" required maxlength="100" placeholder="e.g. Python, React" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none" list="learning-skills-list">
                        @if(($learningSkills ?? collect())->isNotEmpty())
                            <datalist id="learning-skills-list">
                                @foreach($learningSkills as $s)
                                    <option value="{{ $s }}">
                                @endforeach
                            </datalist>
                        @endif
                    </div>
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-semibold" {{ ($balance ?? 0) < ($redeemCost ?? 50) ? 'disabled' : '' }}>
                        Redeem {{ $redeemCost ?? 50 }} points
                    </button>
                    @if(($balance ?? 0) < ($redeemCost ?? 50))
                        <p class="text-xs text-slate-500 mt-2 text-center">Earn more points by teaching or completing challenges.</p>
                    @endif
                </form>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">How to earn points</h2>
                <ul class="space-y-3 text-sm text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">+{{ $pointsPerTeaching ?? 50 }}</span>
                        <span>Teaching — when you mark a session complete (Session requests → Received → Mark complete)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">Varies</span>
                        <span>Challenges — complete weekly and community challenges (points go to your wallet)</span>
                    </li>
                </ul>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <a href="{{ url('/challenges') }}" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">View challenges →</a>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent activity</h2>
            @if(($transactions ?? collect())->isEmpty())
                <p class="text-slate-500 text-sm">No transactions yet. Teach sessions or complete challenges to earn points.</p>
            @else
                <ul class="space-y-3">
                    @foreach($transactions as $tx)
                        <li class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <p class="text-slate-900 font-medium">{{ $tx->description ?? $tx->type }}</p>
                                <p class="text-xs text-slate-400">{{ $tx->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="font-semibold {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
