<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class solidReportController extends Controller
{
    // public function change_store_status(Request $request){
	// 	if (!empty($request->status)) {
	// 		if ($request->status==-1) {
	// 			$sts_toko=0;
	// 		}else{
	// 			$sts_toko=1;
	// 		}
	// 		$data=array('value'=>$sts_toko);
	// 		$this->jm->change_store_status($data);
	// 		if ($this->db->affected_rows()==1) {
	// 			print json_encode(array('status'=>1));
	// 		}else{
	// 			print json_encode(array('status'=>0));
	// 		}
	// 	}
	// }

	public function get_info_toko(Request $request)
	{
		$data['data'] = DB::select("SELECT * FROM misterkong_$request->comp_id.g_db_config");
		return response()->json($data);
	}
}
