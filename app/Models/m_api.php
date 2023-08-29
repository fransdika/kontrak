<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

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

    public static function get_bank()
    {
        $data = null;
        $exec = DB::select("SELECT kd_bank as `id`,nama_bank as bank FROM misterkong_mp.m_bank");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public static function getKatToko()
    {
        $data = null;
        $exec = DB::select("SELECT kd_kategori_usaha as `id`,nama as kategori FROM misterkong_mp.m_kategori_usaha WHERE `status`=1");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public static function get_city($prov)
    {
        $data = null;
        $exec = DB::select("SELECT city_id as `id`,city_name as kab FROM misterkong_mp.m_city WHERE province_id=$prov");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public static function get_subdis($city)
    {
        $data = null;
        $exec = DB::select("SELECT subdis_name as kec,id FROM misterkong_mp.m_subdis WHERE city_id=$city");
        foreach ($exec as $exe) {
            $data[] = $exe;
        }
        return $data;
    }

    public static function get_id_user($email)
    {
        $exec = DB::selectOne("SELECT id FROM misterkong_mp.m_userx WHERE email='$email'");
        $id = $exec->id;
        return $id;
    }

    public static function cekAcc($ph, $em)
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

    public static function reset_pass($data, $param): bool
    {
        $updateUserx = DB::table("m_userx")->where([$param=>$data[$param]])->update($data);
        // select db from user
        $companyId=DB::table("m_user_company")->select("company_id")->where(["kd_user","IN",DB::table("m_userx")->where([$param=>$data[$param]])])->get();
        foreach ($companyId as $id) {
            DB::table("misterkong_".$id->company_id.".m_userx")->update(["passweb"=>$data["passwd"],["kd_user"=>"UAA000"]]);
        }

        return (bool)$updateUserx;
    }

    private function user_id(){
        $ex_max_trans = DB::selectOne("SELECT MAX(kd_user)as notrans FROM m_userx");
        $nomor = $ex_max_trans->notrans;
        $noUrut = (int) substr($nomor, -3);

        if ($nomor == 0 || is_null($nomor)) {
            $no_trans = "UAA001";
        } else {
            $noUrut++;
            $no_trans = "UAA" . sprintf("%03s", $noUrut);
        }

        return $no_trans;
    }

    private static function user_id_id()
    {
        $ex_max_trans = DB::selectOne("SELECT MAX(id) as notrans FROM m_userx");
        $nomor = $ex_max_trans->notrans;
        if ($nomor == 0 || is_null($nomor)) {
            $no_trans = 1;
        } else {
            $no_trans = $nomor + 1;
        }

        return $no_trans;
    }
    private static function company_id()
    {
        $ex_max_trans = DB::table("m_user_company")->select(DB::raw("company_id as notrans"))->where(["company_id","LIKE","'%".date("YmdHis")."%'"])->get();
        $nomor = $ex_max_trans->notrans;
        $noUrut = (int) substr($nomor, -2);


        if ($ex_max_trans->count() == 0) {
            $no_trans = "comp" . date("YmdHis") . "01";
        } else {
            $noUrut++;
            $no_trans = "comp" . date("YmdHis") . sprintf("%02s", $noUrut);
        }

        return $no_trans;
    }
    public function company_id_id()
    {
        $ex_max_trans = DB::selectOne("SELECT MAX(id)as notrans FROM m_user_company");
        $nomor = $ex_max_trans->notrans;

        if ($nomor == 0 || is_null($nomor)) {
            $no_trans = 1;
        } else {
            $no_trans = $nomor + 1;
        }
        return $no_trans;
    }

    public static function companyid($idorder)
    {
        $ar =DB::table("m_user_company")->select("company_id")->where("id",DB::table("t_penjualan")->select("user_id_toko")->where("id",$idorder)->orWhere("no_transaksi",$idorder))->get();
        return $ar->company_id;
    }

    public function get_gmt($idprov)
    {
        $q=DB::table("m_province")->select(DB::raw("IFNULL(gmt,0)as gmt"))->where(["id"=>$idprov])->first();
        return $q->gmt;
    }

    public static function register_ph($data): array
    {
        $num=DB::table("m_userx")->select("kd_user")->where(["no_hp"=>$data[0]["no_hp"]])->count();
        if ($num >= 1) {
            $feedback = null;
            $det = [
                "error" => 1,
                "pesan" => "Nomor Telepon Sudah Digunakan"
            ];
            $feedback[]=$det;
            return $feedback;
        } else {
            $num_hp=DB::table("m_userx")->select("kd_user")->where(["email"=>$data[0]["email"]])->count();

            if ($num_hp >= 1) {
                $feedback = null;
                $det = [
                    "error" => 1,
                    "pesan" => "Email Sudah Digunakan"
                ];
                $feedback[]=$det;
                return $feedback;
            } else {
                $feedback = null;
                $data[0]['kd_user'] = self::user_id();
                $data[0]['id'] = self::user_id_id();


                DB::beginTransaction();
                try {
                    DB::table("m_userx")->insert($data[0]);
                    $data[1]['company_id'] = self::company_id();
                    $data[1]['id'] = self::company_id_id();

                    $data[1]['db_name'] = "misterkong_" . $data[1]['company_id'];
                    $data[1]['nickname_usaha'] = $data[1]['company_id'];
                    $data[1]['kd_user'] = $data[0]['id'];
                    $data[1]['user_id'] = $data[0]['id'];
                    $data[1]['gmt'] = self::get_gmt($data[1]['kd_provinsi']);
                    $data[1]['status'] = "1";
                    DB::table("m_user_company")->insert($data[1]);
                    DB::commit();

                    $det = [
                        "error" => 0,
                        "pesan" => " Berhasil!",
                        "link" => url("/") . 'register/activate/?&type=uaccsacc&nick=' . $data[0]['id'] . '@' . $data[1]['nickname_usaha'],
                        "company_id" => $data[1]['company_id'],
                        "nama_toko" => $data[1]['nama_usaha'],
                        "jenis_user" => "user_usaha",
                        "id" => $data[1]['id'],
                        "kota" => self::get_nama_kota($data[1]['kd_kabupaten']),
                        "bank" => self::get_nama_bank($data[1]['kd_bank']),
                        "gmt" => $data[1]['gmt'],
                        "otp" => rand(1000, 9999)
                    ];
                    $feedback[]=$det;
                    return $feedback;
                }catch (\Exception $er){
                    DB::rollBack();
                    $feedback = [];
                    $det = [
                        "error" => 1,
                        "pesan" => "Tidak Berhasil!"
                    ];
                    $feedback[]=$det;
                    return $feedback;
                }
            } //else no hp
        } //else email

    }
    private function get_nama_bank($kode)
    {
        $res=DB::table("m_bank")->select("nama_bank")->where(["kd_bank"=>$kode])->first();
        return $res->nama_bank;
    }

    private function get_nama_kota($kode)
    {
        $res=DB::table("m_city")->select("city_name")->where(["city_id"=>$kode])->first();
        return $res->city_name;
    }

    public static function check_ph($data)
    {
        if ($data['loginby'] == "phone") {
            if (substr($data['input'], 0, 2) == "62" || substr($data['input'], 0, 3) == "+62") {
                $data['input'] = $data['input'];
            } elseif (substr($data['input'], 0, 1) == "0") {
                $data['input'][0] = "X";
                $data['input'] = str_replace("X", "62", $data['input']);
            }
        }

        $data1 = [];
        $cek_akun_ada = DB::table("m_userx")->select("id")->where("email",$data['input'])->orWhere("no_hp",$data['input'])->count();
        $pesan = ($data['loginby'] == "phone") ? "Kirim Kembali OTP" : "Kirim Verifikasi via email";

        if ($cek_akun_ada > 0) {
            if ($data['loginby'] == "phone") {
                $where_cek_status = [
                    "no_hp" => $data['input'],
                    "status_phone" => 1,
                    "status" => $data['status']
                ];
                $data_login_pass = [
                    "no_hp" => $data['input'],
                    "passwd" => $data['passwd']
                ];
            } else {
                $where_cek_status = [
                    "email" => $data['input'],
                    "status_email" => 1,
                    "status" => $data['status'],
                ];
                $data_login_pass = [
                    "email" => $data['input'],
                    "passwd" => $data['passwd']
                ];
            }

            $cek_akun_Status =DB::table("m_userx")->where($where_cek_status)->count();
            if ($cek_akun_Status > 0) {
                $cek = DB::table("m_userx")->where($data_login_pass)->count();
                if ($cek > 0) {
                    $ex_compid=DB::table("m_user_company")->select("company_id","nama_usaha","alamat")->where("kd_user",DB::table("m_userx")->select("id")->where(["email"=>$data['input'],"passwd"=>$data['passwd']])->orWhere(["no_hp"=>$data['input'],"passwd"=>$data['passwd']]))->get();
                    $jml_data=$ex_compid->count();
                    if ($jml_data > 0) {
                        $company = [
                            "error" => 0,
                            "usaha" => $jml_data,
                            "company" => []
                        ];

                        foreach ($ex_compid as $row) {
                            $det = [
                                "company_id" => $row['company_id'],
                                "nama_usaha" => $row['nama_usaha'],
                                "alamat" => $row['alamat']
                            ];
                            array_push($company['company'], $det);
                        }

                        $data1[]= $company;
                    } else {
                        $company = [
                            "error" => 3,
                            "usaha" => 0,
                            "pesan" => "Tidak ada Data usaha"
                        ];
                        $data1[]=$company;
                    }
                    return $data1;
                    // return $company;

                } // user name tidak ada atau salah
                else {
                    $company = [
                        "error" => 1,
                        "pesan" => "No.hp atau password salah"
                    ];
                    $data1[]=$company;
                    return $data1;
                    // return $company;
                }
            } else {
                $company = [
                    "error" => 2,
                    "pesan" => "Akun Anda belum terverifikasi, apakah anda ingin " . $pesan . "?",
                    "loginby" => $data['loginby']
                ];
                $data1[]=$company;
                return $data1;
            } //cek status email


        } //cek email ada
        else {
            $company = [
                "error" => 1,
                "pesan" => "User atau Password salah"
            ];
            $data1[]=$company;
            return $data1;
        }
    }

    public function open_new_store($data)
    {
        $data1 = null;

        //cek nama usaha/nickname
        $num_nick = DB::table("m_user_company")->select("nickname_usaha")->where(["nickname_usaha"=>$data[1]['nickname_usaha']])->count();
        if ($num_nick >= 1) {
            $feedback = [
                "error" => 1,
                "pesan" => "nickname usaha sudah digunakan"
            ];
            $data1[]=$feedback;
            return $data1;
            // return $feedback;
        } else {
            $getid = DB::table("m_userx")->select("id","passwd","nama")->where(["no_hp"=>$data[0]])->first();

            //get user data
            $u_id = $getid->id;
            $pass = strtoupper($getid->passwd);
            $nama = $getid->nama;
            DB::beginTransaction();
            try {
                $data[1]['company_id'] = self::company_id();
                $data[1]['id'] = self::company_id_id();
                $data[1]['db_name'] = "misterkong_" . $data[1]['company_id'];
                $data[1]['nickname_usaha'] = $data[1]['company_id'];
                $data[1]['kd_user'] = $u_id;
                $data[1]['user_id'] = $u_id;
                $data[1]['gmt'] = self::get_gmt($data[1]['kd_provinsi']);
                $data[1]['status'] = "-1";
                DB::table("m_user_company")->insert($data[1]);
                DB::commit();
                $feedback = [
                    "error" => 0,
                    "pesan" => "Berhasil!",
                    "company" => array(),
                    "jml_usaha" => 0,
                    "nama" => $nama,
                    "password" => $pass,
                    "company_id" => $data[1]['company_id'],
                    "id" => $data[1]['id'],
                    "kota" => self::get_nama_kota($data[1]['kd_kabupaten']),
                    "bank" => self::get_nama_bank($data[1]['kd_bank']),
                    "gmt" => $data[1]['gmt']
                ];

                $sel_comp = DB::table("m_user_company")->select("company_id","nama_usaha","alamat")->where("kd_user",$u_id)->orderBy("id","DESC")->get();
                $cek_toko = $sel_comp->count();
                if ($cek_toko > 0) {
                    $feedback['jml_usaha'] = $cek_toko;
                    foreach ($sel_comp as $row) {
                        $data_comp = [
                            "company_id" => $row->company_id,
                            "nama_usaha" => $row->nama_usaha,
                            "alamat" => $row->alamat
                        ];
                        array_push($feedback['company'], $data_comp);
                    }
                }

                $data1[]=$feedback;
                return $data1;
            }catch (\Exception $er){
                DB::rollBack();
                $feedback = [
                    "error" => 1,
                    "pesan" => "Tidak Berhasil!"
                ];

                $data1[]=$feedback;
                return $data1;
            }
        }
    }
}
