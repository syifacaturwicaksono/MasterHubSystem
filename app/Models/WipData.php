<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WipData extends Model
{
    use HasFactory;

    protected $table = 'wip_datas';

    protected $fillable = [
        'kode_upload',
        'jenis_monitoring',
        'case_id_manual',
        'company_name',
        'finish_date',
        'case_status',
        'hp_part_no',
        'so_no',
        'awb_no_part_return',
        'part_in_date',
        'aging',
    ];
}