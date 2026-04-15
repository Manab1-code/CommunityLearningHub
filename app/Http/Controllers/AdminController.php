<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\LearningMaterial;
use App\Models\SessionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'session_requests' => SessionRequest::count(),
            'session_requests_pending' => SessionRequest::where('status', 'pending')->count(),
            'session_requests_accepted' => SessionRequest::where('status', 'accepted')->count(),
            'session_requests_completed' => SessionRequest::where('status', 'completed')->count(),
            'learning_materials' => LearningMaterial::count(),
            'challenges' => Challenge::count(),
        ];

        $recentUsers = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $recentSessionRequests = SessionRequest::with(['learner', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentSessionRequests' => $recentSessionRequests,
        ]);
    }

    public function users(Request $request)
    {
        $users = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', ['users' => $users]);
    }

    public function sessionRequests(Request $request)
    {
        $requests = SessionRequest::with(['learner.profile', 'teacher.profile'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.session-requests', ['sessionRequests' => $requests]);
    }

    public function learningMaterials(Request $request)
    {
        $materials = LearningMaterial::with('user.profile')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.learning-materials', ['materials' => $materials]);
    }

    /** Weekly & Community Challenges – list */
    public function challenges(Request $request)
    {
        $weekly = Challenge::where('type', 'weekly')
            ->orderBy('start_at', 'desc')
            ->paginate(10, ['*'], 'weekly_page');

        $community = Challenge::where('type', 'community')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'community_page');

        return view('admin.challenges.index', [
            'weeklyChallenges' => $weekly,
            'communityChallenges' => $community,
        ]);
    }

    /** Create form for a new challenge */
    public function createChallenge()
    {
        return view('admin.challenges.create', [
            'targetTypes' => $this->challengeTargetTypes(),
        ]);
    }

    /** Store a new challenge */
    public function storeChallenge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:weekly,community',
            'target_type' => 'required|in:teach_sessions,attend_sessions,share_resources,complete_sessions,give_feedback',
            'target_count' => 'required|integer|min:1|max:999',
            'points' => 'required|integer|min:0|max:9999',
            'icon' => 'nullable|string|max:20',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'nullable|boolean',
        ]);

        $validator->sometimes('start_at', 'required', fn ($input) => $input->type === 'weekly');
        $validator->sometimes('end_at', 'required', fn ($input) => $input->type === 'weekly');

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'target_type' => $request->target_type,
            'target_count' => (int) $request->target_count,
            'points' => (int) $request->points,
            'icon' => $request->icon ?: null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->type === 'weekly') {
            $data['start_at'] = Carbon::parse($request->start_at)->startOfDay();
            $data['end_at'] = Carbon::parse($request->end_at)->endOfDay();
        } else {
            $data['start_at'] = null;
            $data['end_at'] = null;
        }

        Challenge::create($data);

        return redirect()->route('admin.challenges')->with('success', 'Challenge created.');
    }

    /** Edit form */
    public function editChallenge($id)
    {
        $challenge = Challenge::findOrFail($id);

        return view('admin.challenges.edit', [
            'challenge' => $challenge,
            'targetTypes' => $this->challengeTargetTypes(),
        ]);
    }

    /** Update challenge */
    public function updateChallenge(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:weekly,community',
            'target_type' => 'required|in:teach_sessions,attend_sessions,share_resources,complete_sessions,give_feedback',
            'target_count' => 'required|integer|min:1|max:999',
            'points' => 'required|integer|min:0|max:9999',
            'icon' => 'nullable|string|max:20',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->type === 'weekly') {
            $validator->sometimes('start_at', 'required', fn () => true);
            $validator->sometimes('end_at', 'required', fn () => true);
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $challenge->title = $request->title;
        $challenge->description = $request->description;
        $challenge->type = $request->type;
        $challenge->target_type = $request->target_type;
        $challenge->target_count = (int) $request->target_count;
        $challenge->points = (int) $request->points;
        $challenge->icon = $request->icon ?: null;
        $challenge->is_active = $request->boolean('is_active', true);

        if ($request->type === 'weekly') {
            $challenge->start_at = Carbon::parse($request->start_at)->startOfDay();
            $challenge->end_at = Carbon::parse($request->end_at)->endOfDay();
        } else {
            $challenge->start_at = null;
            $challenge->end_at = null;
        }

        $challenge->save();

        return redirect()->route('admin.challenges')->with('success', 'Challenge updated.');
    }

    protected function challengeTargetTypes(): array
    {
        return [
            'teach_sessions' => 'Teach sessions (as teacher)',
            'attend_sessions' => 'Attend sessions (as learner)',
            'share_resources' => 'Share learning materials',
            'complete_sessions' => 'Complete sessions (learner or teacher)',
            'give_feedback' => 'Give feedback (placeholder)',
        ];
    }
}
