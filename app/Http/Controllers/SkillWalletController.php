<?php

namespace App\Http\Controllers;

use App\Models\ProfileSkill;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SkillWalletController extends Controller
{
    public function __construct(
        protected PointService $pointService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $balance = $this->pointService->getBalance($user);

        $transactions = $user->pointTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $learningSkills = $user->profileSkills()
            ->where('type', 'learning')
            ->pluck('name');

        return view('skillwallet', [
            'balance' => $balance,
            'transactions' => $transactions,
            'learningSkills' => $learningSkills,
            'redeemCost' => PointService::POINTS_REDEEM_LEARN,
            'pointsPerTeaching' => PointService::POINTS_PER_TEACHING_SESSION,
        ]);
    }

    public function redeem(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $request->validate([
            'skill_name' => 'required|string|max:100',
        ]);

        $skillName = trim($request->skill_name);
        $cost = PointService::POINTS_REDEEM_LEARN;
        $balance = $this->pointService->getBalance($user);

        if ($balance < $cost) {
            return redirect()->route('skillwallet')->with('error', "You need {$cost} points to redeem. Your balance: {$balance}.");
        }

        $duplicate = ProfileSkill::where('user_id', $user->id)
            ->where('type', 'learning')
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($skillName)])
            ->exists();

        if ($duplicate) {
            return redirect()->route('skillwallet')->with('error', 'You already have this skill on your profile as a learning goal. Pick a different skill to redeem.');
        }

        if (! $this->pointService->redeemForLearning($user, $skillName)) {
            return redirect()->route('skillwallet')->with('error', 'Could not redeem points.');
        }

        ProfileSkill::create([
            'user_id' => $user->id,
            'type' => 'learning',
            'name' => $skillName,
            'skill_level' => 'beginner',
        ]);

        return redirect()->route('skillwallet')->with(
            'success',
            "You spent {$cost} points. \"{$skillName}\" was added to your learning skills — explore teachers and book a session!"
        );
    }
}
