@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Session Requests</h1>
                <p class="text-slate-500 mt-1">Manage your skill exchange session requests</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('/session-requests') }}?status=all" class="px-3 py-1 rounded-full text-sm {{ $status === 'all' ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">All</a>
                <a href="{{ url('/session-requests') }}?status=pending" class="px-3 py-1 rounded-full text-sm {{ $status === 'pending' ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Pending</a>
                <a href="{{ url('/session-requests') }}?status=accepted" class="px-3 py-1 rounded-full text-sm {{ $status === 'accepted' ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Accepted</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Received Requests (as Teacher) -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">📥 Received Requests</h2>
                @if($receivedRequests->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">No received requests</p>
                @else
                    <div class="space-y-4">
                        @foreach($receivedRequests as $req)
                            <div class="border border-slate-200 rounded-lg p-4 {{ $req->status === 'pending' ? 'bg-emerald-50/50' : '' }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        @if($req->learner->profile?->photo_path)
                                            <img src="{{ asset('storage/' . $req->learner->profile->photo_path) }}" alt="{{ $req->learner->name }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold flex items-center justify-center">
                                                {{ strtoupper(mb_substr(trim((string) $req->learner->name), 0, 1)) ?: 'U' }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $req->learner->name }}</p>
                                            <p class="text-xs text-slate-500">wants to learn</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $req->status === 'pending' ? 'bg-orange-100 text-orange-700' : ($req->status === 'completed' ? 'bg-green-100 text-green-800' : ($req->status === 'rescheduled' ? 'bg-blue-100 text-blue-800' : ($req->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600')))) }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <p class="font-medium text-slate-900">{{ $req->skill_name }}</p>
                                    @if($req->skill_level)
                                        <span class="text-xs text-slate-500">Level: {{ ucfirst($req->skill_level) }}</span>
                                    @endif
                                </div>
                                @if($req->message)
                                    <p class="text-sm text-slate-600 mb-3">{{ $req->message }}</p>
                                @endif
                                @if($req->proposed_date)
                                    <p class="text-xs text-slate-500 mb-3">📅 Proposed: {{ $req->proposed_date->format('M d, Y h:i A') }}</p>
                                @endif
                                @if($req->status === 'pending')
                                    <div class="flex gap-2 mt-4">
                                        <form method="POST" action="{{ url('/session-requests/' . $req->id . '/accept') }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium">✓ Accept</button>
                                        </form>
                                        <form method="POST" action="{{ url('/session-requests/' . $req->id . '/reject') }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg text-sm font-medium" onclick="return confirm('Are you sure you want to reject this request?')">✕ Reject</button>
                                        </form>
                                    </div>
                                @elseif(in_array($req->status, ['accepted', 'rescheduled'], true) && $req->accepted_date)
                                    <p class="text-sm text-emerald-600 mb-2">✓ {{ $req->status === 'rescheduled' ? 'Rescheduled' : 'Accepted' }} for: {{ $req->accepted_date->format('M d, Y h:i A') }}</p>
                                    <div class="flex gap-2 flex-wrap">
                                        <a href="{{ route('messages.with', $req->learner_id) }}" class="inline-flex items-center gap-1 text-sm bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg">💬 Chat with learner</a>
                                        <button type="button" onclick="showRescheduleModal({{ $req->id }})" class="text-sm text-emerald-600 hover:text-emerald-700">Reschedule</button>
                                    </div>
                                    <form method="POST" action="{{ route('session-requests.complete', $req->id) }}" class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                                        @csrf
                                        <p class="text-xs font-medium text-slate-700">After you meet, mark the session complete (optional 1–5 rating helps learners earn badges).</p>
                                        <div class="flex flex-col sm:flex-row gap-2 sm:items-end">
                                            <label class="text-xs text-slate-600 shrink-0">Rate learner</label>
                                            <select name="learner_rating" class="text-sm border border-slate-300 rounded-lg px-2 py-1.5 max-w-xs bg-white">
                                                <option value="">— optional —</option>
                                                @for($r = 5; $r >= 1; $r--)
                                                    <option value="{{ $r }}">{{ $r }} / 5</option>
                                                @endfor
                                            </select>
                                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-sm py-1.5 px-4 rounded-lg font-medium">Mark complete</button>
                                        </div>
                                    </form>
                                @elseif($req->status === 'completed')
                                    <p class="text-sm text-green-700 font-medium mb-1">✓ Session completed</p>
                                    @if($req->learner_rating)
                                        <p class="text-xs text-slate-600">Your rating of this learner: <strong>{{ $req->learner_rating }}/5</strong></p>
                                    @endif
                                @elseif($req->status === 'rejected' && $req->rejection_reason)
                                    <p class="text-sm text-red-600">Reason: {{ $req->rejection_reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Sent Requests (as Learner) -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">📤 Sent Requests</h2>
                @if($sentRequests->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">No sent requests</p>
                @else
                    <div class="space-y-4">
                        @foreach($sentRequests as $req)
                            <div class="border border-slate-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        @if($req->teacher->profile?->photo_path)
                                            <img src="{{ asset('storage/' . $req->teacher->profile->photo_path) }}" alt="{{ $req->teacher->name }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold flex items-center justify-center">
                                                {{ strtoupper(mb_substr(trim((string) $req->teacher->name), 0, 1)) ?: 'U' }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $req->teacher->name }}</p>
                                            <p class="text-xs text-slate-500">teaching you</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $req->status === 'pending' ? 'bg-orange-100 text-orange-700' : ($req->status === 'completed' ? 'bg-green-100 text-green-800' : ($req->status === 'rescheduled' ? 'bg-blue-100 text-blue-800' : ($req->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600')))) }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <p class="font-medium text-slate-900">{{ $req->skill_name }}</p>
                                    @if($req->skill_level)
                                        <span class="text-xs text-slate-500">Level: {{ ucfirst($req->skill_level) }}</span>
                                    @endif
                                </div>
                                @if($req->message)
                                    <p class="text-sm text-slate-600 mb-3">{{ $req->message }}</p>
                                @endif
                                @if($req->accepted_date)
                                    <p class="text-sm text-emerald-600 mb-2">📅 Scheduled: {{ $req->accepted_date->format('M d, Y h:i A') }}</p>
                                @elseif($req->proposed_date)
                                    <p class="text-xs text-slate-500 mb-3">📅 Proposed: {{ $req->proposed_date->format('M d, Y h:i A') }}</p>
                                @endif
                                @if($req->status === 'completed')
                                    <p class="text-sm text-green-700 font-medium mt-1">✓ Session completed</p>
                                    @if($req->learner_rating)
                                        <p class="text-xs text-slate-600 mt-1">Tutor rating of your participation: <strong>{{ $req->learner_rating }}/5</strong></p>
                                    @endif
                                @elseif(in_array($req->status, ['accepted', 'rescheduled']))
                                    <a href="{{ route('messages.with', $req->teacher_id) }}" class="inline-flex items-center gap-1 text-sm bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg mb-2">💬 Chat with tutor</a>
                                @endif
                                @if(in_array($req->status, ['pending', 'accepted', 'rescheduled']))
                                    <form method="POST" action="{{ url('/session-requests/' . $req->id . '/cancel') }}" class="mt-4">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-700" onclick="return confirm('Are you sure you want to cancel this request?')">Cancel Request</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Reschedule Session</h3>
        <form id="reschedule-form" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-slate-700">New Date & Time</label>
                <input type="datetime-local" name="new_date" required class="w-full mt-1 px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-400 outline-none">
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-slate-700">Reason (optional)</label>
                <textarea name="reason" rows="3" class="w-full mt-1 px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-400 outline-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-medium">Reschedule</button>
                <button type="button" onclick="closeRescheduleModal()" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 rounded-lg font-medium">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRescheduleModal(requestId) {
    document.getElementById('reschedule-form').action = '/session-requests/' + requestId + '/reschedule';
    document.getElementById('reschedule-modal').classList.remove('hidden');
}
function closeRescheduleModal() {
    document.getElementById('reschedule-modal').classList.add('hidden');
}
</script>
@endsection
