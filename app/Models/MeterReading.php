<?php

namespace App\Models;

use App\Models\User;
use App\Models\Bills;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $guarded = [];
    protected $casts = [
        'reading_date' => 'datetime', // If using Laravel >= 8.x
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function getNameAttribute()
    {
        return $this->user->name ?? '—';
    }

    public function getFormattedReadingDateAttribute()
    {
        return $this->reading_date->format('M d, Y');
    }


    public function bills()
    {
        return $this->belongsTo(Bills::class, 'meter_reading_id', 'id');
    }
}
