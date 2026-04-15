<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // GET /api/conversations
    public function index(Request $request)
    {
        $me = $request->user();

        $conversations = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $me->id))
            ->with([
                'users:id,name',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest('updated_at')
            ->get()
            ->map(function ($c) use ($me) {
                $other = $c->users->firstWhere('id', '!=', $me->id);
                $last = $c->messages->first();

                return [
                    'id' => $c->id,
                    'type' => $c->type,
                    'title' => $other ? $other->name : 'Conversation',
                    'otherUser' => $other ? ['id' => $other->id, 'name' => $other->name] : null,
                    'lastMessage' => $last ? [
                        'body' => $last->body,
                        'createdAt' => $last->created_at,
                    ] : null,
                ];
            });

        return response()->json($conversations);
    }

    // POST /api/conversations/start  { "userId": 2 }
    public function start(Request $request)
    {
        $me = $request->user();

        $request->validate([
            'userId' => ['required', 'integer', 'exists:users,id', 'different:'.$me->id],
        ]);

        $otherId = (int) $request->input('userId');

        // ✅ if already exists DM, return it
        $existing = Conversation::query()
            ->where('type', 'dm')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $me->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
            ->withCount('participants')
            ->get()
            ->first(fn ($c) => $c->participants_count === 2);

        if ($existing) {
            return response()->json(['conversationId' => $existing->id]);
        }

        $conversation = Conversation::create(['type' => 'dm']);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $me->id,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherId,
        ]);

        return response()->json(['conversationId' => $conversation->id], 201);
    }
}
