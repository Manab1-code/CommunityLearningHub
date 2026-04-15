<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Notification;
use App\Models\ProfileSkill;
use App\Models\SessionRequest;
use App\Services\LearnerBadgeService;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionRequestController extends Controller
{
    /**
     * Get all session requests for the authenticated user
     * Shows both sent (as learner) and received (as teacher)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $status = $request->input('status', 'all'); // all, pending, accepted, rejected

        $sentRequests = SessionRequest::where('learner_id', $user->id)
            ->with('teacher.profile')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();

        $receivedRequests = SessionRequest::where('teacher_id', $user->id)
            ->with('learner.profile')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'sent' => $sentRequests,
                'received' => $receivedRequests,
            ]);
        }

        return view('session-requests', [
            'sentRequests' => $sentRequests,
            'receivedRequests' => $receivedRequests,
            'status' => $status,
        ]);
    }

    /**
     * Send a session request
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:users,id',
            'skill_name' => 'required|string|max:100',
            'skill_level' => 'nullable|in:beginner,intermediate,expert',
            'message' => 'nullable|string|max:500',
            'proposed_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        // Check if teacher teaches this skill
        $teacherTeachesSkill = ProfileSkill::where('user_id', $request->teacher_id)
            ->where('type', 'teaching')
            ->where('name', $request->skill_name)
            ->exists();

        if (! $teacherTeachesSkill) {
            $error = "This teacher doesn't teach {$request->skill_name}";
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        // Check if request already exists
        $existingRequest = SessionRequest::where('learner_id', $user->id)
            ->where('teacher_id', $request->teacher_id)
            ->where('skill_name', $request->skill_name)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existingRequest) {
            $error = 'You already have a pending or accepted request for this skill';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $sessionRequest = SessionRequest::create([
            'learner_id' => $user->id,
            'teacher_id' => $request->teacher_id,
            'skill_name' => $request->skill_name,
            'skill_level' => $request->skill_level,
            'message' => $request->message,
            'proposed_date' => $request->proposed_date ? new \DateTime($request->proposed_date) : null,
            'status' => 'pending',
        ]);

        // Notify teacher of new session request
        Notification::create([
            'user_id' => $request->teacher_id,
            'type' => 'session_request_received',
            'message' => "{$user->name} sent you a session request for {$sessionRequest->skill_name}",
            'data' => [
                'session_request_id' => $sessionRequest->id,
                'learner_id' => $user->id,
                'learner_name' => $user->name,
                'skill_name' => $sessionRequest->skill_name,
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session request sent successfully',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ], 201);
        }

        return redirect()->back()->with('success', 'Session request sent successfully!');
    }

    /**
     * Accept a session request
     */
    public function accept(Request $request, $id)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $sessionRequest = SessionRequest::findOrFail($id);

        // Only teacher can accept
        if ($sessionRequest->teacher_id !== $user->id) {
            $error = 'Unauthorized';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return redirect()->back()->with('error', $error);
        }

        if ($sessionRequest->status !== 'pending') {
            $error = 'This request is no longer pending';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $validator = Validator::make($request->all(), [
            'accepted_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $sessionRequest->status = 'accepted';
        $sessionRequest->accepted_date = $request->accepted_date
            ? new \DateTime($request->accepted_date)
            : ($sessionRequest->proposed_date ?? now()->addDays(7));
        $sessionRequest->save();

        // Create notification for learner
        Notification::create([
            'user_id' => $sessionRequest->learner_id,
            'type' => 'session_request_accepted',
            'message' => "{$user->name} accepted your session request for {$sessionRequest->skill_name}",
            'data' => [
                'session_request_id' => $sessionRequest->id,
                'teacher_id' => $user->id,
                'teacher_name' => $user->name,
                'skill_name' => $sessionRequest->skill_name,
                'accepted_date' => $sessionRequest->accepted_date->toDateTimeString(),
            ],
        ]);

        // Create or get DM so learner and tutor can chat after session is accepted
        Conversation::getOrCreateDmBetween($sessionRequest->learner_id, $sessionRequest->teacher_id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session request accepted',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ]);
        }

        return redirect()->back()->with('success', 'Session request accepted!');
    }

    /**
     * Reject a session request
     */
    public function reject(Request $request, $id)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $sessionRequest = SessionRequest::findOrFail($id);

        // Only teacher can reject
        if ($sessionRequest->teacher_id !== $user->id) {
            $error = 'Unauthorized';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return redirect()->back()->with('error', $error);
        }

        if ($sessionRequest->status !== 'pending') {
            $error = 'This request is no longer pending';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $sessionRequest->status = 'rejected';
        $sessionRequest->rejection_reason = $request->input('rejection_reason');
        $sessionRequest->save();

        // Create notification for learner
        $rejectionMessage = "{$user->name} rejected your session request for {$sessionRequest->skill_name}";
        if ($sessionRequest->rejection_reason) {
            $rejectionMessage .= ": {$sessionRequest->rejection_reason}";
        }

        Notification::create([
            'user_id' => $sessionRequest->learner_id,
            'type' => 'session_request_rejected',
            'message' => $rejectionMessage,
            'data' => [
                'session_request_id' => $sessionRequest->id,
                'teacher_id' => $user->id,
                'teacher_name' => $user->name,
                'skill_name' => $sessionRequest->skill_name,
                'rejection_reason' => $sessionRequest->rejection_reason,
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session request rejected',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ]);
        }

        return redirect()->back()->with('success', 'Session request rejected.');
    }

    /**
     * Reschedule a session request
     */
    public function reschedule(Request $request, $id)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $sessionRequest = SessionRequest::findOrFail($id);

        // Both learner and teacher can reschedule if accepted
        if ($sessionRequest->learner_id !== $user->id && $sessionRequest->teacher_id !== $user->id) {
            $error = 'Unauthorized';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return redirect()->back()->with('error', $error);
        }

        if (! in_array($sessionRequest->status, ['accepted', 'pending'])) {
            $error = 'This request cannot be rescheduled';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $validator = Validator::make($request->all(), [
            'new_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $sessionRequest->status = 'rescheduled';
        $sessionRequest->accepted_date = new \DateTime($request->new_date);
        $sessionRequest->reschedule_reason = $request->reason;
        $sessionRequest->save();

        // Create notification for the other party
        $otherUserId = $sessionRequest->learner_id === $user->id
            ? $sessionRequest->teacher_id
            : $sessionRequest->learner_id;

        $otherUser = \App\Models\User::find($otherUserId);
        $rescheduleMessage = $sessionRequest->learner_id === $user->id
            ? "{$user->name} rescheduled the session for {$sessionRequest->skill_name} to ".(new \DateTime($request->new_date))->format('M d, Y h:i A')
            : "{$user->name} rescheduled the session for {$sessionRequest->skill_name} to ".(new \DateTime($request->new_date))->format('M d, Y h:i A');

        if ($request->reason) {
            $rescheduleMessage .= ". Reason: {$request->reason}";
        }

        Notification::create([
            'user_id' => $otherUserId,
            'type' => 'session_request_rescheduled',
            'message' => $rescheduleMessage,
            'data' => [
                'session_request_id' => $sessionRequest->id,
                'rescheduled_by_id' => $user->id,
                'rescheduled_by_name' => $user->name,
                'skill_name' => $sessionRequest->skill_name,
                'new_date' => (new \DateTime($request->new_date))->toDateTimeString(),
                'reason' => $request->reason,
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session rescheduled successfully',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ]);
        }

        return redirect()->back()->with('success', 'Session rescheduled successfully!');
    }

    /**
     * Cancel a session request (learner can cancel pending requests)
     */
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $sessionRequest = SessionRequest::findOrFail($id);

        // Only learner can cancel
        if ($sessionRequest->learner_id !== $user->id) {
            $error = 'Unauthorized';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return redirect()->back()->with('error', $error);
        }

        if (! in_array($sessionRequest->status, ['pending', 'accepted'])) {
            $error = 'This request cannot be cancelled';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $sessionRequest->status = 'cancelled';
        $sessionRequest->save();

        // Notify teacher that learner cancelled the session
        Notification::create([
            'user_id' => $sessionRequest->teacher_id,
            'type' => 'session_request_cancelled',
            'message' => "{$user->name} cancelled the session request for {$sessionRequest->skill_name}",
            'data' => [
                'session_request_id' => $sessionRequest->id,
                'learner_id' => $user->id,
                'learner_name' => $user->name,
                'skill_name' => $sessionRequest->skill_name,
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session request cancelled',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ]);
        }

        return redirect()->back()->with('success', 'Session request cancelled.');
    }

    /**
     * Mark session as completed (teacher only). Optional 1–5 rating of the learner.
     */
    public function complete(Request $request, $id)
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect('/auth/signin');
        }

        $sessionRequest = SessionRequest::findOrFail($id);

        if ($sessionRequest->teacher_id !== $user->id) {
            $error = 'Only the tutor can mark this session complete.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }

            return redirect()->back()->with('error', $error);
        }

        if (! in_array($sessionRequest->status, ['accepted', 'rescheduled'], true)) {
            $error = 'Only accepted or rescheduled sessions can be marked complete.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $error], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        $request->validate([
            'learner_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $sessionRequest->status = 'completed';
        if ($request->filled('learner_rating')) {
            $sessionRequest->learner_rating = (int) $request->input('learner_rating');
        }
        $sessionRequest->save();

        app(PointService::class)->awardTeachingPointsForCompletedSession(
            $user,
            $sessionRequest->skill_name,
            $sessionRequest->id
        );

        $learner = $sessionRequest->learner;
        if ($learner) {
            app(LearnerBadgeService::class)->syncForUser($learner);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session marked complete.',
                'request' => $sessionRequest->load(['learner.profile', 'teacher.profile']),
            ]);
        }

        return redirect()->back()->with(
            'success',
            'Session marked complete. +'.PointService::POINTS_PER_TEACHING_SESSION.' points added to your Skill Wallet. Thank you for teaching!'
        );
    }

    /**
     * Show form to send a session request
     */
    public function showCreateForm(Request $request, $teacherId)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/auth/signin');
        }

        $teacher = \App\Models\User::with(['profile', 'profileSkills' => function ($q) {
            $q->where('type', 'teaching');
        }])->findOrFail($teacherId);

        return view('send-session-request', [
            'teacher' => $teacher,
        ]);
    }
}
