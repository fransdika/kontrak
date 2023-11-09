<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MapperModel;

class MapperController extends Controller
{
    function getDataKontrakMapper(Request $request, $other_cid){
        $data= MapperModel::getKontrakUser($other_cid);
        $response=[
            'status'=>1,
            'error' =>200,
            'message'=>'berhasil',
            // 'jumlah' =>count($data),
            'data'=>$data[0]
        ];
        return response()->json($response,200);
    }
    public function getTarifMapper()
    {
        $data=MapperModel::getTarifMapper();
        $response=[
            'status'=>1,
            'error' =>200,
            'message'=>'berhasil',
            'jumlah' =>count($data),
            'data'=>$data
        ];
        return response()->json($response,200);
    }   
    public function updatePembayaran(Request $request)
    {
        $data_save=[
            'nominal'=>$request->nominal,
            'awal'=>$request->awal,
            'periode'=>$request->periode,
            'bukti_bayar'=>$request->bukti_bayar,
            'tanggal_bayar'=>date('Y-m-d H:i:s'),
            'tarif_mapper_id'=>date('Y-m-d H:i:s'),
        ];
        $condition=[
            'id'=>$request->id
        ];
        $update=MapperModel::updatePembayaran($data_save,$condition);
        if($update){
            $response=[
                'status'=>1,
                'error' =>200,
                'message'=>'berhasil',
                'data'=>[]
            ];
            return response()->json($response,200);
        }else{
            $response=[
                'status'=>0,
                'error' =>500,
                'message'=>'gagal',
                'data'=>[]
            ];
            return response()->json($response,500);
        }
        
        
    }

}
