<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Return_;

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

    public function generateMamboCodePage()
    {
        return view('utilities/mamboCode');
    }

    public function generateRandomString($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[ord(random_bytes(1)) % strlen($characters)];
        }

        return $randomString;
    }
    
    // public function generateCode(Request $request)
    // {
        
    // }

    public function doGenerateMamboCode(Request $request)
    {
        $kd_mambo = DB::select("SELECT kd_mambo FROM misterkong_$request->company_id.m_list_mambo");
        $excludeChars = [];
        foreach ($kd_mambo as $key => $value) {
            $excludeChars[] = $value->kd_mambo;
        }
        // $excludeChars = ['a123eetk', 'b149jd5v'];
        // $char = '';

        // while ($a <= 50000) {
        //     $char .= $this->generateRandomString(8); 
        //     if (in_array($char, $char)) {
        //         $char .= $this->generateRandomString(8); 
        //     }
        //     $a++;
        // }

            $uniqueStrings = [];
            $a = 0;

            while ($a < $request->jumlah) {
                $randomString = $this->generateRandomString(8);
                if (!in_array($randomString, $uniqueStrings)) {
                    $uniqueStrings[] = $randomString;
                }
                $a++;
            }

            $diff = array_diff($uniqueStrings,$excludeChars);

            if (count($diff) < $request->jumlah) {
                $b = 0;
                while ($b < ($request->jumlah - count($diff))) {
                    $randomString = $this->generateRandomString(8);
                    if (!in_array($randomString, $uniqueStrings)) {
                        $uniqueStrings[] = $randomString;
                    }
                    $b++;
                }
            } else {
                foreach ($uniqueStrings as $key => $value) {
                    $data_save[] = [
                        'kd_mambo' => $value,
                        'status' => 1
                    ];
                }
                // DB::insert("INSERT INTO misterkong_$request->company_id.m_list_mambo (kd_mambo,`status`) VALUES('$value',1)");
                DB::table('misterkong_'.$request->company_id.'.m_list_mambo')->insert($data_save);
            }
            return count($diff);




        // do {
        //     $char = $this->generateRandomString(8); 
        // } while (in_array($char, $excludeChars));
        
        

        // for ($i=0; $i < 50000; $i++) { 
        //     $char .= $this->generateRandomString(8);
        // }
        // DB::insert("INSERT INTO m_list_mambo (kd_mambo,`status`) VALUES()")
        // return $char;
    }
}
