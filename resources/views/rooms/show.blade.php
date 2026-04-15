@extends('layouts.app-with-nav')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('communitygroups') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Back to groups</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-900">{{ $room->title }}</h1>
                <p class="text-xs text-slate-500">{{ $room->topic ?: 'General' }} · {{ $room->participants()->count() }} members</p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @if(($canInvite ?? false) && ($isMember ?? false))
                    <button type="button" onclick="toggleInvitePanel()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700 hover:bg-slate-100">
                        <span>Invite people</span>
                    </button>
                @elseif(!($isMember ?? false))
                    <form action="{{ route('rooms.join', $room->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium">
                            Join group
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(($canInvite ?? false) && ($isMember ?? false))
            <div id="invite-panel" class="px-4 py-3 border-b border-slate-200 bg-emerald-50/60 text-xs text-slate-700 hidden">
                <p class="mb-2 font-medium text-slate-800">Share this link to invite others:</p>
                <div class="flex flex-col sm:flex-row gap-2 items-stretch">
                    <input type="text" readonly value="{{ route('rooms.show', $room->id) }}" id="invite-link-input" class="flex-1 px-2 py-1.5 border border-emerald-200 rounded-lg text-[11px] bg-white">
                    <button type="button" onclick="copyInviteLink()" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium whitespace-nowrap">
                        Copy link
                    </button>
                </div>
                <p id="invite-link-hint" class="mt-1 text-[11px] text-slate-500">Anyone with this link can open the group and join.</p>
            </div>
        @endif

        <div class="p-4 min-h-[320px] max-h-[50vh] overflow-y-auto space-y-3" id="room-messages">
            @if(!($isMember ?? false))
                <p class="text-slate-500 text-sm text-center py-8">Join this group to see the conversation and start chatting.</p>
            @else
                @forelse($messages as $m)
                    <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : '' }}">
                        <div class="max-w-[80%] {{ $m->sender_id === auth()->id() ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-800' }} rounded-lg px-3 py-2">
                            <p class="text-xs font-medium {{ $m->sender_id === auth()->id() ? 'text-emerald-100' : 'text-slate-500' }}">{{ $m->sender->name }}</p>
                            <p class="text-sm">{{ $m->body }}</p>
                            <p class="text-xs mt-1 {{ $m->sender_id === auth()->id() ? 'text-emerald-200' : 'text-slate-400' }}">{{ $m->created_at->timezone($userTimezone ?? config('app.timezone'))->format('M j, H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-8">No messages yet. Say hello!</p>
                @endforelse
            @endif
        </div>

        @if($isMember ?? false)
            <form action="{{ route('messages.send') }}" method="POST" class="p-4 border-t border-slate-200 flex gap-2">
                @csrf
                <input type="hidden" name="conv_id" value="{{ $room->id }}">
                <input type="hidden" name="next_url" value="{{ route('rooms.show', $room->id) }}">
                <input type="text" name="body" required maxlength="2000" placeholder="Type your message..." class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium">Send</button>
            </form>
        @else
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center gap-3">
                <p class="text-xs text-slate-500">Join this group to send messages.</p>
                <form action="{{ route('rooms.join', $room->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-medium">Join group</button>
                </form>
            </div>
        @endif
    </div>
</div>
<script>
function toggleInvitePanel() {
    var panel = document.getElementById('invite-panel');
    if (!panel) return;
    panel.classList.toggle('hidden');
}
function copyInviteLink() {
    var input = document.getElementById('invite-link-input');
    var hint = document.getElementById('invite-link-hint');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        if (hint) {
            hint.textContent = 'Link copied! Share it with people you want to invite.';
        }
    } catch (e) {
        if (hint) {
            hint.textContent = 'Select and copy the link above to share it.';
        }
    }
}
</script>
@endsection
