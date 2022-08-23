<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Misterkong extends Model
{
    public function login($data)
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
        $cek_akun_ada = DB::table('m_userx')->select('id')->where('email', '=', $data['input'])->orWhere('no_hp', '=', $data['input'])->count();
        $pesan = ($data['loginby'] == "phone") ? "kirim Kembali OTP" : "Kirim Verivikasi via email";
        if ($cek_akun_ada > 0) {
            if ($data['loginby'] == "phone") {
                $where_cek_status = ["no_hp" => $data['input'], "status_phone" => 1, "status" => $data['status']];
                $data_login_pass = ["no_hp" => $data['input'], "passwd" => $data['passwd']];
            } else {
                $where_cek_status = ["email" => $data['input'], "status_email" => 1, "satatus" => $data['status']];
                $data_login_pass = ["email" => $data['input'], "passwd" => $data['passwd']];
            }
            $cek_akun_status = DB::table('m_userx')->where($where_cek_status)->count();
            if ($cek_akun_status > 0) {
                // dd(\DB::getQueryLog());
                // $cek = DB::table('v_getCompanyUser')->select(array(DB::raw('count(*) as aggregate')))->where($data_login_pass)->count();
                $cek = DB::select($this->getDataTable('v_getCompanyUser', $data_login_pass));
                // dd(\DB::getQueryLog());
                if (count($cek) > 0) {
                    if (count($cek) != 1) {
                        $sub = DB::table('m_userx')->select('id')->where('email', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $query = DB::table('m_userx')->select('id')->where('no_hp', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $ex_compid = DB::table('m_user_company')->select('company_id', 'nama_usaha', 'alamat')->where($sub)->orWhere($query);
                        $jml_data = $ex_compid->count();
                        if ($jml_data > 0) {
                            $company = ["error" => 0, "usaha" => $jml_data, "company" => []];
                            foreach ($ex_compid->get() as $value) {
                                $det = ["company_id" => $value['company_id'],
                                    "nama_usaha" => $value['nama_usaha'],
                                    "alamat" => $value['alamat']];
                                array_push($company['company'], $det);
                            }
                            array_push($data1, $company);
                        } else {
                            $company = ["error" => 3, "usaha" => 0, "pesan" => "Tidak ada Data usaha"];
                        }
                        return $data1;
                    } else {
                        $det = [
                            "company_id" => $cek[0]->company_id,
                            "nama_usaha" => $cek[0]->nama_usaha,
                            "alamat" => $cek[0]->alamat_usaha,
                        ];
                        $company = ["error" => 0, "usaha" => 1, "company" => $det];
                        array_push($data1, $company);
                        return $data1;

                    }
                } else {
                    $company = ["error" => 1, "pesan" => "No.hp atau password salah"];
                    array_push($data1, $company);
                    return $data1;
                }
            } else {
                $company = ["error" => 2, "pesan" => "Akun Anda belum terverifikasi, apakah anda ingin " . $pesan . "?", "loginby" => $data['loginby']];
                array_push($data1, $company);
                return $data1;
            }
        } else {
            $company = ["error" => 1, "pesan" => "User atau Password salah"];
            array_push($data1, $company);
            return $data1;
        }
    }

    public function getDataTable($table, $where)
    {
        foreach ($where as $key => $value) {
            $dataWhere[] = "$key='$value'";
        }
        $sql = "SELECT * FROM $table WHERE " . implode(" AND ", $dataWhere);
        return $sql;
    }
}