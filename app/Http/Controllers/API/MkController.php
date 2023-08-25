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

    public function open_new_store(Request $req)
    {

        $data = null;
        $no_hp = $req->email;
        $no_hp_new = substr($no_hp, 0, 1) == 0 ? "62" . substr($no_hp, 1) : $no_hp;
        $data[] = $no_hp_new;

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

        $respon = m_api::open_new_store($data);

        if ($respon[0]['error'] == 0) {
            $data_profile = [
                'nama_toko' => $req->str_nm,
                'alamat' => $req->str_addr,
                'kota' => $respon[0]['kota'], // kota
                'telp' => $this->format_phone($req->str_ph),
                'hp' => $req->str_ph,
                'email' => $req->str_email,
                'nama_kontak' => $req->pemilik_rekening,
                'no_rekening' => $req->no_rek,
                'nama_rek' => $req->pemilik_rekening,
                'nama_bank' => 'Bank ' . $respon[0]['bank'], // nama bank
                'email_pencairan' => $req->str_email,
                'koordinat_toko' => $req->lat . ',' . $req->lng,
                'gmt' => $respon[0]['gmt'], // gmt
                'profile_tag' => '',
                'comp_profile_img' => '-',
                'kategori_usaha' => $req->str_nama_kategori,
                'cabang_bank' => $req->cabang_bank,
            ];

            $nama_pemilik_usaha = DB::table('m_userx')->select("nama")->where('no_hp', $no_hp_new)->first();

            $this->kirimNotifAdmin(["nama" => $nama_pemilik_usaha->nama, "alamat" => $req->str_addr, "nama_usaha" => $req->str_nm, "kategori" => $req->str_nama_kategori]);
            $this->generate($respon[0]['company_id'], "misterkong_" . $respon[0]['company_id'], $respon[0]['id'], $respon[0]['nama'], $respon[0]['password'], $data_profile);
            // return;
        }

        return response()->json($respon);
    }

    private function kirimNotifAdmin($data)
    {
        $payload = ["nama" => $data["nama"], "alamat" => $data["alamat"], "nama_usaha" => $data["nama_usaha"], "kategori" => $data["kategori"]];
        $headers = ["Authorization: Bearer eyJhbGciOiJIUzM4NCJ9.eyJSb2xlIjoiQWRtaW4iLCJJc3N1ZXIiOiJJc3N1ZXIiLCJVc2VybmFtZSI6IkphdmFJblVzZSIsImV4cCI6MTY1NDIyMTcwMCwiaWF0IjoxNjU0MjIxNzAwfQ.5kKHPyVJEodXFwGDnbQ6aJk4GA6WtPsaDiUcr8Y1oC-_yiqQCZpeVH8mYz00TSc", 'Content-type: application/json'];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://misterkong.com/kong_api/notification/api/telegram_pos',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => response()->json($payload)
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public function registration_pos_ph(Request $req)
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
            ];

            $this->kirimNotifAdmin(["nama" => $_GET["name"], "alamat" => $_GET['str_addr'], "nama_usaha" => $_GET['str_nm'], "kategori" => $_GET['str_nama_kategori']]);
            $this->generate($respon[0]['company_id'], "misterkong_" . $respon[0]['company_id'], $respon[0]['id'], $data_cust_qb['nama'], $data_cust_qb['passwd'], $data_profile);

            $kirim = array(
                "msg" => "<h3>Aktivasi Akun Misterkong-mu!</h3>
                     <p>Terimakasih sudah mau bergabung dengan MisterKong! Sebelum kamu memulai, tolong verifikasi email yang kamu gunakan agar bisa masuk ke dalam Misterkong pada tautan berikut " . $respon[0]['link'] . "</p>",
                "dest" => $_GET['email']
            );

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://ssid.solidtechs.com/all_api/Send_email.php",
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $kirim,
                CURLOPT_RETURNTRANSFER => 1
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
        return response()->json($respon);
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
            echo response()->json([
                "error" => 0,
                "pesan" => "password berhasil di reset!"
            ]);
        } else {
            echo response()->json([
                'error' => 1,
                'pesan' => 'Gagal mengubah Password'
            ]);
        }
    }

    public function check_ph(Request $req)
    {
        $input = $req->mn;
        $password = $req->dp;
        $where = [
            'input' => $input,
            'passwd' => $password,
            'status' => 1
        ];
        $res = strpos($input, "@");
        $where['loginby'] = ($res === false) ? "phone" : "email";
        $respon = m_api::check_ph($where);
        return response()->json($respon);
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

        $cekOtpAttemps = DB::selectOne("CALL misterkong_db_all_histori.get_request_otp_kongpos($tujuan)");
        $statusOtp = $cekOtpAttemps->status_otp;
        //        mysqli_next_result($this->db->conn_id);

        $waktuRequest = date('Y-m-d H:i:s');
        $timeLimit = date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($waktuRequest)));

        if ($statusOtp == '0') {
            $reqLagi = DB::table('misterkong_db_all_histori.h_log_kongpos_otp')->where(["no_hp" => $tujuan])->get();
            return response()->json(["waktu" => $reqLagi->time_limit, "status" => false]);
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

        $postDataJson = response()->json(["messages" => array($message)]);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $postUrl,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Accept:application/json", 'Authorization: App ' . $apikey),
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postDataJson,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = ["otp" => $otp, "waktu" => $timeLimit, "status" => true, "simpan_history" => $simpanHistory, "update_history" => $updateHistory];
        return response()->json($data);
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
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://ssid.solidtechs.com/all_api/Send_email.php",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $kirim,
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $output = curl_exec($ch);
        curl_close($ch);

        return response()->json($output);
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
        $dt = [
            "info_profile" => $data_profile,
            "id_company_id" => $comp_id_id,
            "company_id" => $compid,
            "db_name" => $namadb,
            "username" => $nama,
            "pwd" => $pass
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.pos.misterkong.com/api/generateDB",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => response()->json($dt),
            CURLOPT_RETURNTRANSFER => 1
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public static function update_penjualan_order($compId, $no_order, $status)
    {
        DB::table('misterkong_' . $compId . '.t_penjualan_order')->where("no_order",$no_order)->update(["status"=>$status]);
    }

    static function http_request($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch,[
                CURLOPT_URL=>$url,
                CURLOPT_RETURNTRANSFER=>1
            ]
        );
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function notifRider($idPesanan, $idRider)
    {
        $destinasi = [
            'ios' => [
                'to' => '/topics/ios_general',
                'headers' => [
                    "authorization:key=AAAAFLVl2_0:APA91bG9ce3PpSlf4cRjbbRIglt-6JsK_IcwxpXXkwC2oingJDVFSxncZ8PY3bNbfR8aZsIiq51nzQACLdhMQm1c7rTJciH_owB6mVUSM3gsrNc-ft0BxIluO6oEBN5-M1-GwNZBbADC",
                    'Content-Type: application/json'
                ]
            ],
            'android' =>  [
                'to' => '/topics/kongPesan',
                'headers' => [
                    'Authorization:key=AAAAJrZwZQg:APA91bEp4BYq1kZcVwUyuh02a_s5F3txxf_CJHNbvdwsdjs6qwdHuWIiS3BKN7ETR3gtQkVZgHebKCH4C6N-QaHeJTEC5m8pMT0MDD5i6oG2bqPwbPT3XR3dY9h_zku1TtamNt9_Tn9q',
                    'Content-Type: application/json'
                ]
            ],
        ];

        foreach ($destinasi as $key => $value) {

            $payload = array(
                'to' => $value['to'],
                'priority' => 'high',
                "mutable_content" => true,
                'data' => [
                    'idPesanan' => $idPesanan,
                    'id_dr' => $idRider,
                    'batal' => "1",
                    'isipesan' => "pesan dibatalin sama toko",
                    "jenis_notif" => 7
                ],
            );

            $ch = curl_init();
            curl_setopt_array($ch,[
                CURLOPT_URL=> 'https://fcm.googleapis.com/fcm/send',
                CURLOPT_POST=> true,
                CURLOPT_HTTPHEADER=> $value['headers'],
                CURLOPT_RETURNTRANSFER=> true,
                CURLOPT_SSL_VERIFYPEER=> false,
                CURLOPT_POSTFIELDS=> response()->json($payload)
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }
    public static function notifPos(Request $req)
    {
        $jenis=$req->jenis;
        $id_driver = $req->idDriver;
        $idpesanan = $req->idPesanan;
        $compId = m_api::companyid($idpesanan);
        $noTransaksi =DB::selectOne("SELECT no_transaksi FROM t_penjualan WHERE id = '$idpesanan'");
        switch ($jenis) {
            case '3':
                $body = "dibatalkan";
                break;
            case '4':
                $body = "sudah diterima";
                break;
            case '5':
                $body = "sudah siap";
                break;
            case '6':
                $body = "Nomor PIN diinput oleh Rider";
                self::update_penjualan_order($compId, $noTransaksi, 6);
                break;
            case '7':
                $body = "Rider sudah jalan menuju customer";
                self::update_penjualan_order($compId, $noTransaksi, 1);
                break;
            case '8':
                $body = "dibatalkan oleh toko";

                $cek_batal =DB::table("t_penjualan")->where(["status_barang"=>6,"id"=>$idpesanan])->count();
                if ($cek_batal == 0) {
                    DB::table("t_penjualan")->where("id",$idpesanan)->update(["status_barang","6"]);
                    self::http_request("https://misterkong.com/back_end_mp/api_misterkong/saldo/UpdateSaldo.php?no_transaksi=" . $noTransaksi . "&status=6");
                    self::notifRider($idpesanan, $id_driver);
                }
                break;
            default:
                $body = "";
                break;
        }


        $data = [
            "title" => "KONGMeal",
            "body" => "Order dengan No. Transaksi $noTransaksi $body",
            "jenis_notif" => $jenis,
            'isi' => '...',
            "comp_id" => $compId,
            "no_transaksi" => $noTransaksi,
            "imei" => $req->imei ?? "-",
            "noHp" => $req->noHp ?? "-"
        ];

        $destinasi = [
            'ios' => [
                'to' => '/topics/ios_general',
                'headers' => [
                    "authorization:key=AAAAFLVl2_0:APA91bG9ce3PpSlf4cRjbbRIglt-6JsK_IcwxpXXkwC2oingJDVFSxncZ8PY3bNbfR8aZsIiq51nzQACLdhMQm1c7rTJciH_owB6mVUSM3gsrNc-ft0BxIluO6oEBN5-M1-GwNZBbADC",
                    'Content-Type: application/json'
                ]
            ],
            'android' => [
                'to' => '/topics/kongpos',
                'headers' => [
                    'Authorization:key=AAAAf50odws:APA91bERBP6tLNfAWz_aeNhmXjbOOItI2aZ_bZEy1xNX47SWCr8LbrfNVQfuVJ8xYT7_mCFKRn6pBW7_qO-fG5qFNfIU-8nfWm1-M_zhezLK12dlsIeFi8ZfYeizEhPVQTdIbGj0DtUt',
                    'Content-Type: application/json'
                ]
            ],
        ];

        foreach ($destinasi as $key => $value) {

            $payload = [
                'to' => $value['to'],
                'priority' => 'high',
                "mutable_content" => true,
                'data' => $data
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $value['headers'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => response()->json($payload),

            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}
