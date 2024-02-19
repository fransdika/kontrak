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
        $data_company['company']=DB::table('m_user_company')->get();
        return view('alterDb',$data_company);
    }

    public function query_all_db(Request $request)
    {
        if (md5($request->password) == '637b9adadf7acce5c70e5d327a725b13') {
            $sql = DB::select("SELECT schema_name FROM INFORMATION_SCHEMA.SCHEMATA WHERE schema_name LIKE '%misterkong_comp%'");
            $var_brg = [];
            foreach ($sql as $key => $value) {
                DB::select("use $value->schema_name");
                $var = explode(";;", $request->query_sql);
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

    public function generateRandomString($length)
    {
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

        $diff = array_diff($uniqueStrings, $excludeChars);

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
            DB::table('misterkong_' . $request->company_id . '.m_list_mambo')->insert($data_save);
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
    public function execMultiQuery(Request $request)
    {
        $sql = $request->post('query');
        // print_r($sql);

        $db_list = DB::select("SELECT schema_name FROM INFORMATION_SCHEMA.SCHEMATA WHERE schema_name LIKE '%misterkong_comp%'");
        $var_brg = [];
        
        DB::beginTransaction();
        try {
            $err = [];
            foreach ($db_list as $key => $value) {
                DB::select("use $value->schema_name");
                $exe = DB::unprepared($sql);
                if (!$exe) {
                    $err[] = $value->schema_name;
                }
            }
            if (empty($err)) {
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => false,
                    'message' => 'Berhasil generate query'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => true,
                'message' => 'Gagal Menghapus Data'.$e->getMessage()
            ]);
        }
    }
    public function loadJunk()
    {
        $db_list['db']=DB::select("SELECT db_name,nama_usaha FROM misterkong_mp.m_user_company");
        return view('utilities/remove_junk',$db_list);
    }
    function removeJunk(Request $request){
        $database=$request->db_select;
        foreach ($database as $key => $value) {
            $query="DROP DATABASE $value";
            $data=DB::table('misterkong_mp.m_user_company')->select('kd_user')->where(['db_name'=>$value])->first()->kd_user;
            print_r($data);
            // $execute=DB::select($query);
            // $execute2=DB::select("DELETE FROM misterkong_mp.m_user_company WHERE db_name='$value'");
            // $execute3=DB::select("DELETE FROM misterkong_mp.m_userx  WHERE id='$data'");
            // // echo $this->remove_folder($value);
            // if ($execute && $execute2 && $execute3) {
            //     $sts[]=1;
            // }else{
            //     $sts[]=0;
            // }
        }
        die();
        if (in_array(0, $sts)) {
            return false;
        }else{
            if ($this->remove_folder($database)) {
                return true;
            }else{
                return false;
            }
            
        }
    }
    function remove_folder($db_name){

        // $path_name='../../back_end_mp/'.str_replace('misterkong_', '', $db_name).'_config';
        // $path_name="/home/ssid/public_html/misterkong/back_end_mp/".str_replace('misterkong_', '', $db_name).'_config';
        // $dir = basename('/../').'/'.str_replace('misterkong_', '', $db_name).'_config';
        // $path_name=basename("/back_end_mp");
        // echo getcwd();
        $path_name=__DIR__;
        $dir='';
        // $dir =dirname(__DIR__)."/".str_replace('misterkong_', '', $db_name).'_config/';
        foreach ($db_name as $key => $value) {
            $dir .=dirname(__DIR__)."/".str_replace('misterkong_', '', $value).'_config/ ';
            
        }
        // $dir =dirname(__DIR__)."/test";
        // echo $dir;

        // echo 'rm -rf ' .$dir;
        // $command = escapeshellcmd('rm -rf ' .$dir);

        $output = shell_exec('rm -rf ' .$dir);
        if ($output) {
            return "false";
        }else{
            return "true";
        }

        // echo $output;
        // $files = glob($dir . '/*');
        // foreach ($files as $file) {
        //  is_dir($file) ? removeDirectory($file) : unlink($file);
        // }
        // rmdir($dir);

        // return;
        // print_r(scandir($dir)) ;
        // if(!rmdir($dir)) {
            // echo ("Could not remove $path");
        // }
        // removeDirectory('$dir');
        // foreach(scandir($dir) as $file) {
            // echo $file;
            // if ('.' === $file || '..' === $file) continue;
            // if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
            // else unlink("$dir/$file");
        // }

        // return rmdir($path_name);
    } 

}
