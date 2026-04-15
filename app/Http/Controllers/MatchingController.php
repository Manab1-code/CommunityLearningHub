<?php

namespace App\Http\Controllers;

use App\Services\SkillMatchingService;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    protected $matchingService;

    public function __construct(SkillMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Get recommended matches for the authenticated user
     */
    public function getRecommendations(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $useLocation = $request->boolean('use_location', false);
        $limit = min($request->integer('limit', 10), 50); // Max 50

        $matches = $this->matchingService->getRecommendedMatches($user, $useLocation, $limit);

        return response()->json([
            'matches' => $matches,
            'use_location' => $useLocation,
        ]);
    }

    /**
     * Get teachers for a learner
     */
    public function findTeachers(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $useLocation = $request->boolean('use_location', false);
        $limit = min($request->integer('limit', 10), 50);

        $teachers = $this->matchingService->findTeachersForLearner($user, $useLocation, $limit);

        return response()->json([
            'teachers' => $teachers,
            'use_location' => $useLocation,
        ]);
    }

    /**
     * Get learners for a teacher
     */
    public function findLearners(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $useLocation = $request->boolean('use_location', false);
        $limit = min($request->integer('limit', 10), 50);

        $learners = $this->matchingService->findLearnersForTeacher($user, $useLocation, $limit);

        if ($request->expectsJson()) {
            return response()->json([
                'learners' => $learners,
                'use_location' => $useLocation,
            ]);
        }

        return view('matching.learners', [
            'learners' => $learners,
            'useLocation' => $useLocation,
        ]);
    }
}
