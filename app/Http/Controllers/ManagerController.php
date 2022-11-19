<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Misterkong;
use App\Http\Controllers\API\AuthController;

class ManagerController extends Controller
{

    public function daftar(Request $request)
    {
        $data_user = $this->array_converter($request->data);
        $data_pegawai = $this->array_converter($request->data_pegawai);
        $nohp = $request->no_hp;
        $company_id = $request->company_id;
        $jenis = $request->jenis;
        $email = $request->email;
        $inserts = [];
        $data_manager = [];
        $data_company = [];
        if(substr($nohp, 0,1) == "0"){
            $hp = '62'.substr(trim($nohp), 1);
        }else{
            $hp = $phone;
        }
        $select = DB::table("m_userx")->join('m_user_company', 'm_user_company.kd_user', '=', 'm_userx.id')->select('m_suerx.id', 'm_user_company.kd_user', 'm_userx.nama', 'm_user_company.alamat', 'm_userx.no_hp', 'm_user_company.kd_bank', 'm_user_company.no_rek', 'm_user_company.nama_pemilik_rekening', 'm_user_company.id')->where('m_userx.no_hp', '=', $hp);
        if($select->count() > 0){
        $row = $select->get();
        foreach($row as $exe) {
            $data_manager = ['referal' => $exe->id, 'user_id' => $exe->kd_user, 'nama_pengguna'=> $data_pegawai['nama'], 'alamat' => $data_pegawai['alamat'], 'no_hp' => $hp, 'kd_bank' => $exe->kd_bank, 'no_rek' => $exe->no_rek, 'nama_pemilik_rekening' => $exe->nama_pemilik_rekening, 'no_aktif' => $hp, 'jenis' => $jenis, 'status' => 1];
        }
        DB::beginTransaction();
        try {
            if(empty($data_user['passweb'])){
                $inserts = ['kd_group' => 1, 'nama' => $data_user['nama'], 'keterangan' => '-', 'no_hp' => $hp, 'status_phone' => 1, 'email' => $email, 'status_email' => 0, 'status' => 1];
            }else {
                $inserts = ['user_id_referal' => $data_manager['referal'],'kd_group' => 1, 'nama' => $data_user['nama'], 'passwd' => $data_user['passweb'], 'keterangan' => '-', 'no_hp' => $hp, 'status_phone' => 1, 'email' => $email, 'status_email' => 0, 'status' => 1];
            }
            $insert = DB::table('m_userx')->updateOrInsert(['no_hp' => $hp], $inserts);
            $manager = DB::table('m_user_manager')->updateOrInsert(['no_hp' => $hp], $data_manager);
            $productId = DB::getPdo()->lastInsertId();
            foreach($select as $exe) {
                $data_company = ['id_company_id' => $exe->id, 'id_user_manager' => 1, 'status' => 1, 'keterangan' => '-'];
                }
            $company = DB::table('m_user_manager_company')->updateOrInsert(['id_user_manager' => $productId], $data_company);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        return response()->json(['Sucess'=>'TRUE', 'User_baru' => 'False']);
       }else{
        $getid = DB::table('m_userx')->select('id')->orderBy('id','desc')->first()->id+1;
        if(empty($data_user['passweb'])){
            $insertss = ['kd_group' => 1, 'nama' => $data_user['nama'], 'keterangan' => '-', 'no_hp' => $hp, 'status_phone' => 1, 'email' => $email, 'status_email' => 0, 'status' => 1];
        }else {
            $insertss = ['kd_group' => 1, 'nama' => $data_user['nama'], 'passwd' => $data_user['passweb'], 'keterangan' => '-', 'no_hp' => $hp, 'status_phone' => 1, 'email' => $email, 'status_email' => 0, 'status' => 1];
        }
        $insert = DB::table('m_userx')->updateOrInsert(['no_hp' => $hp], $insertss);
            if($insert == TRUE){
                $data_manager = ['user_id' => $getid, 'nama_pengguna'=> $data_pegawai['nama'], 'alamat' => $data_pegawai['alamat'], 'no_hp' => $hp, 'kd_bank' => '-', 'no_rek' => '-', 'nama_pemilik_rekening' => '-', 'no_aktif' => $hp, 'jenis' => $jenis, 'status' => 1];
                DB::beginTransaction();
                try {
                    $manager = DB::table('m_user_manager')->updateOrInsert(['no_hp' => $hp], $data_manager);
                    $productId = DB::getPdo()->lastInsertId();
                    foreach($select as $exe) {
                        $data_company = ['id_company_id' => $productId, 'id_user_manager' => 1, 'status' => 1, 'keterangan' => '-'];
                        }
                    $company = DB::table('m_user_manager_company')->updateOrInsert(['id_user_manager' => $productId], $data_company);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                }
            }
        return response()->json(['Sucess'=>'TRUE', 'User_baru' => 'TRUE']);
       }

       
   }
   public function array_converter($array)
   {
    $Y = [];
    if(!empty($array)){
        $update = str_replace($array[0], '', $array);
        $exe = explode(',', substr($update, 0, -1));
        foreach($exe as $data) {
            $x = explode('=', $data);
            $Y[trim($x[0])] = $x[1];
        }
    }
    return $Y;
   }

   public function login(Request $request)
   {
    $data = [];
    $input = $request->mn;
    $password = $request->dp;
    $where = ['input' => $input, 'passwd' => $password, 'status' => 1];
    $res = strpos($input, '@');
    $where['loginby'] = ($res == false) ? "phone" : "email";
    $model = new Misterkong();
    // dd(\DB::getQueryLog());
    $respon = $model->login($where);
    // dd(\DB::getQueryLog());
    
    
    //hana punya
    $authC = new AuthController;
    $auth_response = $authC->login_pos($request)->getData();
    if (!empty($auth_response->access_token)) {
        $respon[0]['token']=$auth_response->access_token;
    }
    return response()->json($respon);

   }

   public function pencarian(Request $request)
   {
    $company_id = $request->company_id;
    $phone = $request->no_hp;
    if(substr($phone, 0,1) == "0"){
        $hp = '62'.substr(trim($phone), 1);
    }else{
        $hp = $phone;
    }
    $array = [];
    $array = [$hp, $company_id];
    // DB::enableQueryLog();
    $prosedures = DB::select('call checkUserByPhone(?,?)', $array);
    // dd(\DB::getQueryLog());
    foreach($prosedures as $exe) {
        $data = $exe->result;
    }
    if (!empty($data)) {
        if($data == 2){
            return response()->json(['Sucess'=>'TRUE', 'message' => 'Telah Digunakan', 'result' => $data, 'data' => $prosedures]);
        }else{
            return response()->json(['Sucess'=>'TRUE', 'message' => 'Sudah Terdaftar', 'result' => $data, 'data' => $prosedures]);
        }
    }else{
        return response()->json(['Sucess'=>'TRUE', 'message' => 'blum terdaftar', 'result' => 0, 'data' => []]);
    }
    
   }
}