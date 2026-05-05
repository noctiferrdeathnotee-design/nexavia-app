<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'status',
        'last_login',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'admin_id');
    }

    public function trackingHistories()
    {
        return $this->hasMany(TrackingHistory::class, 'admin_id');
    }
}
