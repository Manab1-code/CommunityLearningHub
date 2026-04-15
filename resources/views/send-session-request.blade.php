@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Send Session Request</h1>
            <p class="text-slate-500">Request a skill exchange session with {{ $teacher->name }}</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-200">
                @if($teacher->profile?->photo_path)
                    <img src="{{ asset('storage/' . $teacher->profile->photo_path) }}" alt="{{ $teacher->name }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 text-xl font-semibold flex items-center justify-center">
                        {{ strtoupper(mb_substr(trim((string) $teacher->name), 0, 1)) ?: 'U' }}
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $teacher->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $teacher->profile?->location ?? 'Location not set' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ url('/session-requests') }}">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                <div class="mb-6">
                    <label class="text-sm font-medium text-slate-700 mb-2 block">Select Skill</label>
                    <select name="skill_name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none">
                        <option value="">Choose a skill...</option>
                        @foreach($teacher->profileSkills->where('type', 'teaching') as $skill)
                            <option value="{{ $skill->name }}">
                                {{ $skill->name }}
                                @if($skill->skill_level)
                                    ({{ ucfirst($skill->skill_level) }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Select a skill that {{ $teacher->name }} teaches</p>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-slate-700 mb-2 block">Your Desired Skill Level</label>
                    <select name="skill_level" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate" selected>Intermediate</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-slate-700 mb-2 block">Message (optional)</label>
                    <textarea name="message" rows="4" placeholder="Tell {{ $teacher->name }} why you'd like to learn this skill..." maxlength="500" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none"></textarea>
                    <p class="text-xs text-slate-500 mt-1">Max 500 characters</p>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-slate-700 mb-2 block">Proposed Date & Time (optional)</label>
                    <input type="datetime-local" name="proposed_date" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 outline-none">
                    <p class="text-xs text-slate-500 mt-1">Suggest a preferred time for the session</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-semibold">Send Request</button>
                    <a href="{{ url()->previous() }}" class="px-6 py-3 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
