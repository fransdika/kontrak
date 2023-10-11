<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Utilites extends Controller
{
    public function AlterDb()
    {
        return view('alterDb');
    }

    public function query_all_db(Request $request)
    {
        if (md5($request->password) == '637b9adadf7acce5c70e5d327a725b13') {
            $sql = DB::select("SELECT schema_name FROM INFORMATION_SCHEMA.SCHEMATA WHERE schema_name LIKE '%misterkong_comp%'");
            $var_brg = [];
            foreach ($sql as $key => $value) {
                DB::select("use $value->schema_name");
                $var = explode(";;",$request->query_sql);
                foreach ($var as $key_var => $value_var) {
                    $var_brg = DB::select("$value_var");
                }
            }
            // return response()->json($var_brg);
            return response()->json([
                "status" => 1,
                "error" => 0,
                "Pesan" => "Berhasil eksekusi query",
                "data" => []
            ], 200);
        } else {
            return response()->json([
                "status" => 0,
                "error" => 500,
                "Pesan" => "Password anda salah",
                "data" => []
            ], 200);
        }
        

    }
}
