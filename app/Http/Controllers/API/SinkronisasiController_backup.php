<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SinkronisasiController_backup extends Controller
{
    private $json;
    private $notif_stats;
    private $file_name;
    public function convert_to_json_mode2(Request $request, $company_id, $imei)
    {
        $last_request = (!empty($request->last_request_time)) ? $request->last_request_time : '2018-00-00 00:00:00';
        $json_no_dt = file_get_contents(base_path('public/sync/table_with_date_modif.json'));

        $table_name_no_dt = json_decode($json_no_dt, true);
        foreach ($table_name_no_dt as $key => $value) {
            $list_table[] = $key;
        }
        foreach ($list_table as $key => $value) {
            if (!preg_match('/t_/', $value)) {
                // $sql_get_data="SELECT $value.* FROM $value INNER JOIN x_last_time_android_get a WHERE date_modif> a.last_success_time";
                // echo $this->get_last_request();
                // $this->empty_folder();
                // echo app_path();
                $sql_get_data = "SELECT $value.* FROM misterkong_" . $company_id . ".$value WHERE date_modif >='" . $last_request . "'";
                // echo $sql_get_data;
                $exe_get_data = db::select($sql_get_data);
                if (count($exe_get_data)) {
                    // echo $value;
                    // $file_json = fopen("../pr_multi_db/back_end_mp/".$company_id."_config/POST/".$imei."/".$value."__".$last_request.".json", "w+");
                    if (!file_exists("../../../public_html/back_end_mp/" . $company_id . "_config/POST/" . $imei)) {
                        mkdir("../../../public_html/back_end_mp/" . $company_id . "_config/POST/" . $imei, 0777, true);
                    }
                    // $path="../../pr_multi_db/back_end_mp/".$company_id."_config/POST/".$imei."/".$value."__".$last_request.".json"; //local
                    $path = "../../../public_html/back_end_mp/" . $company_id . "_config/POST/" . $imei . "/" . $value . "__" . $last_request . ".json"; //vps
                    $file_json = fopen($path, "w+");
                    fclose($file_json);
                    $file_path = $path;
                    file_put_contents($file_path, "");


                    $arr_data_prepare = array();
                    foreach ($exe_get_data as $key => $value) {
                        $arr_data_prepare[] = $value;
                    }
                    // while ($row_get_data=$exe_get_data->fetch_assoc()) {
                    //     $arr_data_prepare[]=$row_get_data;
                    // }
                    $keys = (array)$arr_data_prepare[0];
                    $kolom = array_keys($keys);
                    // print_r($kolom);
                    $json_data = array();
                    // for ($i=0; $i < count($kolom); $i++) { 
                    foreach ($kolom as $key_table => $value_key) {
                        foreach ($arr_data_prepare as $key => $value_rec) {
                            // echo "<pre>";
                            // print_r($value_rec->$value_key);
                            // echo $kolom[$i];
                            // echo "</pre>";
                            // die();
                            $json_data[$value_key][] = $value_rec->$value_key;
                        }
                    }

                    $file_contents = json_encode($json_data);
                    file_put_contents($file_path, $file_contents, FILE_APPEND | LOCK_EX);
                    unset($json_data);
                    // echo "<pre>";
                    // print_r($arr_data_prepare);
                    // echo "</pre>";
                    // echo "<pre>";
                    // print_r($json_data);
                    // echo "</pre>";   
                }
            }
        }
        return response()->json([1], 200);
    }

    // ----------------------------------------------------------------------SYNC EXEC -------------------------------------------------
    public function jsonPosExecutor(Request $request, $company_id, $imei)
    {
        $this->get_json_file_name($company_id, 'GET', $imei);
        $data_json = $this->json;
        $table_name = array();
        $dt_list = array();
        $query[] = "SET FOREIGN_KEY_CHECKS=0";
        foreach ($data_json as $key_table => $value) {
            if (!empty($value)) {
                for ($iterasi = 0; $iterasi < count($data_json[$key_table]); $iterasi++) {
                    $table_name = $key_table;
                    $col_name = array();
                    foreach ($data_json[$key_table][0] as $key_col_name => $value_col_name) {
                        $col_name[] = $key_col_name;
                    }
                    if (array_key_exists('details', $value[$iterasi])) {
                        array_pop($col_name);
                        $col_name_dt = array();
                        $data_dt = array();
                        foreach ($data_json[$key_table][$iterasi]['details'][0] as $key_col_dt => $value_col_dt) {
                            $col_name_dt[] = $key_col_dt;
                        }
                        foreach ($data_json[$key_table][$iterasi]['details'] as $key_rec_dt => $value_rec_dt) {
                            $data_dt[] = $value_rec_dt;
                        }
                        $table_name_dt = explode('__', $key_table)[0] . "_detail";
                    } else {
                        $table_name_dt = "";
                    }
                    if (empty($table_name_dt)) {
                        if ($this->master_service($table_name, implode("','", $col_name), $iterasi, $company_id)) {
                            $query[] = $this->master_service($table_name, implode("','", $col_name), $iterasi, $company_id);
                            $this->notif_stats = true;
                        }
                    } else {
                        if ($this->master_service($table_name, implode("','", $col_name), $iterasi, $company_id)) {
                            $query[] = $this->master_service($table_name, implode("','", $col_name), $iterasi, $company_id);
                        }
                        $query[] = $this->master_detail_service($table_name_dt, implode("','", $col_name_dt), $iterasi, $data_dt[0], $company_id);
                    }
                }
            }
        }
        // echo "<pre>";
        // print_r($query);
        // echo "</pre>";
        try {
            DB::beginTransaction();
            DB::select("INSERT INTO sync_monitoring(company_id,status) VALUES('$company_id','0') ON DUPLICATE KEY UPDATE status=VALUES(status)");
            if (count($query) > 1) {
                $query[] = "SET FOREIGN_KEY_CHECKS=1";

                // $path_result="../../pr_multi_db/back_end_mp/".$company_id."_config/GET/result/"; //local
                $path_result = "../../../public_html/back_end_mp/" . $company_id . "_config/GET/result/"; //vps

                if (!file_exists($path_result)) {
                    mkdir($path_result, 0777, true);
                }
                $file_json = fopen($path_result . date('Y-m-d H.i.s') . ".sql", "w+"); //local 
                // $file_json = fopen($path_result.date('Y-m-d H:i:s').".sql", "w+"); //vps
                fclose($file_json);

                $file_path = $path_result . date('Y-m-d H.i.s') . ".sql"; //local
                // $file_path = $path_result.date('Y-m-d H:i:s').".sql"; //vps
                file_put_contents($file_path, "");

                $file_contents = json_encode($query);
                file_put_contents($file_path, $file_contents, FILE_APPEND | LOCK_EX);
                $status = array();
                foreach ($query as $key => $value) {
                    // echo $value;
                    DB::select($value);
                }
                DB::commit();
                DB::select("INSERT INTO sync_monitoring(company_id,status) VALUES('$company_id','1') ON DUPLICATE KEY UPDATE status=VALUES(status)");
                $this->copy_file($company_id, $imei);
                $this->cek_stok('', [], $company_id);
                if ($this->notif_stats) {
                    $payload = array(
                        'to' => '/topics/kongpos',
                        'priority' => 'high',
                        "mutable_content" => true,
                        'data' => array(
                            "title" => 'Update Master',
                            "comp_id" => $company_id,
                            "jenis_notif" => '2',

                        ),
                    );
                    $this->send_notif_custom($payload);
                }
                $this->empty_folder('exec', $company_id, $imei);
                echo 1;
            }
        } catch (\Exception $e) {

            return response()->json([$e->getMessage()], 500);
            DB::rollBack();
            // $con->kon_mdb->rollback();
        }
    }

    public function master_service($tbl_name, $columns_name, $iterasi, $company_id)
    {
        $col_name = explode("','", $columns_name);
        $col_name_inserted = implode(',', $col_name);
        for ($i = 0; $i < count($col_name); $i++) {
            foreach ($this->json[$tbl_name][$iterasi][$col_name[$i]] as $key => $value) {
                if ($tbl_name == 'm_barang_gambar') {
                }
                if ($col_name[$i] == "date_modif" || $col_name[$i] == "tanggal_server" || $col_name[$i] == "date_add" || $col_name[$i] == "tanggal_jatuh_tempo") {
                    if ($value == '' || $value == 'null') {
                        $value = date("Y-m-d H:i:s");;
                    }
                }
                $rec[$tbl_name][$col_name[$i]][] = $value;
            }
        }
        if (count($rec[$tbl_name][$col_name[0]]) > 0 && !empty(count($rec[$tbl_name]))) {
            for ($j = 0; $j < count($rec[$tbl_name][$col_name[0]]); $j++) {
                $record = array();
                for ($i = 0; $i < count($col_name); $i++) {
                    $record[] = $rec[$tbl_name][$col_name[$i]][$j];
                }
                $record_master[] = "('" . implode("','", $record) . "')";
            }
            $col_updt = array();
            foreach ($col_name as $key_updt_col => $value_updt_col) {
                $col_updt[] = $value_updt_col . "=VALUES(" . $value_updt_col . ")";
            }
            return str_replace("'NULL'", "NULL", "INSERT INTO misterkong_" . $company_id . "." . $tbl_name . "($col_name_inserted) VALUES" . implode(",", $record_master) . "ON DUPLICATE KEY UPDATE " . implode(',', $col_updt));
        } else {
            return false;
        }
    }
    public function master_detail_service($tbl_name, $columns_name, $iterasi, $data_dt, $company_id)
    {
        $col_name = explode("','", $columns_name);
        $test = 0;
        for ($dt_i = 0; $dt_i < count($data_dt['date_modif']); $dt_i++) {
            foreach ($data_dt as $key_dt => $value_dt) {
                if ($key_dt == 'date_modif') {
                    $data_dt['date_modif'][$dt_i] = date("Y-m-d H:i:s");
                }
            }
        }
        $record_dt = array();
        for ($j = 0; $j < count($data_dt['date_modif']); $j++) {
            $record = array();
            foreach ($data_dt as $key => $value) {
                $record[] = $data_dt[$key][$j];
            }
            $record_dt[] = "('" . implode("','", $record) . "')";
        }
        $col_name_inserted = implode(',', $col_name);
        $col_updt = array();
        foreach ($col_name as $key_updt_col => $value_updt_col) {
            $col_updt[] = $value_updt_col . "=VALUES(" . $value_updt_col . ")";
        }
        return str_replace("'NULL'", "NULL", "INSERT INTO misterkong_" . $company_id . "." . $tbl_name . "(" . implode(',', $col_name) . ") VALUES" . implode(",", $record_dt) . "ON DUPLICATE KEY UPDATE " . implode(',', $col_updt));
    }

    function cek_stok($con, $kd_barang, $company_id)
    {
        $condition = implode("','", $kd_barang);
        $sql_get_stok = "SELECT stok_akhir.kd_barang FROM (SELECT kd_barang,kd_divisi,stok FROM misterkong_" . $company_id . ".mon_g_stok_barang_per_divisi_vd WHERE kd_barang IN('$condition')) stok_akhir INNER JOIN (SELECT kd_barang,kd_divisi,stok_min FROM misterkong_" . $company_id . ".m_barang_divisi WHERE kd_barang IN('$condition')) stok_min ON stok_akhir.kd_barang=stok_min.kd_barang AND stok_akhir.kd_divisi=stok_min.kd_divisi WHERE stok_akhir.stok < stok_min.stok_min";

        $exe_get_stok = DB::select($sql_get_stok);
        if (count($exe_get_stok) > 0) {
            foreach ($exe_get_stok as $key => $row_get_stok) {
                $item_stok_minus[] = $row_get_stok->kd_barang;
            }
            $payload = array(
                'to' => '/topics/kongpos',
                'priority' => 'high',
                "mutable_content" => true,
                'data' => array(
                    "title" => 'Stok Kurang',
                    "body" => "terdapat " . count($item_stok_minus) . " item direkomendasikan untuk dipesan",
                    "comp_id" => $company_id,
                    "jenis_notif" => '10',
                    "isi" => '-',
                ),
            );
            $this->send_notif_custom($payload);
        }
    }
    public function copy_file($company_id, $imei)
    {
        // //local
        // $dir = "../../pr_multi_db/back_end_mp/".$company_id."_config/POST";
        // $dir_src = "../../pr_multi_db/back_end_mp/".$company_id."_config/GET";

        //vps
        $dir = "../../../public_html/back_end_mp/" . $company_id . "_config/POST";
        $dir_src = "../../../public_html/back_end_mp/" . $company_id . "_config/GET";

        $hideName = array('.', '..');
        // echo getcwd();
        // echo $dir;

        // print_r(scandir($dir));
        foreach (scandir($dir) as $folder_name) {
            if (!in_array($folder_name, $hideName)) {
                if ($folder_name != $imei) {
                    foreach ($this->file_name as $key => $value) {
                        // echo "../".$this->get_company_id()."/".$dir_src."/".$imei."/".$value."=>"."../".$this->get_company_id()."/POST/".$folder_name."/".$value."<br>";
                        // echo $dir_src."/".$imei."/".$value."<br>";
                        // echo $dir."/".$folder_name."/".$value."<br>";
                        copy($dir_src . "/" . $imei . "/" . $value, $dir . "/" . $folder_name . "/" . $value);
                    }
                }
            }
        }
        foreach ($this->file_name as $key => $value) {
            copy($dir_src . "/" . $imei . "/" . $value, $dir_src . "/result/" . $value);
        }
    }
    function send_notif_custom($payload)
    {

        $headers = array(
            'Authorization:key=AAAAf50odws:APA91bERBP6tLNfAWz_aeNhmXjbOOItI2aZ_bZEy1xNX47SWCr8LbrfNVQfuVJ8xYT7_mCFKRn6pBW7_qO-fG5qFNfIU-8nfWm1-M_zhezLK12dlsIeFi8ZfYeizEhPVQTdIbGj0DtUt', 'Content-Type: application/json',
        );
        // echo "<pre>";
        // print_r($payload);
        // echo "</pre>";
        if (!empty($payload)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
    public function empty_folder($jenis, $company_id, $imei)
    {
        if ($jenis == "exec") {
            $sub_dir = 'GET';
            $imei_dir = "/" . $imei;
        } elseif ($jenis == 'del') {
            $sub_dir = 'DEL';
            $imei_dir='';
        } else {
            $sub_dir = 'POST';
            $imei_dir="/" . $imei;
        }
        // $dir = "../../pr_multi_db/back_end_mp/".$company_id."_config/".$sub_dir.$imei_dir; //local
        $dir = "../../../public_html/back_end_mp/" . $company_id . "_config/" . $sub_dir . $imei_dir; //vps
        foreach ($this->file_name as $key => $value) {
            // echo $value;
            // echo($dir.DIRECTORY_SEPARATOR.$value);
            unlink($dir . "/" . $value);
        }
    }


    // ----------------------------------------------------------------------SYNC EXEC -------------------------------------------------


    // ----------------------------------------------------------------------SYNC DELETE -------------------------------------------------
    public function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(scandir($dir)) == 2);
    }

    // get_json_file_name
    public function get_json_file_name($company_id, $dir, $imei = '')
    {
        // $dir="../../pr_multi_db/back_end_mp/".$company_id."_config/".$dir; //local
        $dir = "../../../public_html/back_end_mp/" . $company_id . "_config/" . $dir; //vps
        if (!empty($imei)) {
            $dir .= "/$imei";
        }
        // $dir=__DIR__;
        $hideName = array('.', '..');
        $files_name = [];
        if ($this->is_dir_empty($dir)) {
            echo "the folder is empty";
        } else {
            $files = array_diff(scandir($dir, SCANDIR_SORT_DESCENDING), array('..', '.'));
            foreach ($files as $file) {
                if (!in_array($file, $hideName)) {

                    $files_name[] = $file;
                }
            }
        }
        $this->file_name = $files_name;

        //json_proccessing
        $this->json = [];
        foreach ($files_name as $key => $value) {
            $tbl_name_tmp = explode('__', $value);
            $str = file_get_contents($dir . '/' . $value);
            if (!empty($str)) {
                $this->json[$tbl_name_tmp[0]][] = json_decode($str, true);
            }
        }
    }

    public function syncDelete(Request $request)
    {
        $company_id = $request->cid;
        // $company_id = $request->company_id;
        $this->get_json_file_name($company_id, 'DEL');
        // print_r($files_name);
        // die();



        //execute_query_delete
        $keys = array_keys($this->json);
        $i = 0;
        $sql_arr = array();
        foreach ($keys as $key_table => $value_table) {
            foreach ($this->json as $key_column => $value) {
                foreach ($value as $key_column => $value_column) {
                    $dt_stats = $value_column['detail'];
                    $sql = "DELETE FROM $value_table WHERE ";
                    $col_keys = array_keys($value_column['key']);
                    $primary_key = array();
                    foreach ($col_keys as $key_val => $value_val) {

                        $primary_key[] = $value_val . "='" . $value_column['key'][$value_val] . "'";
                    }
                    $sql .= implode(' AND ', $primary_key);
                    if ($dt_stats) {
                        $sql_arr[] = "DELETE FROM misterkong_".$company_id."." . $value_table . "_detail WHERE " . implode(' AND ', $primary_key);
                    }
                    $sql_arr[] = $sql;
                }
            }
            $i++;
        }
        // echo "<pre>";
        // print_r($sql_arr);
        // echo "</pre>";

        DB::beginTransaction();
        try {
            if (!empty($sql_arr)) {
                $stats = [];
                foreach ($sql_arr as $key => $value) {
                    if (!(DB::DELETE($value))) {
                        $stats[] = 0;
                        $err = "gagal: " . $value;
                    }
                }
                if (in_array(0, $stats)) {
                    DB::commit();
                    $this->empty_folder('del', $company_id, '');
                    return response()->json([1], 200);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([0], 500);
        }
    }
    // ----------------------------------------------------------------------SYNC DELETE -------------------------------------------------


    // ------------------------------------------------------------------ GET FIRST MASTER ------------------------------------------------

    public function getFirstMaster(Request $request, $company_id, $act)
    {
        $table_name = '';
        if ($act == "getUser") {
            $table_name = 'm_userx';
        } elseif ($act == "getItem") {
            $table_name = 'm_barang';
        } elseif ($act == "getPegawai") {
            $table_name = 'm_pegawai';
        }
        // echo $table_name;

        $data = ['you are not belong here'];
        if (!empty($table_name)) {
            $sql_select = "SELECT * FROM misterkong_" . $company_id . "." . $table_name;
            $exe_sql_select = DB::select($sql_select);
            if (!empty($exe_sql_select)) {
                $data = $exe_sql_select;
            } else {
                $data = array(
                    'error' => true,
                    'status' => -1,
                    'message' => 'No Record Found'
                );
            }
        }
        return response()->json($data, 200);
    }
    // ------------------------------------------------------------------ GET FIRST MASTER ------------------------------------------------

    // ------------------------------------------------ GET PROFILE PERUSAHAAN (g_db_config) ---------------------------------------------

    public function getCompanyProfile($company_id)
    {
        $data = DB::select("SELECT * FROM misterkong_" . $company_id . ".g_db_config");
        if (!empty($data)) {
            $response = $data;
        } else {
            $response['status'] = 0;
            $response['error'] = true;
            // $response['message'] = 'Cannot Access Database, Unknown company id';
            $response['message'] = 'Result Not Found';
        }
        return response()->json($response, 200);
        // print json_encode(array('status'=>1));
    }

    // ------------------------------------------------ GET PROFILE PERUSAHAAN (g_db_config) ------------------------------------------

    // ------------------------------------------------------------------ TOTALAN STRUK ------------------------------------------------
    public function totalanStruk(Request $request, $company_id)
    {
        if (!empty($request->kd_kas) && !empty($request->awal) && !empty($request->akhir)) {
            $kd_kas = $request->kd_kas;
            $awal = $request->awal;
            $akhir = $request->akhir;
            $data = DB::select("CALL misterkong_" . $company_id . ".proc_histori_kas('$kd_kas','$awal','$akhir')");
            return response()->json($data, 200);
        } else {
            $response['status'] = 404;
            $response['error'] = true;
            $response['message'] = 'not_found';
            return response()->json($response, 404);
        }
    }

    // ------------------------------------------------------------------ TOTALAN STRUK ------------------------------------------------





}
