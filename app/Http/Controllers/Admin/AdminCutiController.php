<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Validation\Rule;

class AdminCutiController extends Controller {
    public function getCuti(Request $request){
        try {
            $this->validate($request,[
                'id_cuti' => 'integer'
            ]);

            if($request['id_cuti'] == null){
                $listCuti = Cuti::all();

                return response()->json($listCuti, 200);
            }else{
                $cuti = Cuti::find($request['id_cuti']);
                if(!$cuti){
                    return response()->json(['message' => 'Data not found'], 404);
                }

                return response()->json($cuti, 200);
            }
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function addCuti(Request $request){
        try {
            $this->validate($request,[
                'id_user' => 'integer|required',
                'tgl_mulai' => 'date|required',
                'tgl_akhir' => 'date|required',
                'jenis_cuti' => [
                    'required',
                    Rule::in(['CASUAL LEAVE', 'EMERGENCY LEAVE']),
                ],
                'keterangan' => 'string|required'
            ]);
            $year = Carbon::now()->year;

            $latestCuti = Cuti::where("id_user", $request['id_user'])
                            ->whereYear('tgl_mulai', $year)
                            ->latest('tgl_mulai')
                            ->orderByDesc('tgl_mulai')
                            ->orderByDesc('id_cuti')
                            ->first();

            if($latestCuti != null){
                if($latestCuti['status_cuti'] == "PENDING"){
                    return response()->json(["message" => "Tunggu hingga persetujuan Cuti sebelumnya"],400);
                }
                if($latestCuti['sisa_cuti'] == 0){
                    return response()->json(["message" => "Anda sudah mencapai batas Cuti Anda."],400);
                }
            }

            $newCuti = new Cuti();
            $newCuti['id_user'] = $request['id_user'];
            $newCuti['tgl_mulai'] = $request['tgl_mulai'];
            $newCuti['tgl_akhir'] = $request['tgl_akhir'];
            $newCuti['jenis_cuti'] = $request['jenis_cuti'];
            $newCuti['keterangan'] = $request['keterangan'];
            $newCuti['status_cuti'] = "PENDING";
            $newCuti['id_admin'] = $request['id_admin'];

            if($latestCuti == null){
                $newCuti['sisa_cuti'] = 10;
            }else{
                $tanggal_mulai = new DateTime($request['tgl_mulai']);
                $tanggal_akhir = new DateTime($request['tgl_akhir']);
                $jumlahRequestCuti = $tanggal_mulai->diff($tanggal_akhir)->days + 1;
                if($latestCuti['status_cuti'] == 'DISETUJUI'){
                    $sisa = $latestCuti['sisa_cuti'] - $jumlahRequestCuti;
                }else{
                    $sisa = $latestCuti['sisa_cuti'];
                }
                if($sisa < 0){
                    return response()->json(["message" => "Anda melebihi batas Cuti Anda."],400);
                }
                $newCuti['sisa_cuti'] = $latestCuti['sisa_cuti'];
            }

            $newCuti->save();

            return response()->json($newCuti, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateCuti(Request $request){
        try {
            $this->validate($request,[
                'status_cuti' => [
                    'required',
                    Rule::in(['DISETUJUI', 'PENDING', 'DITOLAK']),
                ],
                'id_admin' => 'integer|required',
                'id_cuti' => 'integer|required'
            ]);
            $cuti = Cuti::find($request['id_cuti']);
            if(!$cuti){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $cuti['status_cuti'] = $request['status_cuti'];
            $cuti['id_admin'] = $request['id_admin'];

            if($request['status_cuti'] == 'DISETUJUI'){
                $tanggal_mulai = new DateTime($cuti['tgl_mulai']);
                $tanggal_akhir = new DateTime($cuti['tgl_akhir']);
                $jumlahRequestCuti = $tanggal_mulai->diff($tanggal_akhir)->days + 1;
                $cuti['sisa_cuti'] = $cuti['sisa_cuti'] - $jumlahRequestCuti;
            }

            $cuti->save();

            return response()->json($cuti, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

// $tanggal_mulai = new DateTime($request['tgl_mulai']);
//                 $tanggal_akhir = new DateTime($request['tgl_akhir']);
//                 $jumlahRequestCuti = $tanggal_mulai->diff($tanggal_akhir)->days + 1;
//                 if($latestCuti['status_cuti'] == 'DISETUJUI'){
//                     $sisa = $latestCuti['sisa_cuti'] - $jumlahRequestCuti;
//                 }else{
//                     $sisa = $latestCuti['sisa_cuti'];
//                 }
//                 if($sisa < 0){
//                     return response()->json(["message" => "Anda melebihi batas Cuti Anda."],400);
//                 }
//                 $newCuti['sisa_cuti'] = $sisa;
