<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Pegawai;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;

use function PHPUnit\Framework\isEmpty;

class PegawaiController extends Controller
{

   public function getPegawai($userId)
{
    $pegawai = Pegawai::where('id_user', $userId)->first();
    error_log($pegawai['alamat_pegawai']);

    if ($pegawai) {
        if (!is_null($pegawai['id_divisi'])) {
            $divisi = Divisi::find($pegawai['id_divisi']);
            if ($divisi) {
                $pegawai['divisi'] = $divisi['nama_divisi'];
            }
        } else {
            $pegawai['divisi'] = '-';
        }
        $pegawai['foto_profil'] = '/pegawai/image/' . $pegawai['id_pegawai'];
        return $pegawai;
    } else {
        return null;
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

    public function imageStore(Request $request)
{
    try {
        $this->validate($request, [
            'id_pegawai' => 'required|integer',
            'image' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max:10000',
        ]);

        $image = $request->file('image');
        $img = new \Imagick($image->getRealPath());

        // Menyesuaikan ukuran gambar ke ukuran tetap
        $img->cropThumbnailImage(250, 250);

        // Mengambil data gambar dalam bentuk BLOB
        $imageData = $img->getImageBlob();

        // Simpan BLOB ke kolom 'foto_profil' di tabel 'pegawai' dengan ID yang ditentukan
        DB::table('pegawai')
            ->where('id_pegawai', $request['id_pegawai'])
            ->update(['foto_profil' => $imageData]);

        return response("Berhasil", Response::HTTP_CREATED);
    } catch (Exception $e) {
        error_log("testtt2".$e->getMessage());
        // Tangani pengecualian jika berkas tidak sesuai format
        return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}



   public function updateProfile(Request $request)
{
    try {
        // Validasi input dari request
        $this->validate($request, [
            'id_pegawai' => 'required|integer',
            'foto_profil' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:10000',
            'alamat_pegawai' => 'nullable|string|max:250',
            'nohp_pegawai' => 'nullable|string|max:14',
            'nip' => 'nullable|string|max:20',
            'id_divisi' => 'nullable|integer',
            'nama_divisi' => 'nullable|string',
            'nama_user' => 'nullable|string|max:255', // Tambahkan validasi untuk nama_user
            'email_user' => 'nullable|email|max:255' // Tambahkan validasi untuk email_user
        ]);

        // Mencari pegawai berdasarkan id
        $pegawai = Pegawai::findOrFail($request->id_pegawai);

        // Cek apakah ada file gambar yang diunggah
        if ($request->hasFile('foto_profil')) {
            $image = $request->file('foto_profil');
            $img = Image::make($image->getRealPath());
            $img->fit(250, 250);
            $imageData = $img->encode();

            // Simpan gambar ke dalam kolom foto_profil
            $pegawai->foto_profil = $imageData;
        }

        // Assign data pegawai dari request jika tidak null
        if (!is_null($request->alamat_pegawai)) {
            $pegawai->alamat_pegawai = $request->alamat_pegawai;
        }

        if (!is_null($request->nohp_pegawai)) {
            $pegawai->nohp_pegawai = $request->nohp_pegawai;
        }

        if (!is_null($request->nip)) {
            $pegawai->nip = $request->nip;
        }

        if (!is_null($request->id_divisi)) {
            $pegawai->id_divisi = $request->id_divisi;
        }
        
        if (!is_null($request->nama_divisi)) {
            $pegawai->nama_divisi = $request->nama_divisi;
        }

        $pegawai->save();

        // Format ulang URL gambar profil
        $pegawai->foto_profil = '/admin/pegawai/image/'. $pegawai->id_pegawai;

        // Ubah nama_user pada model User jika ada
        if (!is_null($request->nama_user)) {
            $user = User::findOrFail($pegawai->id_user);
            $user->nama_user = $request->nama_user;
            $user->save();
        }

        // Ubah email_user pada model User jika ada
        if (!is_null($request->email_user)) {
            $user = User::findOrFail($pegawai->id_user);
            $user->email_user = $request->email_user;
            $user->save();
        }

        return response()->json($pegawai, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}




}
