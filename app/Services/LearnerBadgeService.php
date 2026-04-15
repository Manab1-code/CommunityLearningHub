<?php

namespace App\Services;

use App\Models\LearnerBadgeDefinition;
use App\Models\LearningMaterialCompletion;
use App\Models\SessionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearnerBadgeService
{
    /** Estimated hours per completed session as learner */
    private const HOURS_PER_SESSION = 1;

    public function syncForUser(User $user): void
    {
        $completedSessions = SessionRequest::forLearner($user->id)
            ->where('status', 'completed')
            ->count();

        $learningHours = $completedSessions * self::HOURS_PER_SESSION;

        $modulesCompleted = LearningMaterialCompletion::where('user_id', $user->id)->count();

        $ratingQuery = SessionRequest::forLearner($user->id)
            ->where('status', 'completed')
            ->whereNotNull('learner_rating');

        $ratingCount = (clone $ratingQuery)->count();
        $avgRating = (clone $ratingQuery)->avg('learner_rating');

        $slugs = [];

        if ($learningHours >= 1) {
            $slugs[] = 'first_hour';
        }
        if ($learningHours >= 5) {
            $slugs[] = 'five_hours';
        }
        if ($learningHours >= 10) {
            $slugs[] = 'ten_hours';
        }

        if ($modulesCompleted >= 1) {
            $slugs[] = 'first_module';
        }
        if ($modulesCompleted >= 5) {
            $slugs[] = 'five_modules';
        }
        if ($modulesCompleted >= 15) {
            $slugs[] = 'fifteen_modules';
        }

        if ($ratingCount >= 3 && $avgRating !== null && $avgRating >= 4.5) {
            $slugs[] = 'highly_rated';
        }
        if ($ratingCount >= 5 && $avgRating !== null && $avgRating >= 4.8) {
            $slugs[] = 'star_learner';
        }

        $slugs = array_unique($slugs);
        if ($slugs === []) {
            return;
        }

        $definitions = LearnerBadgeDefinition::whereIn('slug', $slugs)->get()->keyBy('slug');

        DB::transaction(function () use ($user, $definitions, $slugs) {
            $now = now();
            foreach ($slugs as $slug) {
                $def = $definitions->get($slug);
                if (! $def) {
                    continue;
                }
                $exists = $user->earnedLearnerBadges()->where('learner_badge_definition_id', $def->id)->exists();
                if ($exists) {
                    continue;
                }
                $user->earnedLearnerBadges()->attach($def->id, ['earned_at' => $now]);
            }
        });
    }

    /**
     * @return array{learning_hours: float|int, modules_completed: int, avg_learner_rating: ?float, rated_sessions_count: int}
     */
    public function learnerStats(User $user): array
    {
        $completedSessions = SessionRequest::forLearner($user->id)
            ->where('status', 'completed')
            ->count();

        $modulesCompleted = LearningMaterialCompletion::where('user_id', $user->id)->count();

        $ratingQuery = SessionRequest::forLearner($user->id)
            ->where('status', 'completed')
            ->whereNotNull('learner_rating');

        $ratedCount = (clone $ratingQuery)->count();
        $avg = $ratedCount > 0 ? round((float) (clone $ratingQuery)->avg('learner_rating'), 2) : null;

        return [
            'learning_hours' => $completedSessions * self::HOURS_PER_SESSION,
            'modules_completed' => $modulesCompleted,
            'avg_learner_rating' => $avg,
            'rated_sessions_count' => $ratedCount,
        ];
    }
}
