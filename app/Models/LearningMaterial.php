<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'skill_name',
        'file_path',
        'file_name',
        'url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completions()
    {
        return $this->hasMany(LearningMaterialCompletion::class);
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeNotes($query)
    {
        return $query->where('type', 'notes');
    }

    public function scopeGuides($query)
    {
        return $query->where('type', 'guide');
    }

    public function scopeForSkill($query, string $skillName)
    {
        return $query->where('skill_name', $skillName);
    }

    /** Whether this material uses an external URL instead of uploaded file */
    public function isExternalLink(): bool
    {
        return ! empty($this->url);
    }

    /** Get the URL to access the material (storage link or external URL) */
    public function getAccessUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }
        if ($this->file_path) {
            return asset('storage/'.$this->file_path);
        }

        return null;
    }
}
