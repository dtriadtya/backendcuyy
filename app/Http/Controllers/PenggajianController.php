<?php

namespace App\Http\Controllers;

use App\Models\Penggajian;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\DB;

class PenggajianController extends Controller
{

    public function getAllPayRollByUser($userId){
        $penggajians = DB::table('penggajian as p')
        ->select('p.*', 'u.nama_user as nama_admin')
        ->join('user as u', 'p.id_admin', '=', 'u.id_user')
        ->where('p.id_user', $userId)
        ->orderBy('p.id_penggajian', 'desc')
        ->get();

        return response()->json($penggajians);
    }

}
