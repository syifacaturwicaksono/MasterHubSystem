<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderInvoice extends Model
{
    use HasFactory;

    protected $table = 'reminder';

   protected $fillable = [
    'jenis_monitoring',
    'kode_upload',
    'kode_company',
    'company_name',
    'partner',
    'periode_closing',
    'srf_erf',
    'invoice_no',
    'case',
    'kekurangan_dokumen',
    'total',
    'jatuh_tempo',
    'sudah_invoice',
    'tgl_terima_dokumen',
    'srf_erf_diterima',
    'incl'
];

    }