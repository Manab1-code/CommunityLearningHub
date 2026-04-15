<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\LearningMaterial;
use App\Models\SessionRequest;
use App\Models\User;
use Carbon\Carbon;

class ChallengeProgressService
{
    /**
     * Get current progress for a user for a given challenge target type.
     * Used for weekly (current week) or community (all-time) scope.
     */
    public function getProgressForUser(User $user, Challenge $challenge): int
    {
        $start = $challenge->start_at?->startOfDay();
        $end = $challenge->end_at?->endOfDay();

        switch ($challenge->target_type) {
            case 'teach_sessions':
                return $this->countTeachingSessions($user, $start, $end);
            case 'attend_sessions':
                return $this->countAttendedSessions($user, $start, $end);
            case 'share_resources':
                return $this->countSharedResources($user, $start, $end);
            case 'complete_sessions':
                return $this->countCompletedSessionsEitherRole($user, $start, $end);
            case 'give_feedback':
                return $this->countFeedbackGiven($user, $start, $end);
            default:
                return 0;
        }
    }

    protected function countTeachingSessions(User $user, $start, $end): int
    {
        $q = SessionRequest::where('teacher_id', $user->id)
            ->whereIn('status', ['accepted', 'rescheduled', 'completed']);

        if ($start) {
            $q->where('created_at', '>=', $start);
        }
        if ($end) {
            $q->where('created_at', '<=', $end);
        }

        return $q->count();
    }

    protected function countAttendedSessions(User $user, $start, $end): int
    {
        $q = SessionRequest::where('learner_id', $user->id)
            ->whereIn('status', ['accepted', 'rescheduled', 'completed']);

        if ($start) {
            $q->where('created_at', '>=', $start);
        }
        if ($end) {
            $q->where('created_at', '<=', $end);
        }

        return $q->count();
    }

    protected function countSharedResources(User $user, $start, $end): int
    {
        $q = LearningMaterial::where('user_id', $user->id);
        if ($start) {
            $q->where('created_at', '>=', $start);
        }
        if ($end) {
            $q->where('created_at', '<=', $end);
        }

        return $q->count();
    }

    protected function countCompletedSessionsEitherRole(User $user, $start, $end): int
    {
        $q = SessionRequest::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)->orWhere('learner_id', $user->id);
        })->where('status', 'completed');

        if ($start) {
            $q->where('updated_at', '>=', $start);
        }
        if ($end) {
            $q->where('updated_at', '<=', $end);
        }

        return $q->count();
    }

    protected function countFeedbackGiven(User $user, $start, $end): int
    {
        // Placeholder: no feedback table yet - could be messages or ratings later
        return 0;
    }

    /**
     * Sync participant progress from actual activity and mark completed if target reached.
     */
    public function syncParticipantProgress(ChallengeParticipant $participant): void
    {
        $challenge = $participant->challenge;
        $user = $participant->user;
        $progress = $this->getProgressForUser($user, $challenge);

        $participant->progress = $progress;
        if ($progress >= $challenge->target_count && ! $participant->completed_at) {
            $participant->completed_at = now();
        }
        $participant->save();
    }

    /**
     * Ensure weekly challenges exist for the current week (call from scheduler or on challenges page load).
     */
    public function ensureWeeklyChallengesExist(): void
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $defaults = [
            [
                'title' => 'Teaching Master',
                'description' => 'Teach 3 sessions this week and share your expertise',
                'target_type' => 'teach_sessions',
                'target_count' => 3,
                'points' => 150,
                'icon' => '🎓',
            ],
            [
                'title' => 'Eager Learner',
                'description' => 'Attend 5 learning sessions from different teachers',
                'target_type' => 'attend_sessions',
                'target_count' => 5,
                'points' => 100,
                'icon' => '📘',
            ],
            [
                'title' => 'Resource Sharer',
                'description' => 'Share 2 learning materials (videos, notes, or guides) this week',
                'target_type' => 'share_resources',
                'target_count' => 2,
                'points' => 75,
                'icon' => '📤',
            ],
        ];

        foreach ($defaults as $d) {
            Challenge::updateOrCreate(
                [
                    'type' => 'weekly',
                    'title' => $d['title'],
                    'start_at' => $start,
                ],
                array_merge($d, [
                    'type' => 'weekly',
                    'start_at' => $start,
                    'end_at' => $end,
                    'is_active' => true,
                ])
            );
        }
    }
}
