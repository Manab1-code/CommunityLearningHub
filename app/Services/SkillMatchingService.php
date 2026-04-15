<?php

namespace App\Services;

use App\Models\ProfileSkill;
use App\Models\User;

class SkillMatchingService
{
    /**
     * Find teachers for a learner based on their learning skills
     *
     * @param  bool  $useLocation  Whether to consider location in matching
     * @param  int  $limit  Maximum number of matches to return
     * @return array Array of matched teachers with match score
     */
    public function findTeachersForLearner(User $learner, bool $useLocation = false, int $limit = 10): array
    {
        // Get learner's learning skills with levels
        $learnerSkills = ProfileSkill::where('user_id', $learner->id)
            ->where('type', 'learning')
            ->get();

        if ($learnerSkills->isEmpty()) {
            return [];
        }

        // Build skill matching query
        $skillNames = $learnerSkills->pluck('name')->toArray();
        $skillLevels = $learnerSkills->pluck('skill_level', 'name')->toArray();

        // Find teachers who teach any of the learner's desired skills
        $teachers = User::whereHas('profileSkills', function ($query) use ($skillNames) {
            $query->where('type', 'teaching')
                ->whereIn('name', $skillNames);
        })
            ->where('id', '!=', $learner->id) // Exclude self
            ->with(['profile', 'profileSkills' => function ($query) {
                $query->where('type', 'teaching');
            }])
            ->get();

        $matches = [];

        foreach ($teachers as $teacher) {
            $matchScore = $this->calculateMatchScore($learnerSkills, $teacher, $useLocation, $learner);

            if ($matchScore['total_score'] > 0) {
                $matches[] = [
                    'teacher' => $teacher,
                    'score' => $matchScore['total_score'],
                    'skill_matches' => $matchScore['skill_matches'],
                    'level_matches' => $matchScore['level_matches'],
                    'location_match' => $matchScore['location_match'],
                    'details' => $matchScore,
                ];
            }
        }

        // Sort by score descending
        usort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($matches, 0, $limit);
    }

    /**
     * Find learners for a teacher based on their teaching skills
     *
     * @param  bool  $useLocation  Whether to consider location in matching
     * @param  int  $limit  Maximum number of matches to return
     * @return array Array of matched learners with match score
     */
    public function findLearnersForTeacher(User $teacher, bool $useLocation = false, int $limit = 10): array
    {
        // Get teacher's teaching skills
        $teacherSkills = ProfileSkill::where('user_id', $teacher->id)
            ->where('type', 'teaching')
            ->get();

        if ($teacherSkills->isEmpty()) {
            return [];
        }

        $skillNames = $teacherSkills->pluck('name')->toArray();

        // Find learners who want to learn any of the teacher's skills
        $learners = User::whereHas('profileSkills', function ($query) use ($skillNames) {
            $query->where('type', 'learning')
                ->whereIn('name', $skillNames);
        })
            ->where('id', '!=', $teacher->id)
            ->with(['profile', 'profileSkills' => function ($query) {
                $query->where('type', 'learning');
            }])
            ->get();

        $matches = [];

        foreach ($learners as $learner) {
            $learnerSkills = ProfileSkill::where('user_id', $learner->id)
                ->where('type', 'learning')
                ->get();

            // For finding learners for teacher: learner wants to learn, teacher can teach
            $matchScore = $this->calculateMatchScore($learnerSkills, $teacher, $useLocation, $learner);

            if ($matchScore['total_score'] > 0) {
                $matches[] = [
                    'learner' => $learner,
                    'score' => $matchScore['total_score'],
                    'skill_matches' => $matchScore['skill_matches'],
                    'level_matches' => $matchScore['level_matches'],
                    'location_match' => $matchScore['location_match'],
                    'details' => $matchScore,
                ];
            }
        }

        // Sort by score descending
        usort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($matches, 0, $limit);
    }

    /**
     * Calculate match score between learner skills and teacher
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $learnerSkills
     * @return array Match score details
     */
    private function calculateMatchScore($learnerSkills, User $teacher, bool $useLocation, User $learner): array
    {
        $teacherSkills = ProfileSkill::where('user_id', $teacher->id)
            ->where('type', 'teaching')
            ->get()
            ->keyBy('name');

        $skillMatches = [];
        $levelMatches = 0;
        $totalSkillScore = 0;
        $locationMatch = false;
        $locationScore = 0;

        // Check skill matches
        foreach ($learnerSkills as $learnerSkill) {
            $skillName = $learnerSkill->name;

            if ($teacherSkills->has($skillName)) {
                $teacherSkill = $teacherSkills[$skillName];
                $skillMatches[] = [
                    'skill' => $skillName,
                    'learner_level' => $learnerSkill->skill_level ?? 'beginner',
                    'teacher_level' => $teacherSkill->skill_level ?? 'intermediate',
                ];

                // Base score for skill match
                $totalSkillScore += 10;

                // Bonus for level compatibility
                // Learner wants beginner, teacher is expert = good match
                // Learner wants expert, teacher is beginner = poor match
                $levelScore = $this->calculateLevelCompatibility(
                    $learnerSkill->skill_level ?? 'beginner',
                    $teacherSkill->skill_level ?? 'intermediate'
                );
                $levelMatches += $levelScore;
                $totalSkillScore += $levelScore;
            }
        }

        // Location matching (optional)
        if ($useLocation) {
            $learnerLocation = $learner->profile?->location;
            $teacherLocation = $teacher->profile?->location;

            if ($learnerLocation && $teacherLocation) {
                // Simple location matching (same city/country)
                // You can enhance this with geolocation API
                $locationMatch = $this->compareLocations($learnerLocation, $teacherLocation);
                if ($locationMatch) {
                    $locationScore = 5; // Bonus for same location
                    $totalSkillScore += $locationScore;
                }
            }
        }

        return [
            'total_score' => $totalSkillScore,
            'skill_matches' => count($skillMatches),
            'level_matches' => $levelMatches,
            'location_match' => $locationMatch,
            'location_score' => $locationScore,
            'matched_skills' => $skillMatches,
        ];
    }

    /**
     * Calculate level compatibility score
     * Higher score = better match
     */
    private function calculateLevelCompatibility(string $learnerLevel, string $teacherLevel): int
    {
        $levels = ['beginner' => 1, 'intermediate' => 2, 'expert' => 3];

        $learnerValue = $levels[$learnerLevel] ?? 1;
        $teacherValue = $levels[$teacherLevel] ?? 2;

        // Teacher should be at least at the level learner wants
        if ($teacherValue >= $learnerValue) {
            // Perfect match: learner wants intermediate, teacher is intermediate = 5 points
            // Good match: learner wants beginner, teacher is expert = 3 points
            // Great match: learner wants expert, teacher is expert = 5 points
            if ($teacherValue == $learnerValue) {
                return 5; // Perfect level match
            } else {
                return 3; // Teacher is higher level (good)
            }
        } else {
            // Teacher is lower level than learner wants = penalty
            return -2;
        }
    }

    /**
     * Compare two location strings
     * Simple string matching - can be enhanced with geolocation
     */
    private function compareLocations(string $location1, string $location2): bool
    {
        // Normalize locations (lowercase, trim)
        $loc1 = strtolower(trim($location1));
        $loc2 = strtolower(trim($location2));

        // Exact match
        if ($loc1 === $loc2) {
            return true;
        }

        // Check if same city (e.g., "Kathmandu, Nepal" vs "Kathmandu")
        $loc1Parts = array_map('trim', explode(',', $loc1));
        $loc2Parts = array_map('trim', explode(',', $loc2));

        // Same city
        if ($loc1Parts[0] === $loc2Parts[0]) {
            return true;
        }

        // Same country (if both have country)
        if (count($loc1Parts) > 1 && count($loc2Parts) > 1) {
            if (end($loc1Parts) === end($loc2Parts)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get recommended matches for a user (both teachers and learners)
     */
    public function getRecommendedMatches(User $user, bool $useLocation = false, int $limit = 10): array
    {
        $recommendations = [];

        // Find teachers for this user (if they have learning skills)
        $teachers = $this->findTeachersForLearner($user, $useLocation, $limit);
        foreach ($teachers as $match) {
            $recommendations[] = [
                'type' => 'teacher',
                'user' => $match['teacher'],
                'score' => $match['score'],
                'matched_skills' => $match['details']['matched_skills'],
            ];
        }

        // Find learners for this user (if they have teaching skills)
        $learners = $this->findLearnersForTeacher($user, $useLocation, $limit);
        foreach ($learners as $match) {
            $recommendations[] = [
                'type' => 'learner',
                'user' => $match['learner'],
                'score' => $match['score'],
                'matched_skills' => $match['details']['matched_skills'],
            ];
        }

        // Sort by score
        usort($recommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get distinct skill names (categories) for discovery filters
     */
    public function getCategories(): array
    {
        return ProfileSkill::query()
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Local discovery: find skill opportunities (teachers and learners) by categories and optional distance
     *
     * @param  int|null  $userId  Current user (excluded from results)
     * @param  array  $categories  Skill names to filter by (empty = all)
     * @param  float|null  $lat  Center latitude for distance filter
     * @param  float|null  $lng  Center longitude for distance filter
     * @param  float|null  $maxKm  Max distance in km (used only when lat/lng provided)
     * @param  int  $limit  Max results
     * @return array List of opportunities with user, type, skills, distance_km
     */
    public function discoverOpportunities(
        ?int $userId,
        array $categories = [],
        ?float $lat = null,
        ?float $lng = null,
        ?float $maxKm = null,
        int $limit = 20
    ): array {
        $useDistance = $lat !== null && $lng !== null && $maxKm !== null && $maxKm > 0;

        $query = User::query()
            ->whereHas('profile')
            ->with(['profile', 'profileSkills']);

        if ($userId !== null) {
            $query->where('id', '!=', $userId);
        }

        if ($useDistance) {
            $haversine = sprintf(
                '(6371 * acos(least(1, greatest(-1, cos(radians(%s)) * cos(radians(user_profiles.latitude)) * cos(radians(user_profiles.longitude) - radians(%s)) + sin(radians(%s)) * sin(radians(user_profiles.latitude))))))',
                (float) $lat,
                (float) $lng,
                (float) $lat
            );
            $query->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->whereNotNull('user_profiles.latitude')
                ->whereNotNull('user_profiles.longitude')
                ->selectRaw("users.*, {$haversine} as distance_km")
                ->havingRaw('distance_km <= ?', [(float) $maxKm]);
        }

        if (! empty($categories)) {
            $normalized = array_map('trim', array_filter($categories));
            if (! empty($normalized)) {
                $query->whereHas('profileSkills', function ($q) use ($normalized) {
                    $q->whereIn('name', $normalized);
                });
            }
        }

        $query->orderBy($useDistance ? 'distance_km' : 'users.id')->limit($limit * 2); // fetch extra to build opportunities

        $users = $query->get();
        $catList = array_map('trim', array_filter($categories ?? []));

        $opportunities = [];
        $seen = [];
        foreach ($users as $user) {
            $teaching = $user->profileSkills->where('type', 'teaching');
            $learning = $user->profileSkills->where('type', 'learning');

            $matchedTeaching = $teaching->when(! empty($catList), fn ($c) => $c->whereIn('name', $catList));
            $matchedLearning = $learning->when(! empty($catList), fn ($c) => $c->whereIn('name', $catList));

            $distanceKm = $useDistance && isset($user->distance_km) ? round((float) $user->distance_km, 2) : null;

            if ($matchedTeaching->isNotEmpty()) {
                $key = 't'.$user->id;
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $opportunities[] = [
                        'type' => 'teacher',
                        'user' => $user,
                        'skills' => $matchedTeaching->map(fn ($s) => ['name' => $s->name, 'skill_level' => $s->skill_level])->values()->toArray(),
                        'distance_km' => $distanceKm,
                    ];
                }
            }
            if ($matchedLearning->isNotEmpty()) {
                $key = 'l'.$user->id;
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $opportunities[] = [
                        'type' => 'learner',
                        'user' => $user,
                        'skills' => $matchedLearning->map(fn ($s) => ['name' => $s->name, 'skill_level' => $s->skill_level])->values()->toArray(),
                        'distance_km' => $distanceKm,
                    ];
                }
            }
        }

        // Sort by distance when available, then by id
        if ($useDistance) {
            usort($opportunities, function ($a, $b) {
                $da = $a['distance_km'] ?? 999999;
                $db = $b['distance_km'] ?? 999999;

                return $da <=> $db;
            });
        }

        return array_slice($opportunities, 0, $limit);
    }
}
