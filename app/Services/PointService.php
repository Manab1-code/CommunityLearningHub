<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;

class PointService
{
    public const POINTS_PER_TEACHING_SESSION = 50;

    public const POINTS_REDEEM_LEARN = 50;

    public function getBalance(User $user): int
    {
        $wallet = UserWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        return (int) $wallet->balance;
    }

    public function addPoints(User $user, int $amount, string $type, ?string $description = null, ?string $refType = null, ?int $refId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $refType, $refId) {
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );
            $wallet->increment('balance', $amount);
            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);

            return true;
        });
    }

    public function deductPoints(User $user, int $amount, string $type, ?string $description = null, ?string $refType = null, ?int $refId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $refType, $refId) {
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );
            if ($wallet->balance < $amount) {
                return false;
            }
            $wallet->decrement('balance', $amount);
            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => $type,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);

            return true;
        });
    }

    public function awardTeachingPoints(User $teacher, string $skillName, int $sessionRequestId): bool
    {
        return $this->addPoints(
            $teacher,
            self::POINTS_PER_TEACHING_SESSION,
            'earn_teaching',
            "Teaching session completed: {$skillName}",
            'session_request',
            $sessionRequestId
        );
    }

    /**
     * Award teaching points once per session (idempotent — safe if called twice).
     */
    public function awardTeachingPointsForCompletedSession(User $teacher, string $skillName, int $sessionRequestId): bool
    {
        $already = PointTransaction::where('user_id', $teacher->id)
            ->where('type', 'earn_teaching')
            ->where('reference_type', 'session_request')
            ->where('reference_id', $sessionRequestId)
            ->exists();

        if ($already) {
            return false;
        }

        return $this->awardTeachingPoints($teacher, $skillName, $sessionRequestId);
    }

    public function awardChallengePoints(User $user, int $points, string $challengeTitle, int $challengeId): bool
    {
        return $this->addPoints(
            $user,
            $points,
            'earn_challenge',
            "Challenge completed: {$challengeTitle}",
            'challenge',
            $challengeId
        );
    }

    public function redeemForLearning(User $user, string $skillName): bool
    {
        $cost = self::POINTS_REDEEM_LEARN;

        return $this->deductPoints(
            $user,
            $cost,
            'redeem_learn',
            "Redeemed {$cost} pts — unlock learning skill: {$skillName}"
        );
    }
}
