<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\api_m;
use App\Models\Piutang;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class api_all extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->middleware('auth:api');
        // $api_m = new api_m();
    }
    public function m_supplier(Request $request)
    {
        $data = DB::select("SELECT kd_supplier,nama FROM misterkong_$request->comp_id.m_supplier");
        return response()->json($data);
    }

    public function index(Request $request)
    {
        $comp_id = $request->comp_id;
        $data = api_m::get_list_customer_contract($comp_id);
        return response()->json([
            'data' => $data
        ], 200);
    }
    public function selected_contracted(Request $request)
    {
        $data = api_m::get_selected_contract($request->id_kontrak, $request->cid_sumber, $request->cid_tujuan);
        return response()->json($data);
    }

    public function customer_contract(Request $request)
    {
        $sql = "CALL list_customer_contract ('$request->comp_id','$request->order_col','$request->order_type','$request->limit','$request->length','$request->search','$request->count_stats')";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function compare_supplier(Request $request)
    {
        $sql = "CALL list_supplier_contract ('$request->comp_id','$request->order_col','$request->order_type','$request->limit','$request->length','$request->search','$request->count_stats')";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function supplier_response_contract(Request $request)
    {
        $sql = "CALL list_supplier_response_contract ('$request->comp_id','$request->order_col','$request->order_type','$request->limit','$request->length','$request->search','$request->count_stats')";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function procedure_prepare_kontrak(Request $request)
    {
        $data = DB::statement("CALL p_proc_prepare_order_kontrak(?,?),[$request->comp_id,$request->kd_supplier]");
        return response()->json([
            'Data' => $data 
        ], 200);
    }

    public function post_request_contract(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            "cid_sumber"=>"required",
            "cid_tujuan"=>"required",
            "kd_customer"=>"required",
            "periode_bulan"=>"required",
            "id_cid_tujuan"=>"required",
        ]);
        if ($validasi->passes()) {
            // DB::beginTransaction();
            $data = [
                "comp_id_sumber"=>$request->cid_sumber,
                "comp_id_tujuan"=>$request->cid_tujuan,
                "kd_customer"=>$request->kd_customer,
                "status"=>"0",
                "tanggal_request" => date('Y-m-d H:i:s'),
                "periode_bulan"=>$request->periode_bulan
            ];
            // print_r($data);
            try {
                DB::table('h_kontrak_request')->insert($data);
                DB::update("update misterkong_$request->cid_sumber.m_customer_config set `status` = '-2' where kd_customer = ? and
                    customer_user_company_id = ?", [$request->kd_customer, $request->id_cid_tujuan]);
                DB::commit();
                return response()->json([
                    'Pesan' => "Berhasil melakukan permintaan kontrak"
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'Pesan' => "Gagal melakukan permintaan kontrak"
                ], 404);
                return response()->json([
                    'Pesan' => "Gagal melakukan permintaan kontrak"
                ], 500);
            }
        } else {
            return response()->json([
                'Pesan' => "Lengkapi Data"
            ], 404);
        }
    }

    public function post_compare_supplier_data(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            "kd_supplier" => "Required",
            "id_cid_sumber"=> "Required",
            "cid_sumber"=> "Required",
            "cid_tujuan"=> "Required"
        ]);
        if ($validasi->passes()) {
            DB::beginTransaction();
            try {
                DB::insert("insert into misterkong_$request->cid_tujuan.m_supplier_config (kd_supplier, supplier_user_company_id, `status`)
                 values ('$request->kd_supplier','$request->id_cid_sumber','0')");
                DB::update("update h_kontrak_request set kd_supplier = '$request->kd_supplier', `status` = '-1' where 
                    comp_id_sumber='$request->cid_sumber' and comp_id_tujuan='$request->cid_tujuan' and `status`='0'");
                DB::commit();
                return response()->json([
                    "Pesan" => "Berhasil menambahkan Supplier"
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'Pesan' => "Gagal menambahkan supplier"
                ], 404);
                return response()->json([
                    'Pesan' => "Gagal menambahkan supplier"
                ], 500);
            }

        } else {
            return response()->json([
                'Pesan' => "Lengkapi Data"
            ], 404);
        }
    }

    public function post_customer_respons_contract(Request $request)
    {

        $validasi = Validator::make($request->all(), [
            "kd_supplier" => "required",
            "kd_customer" => "required",
            "cid_sumber" => "required",
            "cid_tujuan" => "required",
            "periode" => "required",
            "id_cid_sumber" => "required",
            "id_cid_tujuan" => "required"
        ]);
        if ($validasi->passes()) {
            $due_date = date('Y-m-d H:i:s', strtotime("+$request->periode month", strtotime(date('Y-m-d H:i:s'))));
            
            $row = DB::select('select no_kontrak from t_kontrak order by tanggal desc limit 1');
            if (!empty($row)) {
                $code="KAA";
                $ang = substr($row[0]->no_kontrak, 4);
                $ang=$ang+1;
                $urut = sprintf("%03d", $ang);
                $no_kontrak = strtoupper($code. $urut);
            }else{
                $no_kontrak = "KAA000";
            };

            $id_for_kontrak = DB::select("SELECT MAX(id) as id FROM t_kontrak");
            if (!empty($id_for_kontrak)) {
                $id_kontrak = $id_for_kontrak[0]->id+1;
            } else {
                $id_kontrak = 1;
            }
            $data_kontrak = [
                "id" => $id_kontrak,
                "no_kontrak" => $no_kontrak,
                "status" => "0",
                "user_company_x_id" => $request->id_cid_sumber,
                "user_company_y_id" => $request->id_cid_tujuan,
                "tanggal_jatuh_tempo" => $due_date
            ];
            DB::beginTransaction();
            try {
                DB::update("update misterkong_$request->cid_sumber.m_customer_config set `status` = '-1', kontrak_id='$id_kontrak' where kd_customer='$request->kd_customer' and 
                    `status`='-2' and customer_user_company_id='$request->id_cid_tujuan'");
                DB::update("update misterkong_$request->cid_tujuan.m_supplier_config set `status` = '-2', kontrak_id='$id_kontrak' where kd_supplier='$request->kd_supplier' and 
                    `status`='0' and supplier_user_company_id='$request->id_cid_sumber'");
                DB::update("update h_kontrak_request set `status`='-2', tanggal_response=CURRENT_TIMESTAMP, tanggal_kontrak=CURRENT_TIMESTAMP, 
                    tanggal_jatuh_tempo='$due_date' where comp_id_sumber='$request->cid_sumber' and comp_id_tujuan='$request->cid_tujuan' and `status`='-1'");
                DB::table('t_kontrak')->insert($data_kontrak);
                DB::commit();
                return response()->json([
                    "Pesan" => "Permintaan kontrak diterima"
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'Pesan' => "Gagal Insert dan Update"
                ], 404);
                return response()->json([
                    'Pesan' => "Gagal Insert dan Update"
                ], 500);
            }

        } else {
            return response()->json([
                'Pesan' => "Lengkapi Data"
            ], 404);
        }
    }

    public function post_do_payment(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            "images" => "required|max:1024|mimes:jpg,jpeg,bmp,png,"
        ]);
        if ($validasi->passes()) {
            $image = $request->file('images');
            if ($request->hasFile('images')) {
                $new_name = rand().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('/uploads'),$new_name);
                $contract = DB::select("SELECT tanggal FROM t_kontrak WHERE id='$request->id_kontrak'");
                $due_date = date('Y-m-d H:i:s', strtotime("+$request->periode month", strtotime(date($contract[0]->tanggal))));
                DB::beginTransaction();
                try {
                    DB::update("update t_kontrak set tanggal_jatuh_tempo=DATE_ADD(tanggal, INTERVAL $request->periode MONTH) where id='$request->id_kontrak'");
                    DB::update("update h_kontrak_request set tanggal_bayar=CURRENT_TIMESTAMP where comp_id_sumber='$request->cid_sumber' and
                        comp_id_tujuan='$request->cid_tujuan' and `status`=-2");
                    DB::insert("insert into t_kontrak_pembayaran (kontrak_id, nominal) values ('$request->id_kontrak', '$request->nominal_bayar')");
                    DB::insert("insert into t_kontrak_doc (kontrak_id, path_image) values ('$request->id_kontrak', '$new_name')");
                    DB::update("update misterkong_$request->cid_sumber.m_customer_config set status=-2 where id=$request->id_customer_config");
                    DB::commit();
                    return response()->json([
                        "Pesan" => "Berhasil melakukan pembayaran"
                    ], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'Pesan' => "Lengkapi data"
                    ], 404);
                    return response()->json([
                        'Pesan' => "Lengkapi data"
                    ], 500);
                }
            } else {
                return response()->json([
                    "Pesan" => "Silahkan pilih gambar"
                ], 404);
            }
        } else {
            return response()->json([
                'Pesan' => "Lengkapi Data"
            ], 404);
        }
    }

    public function upload_image(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            "images" => "max:1000|mimes:jpg,jpeg,bmp,png,"
        ]);
        if ($validasi->passes()) {
            $image = $request->file('images');
            if ($request->hasFile('images')) {
                $new_name = rand().'.'.$image->getClientOriginalExtension();
                // $image->move(public_path('/uploads'),$new_name);
                return response()->json($new_name);
            } else {
                return response()->json([
                    "Pesan" => "Silahkan pilih gambar"
                ], 404);
            }
        } else {
            return response()->json([
                "Pesan" => "Ukuran gambar maksimal 1000KB"
            ], 404);
        }

    }
    public function get_list_supplier_item(Request $request)
    {
        $sql = "CALL p_get_supplier_item ('".$request->comp_id."','".$request->order_col."','".$request->order_type."',".$request->limit.",".$request->length.",'".$request->search."',".$request->count_stats.")";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function postBarangSatuan(Request $request)
    {
        // if ($request->mbs_status === '1') {
        //     $status = '2';
        //     $sql1 = "update misterkong_$request->comp_id.m_barang_satuan set `status` = $status where kd_barang = '$request->kd_barang' and kd_satuan = '$request->kd_satuan'";
        // } else {
        //     $status = '1';
        //     $sql1 = "update misterkong_$request->comp_id.m_barang_satuan set `status` = $status where kd_barang = '$request->kd_barang' and kd_satuan = '$request->kd_satuan'";
        // }
        // print_r($sql1);
        if ($request->mbs_status ==='1') {
            $status = 1;
            DB::update("update misterkong_$request->comp_id.m_barang_satuan set `status` = $status where kd_barang = '$request->kd_barang' and kd_satuan = '$request->kd_satuan'");
            return response()->json([
                'Pesan' => 'Berhasil insert data'
            ], 200);
        } elseif ($request->mbs_status === '2') {
            $status = 2;
            DB::beginTransaction();
            try {
                DB::update("update misterkong_$request->comp_id.m_barang_satuan set `status` = $status where kd_barang = '$request->kd_barang' and kd_satuan = '$request->kd_satuan'");
                DB::update("update misterkong_$request->comp_id.m_barang set `status` = '2' where kd_barang = '$request->kd_barang'");
                DB::commit();
                return response()->json([
                    'Pesan' => 'Berhasil insert data'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'Pesan' => "Gagal"
                ], 404);
                return response()->json([
                    'Pesan' => "Gagal"
                ], 500);
            }
        }
    }

    public function get_supplier_contracted(Request $request)
    {
        $data = DB::select("SELECT m_supplier.*,m_supplier_config.supplier_user_company_id AS supplier_user_company_id
          FROM
          misterkong_$request->comp_id.m_supplier_config m_supplier_config
          INNER JOIN t_kontrak kontrak ON kontrak.id = m_supplier_config.kontrak_id
          INNER JOIN misterkong_$request->comp_id .m_supplier m_supplier ON m_supplier_config.kd_supplier = m_supplier.kd_supplier
          WHERE
          kontrak.status = 1");
        return response()->json($data);
    }

    public function get_list_item_contracted(Request $request)
    {
        $sql = "CALL p_list_item_contracted('$request->sup_key',$request->id_cid_supplier,'$request->cid_customer','$request->order_col','$request->order_type',$request->limit,$request->length,'$request->search',$request->count_stats)";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function get_barang(Request $request)
    {
        $req = $request->nama;
        $nama_explode = explode(" ",$req);
        $b = "SELECT ROW_number() OVER(ORDER BY nama) AS `no`, m_barang.*,0 AS urut FROM misterkong_$request->company_id.m_barang m_barang WHERE nama LIKE '%".$req."%'
        UNION 
        SELECT ROW_number() OVER(ORDER BY nama) AS `no`, m_barang.*,0 AS urut FROM misterkong_$request->company_id.m_barang m_barang WHERE 
        kd_barang NOT IN (SELECT kd_barang FROM m_barang WHERE nama LIKE '%".$req."%')
        AND ( nama LIKE '%".$nama_explode[0]."%'";
        for ($x = 1; $x < count($nama_explode); $x++) {
            $a =" OR nama LIKE '%".$nama_explode[$x]."%'";
            $b .= $a;
        }
        $b.=")";
        return DB::select($b);
        // print_r($b);
    }

    public function get_satuan(Request $request)
    {
        $sql = "SELECT
        kd_barang, 
        m_satuan.kd_satuan, 
        jumlah,
        nama
        FROM
        misterkong_$request->comp_id.m_barang_satuan
        INNER JOIN misterkong_$request->comp_id.m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan 
        WHERE
        kd_barang = '$request->kd_barang'";
        return DB::select($sql);
    }

    public function submit_validate(Request $request)
    {
        // print_r("INSERT INTO misterkong_$request->comp_id.m_barang_supplier(kd_supplier,kd_barang,kd_barang_supplier,`status`,user_add,user_modif) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_barang_supplier',1,'$request->user_id','$request->user_id') 
        //             ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', `status`=1-");
        // print_r("INSERT IGNORE INTO misterkong_$request->comp_id.m_barang_satuan(kd_barang,kd_satuan,jumlah,harga_jual,`status`,margin) VALUES('$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->jumlah',0,0,0) - ");
        // print_r("INSERT INTO misterkong_$request->comp_id.m_barang_satuan_supplier(kd_supplier,kd_barang,kd_satuan,kd_barang_supplier,kd_satuan_supplier) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->kd_barang_supplier','$request->kd_satuan_supplier')
        //             ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', kd_satuan_supplier='$request->kd_satuan_supplier'");        
        DB::beginTransaction();
        try {
            DB::update("INSERT INTO misterkong_$request->comp_id.m_barang_supplier(kd_supplier,kd_barang,kd_barang_supplier,`status`,user_add,user_modif) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_barang_supplier',1,'$request->user_id','$request->user_id') 
                ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', `status`=1");
            DB::insert("INSERT IGNORE INTO misterkong_$request->comp_id.m_barang_satuan(kd_barang,kd_satuan,jumlah,harga_jual,`status`,margin) VALUES('$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->jumlah',0,0,0)");
            DB::update("INSERT INTO misterkong_$request->comp_id.m_barang_satuan_supplier(kd_supplier,kd_barang,kd_satuan,kd_barang_supplier,kd_satuan_supplier,`status`) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->kd_barang_supplier','$request->kd_satuan_supplier',1)
                ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', kd_satuan_supplier='$request->kd_satuan_supplier',`status`=1");        
            DB::commit();
            return response()->json([
                'Pesan' => 'Berhasil Upsert Data'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'Pesan' => "Gagal"
            ], 404);
            return response()->json([
                'Pesan' => "Gagal"
            ], 500);
        }
    }
    public function login_pos(Request $request)
    {
        // $selectmd5 = DB::select("SELECT passwd FROM m_userx WHERE no_hp='".$param['no_hp']."' AND passwd='".$param['passwd']."'");
        // if ($selectmd5 == null) {
        //     return response()->json(['message' => 'No hp atau Password salah'], 401);
        // } else {
        $no_hp = $request->mn;
        $passwd = $request->dp;

        print_r($no_hp);

            // $credentials = request($no_hp,$passwd);
            // $user = User::where($credentials)->first();
            // if (! $user )  {
            //     return response()->json(['message' => 'No hp atau Password salah'] , 401);
            // };
            // if (!$token = auth($this->guard)->login($user)) {
            //     return response()->json(['message' => 'No hp atau Password salah'], 401);
            // }
            // return $this->respondWithToken($token, $user);
        // }
    }

    public function info_piutang(Request $request)
    {
        $sql = "CALL p_infoPiutang ('".$request->comp_id."',$request->jenis,'".$request->periode."','".$request->search."',$request->con,'".$request->order_col."', '".$request->order_type."', $request->con_date,$request->limit,$request->length,$request->count_stats)";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }


    public function info_cicilan_piutang(Request $request)
    {
        $sql = "CALL p_infoCicilanPiutang('".$request->comp_id."','".$request->no_transaksi."',$request->limit,$request->length,$request->count_stats)";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
    }

    public function create_piutang(Request $request)
    {
        $no_cicilan = $request->no_cicilan;
        $no_transaksi = $request->no_transaksi;
        $nominal = $request->nominal;

        $cicilan_new = [];
        $transaksi_new = [];
        $nominal_new = [];          
        $cicilan_new = explode(",",$no_cicilan);
        $transaksi_new = explode(",",$no_transaksi);
        $nominal_new = explode(",",$nominal);

        if (count($cicilan_new) == count($transaksi_new) && count($cicilan_new) == count($nominal_new)) {
            $sql = [];
            for ($i=0; $i < count($cicilan_new); $i++) {
                // echo  $i;
                $data_cicilan[] = [
                    "no_cicilan" =>$cicilan_new[$i],
                    "no_transaksi" => $transaksi_new[$i],
                    "kd_jenis"=> $request->kd_jenis,
                    "kd_pegawai"=> $request->kd_pegawai,
                    "kd_kas"=> $request->kd_kas,
                    "nominal"=>$nominal_new[$i],
                    "other"=>0,
                    "no_bukti"=>"-",
                    "keterangan"=>"-",
                    "kd_user"=> $request->kd_user
                ]; 
                $sql[] = "INSERT INTO misterkong_$request->comp_id.t_piutang_cicilan 
                (no_cicilan, no_transaksi, kd_jenis, kd_pegawai, kd_kas, nominal, other, tanggal, no_bukti, keterangan, kd_user, tanggal_server)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
            }
            DB::beginTransaction();
            try {
                foreach ($sql as $key => $value) {
                    DB::insert($value,
                        [
                            $data_cicilan[$key]['no_cicilan'],
                            $data_cicilan[$key]['no_transaksi'],
                            $data_cicilan[$key]['kd_jenis'],
                            $data_cicilan[$key]['kd_pegawai'],
                            $data_cicilan[$key]['kd_kas'],
                            $data_cicilan[$key]['nominal'],
                            $data_cicilan[$key]['other'],
                            date('Y-m-d'),
                            $data_cicilan[$key]['no_bukti'],
                            $data_cicilan[$key]['keterangan'],
                            $data_cicilan[$key]['kd_user'],
                            date('Y-m-d')
                        ]);
                }
                DB::commit();
                return response()->json([
                    'status' => 0,
                    'error' => 200,
                    'message' => 'Berhasil Insert Data'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'error' => 404,
                    'message' => 'Gagal Insert Data'
                ], 404);
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'Gagal Insert Data'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 0,
                'error' => 200,
                'message' => 'Gagal Insert Data'
            ],200);
        }
    }
    public function update_piutang(Request $request)
    {
        $update = DB::update("UPDATE misterkong_$request->comp_id.t_piutang_cicilan SET 
          no_transaksi = ?, kd_jenis = ?, kd_pegawai = ?, kd_kas = ?, nominal = ?, other = ?, tanggal = ?, no_bukti = ?, keterangan = ?, kd_user = ?
          WHERE no_cicilan = ?
          ",
          [$request->no_transaksi, $request->kd_jenis,$request->kd_pegawai,$request->kd_kas,$request->nominal,$request->other,$request->tanggal,$request->no_bukti,$request->keterangan,$request->kd_user, $request->no_cicilan]);
        return response()->json([
            'status' => 0,
            'error' => 200,
            'message' => 'Berhasil Update Data'
        ],200);
    }

    public function delete_piutang(Request $request)
    {
        $delete = DB::delete("DELETE FROM misterkong_$request->comp_id.t_piutang_cicilan WHERE no_cicilan = ?",[$request->no_cicilan]);
        return response()->json([
            'status' => 0,
            'error' => 200,
            'message' => 'Berhasil Delete Data'
        ],200);
    }

    public function info_hutang(Request $request)
    {
        $sql = "CALL p_infoHutang ('".$request->comp_id."',$request->jenis,'".$request->periode."','".$request->search."',$request->con,'".$request->order_col."','".$request->order_type."',$request->con_date,$request->limit,$request->length,$request->count_stats)";
        
        // prints_r($sql);
        try {
            if ($request->count_stats == 0) {
                return DB::select($sql);
            } else {
                return DB::select($sql)[0];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 404);
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 500);
        }
    }
    public function info_cicilan_hutang(Request $request)
    {
        // $sql = "CALL p_infoCicilanHutang('comp2020110310015601','HM2203220016',0,10,0)";
        $sql = "CALL p_infoCicilanHutang('".$request->comp_id."','".$request->no_transaksi."',$request->limit,$request->length,$request->count_stats)";
        if ($request->count_stats == 0) {
            return DB::select($sql);
        } else {
            return DB::select($sql)[0];
        }
        // echo $request->comp_id;
    }

    public function create_hutang(Request $request)
    {
        $no_cicilan = $request->no_cicilan;
        $no_transaksi = $request->no_transaksi;
        $nominal = $request->nominal;

        $cicilan_new = [];
        $transaksi_new = [];
        $nominal_new = [];          
        $cicilan_new = explode(",",$no_cicilan);
        $transaksi_new = explode(",",$no_transaksi);
        $nominal_new = explode(",",$nominal);

        if (count($cicilan_new) == count($transaksi_new) && count($cicilan_new) == count($nominal_new)) {
            $sql = [];
            for ($i=0; $i < count($cicilan_new); $i++) {
                $data_cicilan[] = [
                    "no_cicilan" =>$cicilan_new[$i],
                    "no_transaksi" => $transaksi_new[$i],
                    "kd_jenis"=> $request->kd_jenis,
                    "kd_kas"=> $request->kd_kas,
                    "nominal"=>$nominal_new[$i],
                    "no_bukti"=>"-",
                    "keterangan"=>"-",
                    "kd_user"=> $request->kd_user
                ]; 
                $sql[] = "INSERT INTO misterkong_$request->comp_id.t_hutang_cicilan 
                (no_cicilan, no_transaksi, kd_jenis, kd_kas, nominal, tanggal, no_bukti, keterangan, kd_user, tanggal_server)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
            }
            DB::beginTransaction();
            try {
                foreach ($sql as $key => $value) {
                    DB::insert($value,
                        [
                            $data_cicilan[$key]['no_cicilan'],
                            $data_cicilan[$key]['no_transaksi'],
                            $data_cicilan[$key]['kd_jenis'],
                            $data_cicilan[$key]['kd_kas'],
                            $data_cicilan[$key]['nominal'],
                            date('Y-m-d'),
                            $data_cicilan[$key]['no_bukti'],
                            $data_cicilan[$key]['keterangan'],
                            $data_cicilan[$key]['kd_user'],
                            date('Y-m-d')
                        ]);
                }
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'error' => 200,
                    'message' => 'Berhasil Insert Data'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'error' => $e->getMessage(),
                    'message' => 'Gagal Insert Data'
                ], 404);
                return response()->json([
                    'status' => 0,
                    'error' => 500,
                    'message' => 'Gagal Insert Data'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 0,
                'error' => 200,
                'message' => 'Gagal Insert Data'
            ],200);
        }
        // $insert = DB::insert("INSERT INTO misterkong_$request->comp_id.t_hutang_cicilan 
        //                         (no_cicilan, no_transaksi, kd_jenis, kd_kas, nominal, tanggal, no_bukti, keterangan, kd_user, tanggal_server)
        //                       VALUES (?,?,?,?,?,?,?,?,?,?)", [$request->no_cicilan,$request->no_transaksi,$request->kd_jenis,$request->kd_kas,$request->nominal,NOW(),$request->no_bukti,$request->keterangan,$request->kd_user,NOW()]);
        // return response()->json([
        //     'pesan' => 'Berhasil insert data'
        // ],200);
    }
    public function update_hutang(Request $request)
    {
        $update = DB::update("UPDATE misterkong_$request->comp_id.t_hutang_cicilan SET 
          no_transaksi = ?, kd_jenis = ?, kd_kas = ?, nominal = ?, tanggal = ?, no_bukti = ?, keterangan = ?, kd_user = ?
          WHERE no_cicilan = ?",
          [$request->no_transaksi, $request->kd_jenis,$request->kd_kas,$request->nominal,$request->tanggal,$request->no_bukti,$request->keterangan,$request->kd_user, $request->no_cicilan]);

        return response()->json([
            'status' => 1,
            'error' => 200,
            'message' => 'Berhasil Update Data'
        ],200);
    }

    public function delete_hutang(Request $request)
    {
        $delete = DB::delete("DELETE FROM misterkong_$request->comp_id.t_hutang_cicilan WHERE no_cicilan = ?",[$request->no_cicilan]);
        return response()->json([
            'status' => 1,
            'error' => 200,
            'message' => 'Berhasil Delete Data'
        ],200);
    }

    public function status_buka_tutup_toko(Request $request)
    {
        $sql = DB::select("SELECT v.* FROM `v_status_buka_toko` v INNER JOIN m_user_company ON v.id = m_user_company.id WHERE company_id=?",[$request->comp_id]);
        return response()->json([
            'status' => 1,
            'error' => 0,
            'message' => 'pesanan sudah siap',
            'data' => $sql[0]
        ]);
    }

    public function laba_rugi(Request $request)
    {
        $sql = "CALL misterkong_$request->comp_id.p_mon_report_labaRugi ('".$request->awal."','".$request->akhir."')";
        try {
            return DB::select($sql);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 404);
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 500);
        }
    }

    public function mutasi_Stok(Request $request)
    {
        $sql = "CALL misterkong_$request->comp_id.p_mon_report_mutasi_stok('".$request->awal."','".$request->akhir."')";
        try {
            return DB::select($sql);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 404);
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 500);
        }
    }

    public function kartu_stok(Request $request)
    {
        $sql = "CALL misterkong_$request->company_id.p_mon_report_kartu_stok('".$request->awal."','".$request->akhir."','".$request->kd_barang."')";
        try {
            return DB::select($sql);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 404);
            return response()->json([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'Gagal'
            ], 500);
        }
    }

    public function upload_file(Request $request)
    {

        // if ($request->jenis == 1) {
            // Save the file
        $file = $request->file('file');
        $path = $file->store('barang_gambar', 'public');

            // Save the file details in the database
        $fileRecord = DB::table('m_barang_gambar')->insertGetId([
            'kd_barang' => $request->kd_barang,
            'nomor'=>1,
            'keterangan'=>'-',
            'gambar' => $file->getClientOriginalName(),
            'ismain'=>'1',
            'spesifikasi'=>'-',
            'deskripsi'=>'-'
        ]);

            // Retrieve the saved file record
        $uploadedFile = DB::table("misterkong_$request->company_id.m_barang_gambar")->find($fileRecord);

            // // Return a response
        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => $uploadedFile,
        ]);
		// } elseif ($request->jenis == 2){

		// } else {
        //     return response()->json([
        //         'message' => 'failed',
        //         'file' => '',
        //     ]);
		// }

        // Save the file
        // $file = $request->file('file');
        // $path = $file->store('uploads', 'public');

        // // Save the file details in the database
        // $fileRecord = DB::table('m_barang_gambar')->insertGetId([
        //     'kd_barang' => $request->kd_barang,
        //     'nomor'=>1,
        //     'keterangan'=>'-',
        //     'gambar' => $file->getClientOriginalName(),
        //     'ismain'=>'1',
        //     'spesifikasi'=>'-',
        //     'deskripsi'=>'-'
        // ]);

        // // Retrieve the saved file record
        // $uploadedFile = DB::table("misterkong_$request->company_id.m_barang_gambar")->find($fileRecord);

        // // Return a response
        // return response()->json([
        //     'message' => 'File uploaded successfully.',
        //     'file' => $uploadedFile,
        // ]);
    }

    public function up_file_json(Request $request)
    {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $path = $file->move('../../../public_html/back_end_mp/'.$request->comp_id."_config/GET/".$request->imei,$filename);
        
        // $path = $file->move(public_path('file_json'),$filename);
        // $path = $file->store("../../../public_html/back_end_mp/$request->comp_id/GET/$request->imei");
        // $image->move("../../../public_html/back_end_mp/$company_id/GET/$imei",$path)

        // Return a response
        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => 'misterkong.com/back_end_mp/'.$request->comp_id."_config/GET/".$request->imei."/".$filename
        ]);
    }

    public function get_json_file_name(Request $request)
    {
        $dt =  shell_exec("ls /home/misterkong/public_html/back_end_mp/".$request->comp_id."_config/GET/".$request->imei);
        if (!empty($dt)) {
            $var = preg_split("#[\r\n]+#", trim($dt));
        } else {
            $var = [];
        }
        return response()->json(
            $var
        );
    }

    public function delete_file_json(Request $request)
    {
        $cmd_command = shell_exec("rm -r /home/misterkong/public_html/back_end_mp/".$request->comp_id."_config/GET/".$request->imei."/".$request->nama_file);
        return response()->json([
            'status' => 1,
            'error' => 200,
            'message' => 'Berhasil hapus file'
        ]);
    }

    public function deleteData(Request $request)
    {
        $tbl_name = $request->t_name;
        $key = explode('__', $request->key);
        $val = explode('__', $request->val);
        $detail = ($request->dt == 'true') ? 'true' : 'false';
        for ($i = 0; $i < count($key); $i++) {
            $key_val[$key[$i]] = $val[$i];
        }

        DB::beginTransaction();
        try {


            if ($detail == "true") {
                // $this->db->where($key);
                // $this->db->delete($tbl_name . "_detail");
                DB::delete("DELETE FROM ".$tbl_name."_detail WHERE ".$key[0]." = ?",[$key[1]]);
            }
            // $this->db->where($key);
            // $this->db->delete($tbl_name);

            DB::delete("DELETE FROM ".$tbl_name." WHERE ".$key[0]." = ?",[$key[1]]);
            DB::commit();
            return response()->json([
                'status' => 1,
                'error' => false,
                'message' => 'Berhasil Delete file'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error' => true,
                'message' => 'Gagal Menghapus Data'
            ]);
        }
    }

} 