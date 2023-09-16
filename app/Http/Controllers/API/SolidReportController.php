<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SolidReportController extends Controller
{
    public function get_info_toko(Request $request)
	{
		$data = DB::select("SELECT * FROM misterkong_$request->comp_id.g_db_config");
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $info_toko[$value->name]=$value->value;
            }
            $response = [
                'status' => 1,
                'error' => 0,
                'message' => 'Data Found',
                'data' => $info_toko
            ];
        } else {
            $response = [
                'status' => 0,
                'error' => 500,
                'message' => 'Data Empty',
                'data' => []
            ];
        }
		return response()->json($response);
	}

    public function get_outlet(Request $request)
    {
        $data = DB::select("SELECT * FROM misterkong_$request->comp_id.g_db_config");
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $info_toko[$value->name]=$value->value;
            }
            $exp_tag = explode(",",$info_toko['profile_tag']);
            $dt = [
                'tags' => $exp_tag,
                'profile' => $info_toko['comp_profile_img'],
                'alamat' => $info_toko['alamat']
            ];
            $response = [
                'status' => 1,
                'error' => 0,
                'message' => 'Data Found',
                'data' => $dt
            ];
        } else {
            $response = [
                'status' => 0,
                'error' => 500,
                'message' => 'Data Empty',
                'data' => []
            ];
        }
		return response()->json($response);
    }

    public function updateTags(Request $request)
    {
            DB::beginTransaction();
            try {
                DB::update("UPDATE misterkong_$request->comp_id.g_db_config SET `value`='$request->tags' WHERE `name` = 'profile_tag'");
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => 0,
                    'message' => 'Updated Data',
                    'data' => []
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'Failed Update Data',
                    'data' => []
                ]);
            }
    }

    public function jamOperasional(Request $request)
    {
		$jam_kerja = DB::select("SELECT * FROM misterkong_$request->comp_id.m_jam_buka_toko");
		foreach ($jam_kerja as $key => $value) {
			$schedule[$value->hari]['status']=$value->status;
			$schedule[$value->hari]['keterangan']=$value->keterangan;
			$schedule[$value->hari]['buka']=explode(',', $value->buka);
			$schedule[$value->hari]['tutup']=explode(',', $value->tutup);
		}
		$sts_toko = DB::select("SELECT `value` FROM misterkong_$request->comp_id.g_db_config WHERE `name`='status_toko'")[0];
        // $jenis_jam_kerja = DB::select("SELECT kd_shift, nama FROM misterkong_$request->comp_id.m_jam_kerja");
        // foreach ($jenis_jam_kerja as $key => $value) {
            // $dt_jam_kerja = [
            //     "kd_shift" => $value->kd_shift,
            //     "nama" => $value->nama
            // ];
        // }
		$data['status_toko']= $sts_toko->value;
		$data['company_id']=$request->comp_id;
		$data['data_schedule']=(object)$schedule;
        return response()->json([
            'status' => 1,
            'error' => 0,
            'message' => 'Data Found',
            'data' => $data
        ]);
    }

    public function changeStoreStatus(Request $request)
    {
        if ($request->status == -1) {
            $status = 0;
        } else {
            $status = 1;
        }
        DB::beginTransaction();
        try {
            DB::update("UPDATE misterkong_$request->comp_id.g_db_config SET `value`=$status WHERE `name` = 'status_toko'");
            DB::update("UPDATE m_jam_buka_toko SET manual_status WHERE keterangan=DAYOFWEEK(NOW()) AND company_id=(SELECT id FROM m_user_company WHERE company_id=$request->comp_id)");
            DB::commit();
            return response()->json([
                'status' => 1,
                'error' => 0,
                'message' => 'Updated Data',
                'status_toko' => $status,
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Failed Update Data',
                'status_toko' => '',
                'data' => []
            ]);
        }
    }

    public function getBukaTutupToko(Request $request)
    {
        $data = DB::select("SELECT * FROM misterkong_$request->comp_id.m_jam_buka_toko WHERE hari='$request->hari'")[0];
        if (!empty($data)) {
            $dt = [];
            // foreach ($data as $key => $value) {
            //     print_r($data);
                $dt['buka']=$data->buka;
                $dt['tutup']=$data->tutup;
            // }
            $buka = explode(",",$dt['buka']);
            $tutup = explode(",",$dt['tutup']);
            
            $param = [
                'hari' => $request->hari,
                'buka' => $buka,
                'tutup' => $tutup  
            ];
            $response = [
                'status' => 1,
                'error' => 0,
                'message' => 'Data Found',
                'data' => $param
            ];
        } else {
            $response = [
                'status' => 0,
                'error' => 500,
                'message' => 'Data Empty',
                'data' => []
            ];
        }
		return response()->json($response);

    }
    
    public function updateJadwalBukaTutupToko(Request $request)
    {   
        // print_r("UPDATE misterkong_$request->comp_id.m_jam_buka_toko SET buka='$request->buka', tutup='$request->tutup', `status`=$request->status WHERE hari = '$request->hari'");
        DB::beginTransaction();
        try {
            DB::update("UPDATE misterkong_$request->comp_id.m_jam_buka_toko SET buka='$request->buka', tutup='$request->tutup', `status`=$request->status WHERE hari = '$request->hari'");
            DB::commit();
            return response()->json([
                'status' => 1,
                'error' => 0,
                'message' => 'Updated Data',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Failed Update Data',
                'data' => []
            ]);
        }
    }

    public function updateGambar(Request $request)
    {
        $file = $request->file('file');
        $name = $file->hashName();
        $ext = $file->getClientOriginalExtension();

        if (strcasecmp($ext, 'jpg') == 0 || strcasecmp($ext, 'jpeg') == 0 || strcasecmp($ext, 'bmp') == 0 || strcasecmp($ext, 'png') == 0) {
            // print_r($name);
            $file->move("../../../public_html/back_end_mp/".$request->comp_id."_config/images/",$name);
            DB::beginTransaction();
            try {
                DB::update("UPDATE misterkong_$request->comp_id.g_db_config SET `value`='$name' WHERE `name`='comp_profile_img'");
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => 0,
                    'message' => 'Updated Data',
                    'data' => [
                        'file' => "misterkong.com/back_end_mp/".$request->comp_id."_config/images/$name"
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'Failed Update Data',
                    'data' => []
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Format file harus berextention jpg atau jpeg atau bmp atau png',
                'data' => []
            ]);
        }
    }
}
