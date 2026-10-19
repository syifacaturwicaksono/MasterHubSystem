<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawData extends Model
{
    protected $table = 'raw_data';

   protected $fillable = [
    'case_id',
    'company_name',
    'company_city',
    'case_status',
    'received_date',
    'part_in_date',
    'product_no',
    'product_name',
    'start_repair_date',
    'tat_case'
];
}