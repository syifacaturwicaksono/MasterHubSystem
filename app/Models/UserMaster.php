<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMaster extends Model
{
    protected $fillable = [

        'name',
        'email',
        'role',
        'password',
        'smtp_password',
        'cc'

    ];
}