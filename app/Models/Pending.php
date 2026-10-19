<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pending extends Model
{
    use HasFactory;
    protected $fillable = [

    'jenis',
    'batch',

    'Case ID',
'Received date',
'Start repair date',
'Company Name',
'Aging',
'Case status',
'CE name',
'Company city',
'Part name',
'HP part no.',
'Part request date',
'SO no.',
'ETA date',
'Part in date',
'Product no.',
'Product name'

];
}
