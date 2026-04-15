@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="relative rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-amber-400 p-6 h-48">
            <div class="absolute right-6 bottom-6 flex gap-2">
                @if($profile['isOwnProfile'] ?? true)
                    <a href="{{ url('/update-profile') }}" class="group inline-flex items-center justify-center gap-2 rounded-xl border border-white/50 bg-white px-5 py-2.5 text-sm font-semibold tracking-wide text-emerald-900 shadow-md shadow-emerald-950/10 transition hover:bg-emerald-50 hover:shadow-lg hover:shadow-emerald-950/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600 active:scale-[0.98]">
                        <svg class="h-4 w-4 shrink-0 text-emerald-700 transition group-hover:text-emerald-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.687a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                        Edit Profile
                    </a>
                    <a href="{{ url('/auth/logout') }}" class="bg-red-500 text-white px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-red-600 transition inline-flex items-center gap-1">Logout</a>
                @else
                    <a href="{{ route('messages.with', $profile['userId']) }}" class="bg-white/90 text-emerald-800 px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-emerald-100 transition">💬 Message</a>
                    @if(!empty($profile['teachingSkills']) && count($profile['teachingSkills']) > 0)
                        <a href="{{ url('/session-requests/send/' . $profile['userId']) }}" class="bg-orange-500 text-white px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-orange-600 transition">📅 Request Session</a>
                    @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm -mt-24 relative">
                    <div class="absolute top-0 left-6 -translate-y-1/2">
                        @if(!empty($profile['photoUrl']))
                            <img src="{{ $profile['photoUrl'] }}" alt="profile" class="w-24 h-24 rounded-full border-4 border-white shadow object-cover">
                        @else
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow bg-emerald-100 text-emerald-700 text-3xl font-bold flex items-center justify-center">
                                {{ $profile['initial'] ?? 'U' }}
                            </div>
                        @endif
                    </div>
                    <div class="pt-12">
                        <h2 class="text-xl font-semibold">{{ $profile['name'] ?? 'User' }}</h2>
                        <div class="mt-1 space-y-1 text-sm text-slate-500">
                            <div class="flex items-center gap-2">📍 {{ $profile['location'] ?? '—' }}</div>
                            <div class="flex items-center gap-2">📅 Joined {{ $profile['joinedAt'] ?? date('Y-m-d') }}</div>
                        </div>
                        <p class="mt-4 text-sm text-slate-600 leading-relaxed">{{ $profile['status'] ?? 'No status set yet. Update your profile.' }}</p>
                        <div class="mt-4 flex gap-3">
                            @if(!empty($profile['linkedinUrl']))
                                <a href="{{ $profile['linkedinUrl'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition">in</a>
                            @endif
                            @if(!empty($profile['githubUrl']))
                                <a href="{{ $profile['githubUrl'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center hover:bg-slate-900 hover:text-white transition">⌘</a>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $ls = $profile['learnerStats'] ?? [];
                    $hours = $ls['learning_hours'] ?? 0;
                    $mods = $ls['modules_completed'] ?? 0;
                    $avgR = $ls['avg_learner_rating'] ?? null;
                    $ratedN = $ls['rated_sessions_count'] ?? 0;
                    $catalog = $profile['learnerBadgeCatalog'] ?? [];
                    $earnedCount = collect($catalog)->where('earned', true)->count();
                @endphp
                <div class="bg-white rounded-2xl p-6 shadow-sm grid grid-cols-3 text-center gap-2">
                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-slate-900 tabular-nums">{{ (int) $hours }}</p>
                        <p class="text-xs text-slate-500">Learn hrs <span class="text-slate-400">(est.)</span></p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-slate-900 tabular-nums">{{ (int) $mods }}</p>
                        <p class="text-xs text-slate-500">Modules</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-slate-900 tabular-nums">@if($avgR !== null){{ number_format($avgR, 1) }}@else—@endif</p>
                        <p class="text-xs text-slate-500">Tutor rating @if($ratedN > 0)<span class="text-slate-400">({{ $ratedN }})</span>@endif</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4 gap-2">
                        <h3 class="font-semibold flex items-center gap-2">🏅 Learner achievements</h3>
                        <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $earnedCount }}/{{ count($catalog) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Earned by completing sessions (~1 hr each), finishing learning materials, and strong tutor ratings.</p>
                    <ul class="space-y-3 text-sm">
                        @forelse($catalog as $bd)
                            <li class="flex items-start gap-3 rounded-xl border {{ $bd['earned'] ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-100 bg-slate-50/80 opacity-75' }} p-3">
                                <span class="w-10 h-10 rounded-full {{ $bd['earned'] ? 'bg-white shadow-sm' : 'bg-slate-200' }} flex items-center justify-center text-lg flex-shrink-0" aria-hidden="true">{{ $bd['icon_emoji'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-medium text-slate-900">{{ $bd['name'] }}</p>
                                        <span class="text-[10px] uppercase tracking-wide text-slate-500">{{ $bd['category'] }}</span>
                                        @if($bd['earned'] && !empty($bd['earned_at']))
                                            <span class="text-[11px] text-emerald-600">{{ \Illuminate\Support\Carbon::parse($bd['earned_at'])->timezone($userTimezone ?? config('app.timezone'))->format('M j, Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $bd['description'] }}</p>
                                    @if(!$bd['earned'])
                                        <p class="text-[11px] text-slate-400 mt-1">Locked — keep learning to unlock</p>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Achievement definitions will appear here after setup.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold">Teaching Skills</h3>
                        @if($profile['isOwnProfile'] ?? true)
                            <a href="{{ url('/update-profile') }}" class="text-emerald-600 text-sm hover:text-emerald-800 transition">+ Add Skill</a>
                        @endif
                    </div>
                    @if(!empty($profile['teachingSkills']) && count($profile['teachingSkills']) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($profile['teachingSkills'] as $s)
                                @php
                                    $skillName = is_array($s) ? ($s['name'] ?? '') : $s;
                                    $skillLevel = is_array($s) ? ($s['skill_level'] ?? 'intermediate') : 'intermediate';
                                @endphp
                                <span class="bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-sm">
                                    {{ $skillName }}
                                    @if($skillLevel)
                                        <span class="text-xs opacity-75">({{ ucfirst($skillLevel) }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No teaching skills added yet.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold">Learning Skills</h3>
                        @if($profile['isOwnProfile'] ?? true)
                            <a href="{{ url('/update-profile') }}" class="text-emerald-600 text-sm hover:text-emerald-800 transition">+ Add Skill</a>
                        @endif
                    </div>
                    @if(!empty($profile['learningSkills']) && count($profile['learningSkills']) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($profile['learningSkills'] as $s)
                                @php
                                    $skillName = is_array($s) ? ($s['name'] ?? '') : $s;
                                    $skillLevel = is_array($s) ? ($s['skill_level'] ?? 'beginner') : 'beginner';
                                @endphp
                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm">
                                    {{ $skillName }}
                                    @if($skillLevel)
                                        <span class="text-xs opacity-75">({{ ucfirst($skillLevel) }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No learning skills added yet.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold mb-3 flex items-center gap-2">📊 Learner progress</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Completed <strong>{{ (int) ($profile['completedLearnerSessions'] ?? 0) }}</strong> session(s) as a learner.
                        @if(($ls['modules_completed'] ?? 0) > 0)
                            Finished <strong>{{ (int) $mods }}</strong> learning module(s) from the community library.
                        @endif
                        @if($avgR !== null && $ratedN > 0)
                            Tutors rated your participation an average of <strong>{{ number_format($avgR, 1) }}/5</strong> across {{ $ratedN }} completed session(s).
                        @endif
                    </p>
                    @if(($profile['isOwnProfile'] ?? false) && $earnedCount === 0)
                        <p class="text-xs text-slate-500 mt-3">Tip: ask your tutor to mark sessions complete after you meet, complete materials from <a href="{{ route('learning-materials.index') }}" class="text-emerald-600 hover:underline">Learning materials</a>, and you’ll start collecting badges.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
