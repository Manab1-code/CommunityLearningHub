@extends('layouts.admin')

@section('title', 'Edit Challenge')
@section('heading', 'Edit Challenge')
@section('subheading', $challenge->title)

@section('content')
<form action="{{ route('admin.challenges.update', $challenge->id) }}" method="POST" class="max-w-2xl space-y-6">
    @csrf

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
        <select name="type" id="challenge-type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            <option value="weekly" {{ old('type', $challenge->type) === 'weekly' ? 'selected' : '' }}>Weekly (current week only)</option>
            <option value="community" {{ old('type', $challenge->type) === 'community' ? 'selected' : '' }}>Community (ongoing)</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
        <input type="text" name="title" value="{{ old('title', $challenge->title) }}" required maxlength="200" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="3" required maxlength="1000" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description', $challenge->description) }}</textarea>
        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">What counts (target type)</label>
        <select name="target_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
            @foreach($targetTypes as $value => $label)
            <option value="{{ $value }}" {{ old('target_type', $challenge->target_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Target count</label>
            <input type="number" name="target_count" value="{{ old('target_count', $challenge->target_count) }}" min="1" max="999" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            @error('target_count')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Points reward</label>
            <input type="number" name="points" value="{{ old('points', $challenge->points) }}" min="0" max="9999" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            @error('points')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Icon (emoji, optional)</label>
        <input type="text" name="icon" value="{{ old('icon', $challenge->icon) }}" maxlength="20" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="e.g. 🎓">
    </div>

    <div id="weekly-dates" class="grid grid-cols-2 gap-4 border border-slate-200 rounded-lg p-4 bg-slate-50" style="display: {{ old('type', $challenge->type) === 'weekly' ? 'grid' : 'none' }};">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Start date (weekly)</label>
            <input type="date" name="start_at" id="start_at" value="{{ old('start_at', $challenge->start_at?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            @error('start_at')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">End date (weekly)</label>
            <input type="date" name="end_at" id="end_at" value="{{ old('end_at', $challenge->end_at?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            @error('end_at')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $challenge->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-slate-700">Active (visible to users)</label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Update Challenge</button>
        <a href="{{ route('admin.challenges') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Cancel</a>
    </div>
</form>

<script>
document.getElementById('challenge-type').addEventListener('change', function() {
    var weekly = document.getElementById('weekly-dates');
    weekly.style.display = this.value === 'weekly' ? 'grid' : 'none';
    weekly.querySelector('#start_at').required = this.value === 'weekly';
    weekly.querySelector('#end_at').required = this.value === 'weekly';
});
</script>
@endsection
