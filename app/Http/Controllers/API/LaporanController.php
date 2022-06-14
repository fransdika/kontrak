<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaporanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
	public function getLaporanPenjualan(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$limit=$request->limit;
		$length=$request->length;
		$data = LaporanModel::GetLaporanPenjualan($company_id,$awal,$akhir,$jenis,$limit,$length);	
		return response()->json($data, 200);
	}
	public function getLaporanPembelian(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$limit=$request->limit;
		$length=$request->length;
		$data = LaporanModel::GetLaporanPembelian($company_id,$awal,$akhir,$jenis,$limit,$length);	
		return response()->json($data, 200);
	}
	public function getLaporanHutang(Request $request)
	{
		$company_id=$request->company_id;
		$kd_supplier=$request->kd_supplier;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = LaporanModel::GetLaporanHutang($company_id,$kd_supplier,$periode,$limit,$length);	
		return response()->json($data, 200);
	}

	public function getLaporanPiutang(Request $request)
	{
		$company_id=$request->company_id;
		$kd_customer=$request->kd_customer;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = LaporanModel::GetLaporanPiutang($company_id,$kd_customer,$periode,$limit,$length);	
		return response()->json($data, 200);
	}

	public function getLaporanStok(Request $request)
	{
		$company_id=$request->company_id;
		$kd_barang=$request->kd_barang;
		$kd_divisi=$request->kd_divisi;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = LaporanModel::GetLaporanStok($company_id,$kd_barang,$kd_divisi,$periode,$limit,$length);	
		return response()->json($data, 200);
	}

	public function getLaporanBiaya()
	{
		
	}

	public function getLaporanPendapatan()
	{
		
	}

}
