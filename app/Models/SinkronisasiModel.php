<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SinkronisasiModel extends Model
{
    use HasFactory;
    function convertToQuery($table, $data){

        $query="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'misterkong_comp2020061905541701'  AND TABLE_NAME = '$table'  AND COLUMN_KEY = 'PRI'";
        $exe_primary= DB::select($query);
        $primary=[];
        foreach ($exe_primary as $key => $value) {
            $primary[]=$value->COLUMN_NAME;
        }


        $key_col=array_keys((array)$data[0]);
        $col=implode(',', $key_col);
        $sql="INSERT INTO $table($col) VALUES";
        $values=[];
        foreach ($data as $key_data => $value_data) {
            foreach($value_data as $key_last => $value_last){
                if(preg_match("/'/",$value_last)){
                    $value_data->$key_last=str_replace("'","''",$value_last);
                }
            }
            $row_data=implode("','", (array)$value_data);
            $values[]="('$row_data')";
        }
        $on_conflict=[];
        foreach ($key_col as $key_kolom => $value_kolom) {
            $on_conflict[]="$value_kolom = EXCLUDED.$value_kolom";
        }
        $sql.=implode(',', $values)." ON CONFLICT (".implode(',', $primary).") DO UPDATE SET ".implode(',', $on_conflict);

        return $sql;
    }
    public function updateProfile($query)
    {
        DB::beginTransaction();
        try {
            if (!empty($query)) {
                foreach ($query as $key => $value) {
                    DB::select($value);
                }
                DB::commit();
                return 1;
            }
            
        } catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollback();
            return 0;
        }
    }

    public function getTransactionData($company_id,$table_name, $condition)
    {
        $model = new SinkronisasiModel(); 

        $getSelectedPackage=$model::getTrPackage($table_name);
        if (!empty($getSelectedPackage)) {
            $table_master=$getSelectedPackage[0];
            $detail_data=[];
            if (count($getSelectedPackage)>1) {
                $table_detail=$getSelectedPackage[1];
                $detail_data=DB::table("misterkong_$company_id.".$table_master."_detail")->where($condition)->get();
            }
            $master_data=DB::table("misterkong_$company_id.".$table_master)->where($condition)->get();
            $data=[
                "master" => $master_data,
                "detail" => $detail_data
            ];
            return $data;
        }
        return [
            "master" => [],
            "detail" => []
        ];
    }

    function getTrPackage($table_name){
        $package= [
            "t_absensi" => ["t_absensi"],
            "t_absensi_pegawai_lain" => ["t_absensi_pegawai_lain"],
            "t_biaya_operasional" => ["t_biaya_operasional"],
            "t_gaji" => ["t_gaji"],
            "t_hutang_aset_cicilan" => ["t_hutang_aset_cicilan"],
            "t_hutang_biaya_angkut_cicilan" => ["t_hutang_biaya_angkut_cicilan"],
            "t_hutang_cicilan" => ["t_hutang_cicilan"],
            "t_hutang_pegawai" => ["t_hutang_pegawai"],
            "t_hutang_pegawai_cicilan" => ["t_hutang_pegawai_cicilan"],
            "t_kendaraan_jarak_tempuh" => ["t_kendaraan_jarak_tempuh"],
            "t_kendaraan_pengisian_bbm" => ["t_kendaraan_pengisian_bbm"],
            "t_kendaraan_perawatan" => ["t_kendaraan_perawatan"],
            "t_kendaraan_tanggung_jawab" => ["t_kendaraan_tanggung_jawab"],
            "t_mutasi_internal" => ["t_mutasi_internal","t_mutasi_internal_detail"],
            "t_mutasi_kas" => ["t_mutasi_kas"],
            "t_mutasi_kas_copy" => ["t_mutasi_kas_copy"],
            "t_mutasi_stok" => ["t_mutasi_stok","t_mutasi_stok_detail"],
            "t_opname_stok" => ["t_opname_stok"],
            "t_opname_stok_tmp" => ["t_opname_stok_tmp"],
            "t_pegawai_ganti_shift" => ["t_pegawai_ganti_shift","t_pegawai_ganti_shift_detail"],
            "t_pegawai_izin" => ["t_pegawai_izin"],
            "t_pegawai_lembur" => ["t_pegawai_lembur","t_pegawai_lembur_detail"],
            "t_pegawai_surat_peringatan" => ["t_pegawai_surat_peringatan"],
            "t_pemakaian_barang" => ["t_pemakaian_barang","t_pemakaian_barang_detail"],
            "t_pembelian" => ["t_pembelian"],
            "t_pembelian_biaya_angkut" => ["t_pembelian_biaya_angkut","t_pembelian_detail"],
            "t_pembelian_order" => ["t_pembelian_order","t_pembelian_order_detail"],
            "t_pembelian_order_spare_part" => ["t_pembelian_order_spare_part","t_pembelian_order_spare_part_detail"],
            "t_pembelian_retur" => ["t_pembelian_retur","t_pembelian_retur_detail"],
            "t_penambahan_kas" => ["t_penambahan_kas"],
            "t_pendapatan" => ["t_pendapatan"],
            "t_penerimaan" => ["t_penerimaan","t_penerimaan_detail"],
            "t_pengiriman" => ["t_pengiriman","t_pengiriman_detail"],
            "t_penjualan" => ["t_penjualan","t_penjualan_detail"],
            "t_penjualan_detail_pegawai"=>["t_penjualan_detail_pegawai"],
            "t_penjualan_jasa" => ["t_penjualan_jasa","t_penjualan_jasa_detail"],
            "t_penjualan_jasa_order" => ["t_penjualan_jasa_order","t_penjualan_jasa_order_detail"],
            "t_penjualan_koin" => ["t_penjualan_koin"],
            "t_penjualan_nota_kosong" => ["t_penjualan_nota_kosong"],
            "t_penjualan_order" => ["t_penjualan_order","t_penjualan_order_detail"],
            "t_penjualan_order_tmp" => ["t_penjualan_order_tmp"],
            "t_penjualan_point" => ["t_penjualan_point"],
            "t_penjualan_retur" => ["t_penjualan_retur","t_penjualan_retur_detail"],
            "t_penjualan_selected" => ["t_penjualan_selected"],
            "t_penjualan_total" => ["t_penjualan_total"],
            "t_piutang_cicilan" => ["t_piutang_cicilan"],
            "t_piutang_jasa_cicilan" => ["t_piutang_jasa_cicilan"],
            "t_prive" => ["t_prive"],
            "t_produksi" => ["t_produksi","t_produksi_detail"],
            "t_service_history" => ["t_service_history"],
            "t_sewa" => ["t_sewa"],
            "t_surat_berharga" => ["t_surat_berharga"],
            "t_tagihan" => ["t_tagihan","t_tagihan_detail"],
            "t_testing" => ["t_testing"],
            "t_transaks i_ barang" => ["t_transaksi_barang","t_transaksi_barang_detail"],
            "t_warning"=>["t_warning"]
        ];
        return $package[$table_name]??[];
    }
}
