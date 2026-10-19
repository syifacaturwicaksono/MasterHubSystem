<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PnMaster extends Model
{
    protected $table = 'pn_master';

    protected $fillable = [
        'case_id',
        'pn_code',
        'kategori',
        'part_request',
        'so_no',
        'eta_date',
        'partin_date'
    ];
}