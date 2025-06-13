<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getUserNameAttribute()
    {
        return $this->user->name ?? '—';
    }
}
