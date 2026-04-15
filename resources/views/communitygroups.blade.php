@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen w-full flex bg-slate-50 overflow-x-hidden">
    <main class="flex-1 p-6 overflow-x-hidden max-w-6xl mx-auto w-full">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Community Groups</h1>
                <p class="text-sm text-slate-500">Topic-based discussions and idea-sharing. Create or join a room to chat.</p>
            </div>
            <a href="{{ route('rooms.create') }}" class="flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Create Group</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($rooms ?? [] as $room)
                @php $joined = in_array($room->id, $myRoomIds ?? []); @endphp
                <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col justify-between min-h-[180px]">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $room->title }}</h3>
                                <p class="text-xs text-slate-500">{{ $room->topic ?: 'General' }}</p>
                            </div>
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $room->participants_count }} members</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">{{ $room->messages_count }} messages</p>
                    </div>
                    @if($joined)
                        <a href="{{ route('rooms.show', $room->id) }}" class="mt-3 block w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg text-sm text-center font-medium">Open Chat</a>
                    @else
                        <form action="{{ route('rooms.join', $room->id) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium">Join Group</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 p-8 bg-white border border-slate-200 rounded-xl text-center text-slate-500">
                    <p class="mb-2">No community rooms yet.</p>
                    <a href="{{ route('rooms.create') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Create the first group</a>
                </div>
            @endforelse
        </div>

        @if(isset($rooms) && $rooms->hasPages())
            <div class="mt-6">{{ $rooms->links() }}</div>
        @endif
    </main>
</div>
@endsection
