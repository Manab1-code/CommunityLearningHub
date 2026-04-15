<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'learner_id',
        'teacher_id',
        'skill_name',
        'skill_level',
        'message',
        'status',
        'proposed_date',
        'accepted_date',
        'rejection_reason',
        'reschedule_reason',
        'learner_rating',
    ];

    protected $casts = [
        'proposed_date' => 'datetime',
        'accepted_date' => 'datetime',
    ];

    // Relationships
    public function learner()
    {
        return $this->belongsTo(User::class, 'learner_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeForLearner($query, $learnerId)
    {
        return $query->where('learner_id', $learnerId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}
