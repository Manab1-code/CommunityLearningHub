<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['type', 'title', 'topic'];

    public function scopeDm($query)
    {
        return $query->where('type', 'dm');
    }

    public function scopeRoom($query)
    {
        return $query->where('type', 'room');
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Find existing DM between two users (returns null if none).
     */
    public static function findDmBetween(int $userId1, int $userId2): ?self
    {
        return self::query()
            ->dm()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId2))
            ->withCount('participants')
            ->get()
            ->first(fn ($c) => $c->participants_count === 2);
    }

    /**
     * Get or create a DM conversation between two users (e.g. learner and tutor after session accepted).
     */
    public static function getOrCreateDmBetween(int $userId1, int $userId2): self
    {
        $existing = self::query()
            ->dm()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId2))
            ->withCount('participants')
            ->get()
            ->first(fn ($c) => $c->participants_count === 2);

        if ($existing) {
            return $existing;
        }

        $conversation = self::create(['type' => 'dm']);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $userId1]);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $userId2]);

        return $conversation;
    }
}
