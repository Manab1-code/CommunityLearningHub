<?php

namespace Database\Seeders;

use App\Models\Challenge;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weekly = [
            [
                'title' => 'Teaching Master',
                'description' => 'Teach 3 sessions this week and share your expertise',
                'type' => 'weekly',
                'target_type' => 'teach_sessions',
                'target_count' => 3,
                'points' => 150,
                'icon' => '🎓',
                'start_at' => $weekStart,
                'end_at' => $weekEnd,
            ],
            [
                'title' => 'Eager Learner',
                'description' => 'Attend 5 learning sessions from different teachers',
                'type' => 'weekly',
                'target_type' => 'attend_sessions',
                'target_count' => 5,
                'points' => 100,
                'icon' => '📘',
                'start_at' => $weekStart,
                'end_at' => $weekEnd,
            ],
            [
                'title' => 'Resource Sharer',
                'description' => 'Share 2 learning materials (videos, notes, or guides) this week',
                'type' => 'weekly',
                'target_type' => 'share_resources',
                'target_count' => 2,
                'points' => 75,
                'icon' => '📤',
                'start_at' => $weekStart,
                'end_at' => $weekEnd,
            ],
        ];

        foreach ($weekly as $c) {
            Challenge::updateOrCreate(
                [
                    'type' => 'weekly',
                    'title' => $c['title'],
                    'start_at' => $c['start_at'],
                ],
                array_merge($c, ['is_active' => true])
            );
        }

        $community = [
            [
                'title' => 'Community Builder',
                'description' => 'Help the community by teaching 10 sessions (all time)',
                'type' => 'community',
                'target_type' => 'teach_sessions',
                'target_count' => 10,
                'points' => 200,
                'icon' => '👥',
                'start_at' => null,
                'end_at' => null,
            ],
            [
                'title' => 'Global Collaborator',
                'description' => 'Complete 5 sessions as learner or teacher with different people',
                'type' => 'community',
                'target_type' => 'complete_sessions',
                'target_count' => 5,
                'points' => 200,
                'icon' => '🌍',
                'start_at' => null,
                'end_at' => null,
            ],
            [
                'title' => '5-Star Contributor',
                'description' => 'Share 5 learning resources with the community',
                'type' => 'community',
                'target_type' => 'share_resources',
                'target_count' => 5,
                'points' => 250,
                'icon' => '⭐',
                'start_at' => null,
                'end_at' => null,
            ],
        ];

        foreach ($community as $c) {
            Challenge::updateOrCreate(
                [
                    'type' => 'community',
                    'title' => $c['title'],
                ],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
