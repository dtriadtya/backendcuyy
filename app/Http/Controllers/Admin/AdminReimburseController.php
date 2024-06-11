<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Reimburse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManagerStatic as Image;

use function PHPUnit\Framework\isEmpty;

class AdminReimburseController extends Controller
{
    public function getReimburse(Request $request){
        try {
            $this->validate($request,[
                'id_reimburse' => 'integer'
            ]);

            if($request['id_reimburse'] == null){
                $listReimburse = Reimburse::select('*')->get()->map(function ($reimburse){
                    $reimburse = collect($reimburse)->except('lampiran');
                    $reimburse['lampiran'] = "/admin/reimburse/image/" . $reimburse['id_reimburse'];
                    return $reimburse;
                });

                return response()->json($listReimburse, 200);
            }else{
                $reimburse = Reimburse::find($request['id_reimburse']);
                $reimburse['lampiran'] = "/admin/reimburse/image/" . $reimburse['id_reimburse'];

                return response($reimburse, 200);
            }
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function addReimburse(Request $request){
        try {
            $this->validate($request,[
                'id_user' => 'integer|required',
                'tanggal_reimburse' => 'required|date_format:Y-m-d H:i:s',
                'keterangan' => 'required|string',
                'lampiran' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max:5120',
                'amount' => 'required|string'
            ]);

            $reimburse = new Reimburse();
            $reimburse['id_user'] = $request['id_user'];
            $reimburse['tanggal_reimburse'] = $request['tanggal_reimburse'];
            $reimburse['keterangan'] = $request['keterangan'];
            $reimburse['amount'] = $request['amount'];
            $reimburse['id_admin'] = null;
            $reimburse['status_reimburse'] = "PENDING";

            $image = $request->file('lampiran');
            $img = Image::make($image->getRealPath());

            $imageData = $img->encode();
            $reimburse['lampiran'] = $imageData;

            $reimburse->save();
            $reimburse['lampiran'] = "/admin/reimburse/image/" . $reimburse['id_reimburse'];
            return response()->json($reimburse, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateReimburse(Request $request){
        try {
            $this->validate($request,[
                'id_reimburse' => 'integer|required',
                'id_admin' => 'integer|required',
                'status_reimburse' => [
                    'required',
                    Rule::in(['DISETUJUI', 'DITOLAK']),
                ]
            ]);
            $reimburse = Reimburse::find($request['id_reimburse']);
            if(!$reimburse){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $reimburse['status_reimburse'] = $request['status_reimburse'];
            $reimburse['id_admin'] = $request['id_admin'];

            $reimburse->save();

            $reimburse['lampiran'] = "/admin/reimburse/image/" . $reimburse['id_reimburse'];
            return response()->json($reimburse, 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function removeReimburse(Request $request){
        try {
            $this->validate($request,[
                'id_reimburse' => 'integer|required'
            ]);
            $reimburse = Reimburse::find($request['id_reimburse']);
            if(!$reimburse){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $reimburse->delete();

            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (Exception $e) {
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function showLampiranById($id_reimburse)
    {
        $reimburse = Reimburse::find($id_reimburse);
        if ($reimburse) {
            $fotoBlob = $reimburse->lampiran;

            if ($fotoBlob) {
                header('Content-Type: image/jpeg');

                echo $fotoBlob;
            } else {
                $defaultImage = public_path('image/broken.png');
                if (file_exists($defaultImage)) {
                    header('Content-Type: image/jpeg');
                    readfile($defaultImage);
                }
            }
        }
    }
}
