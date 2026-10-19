<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingCaseTat14d extends Model
{
    use HasFactory;
    protected $table = 'pending_14d';
    protected $fillable = [

        'jenis',
        'kode_upload',

        'company_name',
        'aging',
        'case_id',
        'received_date',
        'start_repair_date',
        'part_request_date',
        'part_order_date',
        'eta_date',
        'part_in_date',
        'part_in_status',
        'so_no',
        'hp_part_no',
        'case_status',
        'product_tower',
        'product_type',
        'ce_name'
    ];
}
