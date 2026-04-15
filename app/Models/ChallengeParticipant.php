<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeParticipant extends Model
{
    protected $fillable = [
        'user_id',
        'challenge_id',
        'progress',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function progressPercent(): int
    {
        $target = $this->challenge->target_count ?? 1;

        return min(100, (int) round(($this->progress / $target) * 100));
    }
}
