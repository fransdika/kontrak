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
	public function getLaporanPembelian($company_id,$awal,$akhir,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats)
	{
		$sql="CALL p_report_pembelian('$company_id','$awal','$akhir',$jenis,'$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}
	public function getLaporanHutang($company_id,$kd_supplier,$periode,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getHutangAktifPerperiode('$company_id', '$kd_supplier','$periode','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}
	public function getLaporanPiutang($company_id,$kd_customer,$periode,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getPiutangAktifPerperiode('$company_id', '$kd_customer','$periode','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
	}

	public function getLaporanStok($company_id,$kd_barang,$kd_divisi,$periode,$jenis,$search,$order_col,$order_type,$limit_start,$limit_length,$count_stats=0)
	{
		$sql="CALL p_report_getStokAkhirFilter('$company_id','$kd_barang','$kd_divisi','$periode','$jenis','$search','$order_col','$order_type',$limit_start,$limit_length,$count_stats)";
		if ($count_stats>0) {
			return DB::select($sql)[0];
		}else{
			return DB::select($sql);
		}
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

	public function getKartuStok($company_id,$awal,$akhir,$limit,$length,$count_stats,$kd_barang)
	{
		// if (!empty($order_col) && !empty($order_type)) {
		// 	$q_order = "AND (m_barang.nama LIKE '%$search%' OR m_barang.kd_barang LIKE '%$search%')";
		// } else {
		// 	$q_order = '';
		// }

		if ($count_stats == 1) {
			$select_final = "COUNT(*) AS jumlah_data";
		} else {
			$select_final = "m_barang.nama AS nama_barang,
							 m_barang.kd_barang,
							 tanggal,
							 jenis_transaksi,
							 qty_masuk AS debet_qty,
							 qty_masuk*rupiah_masuk AS debet_rp,
							 qty_keluar AS kredit_qty,
							 rupiah_keluar AS kredit_rp,
							 saldo_qty AS sisa_stok,
							 satuan_terkecil";
		}
		$sql = DB::select("SELECT $select_final FROM misterkong_$company_id.v_t_result_table v_t_result_table INNER JOIN misterkong_$company_id.m_barang m_barang ON v_t_result_table.kd_barang = m_barang.kd_barang WHERE m_barang.kd_barang = '$kd_barang' AND DATE(tanggal) BETWEEN '$awal' AND '$akhir' ORDER BY rn ASC LIMIT $limit, $length");
		return $sql;
	}
	
}