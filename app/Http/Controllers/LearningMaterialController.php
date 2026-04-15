<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\LearningMaterialCompletion;
use App\Services\LearnerBadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LearningMaterialController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $type = $request->input('type', 'all'); // all, video, notes, guide
        $skill = $request->input('skill');

        $materials = LearningMaterial::with('user.profile')
            ->when($type !== 'all', fn ($q) => $q->where('type', $type))
            ->when($skill, fn ($q) => $q->where('skill_name', $skill))
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Distinct skill names for filter
        $skillNames = LearningMaterial::whereNotNull('skill_name')
            ->where('skill_name', '!=', '')
            ->distinct()
            ->pluck('skill_name')
            ->sort()
            ->values();

        return view('learning-materials.index', [
            'materials' => $materials,
            'type' => $type,
            'skill' => $skill,
            'skillNames' => $skillNames,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $userSkills = $user->profileSkills()
            ->where('type', 'teaching')
            ->pluck('name')
            ->unique()
            ->sort()
            ->values();

        return view('learning-materials.create', [
            'userSkills' => $userSkills,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:video,notes,guide',
            'skill_name' => 'nullable|string|max:100',
            'url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:51200', // 50MB
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('url') && ! $request->hasFile('file')) {
                $validator->errors()->add('file', 'Please provide either a link (URL) or upload a file.');
            }
            if ($request->filled('url') && $request->hasFile('file')) {
                $validator->errors()->add('file', 'Provide either a link or a file, not both.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $allowed = [
                'video' => ['mp4', 'webm', 'mov', 'avi'],
                'notes' => ['pdf', 'doc', 'docx', 'txt', 'md'],
                'guide' => ['pdf', 'doc', 'docx', 'txt', 'md'],
            ];
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, $allowed[$request->type] ?? [])) {
                return redirect()->back()->with('error', 'Invalid file type for '.$request->type.'.')->withInput();
            }
            $filePath = $file->store('learning-materials', 'public');
            $fileName = $file->getClientOriginalName();
        }

        LearningMaterial::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'skill_name' => $request->skill_name ?: null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'url' => $request->url ?: null,
        ]);

        return redirect()->route('learning-materials.index')->with('success', 'Resource shared successfully!');
    }

    public function show($id)
    {
        $user = auth()->user();
        $material = LearningMaterial::with('user.profile')->findOrFail($id);
        $completed = $user && LearningMaterialCompletion::where('user_id', $user->id)
            ->where('learning_material_id', $material->id)
            ->exists();

        return view('learning-materials.show', [
            'material' => $material,
            'completed' => $completed,
        ]);
    }

    public function markComplete(Request $request, $id)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $material = LearningMaterial::findOrFail($id);
        LearningMaterialCompletion::firstOrCreate(
            ['user_id' => $user->id, 'learning_material_id' => $material->id],
            ['completed_at' => now()]
        );

        app(LearnerBadgeService::class)->syncForUser($user);

        return redirect()->back()->with('success', 'Marked as complete. Keep up the great work!');
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $material = LearningMaterial::findOrFail($id);
        if ($material->user_id !== $user->id) {
            return redirect()->back()->with('error', 'You can only delete your own resources.');
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return redirect()->route('learning-materials.index')->with('success', 'Resource deleted.');
    }
}
