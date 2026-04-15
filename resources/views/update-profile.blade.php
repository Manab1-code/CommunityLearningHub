@extends('layouts.app-with-nav')

@section('content')
<div class="min-h-screen bg-slate-50 pt-6 px-6 pb-10">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-500 via-teal-500 to-amber-400 p-6 md:p-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Update Your Profile</h1>
                    <p class="text-white/90 mt-1 text-sm md:text-base">Make your profile look great and help others connect with you.</p>
                </div>
                <button type="submit" form="profile-form" class="group inline-flex items-center justify-center gap-2 rounded-xl border border-white/50 bg-white px-6 py-2.5 text-sm font-semibold tracking-wide text-emerald-900 shadow-md shadow-emerald-950/10 transition hover:bg-emerald-50 hover:shadow-lg hover:shadow-emerald-950/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600 active:scale-[0.98]">
                    <svg class="h-4 w-4 shrink-0 text-emerald-700 transition group-hover:text-emerald-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Save Profile
                </button>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 text-sm">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl p-4 text-sm">{{ session('success') }}</div>
        @endif

        <form id="profile-form" method="POST" action="{{ url('/profile') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl border shadow-sm p-6">
                    <h2 class="font-semibold text-slate-900">Preview</h2>
                    <div class="mt-5 flex flex-col items-center">
                        <div class="relative">
                            <img id="photo-preview" src="{{ $photoPreview }}" alt="preview" class="w-28 h-28 rounded-full object-cover border-4 border-white shadow {{ empty($photoPreview) ? 'hidden' : '' }}">
                            <div id="photo-initial" class="w-28 h-28 rounded-full border-4 border-white shadow bg-emerald-100 text-emerald-700 text-4xl font-bold flex items-center justify-center {{ empty($photoPreview) ? '' : 'hidden' }}">
                                {{ $userInitial ?? 'U' }}
                            </div>
                            <label class="absolute -bottom-1 -right-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full p-2 shadow cursor-pointer">📷
                                <input type="file" name="photo" accept="image/*" class="hidden" id="photo-input">
                            </label>
                        </div>
                        <p class="mt-4 font-semibold text-slate-900">{{ $userName ?? 'Your Name' }}</p>
                        <p class="text-sm text-slate-500 mt-1">📍 {{ $location ?? 'Add your location' }}</p>
                        <div class="mt-4 w-full bg-slate-50 border rounded-2xl p-4">
                            <p class="text-xs font-semibold text-slate-600 mb-2">Status</p>
                            <p class="text-sm text-slate-700">{{ $status ?: "Write a short status about what you're learning/teaching." }}</p>
                        </div>
                        <p class="text-xs text-slate-400 mt-4">Click the camera to upload your photo</p>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border shadow-sm p-6">
                    <h3 class="font-semibold text-slate-900">Quick Tips</h3>
                    <ul class="mt-3 text-sm text-slate-600 space-y-2 list-disc pl-5">
                        <li>Keep your status short and clear.</li>
                        <li>Add 2–5 skills you can teach and want to learn.</li>
                        <li>Use real LinkedIn/GitHub links (not localhost).</li>
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border shadow-sm p-6 md:p-7">
                    <h2 class="text-lg font-semibold text-slate-900">About</h2>
                    <p class="text-sm text-slate-500 mt-1">Basic details people see on your profile.</p>
                    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Location</label>
                            <input name="location" value="{{ old('location', $location ?? '') }}" placeholder="e.g. Kathmandu, Nepal" class="w-full mt-1 px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Timezone</label>
                            <select name="timezone" class="w-full mt-1 px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 outline-none">
                                <option value="">Use app default ({{ config('app.timezone') }})</option>
                                @foreach([
                                    'Asia/Kathmandu' => 'Nepal (Kathmandu)',
                                    'Asia/Kolkata' => 'India (Kolkata)',
                                    'Asia/Dhaka' => 'Bangladesh (Dhaka)',
                                    'Asia/Singapore' => 'Singapore',
                                    'Asia/Dubai' => 'UAE (Dubai)',
                                    'Asia/Tokyo' => 'Japan (Tokyo)',
                                    'Europe/London' => 'UK (London)',
                                    'Europe/Paris' => 'Europe (Paris)',
                                    'America/New_York' => 'US Eastern (New York)',
                                    'America/Los_Angeles' => 'US Pacific (Los Angeles)',
                                    'Australia/Sydney' => 'Australia (Sydney)',
                                    'UTC' => 'UTC',
                                ] as $tz => $label)
                                    <option value="{{ $tz }}" {{ old('timezone', $timezone ?? '') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Message times will show in this timezone.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Status</label>
                            <textarea name="status" rows="4" maxlength="280" placeholder="Share what you're working on or looking for..." class="w-full mt-1 border border-slate-200 rounded-2xl p-3 focus:ring-2 focus:ring-emerald-400 outline-none">{{ old('status', $status ?? '') }}</textarea>
                            <div class="text-right text-xs text-slate-400 mt-1"><span id="status-count">{{ strlen($status ?? '') }}</span>/280</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border shadow-sm p-6 md:p-7">
                    <h2 class="text-lg font-semibold text-slate-900">Skills Exchange</h2>
                    <p class="text-sm text-slate-500 mt-1">Add skills you can teach and skills you want to learn with skill levels.</p>
                    
                    <div class="mt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-medium text-slate-900">🎓 Teaching Skills</p>
                            <button type="button" onclick="addTeachingSkill()" class="text-sm text-emerald-600 hover:text-emerald-800">+ Add Skill</button>
                        </div>
                        <div id="teaching-skills-container" class="space-y-2">
                            @if(!empty($teachingSkills))
                                @foreach($teachingSkills as $skill)
                                    <div class="skill-row flex gap-2 items-center">
                                        <input type="text" name="teaching_skills_data[{{ $loop->index }}][name]" placeholder="Skill name" value="{{ $skill['name'] ?? '' }}" class="flex-1 border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-400 outline-none">
                                        <select name="teaching_skills_data[{{ $loop->index }}][level]" class="border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-400 outline-none">
                                            <option value="beginner" {{ ($skill['level'] ?? 'intermediate') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ ($skill['level'] ?? 'intermediate') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="expert" {{ ($skill['level'] ?? 'intermediate') === 'expert' ? 'selected' : '' }}>Expert</option>
                                        </select>
                                        <button type="button" onclick="removeSkillRow(this)" class="text-red-500 hover:text-red-700 px-2">✕</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Select your skill level for each teaching skill</p>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-medium text-slate-900">📘 Learning Skills</p>
                            <button type="button" onclick="addLearningSkill()" class="text-sm text-orange-600 hover:text-orange-800">+ Add Skill</button>
                        </div>
                        <div id="learning-skills-container" class="space-y-2">
                            @if(!empty($learningSkills))
                                @foreach($learningSkills as $skill)
                                    <div class="skill-row flex gap-2 items-center">
                                        <input type="text" name="learning_skills_data[{{ $loop->index }}][name]" placeholder="Skill name" value="{{ $skill['name'] ?? '' }}" class="flex-1 border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-300 outline-none">
                                        <select name="learning_skills_data[{{ $loop->index }}][level]" class="border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-orange-300 outline-none">
                                            <option value="beginner" {{ ($skill['level'] ?? 'beginner') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ ($skill['level'] ?? 'beginner') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="expert" {{ ($skill['level'] ?? 'beginner') === 'expert' ? 'selected' : '' }}>Expert</option>
                                        </select>
                                        <button type="button" onclick="removeSkillRow(this)" class="text-red-500 hover:text-red-700 px-2">✕</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Select the level you want to learn at</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border shadow-sm p-6 md:p-7">
                    <h2 class="text-lg font-semibold text-slate-900">Social Profiles</h2>
                    <p class="text-sm text-slate-500 mt-1">Add links so people can find you.</p>
                    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">LinkedIn</label>
                            <input name="linkedin" value="{{ old('linkedin', $linkedin ?? '') }}" placeholder="https://linkedin.com/in/yourname" class="w-full mt-1 px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">GitHub</label>
                            <input name="github" value="{{ old('github', $github ?? '') }}" placeholder="https://github.com/yourname" class="w-full mt-1 px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 outline-none">
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-slate-400">Tip: Use full URLs starting with https://</p>
                </div>

                <div class="flex justify-center pt-1">
                    <button type="submit" class="group inline-flex items-center justify-center gap-2 rounded-xl border border-white/50 bg-white px-6 py-2.5 text-sm font-semibold tracking-wide text-emerald-900 shadow-md shadow-emerald-950/10 transition hover:bg-emerald-50 hover:shadow-lg hover:shadow-emerald-950/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600 active:scale-[0.98]">
                        <svg class="h-4 w-4 shrink-0 text-emerald-700 transition group-hover:text-emerald-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Save Profile
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('photo-input')?.addEventListener('change', function(e) {
    const file = e.target.files?.[0];
    if (file) {
        const url = URL.createObjectURL(file);
        const preview = document.getElementById('photo-preview');
        const initial = document.getElementById('photo-initial');
        preview.src = url;
        preview.classList.remove('hidden');
        initial?.classList.add('hidden');
    }
});
document.querySelector('textarea[name="status"]')?.addEventListener('input', function() {
    document.getElementById('status-count').textContent = this.value.length;
});

let teachingSkillIndex = {{ count($teachingSkills ?? []) }};
let learningSkillIndex = {{ count($learningSkills ?? []) }};

function addTeachingSkill() {
    const container = document.getElementById('teaching-skills-container');
    const row = document.createElement('div');
    row.className = 'skill-row flex gap-2 items-center';
    row.innerHTML = `
        <input type="text" name="teaching_skills_data[${teachingSkillIndex}][name]" placeholder="Skill name" class="flex-1 border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-400 outline-none">
        <select name="teaching_skills_data[${teachingSkillIndex}][level]" class="border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="beginner">Beginner</option>
            <option value="intermediate" selected>Intermediate</option>
            <option value="expert">Expert</option>
        </select>
        <button type="button" onclick="removeSkillRow(this)" class="text-red-500 hover:text-red-700 px-2">✕</button>
    `;
    container.appendChild(row);
    teachingSkillIndex++;
}

function addLearningSkill() {
    const container = document.getElementById('learning-skills-container');
    const row = document.createElement('div');
    row.className = 'skill-row flex gap-2 items-center';
    row.innerHTML = `
        <input type="text" name="learning_skills_data[${learningSkillIndex}][name]" placeholder="Skill name" class="flex-1 border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-300 outline-none">
        <select name="learning_skills_data[${learningSkillIndex}][level]" class="border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-orange-300 outline-none">
            <option value="beginner" selected>Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="expert">Expert</option>
        </select>
        <button type="button" onclick="removeSkillRow(this)" class="text-red-500 hover:text-red-700 px-2">✕</button>
    `;
    container.appendChild(row);
    learningSkillIndex++;
}

function removeSkillRow(button) {
    button.closest('.skill-row').remove();
}
</script>
@endsection
