<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id_pegawai';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'foto_profil',
        'alamat_pegawai',
        'nohp_pegawai',
        'nip',
        'id_divisi',
    ];
    

}
