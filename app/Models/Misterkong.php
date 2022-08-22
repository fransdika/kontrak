<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Misterkong extends Model
{
    use HasFactory;
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
                $cek = DB::table('v_getCompanyUser')->select(array(DB::raw('count(*) as aggregate')))->where($data_login_pass)->count();
                if ($cek > 0) {
                    if ($cek != 1) {
                        $sub = DB::table('m_userx')->select('id')->where('email', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $query = DB::table('m_userx')->select('id')->where('no_hp', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $ex_compid = DB::table('m_user_company')->select('company_id', 'nama_usaha', 'alamat')->where($sub)->orWhere($query);
                        $jml_data = $ex_compid->count();
                        if ($jml_data > 0) {
                            $company = ["error" => 0, "usaha" => $jml_data, "company" => []];
                            foreach ($ex_compid->get() as $value) {
                                $det = ["company_id" => $value['ccompany_id'],
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
                        $sub = DB::table('m_userx')->select('id')->where('email', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $query = DB::table('m_userx')->select('id')->where('no_hp', '=', $data['input'])->Where('passwd', '=', $data['passwd']);
                        $ex_compid = DB::table('m_user_company')->select('company_id', 'nama_usaha', 'alamat')->where($sub)->orWhere($query)->get();
                        return $ex_compid;
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
}