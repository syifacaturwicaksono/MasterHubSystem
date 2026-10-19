<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apo extends Model
{
    use HasFactory;

    protected $table = 'apo';

    protected $fillable = [
        'jenis_monitoring',
        'kode_upload',
        'case_id',
        'case_id_manual',
        'company_name',
        'received_date',
        'warranty_status',
        'serial_no',
        'product_tower',
        'product_no',
        'product_name',
        'case_type',
        'case_status',
        'problem_desc',
        'repair_action',
        'customer_company',
        'customer_name',
        'pic_name',
        'city',
        'phone_no',
        'mobile_no',
        'start_repair_date',
        'tat_case',
        'finish_date',
        'closed_date',
        'labor',
        'part_no',        // [DITAMBAHKAN]
        'part_name',      // [DITAMBAHKAN]
        'part_category',  // [DITAMBAHKAN]
        'keterangan',
    ];

    protected $casts = [
        'received_date'      => 'datetime',
        'start_repair_date'  => 'datetime',
        'finish_date'        => 'datetime',
        'closed_date'        => 'datetime',
        'tat_case'           => 'integer',
        'labor'              => 'decimal:2',
    ];
}