<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaporanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth:api');
	}
	public function getLaporanPenjualan(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$search=$request->search;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanPenjualan($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit,$length,$count_stats);	
		return response()->json($data, 200);
	}
	public function getLaporanPembelian(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$search=$request->search;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanPembelian($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit,$length,$count_stats);	
		return response()->json($data, 200);
	}
	public function getLaporanHutang(Request $request)
	{
		$company_id=$request->company_id;
		$kd_supplier=$request->kd_supplier;
		$periode=$request->periode;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		// echo $order_type;
		$data = LaporanModel::GetLaporanHutang($company_id,$kd_supplier,$periode,$order_col,$order_type,$limit,$length,$count_stats);	
		return response()->json($data, 200);
	}

	public function getLaporanPiutang(Request $request)
	{
		$company_id=$request->company_id;
		$kd_customer=$request->kd_customer;
		$periode=$request->periode;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanPiutang($company_id,$kd_customer,$periode,$order_col,$order_type,$limit,$length,$count_stats);
		return response()->json($data, 200);
	}

	public function getLaporanStok(Request $request)
	{
		$company_id=$request->company_id;
		$kd_barang=$request->kd_barang;
		$kd_divisi=$request->kd_divisi;
		$periode=$request->periode;
		$jenis=$request->jenis;
		$search=$request->search;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanStok($company_id,$kd_barang,$kd_divisi,$periode,$jenis,$search,$order_col,$order_type,$limit,$length,$count_stats);
		return response()->json($data, 200);
	}

	public function getLaporanBiaya(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$search=$request->search;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanBiaya($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit,$length,$count_stats);	
		return response()->json($data, 200);		
	}

	public function getLaporanPendapatan(Request $request)
	{
		$company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$search=$request->search;
		$order_col=$request->order_col;
		$order_type=$request->order_type;
		$limit=$request->limit;
		$length=$request->length;
		$count_stats=$request->count_stats;
		$data = LaporanModel::GetLaporanPendapatan($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit,$length,$count_stats);	
		return response()->json($data, 200);		
	}

	public function getPenjualanOrder(Request $request)
	{
		$sql="CALL p_report_penjualan_order('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getPenjualanRetur(Request $request)
	{
		$sql="CALL p_report_penjualan_retur('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getPembelianOrder(Request $request)
	{
		$sql="CALL p_report_pembelian_order('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getPembelianRetur(Request $request)
	{
		$sql="CALL p_report_pembelian_retur('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getPenjualanNewBorn(Request $request)
	{
		$sql=DB::select("CALL p_report_penjualanNewBorn('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit,$request->length)");
		return response()->json($sql);
	}

	public function getPembelianNewBorn(Request $request)
	{
		$sql=DB::select("CALL p_report_pembelianNewBorn('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type',$request->limit,$request->length)");
		return response()->json($sql);
	}

	public function produk(Request $request)
	{
		if ($request->jenis != 2) {
			$sql=DB::select("CALL misterkong_$request->company_id.p_mon_report_mutasi_stok('$request->awal','$request->akhir', $request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit, $request->length, $request->count_stats)");
		} else {
			$sql = LaporanModel::getKartuStok($request->company_id,$request->awal,$request->akhir, $request->limit, $request->length, $request->count_stats,$request->kd_barang);
		}
		try {
			return response()->json($sql);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'status' => 0,
				'error' => $e->getMessage(),
				'message' => 'Gagal'
			], 404);
			return response()->json([
				'status' => 0,
				'error' => $e->getMessage(),
				'message' => 'Gagal'
			], 500);
		}

	}

	public function mutasi_kas(Request $request)
	{
		$sql = DB::select("CALL p_laporan_kas('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit, $request->length, '$request->kd_kas')");
		return response()->json($sql);
	}

	public function getLaporanBiayaNewBorn(Request $request)
	{
		$sql = "CALL p_biayaOperasionalNewBorn('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select("CALL p_biayaOperasionalNewBorn(?,?,?,?,?,?,?,?,?)", ["$request->company_id","$request->awal","$request->akhir","$request->search","$request->order_col","$request->order_type",$request->limit,$request->length,$request->count_stats])[0];
		}else{
			return DB::select("CALL p_biayaOperasionalNewBorn(?,?,?,?,?,?,?,?,?)", ["$request->company_id","$request->awal","$request->akhir","$request->search","$request->order_col","$request->order_type",$request->limit,$request->length,$request->count_stats]);
		}
	}

	public function getLaporanPendapatanNewBorn(Request $request)
	{
		$sql = "CALL p_pendapatanNewBorn('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,$request->count_stats)";
		if ($request->count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}
}