@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Notifications</h1>
                <p class="text-slate-500 mt-1">Stay updated with your session requests and activities</p>
            </div>
            @if($notifications->count() > 0)
                <form method="POST" action="{{ url('/notifications/read-all') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif

        @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 {{ $notification->is_read ? 'border-slate-200' : 'border-orange-500' }} {{ !$notification->is_read ? 'bg-orange-50/30' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    @if(!$notification->is_read)
                                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                    @endif
                                    <p class="text-sm font-medium text-slate-900 {{ !$notification->is_read ? 'font-semibold' : '' }}">
                                        {{ $notification->message }}
                                    </p>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                                @if($notification->data && isset($notification->data['session_request_id']))
                                    <div class="mt-2">
                                        <a href="{{ url('/session-requests') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                            View Session Request →
                                        </a>
                                    </div>
                                @endif
                                @if($notification->type === 'new_message' && isset($notification->data['conversation_id']))
                                    <div class="mt-2">
                                        <a href="{{ url('/messages?conv=' . $notification->data['conversation_id']) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                            Open conversation →
                                        </a>
                                    </div>
                                @endif
                            </div>
                            @if(!$notification->is_read)
                                <form method="POST" action="{{ url('/notifications/' . $notification->id . '/read') }}" class="ml-4">
                                    @csrf
                                    <button type="submit" class="text-xs text-slate-500 hover:text-slate-700" title="Mark as read">
                                        ✓
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl p-12 shadow-sm text-center">
                <span class="text-6xl mb-4 block">🔔</span>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">No notifications</h3>
                <p class="text-slate-500">You're all caught up! New notifications will appear here.</p>
            </div>
        @endif
    </div>
</div>
@endsection
