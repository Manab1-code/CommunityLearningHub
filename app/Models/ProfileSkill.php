<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',   // teaching | learning
        'name',
        'skill_level', // beginner | intermediate | expert
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
