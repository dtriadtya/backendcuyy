<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimburse extends Model
{
    protected $table = 'reimburse';
    protected $primaryKey = 'id_reimburse';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'tanggal_reimburse',
        'keterangan',
        'lampiran',
        'status_reimburse',
        'id_admin',
        'amount'
    ];
}
