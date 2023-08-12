<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class m_api extends Model
{
    use HasFactory;

    public static function get_prov()
    {
        $data = null;
        $exec = DB::select("SELECT id,province as prov FROM misterkong_mp.m_province");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public function get_bank()
    {
        $data = null;
        $exec = DB::select("SELECT * FROM misterkong_mp.m_bank");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public function getKatToko()
    {
        $data = null;
        $exec = DB::select("SELECT kd_kategori_usaha as `id`,nama as kategori FROM misterkong_mp.m_kategori_usaha WHERE `status`=1");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public function get_city($prov)
    {
        $data = null;
        $exec = DB::select("SELECT city_id as `id`,city_name as kab FROM misterkong_mp.m_city WHERE province_id=$prov");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public function get_subdis($city)
    {
        $data = null;
        $exec = DB::select("SELECT subdis_name as kec,id FROM misterkong_mp.m_subdis WHERE city_id=$city");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public function get_id_user($email)
    {
        $exec = DB::select("SELECT id FROM misterkong_mp.m_userx WHERE email='$email'");
        $id = $exec['id'];
        return $id;
    }

    public function cekAcc($ph, $em)
    {
//        $msg = [];
        $isPh = DB::table("m_userx")->where("no_hp", "=", $ph)->count() > 0 ? true : false;
        $isEm = false;

        if ($em != "") {
            $isEm = DB::table("m_userx")->where("email", "=", $em)->count() > 0 ? true : false;
        }

        $msg = ["sukses" => 1];

        if ($isPh && $isEm) {
            $msg = ["sukses" => 0, "msg" => "No. Hp dan Email sudah digunakan"];
        }

        if ($isEm) {
            $msg = ['sukses' => 0, 'msg' => "Email sudah digunakan"];
        }
        if ($isPh) {
            $msg = ['sukses' => 0, 'msg' => "No. Hp sudah digunakan"];
        }

        return $msg;
    }
}
