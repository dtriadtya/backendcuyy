<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;

class PerusahaanController extends Controller
{
    public function index(){
        $perusahaan = Perusahaan::all();
        return response()->json($perusahaan);
    }

}
