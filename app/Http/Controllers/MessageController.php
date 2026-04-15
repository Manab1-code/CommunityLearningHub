<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private function ensureParticipant(Request $request, Conversation $conversation)
    {
        $me = $request->user();

        $isParticipant = $conversation->participants()
            ->where('user_id', $me->id)
            ->exists();

        if (! $isParticipant) {
            abort(403, 'Not allowed');
        }
    }

    // GET /api/conversations/{id}/messages
    public function list(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $this->ensureParticipant($request, $conversation);

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->limit(200)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender' => [
                    'id' => $m->sender->id,
                    'name' => $m->sender->name,
                ],
                'createdAt' => $m->created_at,
            ]);

        return response()->json([
            'conversationId' => $conversation->id,
            'messages' => $messages,
        ]);
    }

    // POST /api/conversations/{id}/messages  { "body": "hello" }
    public function send(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $this->ensureParticipant($request, $conversation);

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $me = $request->user();

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $me->id,
            'body' => $request->input('body'),
        ]);

        // touch conversation for sorting
        $conversation->touch();

        \App\Models\Notification::notifyNewMessage($conversation->id, $me->id, $me->name);

        return response()->json([
            'id' => $msg->id,
            'body' => $msg->body,
            'sender' => ['id' => $me->id, 'name' => $me->name],
            'createdAt' => $msg->created_at,
        ], 201);
    }
}
