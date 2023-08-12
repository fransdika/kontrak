<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\m_api;
use Illuminate\Http\Request;

class MkController extends Controller
{
    public function mk_dir(Request $req)
    {
        $comId = $req->comp_id;
        $imei = $req->imei;
        if (!file_exists("./back_end_mp/" . $comId . "_config/POST/" . $imei)) {
            mkdir("./back_end_mp/" . $comId . "_config/POST/" . $imei, 0777, true);
        }

        if (!file_exists("./back_end_mp/" . $comId . "_config/GET/" . $imei)) {
            mkdir("./back_end_mp/" . $comId . "_config/GET/" . $imei, 0777, true);
        }
    }

    //GET KABUPATEN
    public function get_district($prov)
    {
        $dt = m_api::get_city($prov);
        return response()->json($dt, 200);
    }

    public function get_subdistrict($city)
    {
        $dt = m_api::get_subdis($city);
        return response()->json($dt, 200);
    }

    public function get_category_store()
    {
        $dt = m_api::getKatToko();
        return response()->json($dt);
    }

    //GET PROVINSI
    public function get_prov()
    {
        $dt = m_api::get_prov();
        return response()->json($dt);
    }

    public function get_bank()
    {
        $dt = m_api::get_bank();
        return response()->json($dt);
    }

    public function cekPhone(Request $req)
    {
        $hp = $this->format_phone($req->phone);
        $email = $req->email;
        $dt=m_api::cekAcc($hp,$email);
        return response()->json($dt);
    }

    public function open_new_store()
    {

    }

    public function registration_pos_ph()
    {

    }

    public function verify_reset_password_otp()
    {

    }

    public function resend_otp_phone()
    {

    }

    function send_email_again(Request $req)
    {
        $dest = $req->dest;
        $link = url() . "register/activate/?&type=acc&nick=" . m_api::get_id_user($dest);

        $kirim = [
            "msg" => "<h3>Aktivasi Akun Misterkong-mu!</h3>
			    <p>Terimakasih sudah mau bergabung dengan MisterKong! Sebelum kamu memulai, tolong verifikasi email yang kamu gunakan agar bisa masuk ke dalam Misterkong pada tautan berikut " . $link . "</p>",
            "dest" => $dest
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ssid.solidtechs.com/all_api/Send_email.php");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $kirim);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        echo json_encode($output);
    }

    function format_phone($nope)
    {
        $np="";
        if (substr($nope, 0, 2) == "62" || substr($nope, 0, 3) == "+62") {
            $np = $nope;
        } elseif (substr($nope, 0, 1) == "0") {
            $nope[0] = "X";
            $np = str_replace("X", "62", $nope);
        }
        return $np;
    }

}
