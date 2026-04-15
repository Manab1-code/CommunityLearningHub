<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'target_type',
        'target_count',
        'points',
        'icon',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    public function scopeCommunity($query)
    {
        return $query->where('type', 'community');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isWeekly(): bool
    {
        return $this->type === 'weekly';
    }

    public function isCommunity(): bool
    {
        return $this->type === 'community';
    }

    public function daysRemaining(): ?int
    {
        if (! $this->end_at) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->end_at, false));
    }

    public function isCurrentWeek(): bool
    {
        if (! $this->start_at || ! $this->end_at) {
            return true;
        }

        return now()->between($this->start_at, $this->end_at);
    }
}
