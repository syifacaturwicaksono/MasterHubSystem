<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_template',
        'jenis_monitoring',
        'kategori',
        'subject',
        'body',
    ];
}
