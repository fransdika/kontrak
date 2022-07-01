<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KongPosController extends Controller
{
	public function status_pesanan(Request $request)
	{
		$status = $request->status;
		$company_id = $request->company_id;
		$order = $request->no_order;
		$result = DB::Table('misterkong_' . $company_id . '.t_penjualan_order')->selectRaw('status')->where('no_order', '=', $order)->first();
		$response = [
			'status' => 500,
			'key' => NULL,
			'key_terima'=>NULL,
			'message' => 'invalid parameter',
		];
		if (!empty($result)) {
			$response = [
				'status' => 200,
				'key' => $result->status,
				'key_terima'=>0,
				'message' => $this->getStatusOrder(intval($result->status)),
			];
			if ($result->status == 0) {
				$update = DB::statement("UPDATE misterkong_" . $company_id . ".t_penjualan_order SET status = '$status' WHERE no_order = '$order'");
				if ($status == 3) {
					$response['key'] = 3;
					$response['key_terima'] = 1;
				} elseif ($status == 4) {
					$response['key'] = 4;
					$response['key_terima'] = 3;
				}

			} elseif ($result->status == '3') {
				$response['key_terima'] = 2;
			} elseif ($result->status == '4') {
				$response['key_terima'] = 4;
			} elseif ($result->status == 6) {
				$response['key_terima'] = 0;
			} elseif ($result->status == 2) {
				$response['key_terima'] = 2;
			}

		} else {
			$response = [
				'status' => 500,
				'key' => -1,
				'key_terima' => -1,
				'message' => $this->getStatusOrder(intval(-1)),
			];
		}

		return response()->json($response, 200);
	}

	public function tandaisiap(Request $request)
	{
		$status = $request->status;
		$company_id = $request->company_id;
		$order = $request->no_order;
		$result = DB::Table('misterkong_' . $company_id . '.t_penjualan_order')->selectRaw('status')->where('no_order', '=', $order)->first();
		$response = [
			'status' => 500,
			'key' => 0,
			'key_terima' => 0,
			'message' => 'error server',
		];

		if (($result->status != 6 && $result->status != 1) || $status ==1) {
			$update = DB::statement("UPDATE misterkong_" . $company_id . ".t_penjualan_order SET status = '$status' WHERE no_order = '$order'");
			if ($status == 5 || $status==6 || $status==1) {
				$response = [
					'status' => 200,
					'key' => $status,
					'key_terima' => 1,
					'message' => 'berhasil di update',
				];
			} elseif ($status == 4) {
				$response = [
					'status' => 200,
					'key' => 4,
					'key_terima' => 3,
					'message' => 'berhasil di batalkan',
				];
			}
		} else {
			$response = [
				'status' => 200,
				'key' => $result->status,
				'key_terima' => 2,
				'message' => 'pin sudah dimasukan atau pesanan sudah selesai',
			];
			if($status==4){
				$response = [
					'status' => 200,
					'key' => 0,
					'key_terima' => 4,
					'message' => 'pesanan sudah dibatalkan',
				];	
			}elseif($status==3){
				$response = [
					'status' => 200,
					'key' => 0,
					'key_terima' => 2,
					'message' => 'pesanan sudah siap',
				];	
			}
			
		}
		return response()->json($response, 200);

	}

	public function getStatusOrder($status)
	{
		$dataStatus=array(
			0 => 'pesanan masuk',
			1 => 'order sudah selesai',
			2 => 'order sudah batal dari Driver',
			3 => 'order sudah diterima toko',
			4 => 'order sudah dibatalkan oleh toko',
			5 => 'order sudah siap',
			6 => 'pin sudah dimasukkan driver',
			-1=> 'data tidak tersedia'
		);
		return $dataStatus[$status];
	}

	public function getLastStatus(Request $request)
	{
		$company_id = $request->company_id;
		$order = explode(',', $request->no_order);
		$result = DB::table('misterkong_'.$company_id.'.t_penjualan_order')->whereIn('no_order', $order)->get();
		foreach ($result as $key => $value) {
			$data['no_order'][]=$value->no_order;
			$data['status'][]=$value->status;
		}
		$response = [
			'status' => 200,
			'no_order' => $data['no_order'],
			'status' => $data['status'],
		];
		return response()->json($response, 200);
	}

}