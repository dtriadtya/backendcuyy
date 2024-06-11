<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penggajian;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminPenggajianController extends Controller
{
    public function getPenggajian(Request $request){
        try {
            $this->validate($request, [
                'id_penggajian' => 'integer'
            ]);

            if($request['id_penggajian'] == null){
                $listPenggajian = Penggajian::all();
                return response()->json($listPenggajian, 200);
            }else{
                $penggajian = Penggajian::find($request['id_penggajian']);
                return response()->json($penggajian, 200);
            }

        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function addPenggajian(Request $request){
        try {
            $this->validate($request, [
                'id_user' => 'required|integer',
                'gaji_pokok' => 'required|integer',
                'transportasi' => 'required|string',
                'status_gaji' => 'required|string',
                'keterangan' => 'required|string',
                'bonus' => 'required|integer',
                'tanggal' => 'required|date_format:Y-m-d',
                'id_admin' => 'required|integer',
            ]);

            $newPenggajian = new Penggajian();
            $newPenggajian['id_user'] = $request['id_user'];
            $newPenggajian['gaji_pokok'] = $request['gaji_pokok'];
            $newPenggajian['transportasi'] = $request['transportasi'];
            $newPenggajian['status_gaji'] = $request['status_gaji'];
            $newPenggajian['keterangan'] = $request['keterangan'];
            $newPenggajian['bonus'] = $request['bonus'];
            $newPenggajian['tanggal'] = $request['tanggal'];
            $newPenggajian['id_admin'] = $request['id_admin'];

            $newPenggajian->save();

            return response()->json($newPenggajian, 200);
        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function removePenggajian(Request $request){
        try {
            $this->validate($request, [
                'id_penggajian' => 'required|integer',
            ]);

            $penggajian = Penggajian::find($request['id_penggajian']);

            if(!$penggajian){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $penggajian->delete();

            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}
