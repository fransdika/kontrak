<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class CRUDModel extends Model
{
    use HasFactory;

    public function getLastNumber($table,$col,$condition)
	{
		$key= implode("=? AND ", array_keys($condition))."=?";
		$val=array_values($condition);
		// DB::enableQueryLog();
		$data = DB::table($table)->whereRaw($key,$val)->max($col);
		// dd(DB::getQueryLog());
		return $data;
	}

    public function doBulkUpdateTable($data,$key,$data_delete,$master_delete)
	{
		$affected=0;
		DB::beginTransaction();
		try {
			// print_r($key[$master_delete]);
			// die();
			if (!empty($data_delete)) {
				foreach ($key as $key_delete => $value_delete) {
					if (in_array($key_delete, $data_delete)) {
					// 	print_r($value_delete);
					// 	die();
					// 	foreach ($value_delete as $key_detail_delete => $value_detail_delete) {
					// 		$affected+=DB::table($key_delete)->where($value_detail_delete)->delete();
					// 	}
						$affected+=DB::table($key_delete)->where($key[$master_delete][0])->delete();
					}
				}	
				foreach ($data as $key_data => $value_data) {
					if (in_array($key_data, $data_delete)) {
						DB::table($key_data)->insert($value_data);
					}else{
						foreach ($value_data as $key_update_key => $value_update_key) {
							$affected+=DB::table($key_data)->where($key[$key_data][$key_update_key])->update($value_update_key);
						}	
					}
					
				}
			}else{
				// echo "<pre>";
				// print_r($data);
				// echo "</pre>";
				// die();
				foreach ($data as $key_data => $value_data) {
					foreach ($value_data as $key_update_key => $value_update_key) {
						$affected+=DB::table($key_data)->where($key[$key_data][$key_update_key])->update($value_update_key);
					}
				}
			}

			

			DB::commit();
			if ($affected>0) {
				return 1;
			}else{
				return 0;
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
			DB::rollback();
			return 0;
		}
	}

    public function doBulkInsertTable($data)
	{
		DB::beginTransaction();
		try {
			DB::enableQueryLog();
			foreach ($data as $key_data => $value_data) {
				DB::table($key_data)->insert($value_data);
			}
			DB::commit();
			return 1;
		} catch (\Exception $e) {
			echo $e->getMessage();
			
			DB::rollback();
			return 0;
		}
	}

    public function generate_kode($kd,$old)
    {
        $no = $kd;
        // $dt = date("ymd");
        // if (empty($user)) {
        //     $ang = 1;
        // } else {
            $ang = substr($old, 3);
            $nomor = intval($ang) + 1;
        // }
        $urut = sprintf("%03d", $nomor);
        $no_baru = $no . $urut;
        return $no_baru;
    }
}
