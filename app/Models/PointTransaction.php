<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'reference_type',
        'reference_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEarns($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeSpends($query)
    {
        return $query->where('amount', '<', 0);
    }
}
