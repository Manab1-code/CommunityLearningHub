<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LearnerBadgeDefinition extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'icon_emoji',
        'sort_order',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_learner_badges', 'learner_badge_definition_id', 'user_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
