<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class CutiController extends Controller {
  public function findByUser($userId)
  {
    try {
    $cuti = Cuti::addSelect(['admin' => User::select('nama_user')
      ->whereColumn('id_user', 'cuti.id_admin')
      ->limit(1)]
      )->where('id_user', $userId)->get();

    foreach($cuti as $data){
      if(!$data['admin']){
        $data['admin'] = "-";
      }
    };

    return response()->json($cuti, 201);
    } catch(Exception $ex){
      return response()->json(["message" => $ex->getMessage()], 404);
    }
  }

  public function getDetailCuti($cutiId)
  {
    $cuti = Cuti::find($cutiId);
    if(Cuti::find($cutiId)->admin == null){
      $cuti['admin'] = "-";
    }else{
      $cuti['admin'] = Cuti::find($cutiId)->admin->nama_user;
    }
    return response()->json($cuti, 201);
  }

  public function getAddData($userId)
  {
    $cuti = Cuti::where("id_user", $userId)->first();
    $user = User::find($userId);
    $latestCuti = Cuti::where("id_user", $userId)->where("status_cuti", "DISETUJUI")->latest('tgl_mulai')->first();

    if ($cuti == null || $latestCuti == null) {
      return response()->json([
        'sisa_cuti' => 10,
        'nama_user' => $user->nama_user,
        'enabled' => true
      ], 201);
    }

    $sisa_cuti = $latestCuti->sisa_cuti;
    if (Carbon::parse($latestCuti->tgl_mulai)->year != Carbon::now()->year) {
      $sisa_cuti = 10;
    }

    $sisa = Cuti::where("id_user", $userId)->where("status_cuti", "PENDING")->first();
    if($sisa != null){
      $enabled = false;
    } else {
      $enabled = true;
    }

    return response()->json([
      'sisa_cuti' => (int)$sisa_cuti,
      'nama_user' => $user->nama_user,
      'enabled' => $enabled
    ], 201);
  }

  public function addCuti(Request $request)
  {
    $validate = $request->validate([
      'id_user' => 'required|integer',
      'tgl_mulai'=> 'required|date',
      'tgl_akhir' => 'required|date',
      'jenis_cuti' => 'required',
      'keterangan' => 'required',
    ]);

    try {
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
      $newCuti['message'] = "Berhasil menambahkan cuti";
      return response()->json($newCuti, 201);
    }catch(Exception $ex){
      return response()->json(["message" => $ex->getMessage()], 404);
    }
  }

  public function approveCuti($cutiId)
  {
    try {
      $cuti = Cuti::find($cutiId);

      if ($cuti == null) {
        return response()->json(["message" => "Cuti tidak ditemukan"], 404);
      }

      if ($cuti->status_cuti !== "PENDING") {
        return response()->json(["message" => "Cuti sudah diapprove atau ditolak"], 400);
      }

      $cuti->status_cuti = "DISETUJUI";
      $cuti->save();

      return response()->json(["message" => "Cuti berhasil diapprove"], 200);
    } catch(Exception $ex){
      return response()->json(["message" => $ex->getMessage()], 404);
    }
  }


  public function rejectCuti($cutiId)
  {
    try {
      $cuti = Cuti::find($cutiId);

      if ($cuti == null) {
        return response()->json(["message" => "Cuti tidak ditemukan"], 404);
      }

      if ($cuti->status_cuti !== "PENDING") {
        return response()->json(["message" => "Cuti sudah diapprove atau ditolak"], 400);
      }

      $cuti->status_cuti = "DITOLAK";
      $cuti->save();

      return response()->json(["message" => "Cuti berhasil ditolak"], 200);
    } catch(Exception $ex){
      return response()->json(["message" => $ex->getMessage()], 404);
    }
}}
