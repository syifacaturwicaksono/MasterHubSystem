<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMaster extends Model
{
    protected $fillable = [

        'kode_company',
        'company_name',
        'email',
        'kategori'
       

    ];
}