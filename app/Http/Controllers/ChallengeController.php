<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Services\ChallengeProgressService;
use App\Services\PointService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(
        protected ChallengeProgressService $progressService,
        protected PointService $pointService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Ensure we have weekly challenges for this week
        $weeklyCount = Challenge::weekly()->active()
            ->whereDate('start_at', $weekStart)
            ->whereDate('end_at', $weekEnd)
            ->count();

        if ($weeklyCount === 0) {
            $this->progressService->ensureWeeklyChallengesExist();
        }

        $weeklyChallenges = Challenge::weekly()
            ->active()
            ->whereDate('start_at', $weekStart)
            ->whereDate('end_at', $weekEnd)
            ->orderBy('points', 'desc')
            ->get();

        $communityChallenges = Challenge::community()
            ->active()
            ->orderBy('points', 'desc')
            ->get();

        $participations = ChallengeParticipant::where('user_id', $user->id)
            ->with('challenge')
            ->get()
            ->keyBy('challenge_id');

        $weeklyWithProgress = $weeklyChallenges->map(function ($challenge) use ($user, $participations) {
            $part = $participations->get($challenge->id);
            $progress = $this->progressService->getProgressForUser($user, $challenge);
            if ($part) {
                $part->progress = $progress;
                if ($progress >= $challenge->target_count && ! $part->completed_at) {
                    $part->completed_at = now();
                    $part->save();
                    $this->pointService->awardChallengePoints($user, $challenge->points, $challenge->title, $challenge->id);
                }
            }

            return [
                'challenge' => $challenge,
                'participant' => $part,
                'progress' => $progress,
            ];
        });

        $communityWithProgress = $communityChallenges->map(function ($challenge) use ($user, $participations) {
            $part = $participations->get($challenge->id);
            $progress = $this->progressService->getProgressForUser($user, $challenge);
            if ($part) {
                $part->progress = $progress;
                if ($progress >= $challenge->target_count && ! $part->completed_at) {
                    $part->completed_at = now();
                    $part->save();
                    $this->pointService->awardChallengePoints($user, $challenge->points, $challenge->title, $challenge->id);
                }
            }

            return [
                'challenge' => $challenge,
                'participant' => $part,
                'progress' => $progress,
            ];
        });

        $totalPoints = ChallengeParticipant::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->join('challenges', 'challenges.id', '=', 'challenge_participants.challenge_id')
            ->sum('challenges.points');

        $badgesEarned = ChallengeParticipant::where('user_id', $user->id)->whereNotNull('completed_at')->count();
        $totalChallenges = Challenge::active()->count();

        return view('challenges', [
            'weeklyChallenges' => $weeklyWithProgress,
            'communityChallenges' => $communityWithProgress,
            'totalPoints' => $totalPoints,
            'badgesEarned' => $badgesEarned,
            'totalChallenges' => $totalChallenges,
            'daysRemaining' => max(0, (int) now()->diffInDays($weekEnd, false)),
        ]);
    }

    public function join(Request $request, $id)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $challenge = Challenge::active()->findOrFail($id);

        $existing = ChallengeParticipant::where('user_id', $user->id)->where('challenge_id', $challenge->id)->first();
        if ($existing) {
            return redirect()->route('challenges')->with('info', 'You have already joined this challenge.');
        }

        $progress = $this->progressService->getProgressForUser($user, $challenge);
        ChallengeParticipant::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'progress' => $progress,
            'completed_at' => $progress >= $challenge->target_count ? now() : null,
        ]);

        return redirect()->route('challenges')->with('success', 'You joined the challenge!');
    }
}
