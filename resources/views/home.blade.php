@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-400 text-white p-6 rounded-xl mb-6">
            <h2 class="text-2xl font-semibold">Welcome back, {{ $userName ?? 'Guest' }}!</h2>
            <p class="text-sm opacity-90">Ready to share your knowledge and learn something new today?</p>
        </div>

        @if(!empty($analytics))
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-3">📊 Your Analytics</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition-shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Skills Teaching</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $analytics['teachingSkillsCount'] }}</p>
                    @if(!empty($analytics['teachingSkillNames']))
                        <p class="text-xs text-slate-400 mt-1 truncate" title="{{ implode(', ', $analytics['teachingSkillNames']) }}">{{ implode(', ', array_slice($analytics['teachingSkillNames'], 0, 3)) }}{{ count($analytics['teachingSkillNames']) > 3 ? '…' : '' }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition-shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Skills Learning</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $analytics['learningSkillsCount'] }}</p>
                    @if(!empty($analytics['learningSkillNames']))
                        <p class="text-xs text-slate-400 mt-1 truncate" title="{{ implode(', ', $analytics['learningSkillNames']) }}">{{ implode(', ', array_slice($analytics['learningSkillNames'], 0, 3)) }}{{ count($analytics['learningSkillNames']) > 3 ? '…' : '' }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition-shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Points (balance)</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ number_format($analytics['pointsBalance']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total earned: {{ number_format($analytics['totalPointsEarned']) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow hover:shadow-lg transition-shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Badges Earned</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $analytics['badgesEarned'] }}</p>
                    <a href="{{ url('/challenges') }}" class="text-xs text-emerald-600 hover:text-emerald-700 mt-1 inline-block">View challenges →</a>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl p-4 shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Completed Sessions</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $analytics['completedSessions'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">As learner or teacher</p>
                    <a href="{{ url('/session-requests') }}?status=accepted" class="text-xs text-emerald-600 hover:text-emerald-700 mt-1 inline-block">Session requests →</a>
                </div>
                <div class="bg-white rounded-xl p-4 shadow border border-slate-100">
                    <p class="text-sm text-slate-500">Learning Hours (est.)</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $analytics['estimatedLearningHours'] }}h</p>
                    <p class="text-xs text-slate-400 mt-1">~1h per completed session</p>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-xl p-4 shadow border border-slate-100">
                <h4 class="text-base font-semibold text-slate-800 mb-4">Learning overview</h4>
                <div class="h-64 sm:h-72">
                    <canvas id="analyticsChart" aria-label="Learning overview chart"></canvas>
                </div>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow"><p class="text-sm text-slate-500">Skills Teaching</p><p class="text-xl font-bold text-slate-900">—</p></div>
            <div class="bg-white rounded-xl p-4 shadow"><p class="text-sm text-slate-500">Skills Learning</p><p class="text-xl font-bold text-slate-900">—</p></div>
            <div class="bg-white rounded-xl p-4 shadow"><p class="text-sm text-slate-500">Points</p><p class="text-xl font-bold text-slate-900">—</p></div>
            <div class="bg-white rounded-xl p-4 shadow"><p class="text-sm text-slate-500">Badges</p><p class="text-xl font-bold text-slate-900">—</p></div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Pending Session Requests (for Teachers) -->
                @if(!empty($pendingRequests) && count($pendingRequests) > 0)
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 border-2 border-orange-300 rounded-xl p-4 shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                                    📥 Pending Session Requests
                                    <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">{{ count($pendingRequests) }}</span>
                                </h3>
                                <p class="text-xs text-slate-600 mt-1">You have {{ count($pendingRequests) }} pending request(s) waiting for your response</p>
                            </div>
                            <a href="{{ url('/session-requests') }}?status=pending" class="text-sm text-orange-600 hover:text-orange-700 font-medium">View All →</a>
                        </div>
                        <div class="space-y-3">
                            @foreach($pendingRequests as $req)
                                <div class="bg-white rounded-lg p-3 border border-orange-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            @if($req->learner->profile?->photo_path)
                                                <img src="{{ asset('storage/' . $req->learner->profile->photo_path) }}" alt="{{ $req->learner->name }}" class="w-8 h-8 rounded-full object-cover">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold flex items-center justify-center">
                                                    {{ strtoupper(mb_substr(trim((string) $req->learner->name), 0, 1)) ?: 'U' }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-sm text-slate-900">{{ $req->learner->name }}</p>
                                                <p class="text-xs text-slate-500">wants to learn</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900 mb-1">{{ $req->skill_name }}</p>
                                    @if($req->skill_level)
                                        <p class="text-xs text-slate-500 mb-2">Level: {{ ucfirst($req->skill_level) }}</p>
                                    @endif
                                    @if($req->message)
                                        <p class="text-xs text-slate-600 mb-2 italic">"{{ \Illuminate\Support\Str::limit($req->message, 60) }}"</p>
                                    @endif
                                    <div class="flex gap-2 mt-3">
                                        <form method="POST" action="{{ url('/session-requests/' . $req->id . '/accept') }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-1.5 rounded-lg text-xs font-medium">✓ Accept</button>
                                        </form>
                                        <form method="POST" action="{{ url('/session-requests/' . $req->id . '/reject') }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-1.5 rounded-lg text-xs font-medium" onclick="return confirm('Are you sure?')">✕ Reject</button>
                                        </form>
                                        <a href="{{ url('/session-requests') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">View Details</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Recent Notifications (for Learners) -->
                @if(!empty($recentNotifications) && count($recentNotifications) > 0)
                    <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-300 rounded-xl p-4 shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                                    🔔 Recent Notifications
                                    <span class="bg-emerald-500 text-white text-xs px-2 py-1 rounded-full">{{ count($recentNotifications) }}</span>
                                </h3>
                                <p class="text-xs text-slate-600 mt-1">You have {{ count($recentNotifications) }} unread notification(s)</p>
                            </div>
                            <a href="{{ url('/notifications') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All →</a>
                        </div>
                        <div class="space-y-2">
                            @foreach($recentNotifications as $notification)
                                <div class="bg-white rounded-lg p-3 border border-emerald-200">
                                    <p class="text-sm text-slate-900 font-medium">{{ $notification->message }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if($notification->data && isset($notification->data['session_request_id']))
                                        <a href="{{ url('/session-requests') }}" class="text-xs text-emerald-600 hover:text-emerald-700 mt-1 inline-block">View Details →</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Recommended Matches -->
                <div class="bg-white rounded-xl p-4 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Recommended Matches</h3>
                        <div class="flex items-center gap-3">
                            @if(!empty($matches))
                                <span class="text-xs text-slate-500">{{ count($matches) }} matches</span>
                            @endif
                            <a href="{{ url('/home') }}?use_location={{ request('use_location') ? '0' : '1' }}" class="text-xs px-2 py-1 rounded-full {{ request('use_location') ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ request('use_location') ? '📍 Location ON' : '📍 Location OFF' }}
                            </a>
                        </div>
                    </div>
                @if(!empty($matches))
                    @foreach($matches as $match)
                        @php
                            $matchedUser = $match['user'];
                            $matchedSkills = $match['matched_skills'] ?? ($match['details']['matched_skills'] ?? []);
                            $matchType = $match['type'] ?? 'teacher';
                            $score = $match['score'] ?? 0;
                        @endphp
                        <div class="flex justify-between items-center py-3 {{ !$loop->last ? 'border-b border-slate-200' : '' }} hover:bg-slate-50 px-2 rounded-lg">
                            <div class="flex items-center gap-3 flex-1">
                                @if($matchedUser->profile?->photo_path)
                                    <img src="{{ asset('storage/' . $matchedUser->profile->photo_path) }}" alt="{{ $matchedUser->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold flex items-center justify-center">
                                        {{ strtoupper(mb_substr(trim((string) $matchedUser->name), 0, 1)) ?: 'U' }}
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900">{{ $matchedUser->name }}</p>
                                    <div class="flex items-center gap-2 flex-wrap mt-1">
                                        @foreach(array_slice($matchedSkills, 0, 3) as $skill)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                {{ $skill['skill'] ?? $skill['name'] ?? '' }}
                                                @if(isset($skill['learner_level']))
                                                    ({{ ucfirst($skill['learner_level']) }})
                                                @endif
                                            </span>
                                        @endforeach
                                        @if(count($matchedSkills) > 3)
                                            <span class="text-xs text-slate-500">+{{ count($matchedSkills) - 3 }} more</span>
                                        @endif
                                    </div>
                                    @if($matchType === 'teacher')
                                        <p class="text-xs text-slate-500 mt-1">Can teach you • Match score: {{ $score }}</p>
                                    @else
                                        <p class="text-xs text-slate-500 mt-1">Wants to learn from you • Match score: {{ $score }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ url('/profile') }}?user_id={{ $matchedUser->id }}" class="text-sm bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded-lg transition-colors">View</a>
                                @if($matchType === 'teacher')
                                    <a href="{{ url('/session-requests/send/' . $matchedUser->id) }}" class="text-sm bg-orange-400 hover:bg-orange-500 text-white px-3 py-1 rounded-lg transition-colors">Request Session</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8 text-slate-500">
                        <p class="mb-2">No matches found yet.</p>
                        <p class="text-sm">Add skills to your profile to find teachers and learners!</p>
                        <a href="{{ url('/update-profile') }}" class="inline-block mt-4 text-sm text-emerald-600 hover:text-emerald-700">Update Profile →</a>
                    </div>
                @endif
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-500 text-white rounded-xl p-6 flex flex-col items-center justify-center">
                <span class="text-4xl mb-3">🏆</span>
                <h3 class="text-lg font-semibold">Weekly Challenge</h3>
                <p class="text-sm text-center my-2">Teach 3 new skills this week and earn bonus points!</p>
                <a href="{{ url('/challenges') }}" class="mt-3 bg-white text-orange-500 px-4 py-2 rounded-lg font-semibold hover:bg-orange-400 hover:text-white transition-colors">Join Challenge</a>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-xl p-4 shadow">
            <h3 class="font-semibold mb-2 text-slate-900">Upcoming Sessions</h3>
            @if(!empty($upcomingSessions) && $upcomingSessions->count() > 0)
                <div class="space-y-3">
                    @foreach($upcomingSessions as $s)
                        @php
                            $other = $s->learner_id === auth()->id() ? $s->teacher : $s->learner;
                            $date = $s->accepted_date ?? $s->proposed_date;
                        @endphp
                        <div class="flex justify-between items-start py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $s->skill_name }}</p>
                                <p class="text-xs text-slate-500">with {{ $other->name ?? '—' }}</p>
                                @if($date)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $date->format('M j, Y g:i A') }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-emerald-500 font-medium">Confirmed</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ url('/session-requests') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium mt-3 inline-block">View all →</a>
            @else
                <p class="text-sm text-slate-500">No upcoming sessions. Request or accept sessions to see them here.</p>
                <a href="{{ url('/explore') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium mt-2 inline-block">Find teachers →</a>
            @endif
        </div>
    </div>
</div>

@if(!empty($analytics))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    var canvas = document.getElementById('analyticsChart');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var data = @json($analyticsChartData ?? []);
    var labels = data.map(function (r) { return r[0]; });
    var values = data.map(function (r) { return r[1]; });
    var colors = [
        { bg: 'rgba(16, 185, 129, 0.85)', border: '#059669' },
        { bg: 'rgba(52, 211, 153, 0.85)', border: '#10b981' },
        { bg: 'rgba(34, 197, 94, 0.85)', border: '#16a34a' },
        { bg: 'rgba(6, 182, 212, 0.85)', border: '#0891b2' },
        { bg: 'rgba(99, 102, 241, 0.85)', border: '#4f46e5' },
    ];
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Count',
                data: values,
                backgroundColor: colors.map(function (c) { return c.bg; }),
                borderColor: colors.map(function (c) { return c.border; }),
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 36,
                hoverBackgroundColor: colors.map(function (c) { return c.border; }),
                hoverBorderColor: '#0f172a',
                hoverBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 12, right: 16, bottom: 12, left: 8 } },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function (ctx) { return ' ' + ctx.parsed.y; }
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, family: "'Instrument Sans', sans-serif" },
                        color: '#64748b',
                        maxRotation: 25,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)',
                        drawTicks: false,
                    },
                    border: { display: false },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11, family: "'Instrument Sans', sans-serif" },
                        color: '#64748b',
                        padding: 8,
                    },
                },
            },
        },
    });
})();
</script>
@endif
@endsection
