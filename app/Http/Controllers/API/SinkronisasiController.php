<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SinkronisasiController extends Controller
{
    public function convert_to_json_mode2(Request $request, $company_id,$imei)
    {
        $last_request=(!empty($request->last_request_time))?$request->last_request_time:'2018-00-00 00:00:00';
        $json_no_dt = file_get_contents(base_path('public/sync/table_with_date_modif.json'));

        $table_name_no_dt = json_decode($json_no_dt, true);
        foreach ($table_name_no_dt as $key => $value) {
            $list_table[]=$key;
        }
        foreach ($list_table as $key => $value) {
            if (!preg_match('/t_/', $value)) {
                // $sql_get_data="SELECT $value.* FROM $value INNER JOIN x_last_time_android_get a WHERE date_modif> a.last_success_time";
                // echo $this->get_last_request();
                // $this->empty_folder();
                // echo app_path();
                $sql_get_data="SELECT $value.* FROM misterkong_".$company_id.".$value WHERE date_modif >='".$request->get_last_request."'";
                // echo $sql_get_data;
                $exe_get_data=db::select($sql_get_data);
                if (count($exe_get_data)) {
                    // echo $value;
                    // $file_json = fopen("../pr_multi_db/back_end_mp/".$company_id."_config/POST/".$imei."/".$value.".json", "w+");
                    $path="../../pr_multi_db/back_end_mp/".$company_id."_config/POST/".$imei."/".$value.".json"; //local
                    // $path="../../../public_html/back_end_mp/".$company_id."_config/POST/".$imei."/".$value.".json"; //vps
                    $file_json = fopen($path, "w+");
                    fclose($file_json);
                    $file_path = $path;
                    file_put_contents($file_path, "");


                    $arr_data_prepare=array();
                    foreach ($exe_get_data as $key => $value) {
                        $arr_data_prepare[]=$value;
                    }
                    // while ($row_get_data=$exe_get_data->fetch_assoc()) {
                    //     $arr_data_prepare[]=$row_get_data;
                    // }
                    $keys=(array)$arr_data_prepare[0];
                    $kolom=array_keys($keys);
                    // print_r($kolom);
                    $json_data=array();
                    // for ($i=0; $i < count($kolom); $i++) { 
                    foreach ($kolom as $key_table => $value_key) {
                        foreach ($arr_data_prepare as $key => $value_rec) {
                            // echo "<pre>";
                            // print_r($value_rec->$value_key);
                            // echo $kolom[$i];
                            // echo "</pre>";
                            // die();
                            $json_data[$value_key][]= $value_rec->$value_key;
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
    }
    public function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(scandir($dir)) == 2);
    }
    public function syncDelete(Request $request)
    {
        $company_id=$request->company_id;

        $dir="../../pr_multi_db/back_end_mp/".$company_id."_config/DEL"; //local
        // $dir=__DIR__;
        $hideName = array('.', '..');
        // echo $dir;
        // die();


        //get_json_file_name
        $files_name=[];
        $json=[];
        if ($this->is_dir_empty($dir)) {
            echo "the folder is empty";
        } else {
            $files = array_diff(scandir($dir,SCANDIR_SORT_DESCENDING), array('..', '.'));
            foreach ($files as $file) {
                if (!in_array($file, $hideName)) {

                    $files_name[] = $file;
                }
            }
        }
        // print_r($files_name);
        // die();

        //json_proccessing
        foreach ($files_name as $key => $value) {
            $tbl_name_tmp = explode('__', $value);
            $str = file_get_contents($dir.'/' . $value);
            if (!empty($str)) {
                $json[$tbl_name_tmp[0]][] = json_decode($str, true);
            }
        }

        //execute_query_delete
        $keys=array_keys($json);
        $i=0;
        $sql_arr=array();
        foreach ($keys as $key_table => $value_table) {
            foreach ($json as $key_column => $value_column) {
                // print_r($value_column);
                $dt_stats=$value_column['detail'];
                $sql="DELETE FROM $value_table WHERE ";
                $col_keys=array_keys($value_column['key']);
                $primary_key=array();
                foreach ($col_keys as $key_val => $value_val) {

                    $primary_key[]=$value_val."='".$value_column['key'][$value_val]."'";
                }
                $sql.=implode(' AND ', $primary_key);
                if ($dt_stats) {
                    $sql_arr[]="DELETE FROM ".$value_table."_detail WHERE ".implode(' AND ', $primary_key);
                }
                $sql_arr[]=$sql;
            }
            $i++;
        }
        // echo "<pre>";
        // print_r($sql_arr);
        // echo "</pre>";

        DB::beginTransaction();
        try {
            if (!empty($sql_arr)) {
                $stats=[];
                foreach ($sql_arr as $key => $value) {
                    if (!(DB::DELETE($value))) {
                        $stats[]=0;
                        $err="gagal: ".$value;
                        throw new Exception($err);
                    }
                }
                if (in_array(0, haystack) {
                    DB::commit();
                    $this->empty_folder($dir);
                    return response()->json([1],200);

                }
            }


        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([0], 500);
        }
    }

    



}
