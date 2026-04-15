@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Share a resource</h1>
            <p class="text-slate-500 mt-1">Upload a video, notes, or guide, or share a link</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <form action="{{ route('learning-materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="200" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none" placeholder="e.g. React Hooks tutorial">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
                    <textarea name="description" rows="3" maxlength="1000" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none" placeholder="Brief description of the resource">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
                    <select name="type" id="resource-type" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none">
                        <option value="video" {{ old('type') === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="notes" {{ old('type') === 'notes' ? 'selected' : '' }}>Notes</option>
                        <option value="guide" {{ old('type') === 'guide' ? 'selected' : '' }}>Guide</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Related skill / topic (optional)</label>
                    <input type="text" name="skill_name" value="{{ old('skill_name') }}" list="skill-list" maxlength="100" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none" placeholder="e.g. React, Python">
                    @if($userSkills->isNotEmpty())
                        <datalist id="skill-list">
                            @foreach($userSkills as $s)
                                <option value="{{ $s }}">
                            @endforeach
                        </datalist>
                    @endif
                </div>

                <div class="mb-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-sm font-medium text-slate-700 mb-2">Add resource</p>
                    <p class="text-xs text-slate-500 mb-3">Provide either a link (e.g. YouTube, Google Doc) or upload a file.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Link (URL)</label>
                            <input type="url" name="url" value="{{ old('url') }}" placeholder="https://..." class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-400 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Or upload file</label>
                            <input type="file" name="file" accept=".mp4,.webm,.mov,.avi,.pdf,.doc,.docx,.txt,.md" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="text-xs text-slate-400 mt-1">Videos: mp4, webm, mov, avi. Notes/Guides: pdf, doc, docx, txt, md. Max 50MB.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-semibold">Share resource</button>
                    <a href="{{ route('learning-materials.index') }}" class="px-6 py-3 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
