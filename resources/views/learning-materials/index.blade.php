@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Learning Materials</h1>
                <p class="text-slate-500 mt-1">Videos, notes, and guides shared by the community</p>
            </div>
            <a href="{{ route('learning-materials.create') }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl font-medium shadow-sm">
                Share a resource
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-6">
            <aside class="bg-white border border-slate-200 rounded-xl p-4 h-fit">
                <h3 class="font-semibold text-slate-900 mb-3">Filter</h3>
                <div class="space-y-3 text-sm">
                    <p class="font-medium text-slate-700">Type</p>
                    <a href="{{ route('learning-materials.index', ['type' => 'all'] + request()->only('skill')) }}" class="block py-1.5 px-2 rounded {{ ($type ?? 'all') === 'all' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">All</a>
                    <a href="{{ route('learning-materials.index', ['type' => 'video'] + request()->only('skill')) }}" class="block py-1.5 px-2 rounded {{ ($type ?? '') === 'video' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Videos</a>
                    <a href="{{ route('learning-materials.index', ['type' => 'notes'] + request()->only('skill')) }}" class="block py-1.5 px-2 rounded {{ ($type ?? '') === 'notes' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Notes</a>
                    <a href="{{ route('learning-materials.index', ['type' => 'guide'] + request()->only('skill')) }}" class="block py-1.5 px-2 rounded {{ ($type ?? '') === 'guide' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Guides</a>
                </div>
                @if($skillNames->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <p class="font-medium text-slate-700 mb-2">Skill / Topic</p>
                        <a href="{{ route('learning-materials.index', request()->only('type')) }}" class="block py-1.5 px-2 rounded {{ !$skill ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">All</a>
                        @foreach($skillNames as $name)
                            <a href="{{ route('learning-materials.index', ['skill' => $name] + request()->only('type')) }}" class="block py-1.5 px-2 rounded {{ $skill === $name ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">{{ $name }}</a>
                        @endforeach
                    </div>
                @endif
            </aside>

            <div>
                @if($materials->count() > 0)
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($materials as $m)
                            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition">
                                <div class="p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        @if($m->type === 'video')
                                            <span class="text-2xl">🎬</span>
                                        @elseif($m->type === 'notes')
                                            <span class="text-2xl">📄</span>
                                        @else
                                            <span class="text-2xl">📖</span>
                                        @endif
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ ucfirst($m->type) }}</span>
                                        @if($m->skill_name)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $m->skill_name }}</span>
                                        @endif
                                    </div>
                                    <h3 class="font-semibold text-slate-900 line-clamp-2">{{ $m->title }}</h3>
                                    @if($m->description)
                                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $m->description }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-2">by {{ $m->user->name }}</p>
                                </div>
                                <div class="px-4 pb-4 flex gap-2">
                                    <a href="{{ route('learning-materials.show', $m->id) }}" class="flex-1 text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium">View</a>
                                    @if(auth()->id() === $m->user_id)
                                        <form action="{{ route('learning-materials.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Delete this resource?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $materials->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <p class="text-slate-500 mb-4">No learning materials yet.</p>
                        <a href="{{ route('learning-materials.create') }}" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl font-medium">Share the first resource</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
