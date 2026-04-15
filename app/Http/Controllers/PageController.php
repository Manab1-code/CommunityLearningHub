<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\LearnerBadgeDefinition;
use App\Models\Message;
use App\Models\SessionRequest;
use App\Services\LearnerBadgeService;
use App\Services\SkillMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function home(Request $request, SkillMatchingService $matchingService)
    {
        $user = auth()->user();
        $userName = $user ? $user->name : ($request->session()->get('user')['name'] ?? 'Guest');

        // Get recommended matches if user is logged in
        $matches = [];
        $useLocation = false;
        $pendingRequests = [];
        $recentNotifications = [];

        $analytics = null;
        if ($user) {
            $useLocation = $request->boolean('use_location', false);
            $matches = $matchingService->getRecommendedMatches($user, $useLocation, 5);

            // Get pending session requests for teachers
            $pendingRequests = \App\Models\SessionRequest::where('teacher_id', $user->id)
                ->where('status', 'pending')
                ->with('learner.profile')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get recent unread notifications
            $recentNotifications = \App\Models\Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Analytics: skill growth, points, badges, completed sessions, learning hours
            $teachingSkills = $user->profileSkills()->where('type', 'teaching')->get();
            $learningSkills = $user->profileSkills()->where('type', 'learning')->get();
            $pointsBalance = app(\App\Services\PointService::class)->getBalance($user);
            $totalPointsEarned = \App\Models\PointTransaction::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount');
            $badgesEarned = \App\Models\ChallengeParticipant::where('user_id', $user->id)->whereNotNull('completed_at')->count();
            $completedSessions = \App\Models\SessionRequest::where(function ($q) use ($user) {
                $q->where('learner_id', $user->id)->orWhere('teacher_id', $user->id);
            })->where('status', 'completed')->count();
            $estimatedLearningHours = $completedSessions * 1; // 1 hour per completed session (estimated)

            $analytics = [
                'teachingSkillsCount' => $teachingSkills->count(),
                'learningSkillsCount' => $learningSkills->count(),
                'teachingSkillNames' => $teachingSkills->pluck('name')->toArray(),
                'learningSkillNames' => $learningSkills->pluck('name')->toArray(),
                'pointsBalance' => $pointsBalance,
                'totalPointsEarned' => $totalPointsEarned,
                'badgesEarned' => $badgesEarned,
                'completedSessions' => $completedSessions,
                'estimatedLearningHours' => $estimatedLearningHours,
            ];
            $analyticsChartData = [
                ['Teaching skills', $analytics['teachingSkillsCount']],
                ['Learning skills', $analytics['learningSkillsCount']],
                ['Completed sessions', $analytics['completedSessions']],
                ['Badges earned', $analytics['badgesEarned']],
                ['Learning hours (est.)', $analytics['estimatedLearningHours']],
            ];

            // Upcoming sessions (accepted, not yet completed, with a future date)
            $upcomingSessions = \App\Models\SessionRequest::where(function ($q) use ($user) {
                $q->where('learner_id', $user->id)->orWhere('teacher_id', $user->id);
            })->whereIn('status', ['accepted', 'rescheduled'])
                ->where(function ($q) {
                    $q->whereNotNull('proposed_date')->where('proposed_date', '>=', now())
                        ->orWhereNotNull('accepted_date')->where('accepted_date', '>=', now());
                })
                ->with('learner:id,name')
                ->with('teacher:id,name')
                ->orderByRaw('COALESCE(accepted_date, proposed_date) ASC')
                ->limit(5)
                ->get();
        } else {
            $upcomingSessions = collect();
            $analyticsChartData = [];
        }

        return view('home', [
            'userName' => $userName,
            'matches' => $matches,
            'useLocation' => $useLocation,
            'pendingRequests' => $pendingRequests,
            'recentNotifications' => $recentNotifications,
            'analytics' => $analytics ?? null,
            'analyticsChartData' => $analyticsChartData ?? [],
            'upcomingSessions' => $upcomingSessions ?? collect(),
        ]);
    }

    public function explore(Request $request, SkillMatchingService $matchingService)
    {
        $user = auth()->user();
        $opportunities = [];
        $categoriesList = $matchingService->getCategories();
        $useLocation = $request->boolean('use_location', false);
        $selectedCategories = $request->input('categories', []);
        if (is_string($selectedCategories)) {
            $selectedCategories = array_filter(array_map('trim', explode(',', $selectedCategories)));
        }
        $maxKm = $request->has('max_km') ? (float) $request->input('max_km') : 25.0;
        $distanceOptions = [5, 10, 25, 50, 100];

        if ($user) {
            $lat = null;
            $lng = null;
            if ($useLocation && $user->profile) {
                $lat = $user->profile->latitude ? (float) $user->profile->latitude : null;
                $lng = $user->profile->longitude ? (float) $user->profile->longitude : null;
                if ($lat === null || $lng === null) {
                    $useLocation = false;
                }
            }
            $opportunities = $matchingService->discoverOpportunities(
                $user->id,
                $selectedCategories,
                $useLocation ? $lat : null,
                $useLocation ? $lng : null,
                $useLocation ? $maxKm : null,
                30
            );
        }

        return view('explore', [
            'opportunities' => $opportunities,
            'categoriesList' => $categoriesList,
            'useLocation' => $useLocation,
            'selectedCategories' => $selectedCategories,
            'maxKm' => $maxKm,
            'distanceOptions' => $distanceOptions,
        ]);
    }

    public function learn(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $inProgress = \App\Models\SessionRequest::forLearner($user->id)
            ->whereIn('status', ['accepted', 'rescheduled'])
            ->with('teacher.profile')
            ->orderBy('proposed_date', 'desc')
            ->get();

        $completed = \App\Models\SessionRequest::forLearner($user->id)
            ->where('status', 'completed')
            ->with('teacher.profile')
            ->orderBy('accepted_date', 'desc')
            ->get();

        $inProgressCount = $inProgress->count();
        $completedCount = $completed->count();
        $total = $inProgressCount + $completedCount;
        $avgProgressPercent = $total > 0 ? (int) round(($completedCount / $total) * 100) : 0;

        $recentMaterials = \App\Models\LearningMaterial::with('user.profile')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('learn', [
            'inProgress' => $inProgress,
            'completed' => $completed,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'avgProgressPercent' => $avgProgressPercent,
            'recentMaterials' => $recentMaterials,
        ]);
    }

    public function teaching(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $inProgress = \App\Models\SessionRequest::forTeacher($user->id)
            ->whereIn('status', ['accepted', 'rescheduled'])
            ->with('learner.profile')
            ->orderBy('proposed_date', 'desc')
            ->get();

        $completed = \App\Models\SessionRequest::forTeacher($user->id)
            ->where('status', 'completed')
            ->with('learner.profile')
            ->orderBy('accepted_date', 'desc')
            ->get();

        $inProgressCount = $inProgress->count();
        $completedCount = $completed->count();
        $total = $inProgressCount + $completedCount;
        $avgProgressPercent = $total > 0 ? (int) round(($completedCount / $total) * 100) : 0;

        $myMaterialsCount = \App\Models\LearningMaterial::where('user_id', $user->id)->count();

        return view('teaching', [
            'inProgress' => $inProgress,
            'completed' => $completed,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'avgProgressPercent' => $avgProgressPercent,
            'myMaterialsCount' => $myMaterialsCount,
        ]);
    }

    public function profile(Request $request)
    {
        $currentUser = auth()->user();
        if (! $currentUser) {
            return redirect('/auth/signin');
        }

        // Check if viewing another user's profile
        $userId = $request->input('user_id');
        $viewingUser = $userId ? \App\Models\User::find($userId) : $currentUser;

        if (! $viewingUser) {
            return redirect('/profile')->with('error', 'User not found');
        }

        $isOwnProfile = $viewingUser->id === $currentUser->id;
        $profile = $viewingUser->profile;
        $skills = $viewingUser->profileSkills()->get();

        $badgeService = app(LearnerBadgeService::class);
        $badgeService->syncForUser($viewingUser);
        $learnerStats = $badgeService->learnerStats($viewingUser);
        $learnerBadges = $viewingUser->earnedLearnerBadges()->get()->map(fn ($b) => [
            'slug' => $b->slug,
            'name' => $b->name,
            'description' => $b->description,
            'icon_emoji' => $b->icon_emoji,
            'category' => $b->category,
            'earned_at' => $b->pivot->earned_at,
        ])->values()->all();

        $badgesBySlug = collect($learnerBadges)->keyBy('slug');
        $learnerBadgeCatalog = LearnerBadgeDefinition::orderBy('sort_order')->get()->map(function ($d) use ($badgesBySlug) {
            $row = $badgesBySlug->get($d->slug);

            return [
                'slug' => $d->slug,
                'name' => $d->name,
                'description' => $d->description,
                'icon_emoji' => $d->icon_emoji,
                'category' => $d->category,
                'earned' => $row !== null,
                'earned_at' => $row['earned_at'] ?? null,
            ];
        })->values()->all();

        $completedLearnerSessions = \App\Models\SessionRequest::forLearner($viewingUser->id)
            ->where('status', 'completed')
            ->count();

        $profileData = [
            'name' => $viewingUser->name,
            'initial' => strtoupper(mb_substr(trim((string) $viewingUser->name), 0, 1)) ?: 'U',
            'status' => $profile?->status,
            'location' => $profile?->location ?? null,
            'linkedinUrl' => $profile?->linkedin_url,
            'githubUrl' => $profile?->github_url,
            'photoUrl' => $profile?->photo_path
                ? asset('storage/'.$profile->photo_path)
                : null,
            'joinedAt' => $viewingUser->created_at?->format('Y-m-d'),
            'teachingSkills' => $skills->where('type', 'teaching')->values()->map(fn ($s) => [
                'name' => $s->name,
                'skill_level' => $s->skill_level,
            ])->all(),
            'learningSkills' => $skills->where('type', 'learning')->values()->map(fn ($s) => [
                'name' => $s->name,
                'skill_level' => $s->skill_level,
            ])->all(),
            'isOwnProfile' => $isOwnProfile,
            'userId' => $viewingUser->id,
            'learnerStats' => $learnerStats,
            'learnerBadges' => $learnerBadges,
            'learnerBadgeCatalog' => $learnerBadgeCatalog,
            'completedLearnerSessions' => $completedLearnerSessions,
        ];

        return view('profile', ['profile' => $profileData]);
    }

    public function showUpdateProfile(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $profile = $user->profile;
        $skills = $user->profileSkills()->get();
        $teachingSkills = $skills->where('type', 'teaching')->map(fn ($s) => [
            'name' => $s->name,
            'level' => $s->skill_level ?? 'intermediate',
        ])->all();
        $learningSkills = $skills->where('type', 'learning')->map(fn ($s) => [
            'name' => $s->name,
            'level' => $s->skill_level ?? 'beginner',
        ])->all();

        return view('update-profile', [
            'userName' => $user->name,
            'userInitial' => strtoupper(mb_substr(trim((string) $user->name), 0, 1)) ?: 'U',
            'status' => $profile?->status ?? '',
            'location' => $profile?->location ?? '',
            'timezone' => $profile?->timezone ?? '',
            'linkedin' => $profile?->linkedin_url ?? '',
            'github' => $profile?->github_url ?? '',
            'photoPreview' => $profile?->photo_path
                ? asset('storage/'.$profile->photo_path)
                : null,
            'teachingSkills' => $teachingSkills,
            'learningSkills' => $learningSkills,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $teachingText = $request->input('teaching_skills_text', '');
        $learningText = $request->input('learning_skills_text', '');
        $teachingSkills = array_filter(array_map('trim', explode(',', $teachingText)));
        $learningSkills = array_filter(array_map('trim', explode(',', $learningText)));

        $validator = Validator::make(array_merge($request->all(), [
            'teachingSkills' => $teachingSkills,
            'learningSkills' => $learningSkills,
        ]), [
            'status' => ['nullable', 'string', 'max:280'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
            'teachingSkills' => ['nullable', 'array'],
            'teachingSkills.*' => ['string', 'max:50'],
            'learningSkills' => ['nullable', 'array'],
            'learningSkills.*' => ['string', 'max:50'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'timezone' => ['nullable', 'string', 'max:50', 'timezone'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => null, 'linkedin_url' => null, 'github_url' => null, 'photo_path' => null, 'location' => null, 'timezone' => null]
        );

        $profile->status = $request->input('status');
        $profile->linkedin_url = $request->input('linkedin');
        $profile->github_url = $request->input('github');
        $profile->location = $request->input('location');
        if ($request->has('timezone')) {
            $profile->timezone = $request->input('timezone') ?: null;
        }

        if ($request->hasFile('photo')) {
            if ($profile->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->photo_path = $request->file('photo')->store('profiles', 'public');
        }
        $profile->save();

        $user->profileSkills()->delete();

        // Handle teaching skills with levels
        $teachingSkillsData = $request->input('teaching_skills_data', []);
        if (! empty($teachingSkillsData)) {
            foreach ($teachingSkillsData as $skill) {
                if (! empty($skill['name'])) {
                    $user->profileSkills()->create([
                        'type' => 'teaching',
                        'name' => trim($skill['name']),
                        'skill_level' => in_array($skill['level'] ?? 'intermediate', ['beginner', 'intermediate', 'expert'])
                            ? ($skill['level'] ?? 'intermediate')
                            : 'intermediate',
                    ]);
                }
            }
        } else {
            // Fallback: use comma-separated text (backward compatibility)
            foreach ($teachingSkills as $name) {
                if ($name !== '') {
                    $user->profileSkills()->create([
                        'type' => 'teaching',
                        'name' => $name,
                        'skill_level' => 'intermediate',
                    ]);
                }
            }
        }

        // Handle learning skills with levels
        $learningSkillsData = $request->input('learning_skills_data', []);
        if (! empty($learningSkillsData)) {
            foreach ($learningSkillsData as $skill) {
                if (! empty($skill['name'])) {
                    $user->profileSkills()->create([
                        'type' => 'learning',
                        'name' => trim($skill['name']),
                        'skill_level' => in_array($skill['level'] ?? 'beginner', ['beginner', 'intermediate', 'expert'])
                            ? ($skill['level'] ?? 'beginner')
                            : 'beginner',
                    ]);
                }
            }
        } else {
            // Fallback: use comma-separated text (backward compatibility)
            foreach ($learningSkills as $name) {
                if ($name !== '') {
                    $user->profileSkills()->create([
                        'type' => 'learning',
                        'name' => $name,
                        'skill_level' => 'beginner',
                    ]);
                }
            }
        }

        return redirect('/profile')->with('success', 'Profile saved!');
    }

    public function challenges()
    {
        return view('challenges');
    }

    public function messages(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $convId = $request->integer('conv', 0);
        $unreadConvIds = \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'new_message')
            ->where('is_read', false)
            ->get()
            ->pluck('data')
            ->pluck('conversation_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        $dmConversations = Conversation::dm()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with(['participants' => fn ($q) => $q->where('user_id', '!=', $user->id)->with('user:id,name'), 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($c) use ($unreadConvIds) {
                $other = $c->participants->first()?->user;
                $last = $c->messages->first();

                return [
                    'id' => $c->id,
                    'otherUser' => $other ? ['id' => $other->id, 'name' => $other->name] : null,
                    'lastMessage' => $last ? ['body' => \Str::limit($last->body, 40), 'created_at' => $last->created_at] : null,
                    'hasUnread' => in_array($c->id, $unreadConvIds),
                ];
            });

        $acceptedSessionsForChat = SessionRequest::where(function ($q) use ($user) {
            $q->where('learner_id', $user->id)->orWhere('teacher_id', $user->id);
        })->whereIn('status', ['accepted', 'rescheduled', 'completed'])
            ->with('learner:id,name')
            ->with('teacher:id,name')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($sr) use ($user, $unreadConvIds) {
                $other = $sr->learner_id === $user->id ? $sr->teacher : $sr->learner;
                $dm = $other ? Conversation::findDmBetween($user->id, $other->id) : null;

                return [
                    'session_request' => $sr,
                    'otherUser' => $other,
                    'hasUnread' => $dm && in_array($dm->id, $unreadConvIds),
                ];
            });

        $selectedConv = null;
        $selectedMessages = [];
        if ($convId > 0) {
            $selectedConv = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->with(['participants' => fn ($q) => $q->where('user_id', '!=', $user->id)->with('user:id,name')])
                ->find($convId);
            if ($selectedConv) {
                $selectedMessages = Message::where('conversation_id', $selectedConv->id)
                    ->with('sender:id,name')
                    ->orderBy('created_at', 'asc')
                    ->limit(200)
                    ->get();

                \App\Models\Notification::where('user_id', $user->id)
                    ->where('type', 'new_message')
                    ->where('data->conversation_id', $selectedConv->id)
                    ->update(['is_read' => true, 'read_at' => now()]);
            }
        }

        return view('messages', [
            'dmConversations' => $dmConversations,
            'acceptedSessionsForChat' => $acceptedSessionsForChat,
            'selectedConv' => $selectedConv,
            'selectedMessages' => $selectedMessages,
        ]);
    }

    /** Get or create DM with user, redirect to messages with that conversation */
    public function messagesWithUser($userId)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }
        $conv = Conversation::getOrCreateDmBetween($user->id, (int) $userId);

        return redirect()->route('messages', ['conv' => $conv->id]);
    }

    /** Send a message (web form) */
    public function sendMessage(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }
        $request->validate(['conv_id' => 'required|integer', 'body' => 'required|string|max:2000']);
        $conv = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $user->id))->findOrFail($request->conv_id);
        Message::create([
            'conversation_id' => $conv->id,
            'sender_id' => $user->id,
            'body' => $request->body,
        ]);
        $conv->touch();

        \App\Models\Notification::notifyNewMessage($conv->id, $user->id, $user->name);
        $redirect = $request->input('next_url');
        if ($redirect && \Illuminate\Support\Str::startsWith($redirect, url('/'))) {
            return redirect($redirect);
        }

        return redirect()->route('messages', ['conv' => $conv->id]);
    }
}
