<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\CRUDModel;

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
                'profile' => $info_toko['comp_profile_img'],
                'alamat' => $info_toko['alamat']
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

    public function updateTags(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::update("UPDATE misterkong_$request->comp_id.g_db_config SET `value`='$request->tags' WHERE `name` = 'profile_tag'");
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

    public function jamOperasional(Request $request)
    {
        $jam_kerja = DB::select("SELECT * FROM misterkong_$request->comp_id.m_jam_buka_toko");
        foreach ($jam_kerja as $key => $value) {
            $schedule[$value->hari]['status']=$value->status;
            $schedule[$value->hari]['keterangan']=$value->keterangan;
            $schedule[$value->hari]['buka']=explode(',', $value->buka);
            $schedule[$value->hari]['tutup']=explode(',', $value->tutup);
        }
        $sts_toko = DB::select("SELECT `value` FROM misterkong_$request->comp_id.g_db_config WHERE `name`='status_toko'")[0];
        $jenis_jam_kerja = DB::select("SELECT kd_shift, nama FROM misterkong_$request->comp_id.m_jam_kerja");
        // foreach ($jenis_jam_kerja as $key => $value) {
        //     $dt_jam_kerja = [
        //         "kd_shift" => $value->kd_shift,
        //         "nama" => $value->nama
        //     ];
        // }
        $data['status_toko']= $sts_toko->value;
        $data['company_id']=$request->comp_id;
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
            DB::update("UPDATE m_jam_buka_toko SET manual_status=$status WHERE company_id=(SELECT id FROM m_user_company WHERE company_id='$request->comp_id')");
            DB::commit();
            return response()->json([
                'status' => 1,
                'error' => 0,
                'message' => 'Updated Data',
                'status_toko' => $status,
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $status_toko_failed = DB::select("SELECT `value` AS `status` FROM misterkong_$request->comp_id.g_db_config WHERE name='status_toko'");
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Failed Update Data',
                'status_toko' => $status_toko_failed[0]->status,
                'data' => []
            ]);
        }
    }

    public function getBukaTutupToko(Request $request)
    {
        $data = DB::select("SELECT * FROM misterkong_$request->comp_id.m_jam_buka_toko WHERE hari='$request->hari'")[0];
        if (!empty($data)) {
            $dt = [];
            // foreach ($data as $key => $value) {
            //     print_r($data);
            $dt['buka']=$data->buka;
            $dt['tutup']=$data->tutup;
            // }
            $buka = explode(",",$dt['buka']);
            $tutup = explode(",",$dt['tutup']);

            $param = [
                'hari' => $request->hari,
                'buka' => $buka,
                'tutup' => $tutup  
            ];
            $response = [
                'status' => 1,
                'error' => 0,
                'message' => 'Data Found',
                'data' => $param
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

    public function updateJadwalBukaTutupToko(Request $request)
    {   
        // print_r("UPDATE misterkong_$request->comp_id.m_jam_buka_toko SET buka='$request->buka', tutup='$request->tutup', `status`=$request->status WHERE hari = '$request->hari'");
        DB::beginTransaction();
        try {
            DB::update("UPDATE misterkong_$request->comp_id.m_jam_buka_toko SET buka='$request->buka', tutup='$request->tutup', `status`=$request->status WHERE hari = '$request->hari'");
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

    public function updateGambar(Request $request)
    {
        $file = $request->file('file');
        $name = $file->hashName();
        $ext = $file->getClientOriginalExtension();

        if (strcasecmp($ext, 'jpg') == 0 || strcasecmp($ext, 'jpeg') == 0 || strcasecmp($ext, 'bmp') == 0 || strcasecmp($ext, 'png') == 0) {
            // print_r($name);
            $file->move("../../../public_html/back_end_mp/".$request->comp_id."_config/images/",$name);
            DB::beginTransaction();
            try {
                DB::update("UPDATE misterkong_$request->comp_id.g_db_config SET `value`='$name' WHERE `name`='comp_profile_img'");
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => 0,
                    'message' => 'Updated Data',
                    'data' => [
                        'file' => "misterkong.com/back_end_mp/".$request->comp_id."_config/images/$name"
                    ]
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
        } else {
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Format file harus berextention jpg atau jpeg atau bmp atau png',
                'data' => []
            ]);
        }
    }

    public function loadMasterOpname(Request $request,$company_id)
    {
        // $company_id=$request->company_id;

        $search=$request->search;
        $limit=$request->limit;
        $length=$request->length;
        $awal=$request->awal;
        $akhir=$request->akhir;
        $limitation='';
        if (empty($limit) && empty($length)) {
        }else{
            $limitation=" LIMIT $limit,$length";
        }

        $filter=[];
        $filter_fix='';
        $filter_final='';
        if (!empty($request->kd_merk)) {
            $filter[]="kd_merk = '".$request->kd_merk."'";
        }
        if (!empty($request->kd_jenis_bahan)) {
            $filter[]="kd_jenis_bahan = '".$request->kd_jenis_bahan."'";
        }
        if (!empty($request->kd_kategori)) {
            $filter[]="kd_kategori = '".$request->kd_kategori."'";
        }
        if (!empty($request->kd_model)) {
            $filter[]="kd_model = '".$request->kd_model."'";
        }
        if (!empty($request->kd_warna)) {
            $filter[]="kd_warna = '".$request->kd_warna."'";
        }
        $filter_fix=implode(" OR ", $filter);

        if (!empty($filter_fix)) {
            $filter_final="AND ($filter_fix)";
        }

        if (empty($search)) {
            $tmp_table="DROP TABLE IF EXISTS tmp_master_opname_$company_id; CREATE TABLE tmp_master_opname_$company_id AS 
            SELECT kd_barang,kd_divisi,stok,barang FROM 
            misterkong_$company_id .mon_g_stok_barang_per_divisi_vd 
            WHERE kd_divisi='$request->kd_divisi'";
            DB::unprepared($tmp_table);
        }
        $tmp_table="CREATE TABLE IF NOT EXISTS tmp_master_opname_$company_id AS 
        SELECT kd_barang,kd_divisi,stok,barang FROM 
        misterkong_$company_id .mon_g_stok_barang_per_divisi_vd 
        WHERE kd_divisi='$request->kd_divisi'";
        DB::select($tmp_table);



        $sql_opname="SELECT brg_opname.kd_barang,m_barang.nama,satuan_terkecil,varian_kd_satuan, varian_satuan, stok,last_opname
        -- ,kd_kategori,kd_merk,kd_jenis_bahan,kd_model,kd_warna
        ,status_opname
        FROM tmp_master_opname_$company_id vd
        INNER JOIN misterkong_$company_id .m_barang ON vd.kd_barang=m_barang.kd_barang
        INNER JOIN
        (
            SELECT m_bardiv.*, satuan_terkecil,varian_kd_satuan,varian_satuan,IFNULL(status_opname,0) AS status_opname,IFNULL(last_opname,(SELECT MAX(tanggal) FROM misterkong_$company_id .g_tutup_buku)) AS last_opname
            FROM
            (
                SELECT kd_barang,kd_divisi,CONCAT(kd_barang,kd_divisi) AS kd_barang_divisi
                FROM
                (
                    SELECT kd_barang,kd_divisi FROM misterkong_$company_id .m_barang_divisi
                    WHERE kd_divisi='$request->kd_divisi'
                    ) m_div
                ) m_bardiv
            INNER JOIN
            (
                SELECT kd_barang, 
                GROUP_CONCAT(kd_satuan ORDER BY jumlah) AS varian_kd_satuan,
                GROUP_CONCAT(
                    (SELECT nama FROM misterkong_$company_id .m_satuan WHERE kd_satuan=m_barang_satuan.kd_satuan) ORDER BY jumlah
                    ) AS varian_satuan,
                GROUP_CONCAT(
                    (SELECT nama FROM misterkong_$company_id .m_satuan WHERE kd_satuan=m_barang_satuan.kd_satuan) ORDER BY jumlah LIMIT 1
                    ) AS satuan_terkecil
                FROM misterkong_$company_id .m_barang_satuan m_barang_satuan
                GROUP BY kd_barang
                ) mbs
            ON m_bardiv.kd_barang=mbs.kd_barang
            LEFT JOIN 
            (
                SELECT CONCAT(kd_barang,kd_divisi) AS kd_barang_divisi, 1 AS status_opname
                FROM misterkong_$company_id .t_opname_stok 
                WHERE DATE(tanggal) BETWEEN '$awal' AND '$akhir' AND kd_divisi='$request->kd_divisi'
                ) opname
            ON m_bardiv.kd_barang_divisi=opname.kd_barang_divisi
            LEFT JOIN (
                SELECT kd_barang,GROUP_CONCAT(tanggal ORDER BY tanggal DESC LIMIT 1) AS last_opname FROM misterkong_$company_id .t_opname_stok GROUP BY kd_barang
                ) last_opname
            ON m_bardiv.kd_barang=last_opname.kd_barang
            ) brg_opname
        ON vd.kd_barang=brg_opname.kd_barang AND vd.kd_divisi=brg_opname.kd_divisi
        WHERE (brg_opname.kd_barang LIKE '%$search%' OR nama LIKE '%$search%') $filter_final $limitation
        ";

        

        // $sql="SELECT kd_barang,nama,satuan_terkecil,varian_kd_satuan,varian_satuan,last_opname,stok,status_opname FROM tmp_master_opname_$company_id WHERE (kd_barang LIKE '%$search%' OR nama LIKE '%$search%') $filter_final $limitation";
        // echo $sql;
        // die();
        $data=DB::select($sql_opname);

        return response()->json([
            'status' => 1,
            'error' => 200,
            'message' => 'berhasil',
            'jumlah_record' => count($data),
            'data' => $data
        ]);

    }

    public function doOpname_old(Request $request)
    {
        $qty=0;
        $status_opname=0;
        $company_id=$request->company_id;
        $imei=$request->imei;

        $stok_akhir=DB::table("misterkong_$company_id.mon_g_stok_barang_per_divisi_vd")->select('kd_barang','stok','kd_divisi')->where(['kd_divisi'=>$request->kd_divisi])->get();
        $stok_sistem=[];
        // echo"<pre>";
        // print_r($stok_akhir);
        // echo"</pre>";
        foreach ($stok_akhir as $key_stok => $value_stok) {
            $stok_sistem[$value_stok->kd_barang]=$value_stok->stok;
        } 
        // print_r($stok_sistem);
        $err_kd_barang=[];
        foreach ($request->data as $key_save => $value_save) {
            $no_transaksi="OS".substr($company_id, -4).substr($imei, -4).date('ymd').sprintf("%04d", $key_save+1);
            // print_r($value_save);
            $qty=$value_save['qty'];
            $kd_barang=$value_save['kd_barang'];
            if(!empty($stok_sistem[$kd_barang])){
                $stok_sistem_cal=$stok_sistem[$kd_barang];
                $qty_opname=floatval($qty)-floatval($stok_sistem_cal);
                if ($qty_opname>0) {
                    $status_opname=2;
                    $qty_opname=abs($qty_opname);
                }else{
                    $status_opname=3;
                    $qty_opname*=-1;
                }
                // echo $stok_sistem_cal."=>".$qty;
                $data_save[]=[
                    "no_transaksi" =>$no_transaksi,
                    "kd_divisi" =>$request->kd_divisi,
                    "kd_barang" =>$value_save['kd_barang'],
                    "kd_satuan" =>$value_save['kd_satuan'],
                    "tanggal" =>$request['tanggal'],
                    "qty" =>$qty_opname,
                    "keterangan" =>"POS Opname",
                    "kd_user" =>$request['kd_user'],
                    "status" =>$status_opname,
                    "tanggal_server" =>date('Y-m-d H:i:s'),
                    "harga" =>0,
                ];
            }else{
                $err_kd_barang[]=$kd_barang;
            }
        }
        // echo "<pre>";
        // print_r($data_save);
        // echo "</pre>";
        // die();
        
        if(empty($err_kd_barang)){
            DB::beginTransaction();
            try {
                foreach($data_save as $ey_save=> $value_save){
                    $exe=DB::table("misterkong_$company_id.t_opname_stok")->insert($value_save);
                }
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => 200,
                    'message' => 'berhasil',
                    'data' => []
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'gagal'.$e->getMessage(),
                    'data' => []
                ]);
            }
        }else{
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'gagal, kode '.implode($err_kd_barang)." tidak ditemukan",
                'data' => ['kd_barang'=> $err_kd_barang]
            ]);
        }
    }


    public function doOpname(Request $request,$company_id)
    {
        // $qty=0;
        $status_opname=0;
        // $company_id=$company_id;
        $imei=$request->imei;

        $stok_akhir=DB::table("misterkong_$company_id.mon_g_stok_barang_per_divisi_vd")->select('kd_barang','stok','kd_divisi')->where(['kd_divisi'=>$request->kd_divisi])->get();
        $stok_sistem=[];
        // echo"<pre>";
        // print_r($stok_akhir);
        // echo"</pre>";
        foreach ($stok_akhir as $key_stok => $value_stok) {
            $stok_sistem[$value_stok->kd_barang]=$value_stok->stok;
        } 
        // print_r($stok_sistem);


        $col=array_keys($request->post());
        $no_transaksi=$request->no_transaksi;
        $kd_divisi=$request->kd_divisi;
        $kd_barang=$request->kd_barang;
        $kd_satuan=$request->kd_satuan;
        $tanggal=$request->tanggal;
        $keterangan=$request->keterangan;
        $kd_user=$request->kd_user;
        $qty=$request->qty;
        if (!empty($no_transaksi)) {
            for ($i=0; $i < count($no_transaksi); $i++) {
                if(isset($stok_sistem[$kd_barang[$i]])){
                    $stok_sistem_cal=$stok_sistem[$kd_barang[$i]];
                    $qty_opname=abs($qty[$i]-$stok_sistem_cal);

                    if ($qty_opname>0) {
                        $status_opname=2;
                        $qty_opname=abs($qty_opname);
                    }else{
                        $status_opname=3;
                        $qty_opname*=-1;
                    }

                    $data_save[]=[
                        "no_transaksi" =>$no_transaksi[$i],
                        "kd_divisi" =>$kd_divisi[$i],
                        "kd_barang" =>$kd_barang[$i],
                        "kd_satuan" =>$kd_satuan[$i],
                        "tanggal" =>$tanggal[$i],
                        "qty" =>$qty_opname,
                        "keterangan" =>$keterangan[$i],
                        "kd_user" =>$kd_user[$i],
                        "status" =>$status_opname,
                        "tanggal_server" =>date('Y-m-d H:i:s'),
                        "harga" =>0,
                    ];
                }else{
                    $err_kd_barang[]=$kd_barang[$i];
                }
            }
            if(empty($err_kd_barang)){
                DB::beginTransaction();
                try {
                    foreach($data_save as $ey_save=> $value_save){
                        $exe=DB::table("misterkong_$company_id.t_opname_stok")->insert($value_save);
                    }
                    DB::commit();
                    return response()->json([
                        'status' => 1,
                        'error' => 200,
                        'message' => 'berhasil',
                        'data' => []
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 0,
                        'error' => 500,
                        'message' => 'gagal'.$e->getMessage(),
                        'data' => []
                    ]);
                }
            }else{
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'gagal, kode '.implode(" , ",$err_kd_barang)." tidak ditemukan",
                    'data' => ['kd_barang'=> $err_kd_barang]
                ]);
            }
            
        }
    }
    public function getLaporanOpname(Request $request,$company_id)
    {
        $search=$request->search;
        $order_col=$request->order_col;
        $order_type=$request->order_type;
        $sql_order='';
        if (!empty($order_col)) {
            $sql_order=" ORDER BY $order_col $order_type";
        }

        if ($request->limit==0 && $request->length==0) {
            $limit='';
        }else{
            $limit=" LIMIT $request->limit , $request->length";
        }
        $sql="SELECT 
        m_divisi.kd_divisi,
        m_divisi.nama AS divisi,
        m_barang.kd_barang AS kd_barang,
        m_barang.nama AS barang,
        m_satuan.kd_satuan,
        m_satuan.nama AS satuan,
        no_transaksi,
        qty,
        tanggal,
        kd_user,
        opname.`status`,
        harga
        FROM
        (
            SELECT * FROM misterkong_$company_id .t_opname_stok WHERE DATE(tanggal) BETWEEN '$request->awal' AND '$request->akhir'
            ) opname
        INNER JOIN misterkong_$company_id .m_barang ON opname.kd_barang=m_barang.kd_barang
        INNER JOIN misterkong_$company_id .m_satuan ON opname.kd_satuan=m_satuan.kd_satuan
        INNER JOIN misterkong_$company_id .m_divisi ON opname.kd_divisi=m_divisi.kd_divisi $search $sql_order $limit";
        $param=[
            'search'=>$request->search,
            'order_col'=>$request->order_col,
            'order_type'=>$request->order_type,
            'limit'=>$request->order_limit,
            'length'=>$request->order_length,
        ];
        $data=DB::select($sql,$param);
        return response()->json([
            'status' => 1,
            'error' => 200,
            'message' => count($data)." data ditemukan",
            'jumlah_record'=>count($data),
            'data' => $data
        ]);            
    }



}
