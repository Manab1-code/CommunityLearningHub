<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationships
     */

    // One user has one profile
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // One user has many skills (teaching + learning)
    public function profileSkills()
    {
        return $this->hasMany(ProfileSkill::class);
    }

    // Session requests where this user is the learner
    public function sentSessionRequests()
    {
        return $this->hasMany(SessionRequest::class, 'learner_id');
    }

    // Session requests where this user is the teacher
    public function receivedSessionRequests()
    {
        return $this->hasMany(SessionRequest::class, 'teacher_id');
    }

    // Notifications for this user
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Learning materials shared by this user
    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    // Challenge participation
    public function challengeParticipations()
    {
        return $this->hasMany(ChallengeParticipant::class, 'user_id');
    }

    // Wallet and points
    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function earnedLearnerBadges()
    {
        return $this->belongsToMany(LearnerBadgeDefinition::class, 'user_learner_badges', 'user_id', 'learner_badge_definition_id')
            ->withPivot('earned_at')
            ->withTimestamps()
            ->orderByPivot('earned_at', 'desc');
    }
}
