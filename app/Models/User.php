<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Unit;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'unit_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User (role unit) belongs to Unit.
     .
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Pengadaan yang dibuat user 
     */
    public function pengadaansCreated()
    {
        return $this->hasMany(Pengadaan::class, 'created_by');
    }
}
