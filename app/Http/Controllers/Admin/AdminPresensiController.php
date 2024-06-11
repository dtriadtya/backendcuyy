<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class AdminPresensiController extends Controller
{
    public function getPresensi(Request $request){
        try {
            $this->validate($request, [
                'id_presensi' => 'integer',
                'id_user' => 'integer'
            ]);

            if($request['id_presensi'] == null && $request['id_user'] == null){
                $listPresensi = Presensi::all();

                return response()->json($listPresensi, 200);
            }
            if($request['id_user'] != null){
                $listPresensi = Presensi::where('id_user', $request['id_user'])->get();
                if(!$listPresensi){
                    return response()->json(['message' => 'Data not found'], 404);
                }
                return response()->json($listPresensi, 200);
            }
            if($request['id_presensi'] != null){
                $presensi = Presensi::find($request['id_presensi']);
                if(!$presensi){
                    return response()->json(['message' => 'Data not found'], 404);
                }
                return response()->json($presensi, 200);
            }

        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function addPresensi(Request $request){
        try {
            $this->validate($request, [
                'id_user' => 'integer|required',
                'check_in' => 'required|date_format:Y-m-d H:i:s',
                'check_out' => 'date_format:Y-m-d H:i:s',
                'maps_checkin' => 'required|string',
                'maps_checkout' => 'string'
            ]);

            $newpresensi = new Presensi();

            $newpresensi['id_user'] = $request['id_user'];
            $newpresensi['check_in'] = $request['check_in'];
            $newpresensi['check_out'] = $request['check_out'];
            $newpresensi['maps_checkin'] = $request['maps_checkin'];
            $newpresensi['maps_checkout'] = $request['maps_checkout'];

            $newpresensi->save();

            return response()->json($newpresensi, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePresensi(Request $request){
        try {
            $this->validate($request, [
                'id_presensi' => 'integer|required',
                'check_in' => 'required|date_format:Y-m-d H:i:s',
                'check_out' => 'required|date_format:Y-m-d H:i:s',
                'maps_checkin' => 'required|string',
                'maps_checkout' => 'required|string'
            ]);

            $presensi = Presensi::find($request['id_presensi']);
            if(!$presensi){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $presensi['check_in'] = $request['check_in'];
            $presensi['check_out'] = $request['check_out'];
            $presensi['maps_checkin'] = $request['maps_checkin'];
            $presensi['maps_checkout'] = $request['maps_checkout'];

            $presensi->save();

            return response()->json($presensi, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
