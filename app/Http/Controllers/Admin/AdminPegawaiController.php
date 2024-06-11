<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Intervention\Image\ImageManagerStatic as Image;

class AdminPegawaiController extends Controller
{

    public function getPegawai(Request $request)
    {
        if($request['id_pegawai'] == null){
            // get all pegawai
            $allPegawai = Pegawai::select('*')->get()->map(function ($pegawai){
                $pegawai = collect($pegawai)->except('foto_profil');
                $pegawai['foto_profil'] = "/admin/pegawai/image/" . $pegawai['id_pegawai'];
                return $pegawai;
            });
            return response()->json($allPegawai, 200);
        }
    }


public function getPegawaiById($id)
{
    //$pegawai = Pegawai::find($id);v
    
      $pegawai = Pegawai::where('id_user', $id)->first();
    
    if ($pegawai) {
        $pegawai['foto_profil'] = "/admin/pegawai/image/" . $pegawai['id_pegawai'];
        return response()->json($pegawai, 200);
    } else {
        return response()->json(['message' => 'Pegawai not found'], 404);
    }
}

    public function addPegawai(Request $request){
        try {
            $this->validate($request, [
                'id_user' => 'required|integer',
                'foto_profil' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max:10000',
                'alamat_pegawai' => 'required|string|max:250',
                'nohp_pegawai' => 'required|string|max:14',
                'nip' => 'required|string|max:20',
                'id_divisi' => 'required|integer'
            ]);

            $image = $request->file('foto_profil');
            $img = Image::make($image->getRealPath());

            // Menyesuaikan ukuran gambar ke ukuran tetap
            $img->fit(250, 250);
            $imageData = $img->encode();

            $newPegawai = new Pegawai();
            $newPegawai['id_user'] = $request['id_user'];
            $newPegawai['alamat_pegawai'] = $request['alamat_pegawai'];
            $newPegawai['nohp_pegawai'] = $request['nohp_pegawai'];
            $newPegawai['nip'] = $request['nip'];
            $newPegawai['id_divisi'] = $request['id_divisi'];
            $newPegawai['foto_profil'] = $imageData;
            $newPegawai->save();
            $newPegawai['foto_profil'] = "/admin/pegawai/image/". $newPegawai['id_pegawai'];

            return response($newPegawai, Response::HTTP_CREATED);
        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePegawai(Request $request){
        try {
            $this->validate($request, [
                'id_pegawai' => 'required|integer',
                'foto_profil' => 'nullable|file|mimes:jpg,png,jpeg,gif,svg|max:10000',
                'alamat_pegawai' => 'nullable|string|max:250',
                'nohp_pegawai' => 'nullable|string|max:14',
                'nip' => 'nullable|string|max:20',
                'id_divisi' => 'nullable|integer'
            ]);

            $pegawai = Pegawai::find($request['id_pegawai']);
            if(!$pegawai){
                return response()->json(['message' => 'Data not found'], 404);
            }
            $pegawai['id_pegawai'] = $request['id_pegawai'];
            $pegawai['alamat_pegawai'] = $request['alamat_pegawai'];
            $pegawai['nohp_pegawai'] = $request['nohp_pegawai'];
            $pegawai['nip'] = $request['nip'];
            $pegawai['id_divisi'] = $request['id_divisi'];

            $image = $request->file('foto_profil');
            $img = Image::make($image->getRealPath());

            // Menyesuaikan ukuran gambar ke ukuran tetap
            $img->fit(250, 250);
            $imageData = $img->encode();

            $pegawai['foto_profil'] = $imageData;

            $pegawai->save();

            $pegawai['foto_profil'] = '/admin/pegawai/image/'. $pegawai['id_pegawai'];

            return response()->json($pegawai, 200);
        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function removePegawai(Request $request){
        try {
            $this->validate($request, [
                'id_pegawai' => 'required|integer'
            ]);

            $pegawai = Pegawai::find($request['id_pegawai']);
            if(!$pegawai){
                return response()->json(['message' => 'Data not found'], 404);
            }

            $pegawai->delete();

            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (Exception $e) {
            //throw $th;`
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    public function getPhotoByPegawaiId($pegawaiId)
    {
        $pegawai = Pegawai::find($pegawaiId);

        if ($pegawai) {
            $fotoBlob = $pegawai->foto_profil;

            if ($fotoBlob) {
                header('Content-Type: image/jpeg');

                echo $fotoBlob;
            } else {
                $defaultImage = public_path('image/no_pict.png');
                if (file_exists($defaultImage)) {
                    header('Content-Type: image/jpeg');
                    readfile($defaultImage);
                }
            }
        }
    }
}
