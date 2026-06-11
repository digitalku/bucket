<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
        'totp_secret',
        'totp_enabled',
    ];

    protected $hidden = [
        'password',
        'totp_secret',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'totp_enabled' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
