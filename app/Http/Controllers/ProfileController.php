<?php

namespace App\Http\Controllers;

use App\Models\ProfileSkill;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // GET: /api/me/profile
    public function me(Request $request)
    {
        $user = auth()->user(); // ✅ works with ApiTokenAuth

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $profile = $user->profile; // hasOne
        $skills = $user->profileSkills()->get(); // hasMany

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,

            'status' => $profile?->status,
            'linkedinUrl' => $profile?->linkedin_url,
            'githubUrl' => $profile?->github_url,
            'photoUrl' => $profile?->photo_path ? asset('storage/'.$profile->photo_path) : null,
            'location' => $profile?->location,
            'latitude' => $profile?->latitude,
            'longitude' => $profile?->longitude,
            'timezone' => $profile?->timezone,

            'teachingSkills' => $skills
                ->where('type', 'teaching')
                ->values()
                ->map(fn ($s) => [
                    'name' => $s->name,
                    'skill_level' => $s->skill_level,
                ]),

            'learningSkills' => $skills
                ->where('type', 'learning')
                ->values()
                ->map(fn ($s) => [
                    'name' => $s->name,
                    'skill_level' => $s->skill_level,
                ]),
        ]);
    }

    // POST: /api/profile
    public function upsert(Request $request)
    {
        $user = auth()->user(); // ✅ works with ApiTokenAuth

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['nullable', 'string', 'max:280'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'timezone' => ['nullable', 'string', 'max:50', 'timezone'],

            'teachingSkills' => ['nullable', 'array'],
            'teachingSkills.*' => ['nullable'], // Can be string or array with name/level

            'learningSkills' => ['nullable', 'array'],
            'learningSkills.*' => ['nullable'], // Can be string or array with name/level

            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // upsert profile
        $profile = UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => null, 'linkedin_url' => null, 'github_url' => null, 'photo_path' => null, 'location' => null, 'latitude' => null, 'longitude' => null, 'timezone' => null]
        );

        $profile->status = $request->input('status');
        $profile->linkedin_url = $request->input('linkedin');
        $profile->github_url = $request->input('github');
        if ($request->has('location')) {
            $profile->location = $request->input('location');
        }
        if ($request->has('latitude')) {
            $profile->latitude = $request->input('latitude');
        }
        if ($request->has('longitude')) {
            $profile->longitude = $request->input('longitude');
        }
        if ($request->has('timezone')) {
            $profile->timezone = $request->input('timezone') ?: null;
        }

        if ($request->hasFile('photo')) {
            if ($profile->photo_path && Storage::disk('public')->exists($profile->photo_path)) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->photo_path = $request->file('photo')->store('profiles', 'public');
        }

        $profile->save();

        // replace skills
        ProfileSkill::where('user_id', $user->id)->delete();

        foreach ($request->input('teachingSkills', []) as $s) {
            $name = is_array($s) ? trim($s['name'] ?? '') : trim($s);
            $level = is_array($s) ? ($s['skill_level'] ?? 'intermediate') : 'intermediate';

            if ($name !== '') {
                ProfileSkill::create([
                    'user_id' => $user->id,
                    'type' => 'teaching',
                    'name' => $name,
                    'skill_level' => in_array($level, ['beginner', 'intermediate', 'expert']) ? $level : 'intermediate',
                ]);
            }
        }

        foreach ($request->input('learningSkills', []) as $s) {
            $name = is_array($s) ? trim($s['name'] ?? '') : trim($s);
            $level = is_array($s) ? ($s['skill_level'] ?? 'beginner') : 'beginner';

            if ($name !== '') {
                ProfileSkill::create([
                    'user_id' => $user->id,
                    'type' => 'learning',
                    'name' => $name,
                    'skill_level' => in_array($level, ['beginner', 'intermediate', 'expert']) ? $level : 'beginner',
                ]);
            }
        }

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
