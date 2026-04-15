@extends('layouts.app-with-nav')

@section('content')
<div class="bg-slate-50 min-h-screen px-6 py-10">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold text-slate-900 mb-1">Local Discovery</h2>
        <p class="text-slate-600 mb-6">Find skill opportunities and filter by categories or distance.</p>

        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
            <aside class="bg-white border border-slate-200 rounded-xl p-6 h-fit shadow-sm">
                <h3 class="font-semibold text-slate-900 mb-4">Filters</h3>
                <form method="get" action="{{ url('/explore') }}" id="discovery-filters">
                    <div class="space-y-5">
                        <div>
                            <p class="font-medium text-slate-800 mb-2">Categories (skills)</p>
                            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                                @foreach($categoriesList ?? [] as $cat)
                                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                                        <input type="checkbox" name="categories[]" value="{{ $cat }}" {{ in_array($cat, $selectedCategories ?? []) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm">{{ $cat }}</span>
                                    </label>
                                @endforeach
                                @if(empty($categoriesList))
                                    <p class="text-sm text-slate-500">No categories yet.</p>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Leave unchecked to show all skills.</p>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800 mb-2">Distance</p>
                            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                                <input type="checkbox" name="use_location" value="1" {{ $useLocation ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm">Match by my location</span>
                            </label>
                            @if($useLocation)
                                <div class="mt-2">
                                    <label for="max_km" class="text-xs text-slate-500">Within (km)</label>
                                    <select name="max_km" id="max_km" class="mt-0.5 w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
                                        @foreach($distanceOptions ?? [5, 10, 25, 50, 100] as $km)
                                            <option value="{{ $km }}" {{ (float)($maxKm ?? 25) == (float)$km ? 'selected' : '' }}>{{ $km }} km</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Uses your profile location.</p>
                            @endif
                        </div>
                    </div>
                    <button type="submit" class="mt-4 w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-lg font-medium text-sm transition">
                        Apply filters
                    </button>
                </form>
            </aside>

            <div>
                <p class="text-sm text-slate-500 mb-4">
                    @if(!empty($opportunities))
                        Showing {{ count($opportunities) }} opportunity(ies)
                        @if($useLocation)
                            <span class="text-emerald-600">within {{ $maxKm }} km</span>
                        @endif
                        @if(!empty($selectedCategories))
                            <span class="text-slate-600">in {{ implode(', ', array_slice($selectedCategories, 0, 3)) }}{{ count($selectedCategories) > 3 ? '…' : '' }}</span>
                        @endif
                    @else
                        No opportunities found. Try changing categories or increasing distance.
                    @endif
                </p>
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @if(!empty($opportunities))
                        @foreach($opportunities as $opp)
                            @php
                                $u = $opp['user'];
                                $location = $u->profile?->location ?? 'Not specified';
                                $isTeacher = ($opp['type'] ?? '') === 'teacher';
                            @endphp
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($u->profile?->photo_path)
                                        <img src="{{ asset('storage/' . $u->profile->photo_path) }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold flex items-center justify-center">
                                            {{ strtoupper(mb_substr(trim((string) $u->name), 0, 1)) ?: 'U' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-semibold text-slate-900 truncate">{{ $u->name }}</h4>
                                        <span class="text-xs font-medium {{ $isTeacher ? 'text-emerald-600 bg-emerald-50' : 'text-sky-600 bg-sky-50' }} px-2 py-0.5 rounded">
                                            {{ $isTeacher ? 'Teaches' : 'Learns' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($opp['skills'] ?? [], 0, 3) as $skill)
                                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                            {{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}
                                            @if(is_array($skill) && !empty($skill['skill_level']))
                                                ({{ ucfirst($skill['skill_level']) }})
                                            @endif
                                        </span>
                                    @endforeach
                                    @if(count($opp['skills'] ?? []) > 3)
                                        <span class="text-xs text-slate-400">+{{ count($opp['skills']) - 3 }}</span>
                                    @endif
                                </div>
                                <div class="mb-4">
                                    <p class="text-xs text-slate-500">📍 {{ $location }}</p>
                                    @if(isset($opp['distance_km']))
                                        <p class="text-xs text-emerald-600 mt-0.5">{{ number_format((float)$opp['distance_km'], 1) }} km away</p>
                                    @endif
                                </div>
                                <a href="{{ url('/profile') }}?user_id={{ $u->id }}" class="block w-full text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium transition mb-2">View Profile</a>
                                @if($isTeacher)
                                    <a href="{{ url('/session-requests/send/' . $u->id) }}" class="block w-full text-center border border-orange-400 text-orange-500 hover:bg-orange-50 py-2 rounded-lg text-sm transition">Request Session</a>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="col-span-full text-center py-12 bg-white rounded-xl border border-slate-200">
                            <p class="text-slate-500 mb-4">No opportunities found. Adjust filters or add skills to your profile.</p>
                            <a href="{{ url('/update-profile') }}" class="inline-block text-emerald-600 hover:text-emerald-700">Update your profile →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
