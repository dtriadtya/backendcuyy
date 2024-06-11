<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    protected $table = 'penggajian';
    protected $primaryKey = 'id_penggajian';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'gaji_pokok',
        'transportasi',
        'status_gaji',
        'id_admin',
        'keterangan',
        'bonus',
        'tanggal'
    ];
}
