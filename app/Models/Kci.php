<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kci extends Model
{
    use HasFactory;

    protected $table = 'kci';

   protected $fillable = [
    'jenis_monitoring',
    'kode_upload',
    'case_id',
    'count',
    'company_name',
    'aging',
    'customer_name',
    'customer_company_hierarchy',
    'case_status',
    'ce_name',
    'company_city',
];

    }