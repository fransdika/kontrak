<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class ExportQueries extends Model
{
    use HasFactory;

    public function rekap_harian($company_id)
    {
        $sql="
        SELECT rekap.*, 
        total_penjualan-total_modal AS laba,
        ROUND((total_penjualan-total_modal)/total_modal*100,2) AS margin
        FROM
        (
            SELECT no_transaksi,penjualan.kd_barang, 
            (SELECT nama FROM m_barang WHERE kd_barang=penjualan.kd_barang) AS barang,
            qty,satuan, jual_bersih as harga_jual, 
            ROUND(IFNULL(last_purchase,(SELECT harga_beli_awal FROM m_barang_divisi WHERE kd_barang=penjualan.kd_barang LIMIT 1)),2) AS modal_terakhir,
            ROUND(jual_bersih*qty,2) AS total_penjualan,
            ROUND(IFNULL(last_purchase,(SELECT harga_beli_awal FROM m_barang_divisi WHERE kd_barang=penjualan.kd_barang LIMIT 1))*qty,2) AS total_modal
            FROM
            (
                SELECT jl.no_transaksi,kd_barang,
                GetHargaBersih(
                    GetHargaBersih(harga_jual,jl_dt.diskon1,jl_dt.diskon2,jl_dt.diskon3,jl_dt.diskon4,0,0),
                    jl.diskon1,jl.diskon2,jl.diskon3,jl.diskon4,pajak,0
                    ) / jumlah AS jual_bersih,
                qty*jumlah AS qty
                FROM 
                (
                    SELECT no_transaksi,diskon1,diskon2,diskon3,diskon4,pajak
                    FROM t_penjualan WHERE 
                    DATE(tanggal)=DATE(DATE_ADD(NOW(),INTERVAL -1 DAY))
                    ) jl
                INNER JOIN 
                (
                    SELECT no_transaksi,mbs.kd_barang,mbs.kd_satuan,t_penjualan_detail.harga_jual,jumlah,qty,diskon1,diskon2,diskon3,diskon4 
                    FROM t_penjualan_detail
                    INNER JOIN m_barang_satuan mbs ON mbs.kd_barang=t_penjualan_detail.kd_barang AND mbs.kd_satuan=t_penjualan_detail.kd_satuan
                    )jl_dt
                ON jl.no_transaksi=jl_dt.no_transaksi
                ) penjualan
            INNER JOIN (
                SELECT kd_barang,
                (SELECT nama FROM m_satuan WHERE kd_satuan=m_barang_satuan.kd_satuan) AS satuan
                FROM m_barang_satuan WHERE status<>0 AND jumlah =1
                ) mbs_kecil
            ON penjualan.kd_barang=mbs_kecil.kd_barang
            LEFT JOIN 
            (
                SELECT kd_barang,GROUP_CONCAT(beli_bersih ORDER BY tanggal DESC LIMIT 1) AS last_purchase
                FROM
                (
                    SELECT mbs.kd_barang,tanggal,
                    GetHargaBersih(
                        GetHargaBersih(harga_beli,bl_dt.diskon1,bl_dt.diskon2,bl_dt.diskon3,bl_dt.diskon4,0,0),
                        bl.diskon1,bl.diskon2,bl.diskon3,bl.diskon4,pajak,ppnbm
                        )/jumlah AS beli_bersih
                    FROM t_pembelian bl INNER JOIN t_pembelian_detail bl_dt ON bl.no_transaksi=bl_dt.no_transaksi
                    INNER JOIN m_barang_satuan mbs ON mbs.kd_barang=bl_dt.kd_barang AND mbs.kd_satuan=bl_dt.kd_satuan
                    GROUP BY kd_barang,tanggal
                    ) pertanggal
                GROUP BY kd_barang
                ) pembelian
            ON penjualan.kd_barang=pembelian.kd_barang
        ) rekap";
        DB::select("USE misterkong_".$company_id);
        $data = DB::select($sql);
        return $data;
    }
}
