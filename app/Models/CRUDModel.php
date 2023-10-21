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
}
