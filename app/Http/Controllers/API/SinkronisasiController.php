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

}
