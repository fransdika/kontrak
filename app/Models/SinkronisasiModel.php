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
}
