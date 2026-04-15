<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\RoomCallLog;
use App\Models\RoomCallSignal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DmCallController extends Controller
{
    private function memberDmOrFail(int $conversationId, int $userId): Conversation
    {
        $dm = Conversation::dm()->findOrFail($conversationId);
        $isMember = $dm->participants()->where('user_id', $userId)->exists();
        abort_unless($isMember, 403, 'Only participants in this chat can use video call.');

        return $dm;
    }

    public function start(Request $request, int $id)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $dm = $this->memberDmOrFail($id, (int) $user->id);
        $active = RoomCallLog::where('room_id', $dm->id)->where('status', 'in_progress')->latest('id')->first();

        if (! $active) {
            $active = RoomCallLog::create([
                'room_id' => $dm->id,
                'started_by' => $user->id,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        return response()->json([
            'call_id' => $active->id,
            'status' => $active->status,
            'started_at' => $active->started_at,
        ]);
    }

    public function end(Request $request, int $id)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $dm = $this->memberDmOrFail($id, (int) $user->id);
        $active = RoomCallLog::where('room_id', $dm->id)->where('status', 'in_progress')->latest('id')->first();

        if ($active) {
            $end = now();
            $start = $active->started_at ? Carbon::parse($active->started_at) : $end;
            $active->ended_at = $end;
            $active->duration_seconds = max(0, $end->diffInSeconds($start));
            $active->status = 'ended';
            $active->save();
        }

        RoomCallSignal::create([
            'room_id' => $dm->id,
            'sender_id' => $user->id,
            'type' => 'hangup',
            'payload' => null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function sendSignal(Request $request, int $id)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $dm = $this->memberDmOrFail($id, (int) $user->id);
        $validated = $request->validate([
            'type' => 'required|in:ready,offer,answer,ice,hangup',
            'payload' => 'nullable',
            'target_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $targetUserId = $validated['target_user_id'] ?? null;
        if ($targetUserId) {
            $targetIsMember = $dm->participants()->where('user_id', $targetUserId)->exists();
            if (! $targetIsMember) {
                return response()->json(['message' => 'Invalid target user for this chat.'], 422);
            }
        }

        $signal = RoomCallSignal::create([
            'room_id' => $dm->id,
            'sender_id' => $user->id,
            'target_user_id' => $targetUserId,
            'type' => $validated['type'],
            'payload' => isset($validated['payload']) ? json_encode($validated['payload']) : null,
        ]);

        return response()->json(['id' => $signal->id]);
    }

    public function poll(Request $request, int $id)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $dm = $this->memberDmOrFail($id, (int) $user->id);
        $afterId = (int) $request->query('after_id', 0);

        $signals = RoomCallSignal::where('room_id', $dm->id)
            ->where('id', '>', $afterId)
            ->where('sender_id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('target_user_id')->orWhere('target_user_id', $user->id);
            })
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get()
            ->map(function ($signal) {
                return [
                    'id' => $signal->id,
                    'sender_id' => $signal->sender_id,
                    'target_user_id' => $signal->target_user_id,
                    'type' => $signal->type,
                    'payload' => $signal->payload ? json_decode($signal->payload, true) : null,
                    'created_at' => $signal->created_at,
                ];
            });

        return response()->json(['signals' => $signals]);
    }
}
