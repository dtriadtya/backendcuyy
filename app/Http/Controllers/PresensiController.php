<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class PresensiController extends Controller
{
    public function index(){
        $presensi = Presensi::all();
        return response()->json($presensi);
    }

    public function byUser($userId){
        $presensi = Presensi::where('id_user', $userId)->get();

        return response()->json($presensi);
    }

    public function getPresensiToday($userId){
        $today = Carbon::today();
        $presensi = Presensi::where('id_user',$userId)
        ->whereDate('check_in', $today) 
        ->get();
        if($presensi->isEmpty()){
            $presensi = null;
        }else{
            $presensi = $presensi[0];
        }
        
        return response()->json($presensi);
    }

    public function presensi(Request $request){
        $today = Carbon::today();
        $data = $request->validate([
            'id_user' => 'required|exists:user,id_user', // Memastikan id_user ada di tabel users
            'check_in' => 'required|date',
            'check_out' => 'date',
            'maps_checkin' => 'required|max:100',
            'maps_checkout' => 'max:100',
        ]);


        $presensi = Presensi::where('id_user', $data['id_user'])
        ->whereDate('check_in', $today) 
        ->get();
        if($presensi->isEmpty()){
            $data['check_out'] = null;
            $data['maps_checkout'] = null;
            $presensi = Presensi::create($data);
        }else{
            $pUpdate = Presensi::find($presensi[0]['id_presensi']);
            error_log("checkout");
            if($pUpdate['check_out'] == null && $pUpdate['maps_checkout'] == null){
                $pUpdate->update([
                    'check_out' => $data['check_out'],
                    'maps_checkout' => $data['maps_checkout']
                ]);
                $presensi =$pUpdate;
                error_log("checkout". $pUpdate);
            }
        }

        return response()->json($presensi, 201);
    }
}
