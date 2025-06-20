<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Model;

class Bills extends Model
{
    protected $guarded = [];

    public function getMeterAttribute()
    {
        return $this->user->meter_number ?? '—';
    }

    public function getAddressAttribute()
    {
        return $this->user->address ?? '—';
    }

    public function getContactNumberAttribute()
    {
        return $this->user->contact_number ?? '—';
    }


    public function getNameAttribute()
    {
        return $this->user->name ?? '—';
    }

    public function getAmountAttribute()
    {
        return isset($this->meterReading->amount) ? '₱' . number_format($this->meterReading->amount, 2) : '—';
    }

    public function getPreviousReadingAttribute()
    {
        return $this->meterReading->previous_reading ?? '—';
    }
    public function getFormattedAmountDueAttribute()
    {
        return '₱' . number_format($this->amount_due, 2);
    }


    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class, 'meter_reading_id', 'id');
    }
    public function getCurrentReadingAttribute()
    {
        return $this->meterReading->current_reading ?? '-';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payments()
    {
        return $this->belongsTo(Payment::class, 'id', 'bill_id');
    }
}
