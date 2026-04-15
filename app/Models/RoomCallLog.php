<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomCallLog extends Model
{
    protected $fillable = [
        'room_id',
        'started_by',
        'started_at',
        'ended_at',
        'duration_seconds',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
