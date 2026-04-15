<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Create "new message" notifications for all participants in a conversation except the sender.
     * Used when someone sends a chat message so recipients see the notification badge.
     */
    public static function notifyNewMessage(int $conversationId, int $senderId, string $senderName): void
    {
        $participantIds = \App\Models\ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $senderId)
            ->pluck('user_id');

        foreach ($participantIds as $userId) {
            self::create([
                'user_id' => $userId,
                'type' => 'new_message',
                'message' => "{$senderName} sent you a message",
                'data' => [
                    'conversation_id' => $conversationId,
                    'sender_id' => $senderId,
                    'sender_name' => $senderName,
                ],
                'is_read' => false,
            ]);
        }
    }
}
