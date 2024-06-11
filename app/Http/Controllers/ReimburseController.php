<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Reimburse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;

use function PHPUnit\Framework\isEmpty;

class ReimburseController extends Controller
{
    public function getAllReimburseByUser($userId)
    {
        $reimburseList = Reimburse::where('id_user', $userId)
            ->select(
                'id_reimburse',
                'id_user',
                'tanggal_reimburse',
                'keterangan',
                'status_reimburse',
                'id_admin',
                'amount'
            )
            ->get()
            ->map(function ($reimburse) {
                $reimburse['lampiran'] = '/reimburse/lampiran/' . $reimburse['id_reimburse'];

                if ($reimburse['id_admin'] != null) {
                    $user = User::find($reimburse['id_admin']);
                    $reimburse['nama_admin'] = $user->nama_user;
                } else {
                    $reimburse['nama_admin'] = '-';
                }
                return $reimburse;
            });

        return response()->json($reimburseList, 200);
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

    // public function storeLampiranReimburseById(Request $request)
    // {
    //     error_log($request->image);
    //     error_log($request->id_reimburse);

    //     try {
    //         $this->validate($request, [
    //             'image' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max:5120',
    //         ]);

    //         $image = $request->file('image');

    //         // Menggunakan Intervention Image untuk memanipulasi gambar
    //         $img = Image::make($image->getRealPath());

    //         // Menyesuaikan ukuran gambar ke ukuran tetap
    //         $img->fit(800, 800);

    //         // Mengubah gambar menjadi data biner
    //         $imageData = $img->encode();

    //         DB::table('reimburse')
    //             ->where('id_reimburse', $request->id_reimburse)
    //             ->update(['lampiran' => $imageData]);

    //         return response("Berhasil", Response::HTTP_CREATED);
    //     } catch (Exception $e) {
    //         // Tangani pengecualian jika berkas tidak sesuai format
    //         return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }

    public function storeLampiranReimburseById(Request $request)
    {
        error_log($request->image);
        try {
            $this->validate($request, [
                'image' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max:10000',
            ]);

            $image = $request->file('image');
            $img = new \Imagick($image->getRealPath());
    
            // Menyesuaikan ukuran gambar ke ukuran tetap
            $img->cropThumbnailImage(250, 250);
    
            // Mengambil data gambar dalam bentuk BLOB
            $imageData = $img->getImageBlob();
    
            error_log($imageData);

            DB::table('reimburse')
                ->where('id_reimburse', $request->id_reimburse)
                ->update(['lampiran' => $imageData]);

            return response("Berhasil", Response::HTTP_CREATED);
        } catch (Exception $e) {
            // Tangani pengecualian jika berkas tidak sesuai format
            return response("Gagal: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeReimburseByUser(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'tanggal_reimburse' => 'required',
            'keterangan' => 'required',
            'amount' => 'required'
        ]);

        $reimburse = new Reimburse();

        $reimburse->id_user = $request->input('id_user');
        $reimburse->tanggal_reimburse = $request->input('tanggal_reimburse');
        $reimburse->keterangan = $request->input('keterangan');
        $reimburse->status_reimburse = "PENDING";
        $reimburse->amount = $request->input('amount');

        $reimburse->save();

        return response()->json($reimburse, 201);
    }
}
