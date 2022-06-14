<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanModel extends Model
{
	use HasFactory;

	public function getLaporanPenjualan($company_id,$awal,$akhir,$jenis,$limit_start,$limit_length)
	{
		$sql="CALL p_report_penjualan('$company_id','$awal','$akhir',$jenis,$limit_start,$limit_length)";
		return DB::select($sql);
	}
	public function getLaporanPembelian($company_id,$awal,$akhir,$jenis,$limit_start,$limit_length)
	{
		$sql="CALL p_report_pembelian('$company_id','$awal','$akhir',$jenis,$limit_start,$limit_length)";
		return DB::select($sql);
	}
	public function getLaporanHutang($company_id,$kd_supplier,$periode,$limit_start,$limit_length)
	{
		$sql="CALL p_report_getHutangAktifPerperiode('$company_id', '$kd_supplier','$periode','$limit_start','$limit_length')";
		return DB::select($sql);
	}
	public function getLaporanPiutang($company_id,$kd_customer,$periode,$limit_start,$limit_length)
	{
		$sql="CALL p_report_getHutangAktifPerperiode('$company_id', '$kd_supplier','$periode','$limit_start' ,'$limit_length')";
		return DB::select($sql);
	}

	public function getLaporanStok($company_id,$kd_barang,$kd_divisi,$periode,$limit_start,$limit_length)
	{
		$sql="CALL p_report_getStokAkhirFilter('$company_id','$kd_barang','$kd_divisi','$periode','$limit_start','$limit_length')";
		return DB::select($sql);
	}
	
}
