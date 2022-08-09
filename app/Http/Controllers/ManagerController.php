<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{

    public function daftar(Request $request)
    {
        $data_user = $this->array_converter($request->data);
        $data_pegawai = $this->array_converter($request->data_pegawai);
        $nohp = $request->no_hp;
        $company_id = $request->company_id;
        $jenis = $request->jenis;
        $inserts = [];
        $data_manager = [];
        $data_company = [];
        $inserts = ['kd_group' => 1, 'nama' => $data_user['nama'], 'passwd' => $data_user['password'], 'keterangan' => '-', 'no_hp' => $nohp, 'status_phone' => 1, 'email' => $data_user['email'], 'status_email' => 0, 'status' => 1];
        $select = DB::table("m_userx")->join('m_user_company', 'm_user_company.kd_user', '=', 'm_userx.id')->select('m_user_company.kd_user', 'm_userx.nama', 'm_user_company.alamat', 'm_userx.no_hp', 'm_user_company.kd_bank', 'm_user_company.no_rek', 'm_user_company.nama_pemilik_rekening', 'm_user_company.id')->where('m_user_company.company_id', '=', $company_id)->get();
        if(!empty($select)){
        foreach($select as $exe) {
            $data_manager = ['user_id' => $exe->kd_user, 'nama_pengguna'=> $data_pegawai['nama'], 'alamat' => $data_pegawai['alamat'], 'no_hp' => $nohp, 'kd_bank' => $exe->kd_bank, 'no_rek' => $exe->no_rek, 'nama_pemilik_rekening' => $exe->nama_pemilik_rekening, 'no_aktif' => $nohp, 'jenis' => $jenis, 'status' => 1];
        }
        DB::beginTransaction();
        try {
            $insert = DB::table('m_userx')->updateOrInsert(['no_hp' => $nohp], $inserts);
            $manager = DB::table('m_user_manager')->updateOrInsert(['no_hp' => $nohp], $data_manager);
            $productId = DB::getPdo()->lastInsertId();
            foreach($select as $exe) {
                $data_company = ['id_company_id' => $exe->id, 'id_user_manager' => 1, 'status' => 1, 'keterangan' => '-'];
                }
            $company = DB::table('m_user_manager_company')->updateOrInsert(['id_user_manager' => $productId], $data_company);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        return response()->json(['Sucess'=>'TRUE']);
       }else{
        $insert = DB::table('m_userx')->updateOrInsert(['no_hp' => $nohp], $inserts);
            if($insert == TRUE){
                DB::beginTransaction();
                try {
                    $manager = DB::table('m_user_manager')->updateOrInsert(['no_hp' => $nohp], $data_manager);
                    foreach($select as $exe) {
                        $data_company = ['id_company_id' => $exe->id, 'id_user_manager' => 1, 'status' => 1, 'keterangan' => '-'];
                        }
                    DB::table('m_userx')->updateOrInsert(['id_company_id' => $manager->id], $data_company);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                }
            }
        return response()->json(['Sucess'=>'FALSE']);
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
}
