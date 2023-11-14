<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MapperModel;
use DB;

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
        $data_cid=DB::table('misterkong_mapper.m_other_company_mapper')->where(['id'=>$request->id])->first();
    
    	if(!empty($data_cid)){
            if (!file_exists("../../../public_html/back_end_mp/" . $data_cid->company_id . "_config/images/mapper")) {
                mkdir("../../../public_html/back_end_mp/" . $data_cid->company_id . "_config/images/mapper", 0777, true);
            }

            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $file->move('../../../public_html/back_end_mp/'.$data_cid->company_id."_config/images/mapper",$filename);

            $data_save=[
                'nominal'=>$request->nominal,
                'awal'=>$request->awal,
                'periode'=>$request->periode,
                'bukti_bayar'=>$file->getClientOriginalName(),
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
        }else{
        	$response=[
                'status'=>0,
                'error' =>500,
                'message'=>'wrong company id',
                'data'=>[]
            ];
            return response()->json($response,500);
        }   
    }
    public function cekMapperFeature($company_id)
    {
        if (!file_exists("../../../public_html/back_end_mp/" . $company_id . "_config/POST/mapper/update__mapper.json")) {
            $response=[
                'status'=>0,
                'error' =>200,
                'message'=>'tidak menggunakan fitur mapper',
                'data'=>[]
            ];
        }else{
            $data_json = json_decode(file_get_contents("../../../public_html/back_end_mp/" . $company_id . "_config/POST/mapper/update__mapper.json"), true);
            $response=[
                'status'=>1,
                'error' =>200,
                'message'=>'terdapat update pada fitur mapper',
                'data'=>$data_json
            ];
        }
        return response()->json($response,200);
    }
}
