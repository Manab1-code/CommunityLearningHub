<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /** List all community rooms (topic-based chat rooms) */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $rooms = Conversation::room()
            ->withCount('participants')
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        $myRoomIds = $user->id
            ? ConversationParticipant::where('user_id', $user->id)->pluck('conversation_id')->toArray()
            : [];

        return view('communitygroups', [
            'rooms' => $rooms,
            'myRoomIds' => $myRoomIds,
        ]);
    }

    /** Show create room form */
    public function create()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        return view('rooms.create');
    }

    /** Store a new room */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'topic' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $room = Conversation::create([
            'type' => 'room',
            'title' => $request->title,
            'topic' => $request->topic ?: null,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $room->id,
            'user_id' => $user->id,
        ]);

        return redirect()->route('rooms.show', $room->id)->with('success', 'Room created. Invite others to join!');
    }

    /** Show room chat (messages + send form). Non-members can see info + join button. */
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $room = Conversation::room()->findOrFail($id);
        $isMember = $room->participants()->where('user_id', $user->id)->exists();

        // Treat the first participant as the creator/owner of the room
        $creatorId = $room->participants()->orderBy('id')->value('user_id');
        $canInvite = $isMember && $creatorId && ((int) $creatorId === (int) $user->id);

        $messages = collect();
        $participants = collect();
        if ($isMember) {
            $messages = Message::where('conversation_id', $room->id)
                ->with('sender:id,name')
                ->orderBy('created_at', 'asc')
                ->limit(200)
                ->get();

            $participants = $room->participants()
                ->with('user:id,name')
                ->get()
                ->pluck('user')
                ->filter();
        }

        return view('rooms.show', [
            'room' => $room,
            'messages' => $messages,
            'participants' => $participants,
            'isMember' => $isMember,
            'canInvite' => $canInvite,
        ]);
    }

    /** Join a room */
    public function join(Request $request, $id)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/signin');
        }

        $room = Conversation::room()->findOrFail($id);
        $exists = $room->participants()->where('user_id', $user->id)->exists();
        if (! $exists) {
            ConversationParticipant::create([
                'conversation_id' => $room->id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('rooms.show', $room->id)->with('success', 'You joined the room.');
    }
}
