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
                'profile' => $info_toko['comp_profile_img'] 
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

    public function cdTags(Request $request)
    {
        if ($request->isMethod('POST')) {
            
        } else {

        }
    }

    public function jamOperasional(Request $request)
    {
        $array_hari=array('senin','selasa','rabu','kamis','jum\'at','sabtu','minggu');
		$jam_kerja = DB::select("SELECT * FROM misterkong_$request->comp_id.m_jam_buka_toko");
		foreach ($jam_kerja as $key => $value) {
			$schedule[$value->hari]['status']=$value->status;
			$schedule[$value->hari]['keterangan']=$value->keterangan;
			$schedule[$value->hari]['buka']=explode(',', $value->buka);
			$schedule[$value->hari]['tutup']=explode(',', $value->tutup);
		}
		$sts_toko = DB::select("SELECT `value` FROM misterkong_$request->comp_id.g_db_config WHERE `name`='status_toko'")[0];
        $jenis_jam_kerja = DB::select("SELECT kd_shift, nama FROM misterkong_$request->comp_id.m_jam_kerja");
        foreach ($jenis_jam_kerja as $key => $value) {
            $dt_jam_kerja = [
                "kd_shift" => $value->kd_shift,
                "nama" => $value->nama
            ];
        }
		$data['status_toko']= $sts_toko->value;
		$data['company_id']=$request->comp_id;
		$data['hari']=$array_hari;
		$data['jenis_jam_kerja']=$dt_jam_kerja;
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
}
