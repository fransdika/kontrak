<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SinkronisasiModel extends Model
{
    use HasFactory;
    function convertToQuery($table, $data){
        $key_col=array_keys((array)$data[0]);
        $col=implode(',', $key_col);
        $sql="INSERT INTO $table($col) VALUES";
        $values=[];
        foreach ($data as $key_data => $value_data) {
            $row_data=implode("','", (array)$value_data);
            $values[]="('$row_data')";
        }
        $on_conflict=[];
        foreach ($key_col as $key_kolom => $value_kolom) {
            $on_conflict[]="$value_kolom = EXCLUDED.$value_kolom";
        }
        $sql.=implode(',', $values)." ON CONFLICT ".implode(',', $key_col)." DO UPDATE SET ".implode(',', $on_conflict);

        return $sql;
    }
}
