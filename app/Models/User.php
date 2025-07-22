<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Bills;
use App\Models\group;
use App\Models\Category;
use App\Models\MeterReading;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'address',
        'contact_number',
        'meter_number',
        'group_id',
        'role',
        'account_id',
        'category_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeStaffs($query)
    {
        return $query->whereIn('role', ['cashier', 'plumber']);
    }

    public function meter()
    {
        return $this->hasMany(MeterReading::class, 'user_id', 'id');
    }

    public function bills()
    {
        return $this->hasMany(Bills::class, 'user_id', 'id');
    }
    public function latestMeter()
    {
        return $this->hasOne(MeterReading::class, 'user_id', 'id')->latestOfMany('reading_date');
    }

    public function group()
    {
        return $this->belongsTo(group::class, 'group_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function getTotalUnpaidBillAttribute()
    {
        return $this->bills->sum(fn ($bill) => $bill->amount_due + $bill->penalty);
    }
}
