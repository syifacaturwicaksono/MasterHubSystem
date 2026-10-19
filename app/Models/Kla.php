<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kla extends Model
{
    use HasFactory;

    protected $table = 'kla';

   protected $fillable = [
    'jenis_monitoring',
    'kode_upload',
    'timestamp',
    'case_id',
    'company_name',
    'teknisi',
    'invoice',
    'upload',
    'transaksi',
    'keterangan',
    'tanggal_transaksi',
    'pembayaran',
];

    }