@extends('layouts.app-with-nav')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Create a Community Room</h1>
    <p class="text-slate-500 mb-6">Start a topic-based discussion. Others can join to share ideas and engage.</p>

    <form action="{{ route('rooms.store') }}" method="POST" class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Room name</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="150" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Web Dev Masters">
            @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Topic (optional)</label>
            <input type="text" name="topic" value="{{ old('topic') }}" maxlength="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Programming & Tech">
            @error('topic')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium">Create Room</button>
            <a href="{{ route('communitygroups') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
