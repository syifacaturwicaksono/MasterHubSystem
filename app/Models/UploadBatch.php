<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadBatch extends Model
{
    use HasFactory;

    protected $table = 'upload_batches';

    protected $fillable = [
        'jenis_upload',
        'kode_upload',
        'file_name'
    ];
}