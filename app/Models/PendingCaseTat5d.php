<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingCaseTat5d extends Model
{
    use HasFactory;
    protected $table = 'pending';
    protected $fillable = [

        'jenis',
        'kode_upload',

        'case_id',
        'received_date',
        'start_repair_date',
        'company_name',
        'aging',
        'case_status',
        'ce_name',
        'company_city',
        'part_name',
        'hp_part_no',
        'part_request_date',
        'so_no',
        'eta_date',
        'part_in_date',
        'product_no',
        'product_name'
    ];
}
