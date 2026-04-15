@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('learning-materials.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Learning Materials</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    @if($material->type === 'video')
                        <span class="text-3xl">🎬</span>
                    @elseif($material->type === 'notes')
                        <span class="text-3xl">📄</span>
                    @else
                        <span class="text-3xl">📖</span>
                    @endif
                    <span class="text-sm px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ ucfirst($material->type) }}</span>
                    @if($material->skill_name)
                        <span class="text-sm px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">{{ $material->skill_name }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $material->title }}</h1>
                @if($material->description)
                    <p class="text-slate-600 mt-2">{{ $material->description }}</p>
                @endif
                <p class="text-sm text-slate-500 mt-3">Shared by {{ $material->user->name }}</p>
            </div>

            <div class="p-6">
                @php $accessUrl = $material->getAccessUrl(); @endphp
                @if($accessUrl)
                    @if($material->type === 'video')
                        @if($material->isExternalLink() && (str_contains($material->url, 'youtube.com') || str_contains($material->url, 'youtu.be')))
                            @php
                                $vid = $material->url;
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $vid, $m)) {
                                    $embed = 'https://www.youtube.com/embed/' . $m[1];
                                } else {
                                    $embed = $material->url;
                                }
                            @endphp
                            <div class="aspect-video rounded-xl overflow-hidden bg-slate-900">
                                <iframe src="{{ $embed }}" class="w-full h-full" allowfullscreen></iframe>
                            </div>
                        @else
                            <div class="aspect-video rounded-xl overflow-hidden bg-slate-900">
                                <video src="{{ $accessUrl }}" controls class="w-full h-full"></video>
                            </div>
                        @endif
                    @else
                        <a href="{{ $accessUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl font-medium">
                            Open resource
                        </a>
                        @if($material->file_name)
                            <p class="text-sm text-slate-500 mt-2">File: {{ $material->file_name }}</p>
                        @endif
                    @endif
                @else
                    <p class="text-slate-500">No file or link available.</p>
                @endif

                @auth
                    @if(!empty($completed))
                        <div class="mt-6 inline-flex items-center gap-2 text-emerald-700 text-sm font-medium bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <span aria-hidden="true">✓</span> You’ve marked this module complete
                        </div>
                    @else
                        <form action="{{ route('learning-materials.complete', $material->id) }}" method="POST" class="mt-6">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium">
                                Mark module complete
                            </button>
                            <p class="text-xs text-slate-500 mt-2">Counts toward learner achievement badges on your profile.</p>
                        </form>
                    @endif
                @endauth
            </div>

            @if(auth()->id() === $material->user_id)
                <div class="px-6 pb-6">
                    <form action="{{ route('learning-materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Delete this resource?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">Delete resource</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
