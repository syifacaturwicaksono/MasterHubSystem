<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MprData extends Model
{
    protected $table = 'mpr_datas';

    protected $fillable = [
        'jenis_monitoring',
        'kode_upload',
        'kode_company',
        'company_name',
        'case_ipg',
        'case_psg',
        'total_case',
        'tat',
        'tat_wo_bo',
        'ola',
        'rrp',
        'rrp_percent',
        'wip',
        'unupdate_wip',
        'latest_aging',
        'catatan',
    ];
}