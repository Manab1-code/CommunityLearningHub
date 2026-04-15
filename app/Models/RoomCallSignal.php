<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomCallSignal extends Model
{
    protected $fillable = [
        'room_id',
        'sender_id',
        'target_user_id',
        'type',
        'payload',
    ];
}
