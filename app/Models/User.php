<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'user_masters';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'smtp_password',
        'cc'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}