<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\m_api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $dt = m_api::cekAcc($hp, $email);
        return response()->json($dt);
    }

    public function open_new_store()//waiting
    {

    }

    private function kirimNotifAdmin($data)
    {
        $payload = ["nama" => $data["nama"], "alamat" => $data["alamat"], "nama_usaha" => $data["nama_usaha"], "kategori" => $data["kategori"]];
        $headers = ["Authorization: Bearer eyJhbGciOiJIUzM4NCJ9.eyJSb2xlIjoiQWRtaW4iLCJJc3N1ZXIiOiJJc3N1ZXIiLCJVc2VybmFtZSI6IkphdmFJblVzZSIsImV4cCI6MTY1NDIyMTcwMCwiaWF0IjoxNjU0MjIxNzAwfQ.5kKHPyVJEodXFwGDnbQ6aJk4GA6WtPsaDiUcr8Y1oC-_yiqQCZpeVH8mYz00TSc", 'Content-type: application/json'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://misterkong.com/kong_api/notification/api/telegram_pos');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function registration_pos_ph(Request $req)//waiting
    {
        $data = null;

        $data_cust_qb = [
            "id" => 0,
            "kd_user" => "",
            "kd_group" => 1,
            "nama" => $req->name,
            "email" => $req->email,
            "passwd" => $req->pass,
            "keterangan" => "-",
            "no_hp" => $this->format_phone($req->phone),
            "jenis_user" => 1,
            "date_add" => date('Y-m-d H:i:s'),
            "date_modif" => date('Y-m-d H:i:s'),
            "status_email" => 0,
            "status" => 1,
            "status_phone" => 1
        ];
        $data[] = $data_cust_qb;

        $data_usaha_qb = [
            "id" => 0,
            "company_id" => "",
            "db_name" => "",
            "kd_user" => 0,
            "nama_usaha" => $req->str_nm,
            "nickname_usaha" => $req->str_nick,
            "no_telepon" => $this->format_phone($req->str_ph),
            "email_usaha" => $req->str_email,
            "kategori_usaha" => $req->str_catgr,
            "alamat" => $req->str_addr,
            "kd_desa" => $req->str_vil,
            "kd_kecamatan" => $req->str_dist,
            "kd_kabupaten" => $req->str_cty,
            "kd_provinsi" => $req->str_prov,
            "koordinat_lat" => $req->lat,
            "koordinat_lng" => $req->lng,
            "date_add" => date('Y-m-d H:i:s'),
            "date_modif" => date('Y-m-d H:i:s'),
            "user_id" => 0,
            "kd_bank" => $req->kd_bank,
            "no_rek" => $req->no_rek,
            "nama_pemilik_rekening" => $req->pemilik_rekening
        ];
        $data[] = $data_usaha_qb;
//        array_push($data, $data_usaha_qb);

        $respon = m_api::register_ph($data);

        if ($respon[0]['error'] == 0) {

            $data_profile = [
                'nama_toko' => $req->str_nm,
                'alamat' => $req->str_addr,
                'kota' => $respon[0]['kota'], // kota
                'telp' => $this->format_phone($req->str_ph),
                'hp' => $req->str_ph,
                'email' => $req->str_email,
                'nama_kontak' => $req->name,
                'no_rekening' => $req->no_rek,
                'nama_rek' => $req->pemilik_rekening,
                'nama_bank' => 'Bank ' . $respon[0]['bank'], // nama bank
                'email_pencairan' => $req->str_email,
                'koordinat_toko' => $req->lat . ',' . $req->lng,
                'gmt' => $respon[0]['gmt'], // gmt
                'profile_tag' => '',
                'comp_profile_img' => '-',
//                'header1' => '-',
//                'header2' => '-',
//                'header3' => '-',
//                'footer1' => '-',
//                'footer2' => '-',
//                'footer3' => '-',
//                'kategori_usaha' => $req->str_nama_kategori,
//                'cabang_bank' => $req->cabang_bank,
            ];

            $this->kirimNotifAdmin(["nama" => $_GET["name"], "alamat" => $_GET['str_addr'], "nama_usaha" => $_GET['str_nm'], "kategori" => $_GET['str_nama_kategori']]);
            $this->generate($respon[0]['company_id'], "misterkong_" . $respon[0]['company_id'], $respon[0]['id'], $data_cust_qb['nama'], $data_cust_qb['passwd'], $data_profile);

            $kirim = array(
                "msg" => "<h3>Aktivasi Akun Misterkong-mu!</h3>
			     <p>Terimakasih sudah mau bergabung dengan MisterKong! Sebelum kamu memulai, tolong verifikasi email yang kamu gunakan agar bisa masuk ke dalam Misterkong pada tautan berikut " . $respon[0]['link'] . "</p>",
                "dest" => $_GET['email']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://ssid.solidtechs.com/all_api/Send_email.php");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $kirim);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($output, true);
        }
        echo json_encode($respon);
    }

    public function verify_reset_password_otp(Request $req)
    {
        $ph = $req->phone;
        $new_pass = $req->new_pass;
        $data = [
            "no_hp" => $this->format_phone($ph),
            "passwd" => $new_pass,
            "date_modif" => date('Y-m-d H:i:s')
        ];

        $update = m_api::reset_pass($data, "no_hp");

        if ($update) {
            echo json_encode([
                "error" => 0,
                "pesan" => "password berhasil di reset!"
            ]);
        } else {
            echo json_encode([
                'error' => 1,
                'pesan' => 'Gagal mengubah Password'
            ]);
        }
    }

    public function resend_otp_phone(Request $req)
    {
        $tujuan = trim($req->phone);
        $from = ""; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
        $apikey = "dd4cfd6168564ae033110fa7ec0e66fd-4a8acf79-b3da-4063-8368-b8c9d124eb48"; //get your API KEY from our sms dashboard
        $postUrl = "https://api.smsviro.com/restapi/sms/1/text/advanced"; # DO NOT CHANGE THIS


        if (substr($tujuan, 0, 2) == "62" || substr($tujuan, 0, 3) == "+62") {
        } elseif (substr($tujuan, 0, 1) == "0") {
            $tujuan[0] = "X";
            $tujuan = str_replace("X", "62", $tujuan);
        } else {
            echo "Invalid mobile number format";
        }

        $destination = ["to" => $tujuan];
        $otp = rand(1000, 9999);
        $message = [
            "from" => $from,
            "destinations" => $destination,
            "text" => "<#> KONGPOS Kode OTP anda adalah " . $otp . ", jangan pernah memberitahukan kode otp ini kepada siapapun"
        ];

        $cekOtpAttemps = DB::selectOne(DB::raw("CALL misterkong_db_all_histori.get_request_otp_kongpos($tujuan)"));
        $statusOtp = $cekOtpAttemps->status_otp;
//        mysqli_next_result($this->db->conn_id);

        $waktuRequest = date('Y-m-d H:i:s');
        $timeLimit = date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($waktuRequest)));

        if ($statusOtp == '0') {
            $reqLagi = DB::table('misterkong_db_all_histori.h_log_kongpos_otp')->where(["no_hp" => $tujuan])->get();
            echo json_encode(["waktu" => $reqLagi->time_limit, "status" => false]);
            return;
        }

        // update histori otp pos

        $simpanHistory = DB::table("misterkong_db_all_histori.h_kongpos_otp")->insert([
            "no_hp" => $tujuan,
            "imei" => "-",
            "otp" => $req->otp,
            "request_at" => $waktuRequest,
            "keterangan" => "-"
        ]);

        $updateHistory = DB::table("misterkong_db_all_histori.h_kongpos_otp")
            ->whereColumn([
                ["no_hp", "=", $tujuan],
                ["time_limit", "<", $waktuRequest]
            ])
            ->update([
                "time_request" => $waktuRequest,
                "time_limit" => $timeLimit
            ]);

        $postData = ["messages" => array($message)];
        $postDataJson = json_encode($postData);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Accept:application/json", 'Authorization: App ' . $apikey));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseBody = json_decode($response);
        curl_close($ch);

        $data = ["otp" => $otp, "waktu" => $timeLimit, "status" => true, "simpan_history" => $simpanHistory, "update_history" => $updateHistory];
        echo json_encode($data);
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
        $np = "";
        if (substr($nope, 0, 2) == "62" || substr($nope, 0, 3) == "+62") {
            $np = $nope;
        } elseif (substr($nope, 0, 1) == "0") {
            $nope[0] = "X";
            $np = str_replace("X", "62", $nope);
        }
        return $np;
    }

    function generate($compid, $namadb, $comp_id_id, $nama, $pass, $data_profile)
    {
        $dt=[
            "info_profile"=>$data_profile,
            "id_company_id"=>$comp_id_id,
            "company_id"=>$compid,
            "db_name"=>$namadb,
            "username"=>$nama,
            "pwd"=>$pass
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.pos.misterkong.com/api/generateDB");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dt));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

}
