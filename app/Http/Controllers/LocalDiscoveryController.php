<?php

namespace App\Http\Controllers;

use App\Services\SkillMatchingService;
use Illuminate\Http\Request;

class LocalDiscoveryController extends Controller
{
    public function __construct(
        protected SkillMatchingService $matchingService
    ) {}

    /**
     * GET /api/discovery/opportunities
     *
     * Find skill opportunities (teachers and learners) with optional filters:
     * - categories[]: skill names (e.g. PHP, Laravel, React)
     * - lat, lng: center point for distance filter
     * - max_km: max distance in km (used only with lat/lng)
     * - limit: max results (default 20, max 50)
     */
    public function opportunities(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;

        $categories = $request->input('categories', []);
        if (is_string($categories)) {
            $categories = array_filter(explode(',', $categories));
        }
        $lat = $request->has('lat') ? (float) $request->input('lat') : null;
        $lng = $request->has('lng') ? (float) $request->input('lng') : null;
        $maxKm = $request->has('max_km') ? (float) $request->input('max_km') : null;
        $limit = min($request->integer('limit', 20), 50);

        $opportunities = $this->matchingService->discoverOpportunities(
            $userId,
            $categories,
            $lat,
            $lng,
            $maxKm,
            $limit
        );

        $list = array_map(function ($opp) {
            $u = $opp['user'];
            $profile = $u->profile;

            return [
                'type' => $opp['type'],
                'user' => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'location' => $profile?->location,
                    'photo_url' => $profile?->photo_path ? asset('storage/'.$profile->photo_path) : null,
                    'latitude' => $profile?->latitude,
                    'longitude' => $profile?->longitude,
                ],
                'skills' => $opp['skills'],
                'distance_km' => $opp['distance_km'],
            ];
        }, $opportunities);

        return response()->json([
            'opportunities' => $list,
            'filters' => [
                'categories' => $categories,
                'lat' => $lat,
                'lng' => $lng,
                'max_km' => $maxKm,
            ],
        ]);
    }

    /**
     * GET /api/discovery/categories
     *
     * List all skill names (categories) for filter dropdowns
     */
    public function categories(Request $request)
    {
        $categories = $this->matchingService->getCategories();

        return response()->json(['categories' => $categories]);
    }
}
