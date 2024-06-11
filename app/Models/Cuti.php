<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cuti extends Model
{
    protected $table = 'cuti';
    protected $primaryKey = 'id_cuti';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'tgl_mulai',
        'tgl_akhir',
        'jenis_cuti',
        'keterangan',
        'sisa_cuti',
        'status_cuti',
        'id_admin'
    ];

    public function user() : HasOne
    {
        return $this->hasOne(User::class, 'id_user');
    }

    public function admin() : HasOne
    {
        return $this->hasOne(User::class, 'id_user', 'id_admin'); 
    }
}
