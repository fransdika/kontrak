<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanModel extends Model
{
	use HasFactory;

	public function getLaporanPenjualan($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_penjualan('$company_id','$awal','$akhir',$jenis,'$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}
	public function getLaporanPembelian($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_pembelian('$company_id','$awal','$akhir',$jenis,'$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		return DB::select($sql);
	}
	public function getLaporanHutang($company_id,$kd_supplier,$periode,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getHutangAktifPerperiode('$company_id', '$kd_supplier','$periode','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		
		return DB::select($sql);
	}
	public function getLaporanPiutang($company_id,$kd_customer,$periode,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getPiutangAktifPerperiode('$company_id', '$kd_customer','$periode','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		return DB::select($sql);
	}

	public function getLaporanStok($company_id,$kd_barang,$kd_divisi,$periode,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getStokAkhirFilter('$company_id','$kd_barang','$kd_divisi','$periode','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		return DB::select($sql);
	}

	public function getLaporanBiaya($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getBiayaOperasional('$company_id','$awal','$akhir',$jenis,'$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getLaporanPendapatan($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getPendapatan('$company_id','$awal','$akhir',$jenis,'$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}
	
}
