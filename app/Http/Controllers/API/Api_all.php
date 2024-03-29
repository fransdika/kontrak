<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\api_m;
use App\Models\Piutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Wapmorgan\UnifiedArchive\UnifiedArchive;
use App\Models\CRUDModel;
use Facade\FlareClient\Http\Response;
use Carbon\Carbon;
use PhpParser\Builder\Function_;

// wapmorgan\unified-archive;


// use App\Http\Controllers\API\RarArchive;

class Api_all extends Controller
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
                        // status -2 untuk menunggu konfirmasi
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
        $file = $request->file('file');
        $name = $file->getClientOriginalName(); 
        $ext = $file->getClientOriginalExtension();

        if (strcasecmp($ext, 'jpg') == 0 || strcasecmp($ext, 'jpeg') == 0 || strcasecmp($ext, 'bmp') == 0 || strcasecmp($ext, 'png') == 0) {
            $file->move("../../../public_html/back_end_mp/".$request->comp_id."_config/images/",$name);
            return response()->json([
                'status' => 1,
                'error' => 0,
                'message' => 'Berhasil upload gambar',
                'data' => [
                    'file' => "misterkong.com/back_end_mp/".$request->comp_id."_config/images/$name"
                ]
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'error' => 500,
                'message' => 'Format file harus berextention jpg atau jpeg atau bmp atau png',
                'data' => []
            ]);
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
            $b.=") LIMIT $request->limit, $request->length";
            return DB::select($b);
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
        
        $select = DB::select("SELECT * FROM misterkong_$request->comp_id.m_barang_satuan_supplier WHERE kd_barang_supplier='$request->kd_barang_supplier' AND kd_satuan_supplier='$request->kd_satuan_supplier'");
        if (empty($select)) {
            $mbss = "INSERT INTO misterkong_$request->comp_id.m_barang_satuan_supplier(kd_supplier,kd_barang,kd_satuan,kd_barang_supplier,kd_satuan_supplier,`status`) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->kd_barang_supplier','$request->kd_satuan_supplier',1)
            ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', kd_satuan_supplier='$request->kd_satuan_supplier',`status`=1";
        } else {
            $mbss = "UPDATE misterkong_$request->comp_id.m_barang_satuan_supplier SET kd_barang='$request->kd_barang_validasi', kd_satuan='$request->kd_satuan_validasi' WHERE kd_barang_supplier='$request->kd_barang_supplier' AND kd_satuan_supplier='$request->kd_satuan_supplier'";
        }

        DB::beginTransaction();
        try {
            DB::update("INSERT INTO misterkong_$request->comp_id.m_barang_supplier(kd_supplier,kd_barang,kd_barang_supplier,`status`,user_add,user_modif) VALUES('$request->kd_supplier','$request->kd_barang_validasi','$request->kd_barang_supplier',1,'$request->user_id','$request->user_id') 
                ON DUPLICATE KEY UPDATE kd_barang_supplier='$request->kd_barang_supplier', `status`=1");
            DB::insert("INSERT IGNORE INTO misterkong_$request->comp_id.m_barang_satuan(kd_barang,kd_satuan,jumlah,harga_jual,`status`,margin) VALUES('$request->kd_barang_validasi','$request->kd_satuan_validasi','$request->jumlah',0,0,0)");
            DB::update($mbss); 
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

    // public function mutasiStockBackOffice(Request $request)
    // {
    //     $sql = "CALL misterkong_$request->comp_id.p_mon_report_mutasi_stok('".$request->awal."','".$request->akhir."')";
    //     try {
    //         return DB::select($sql);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => 0,
    //             'error' => $e->getMessage(),
    //             'message' => 'Gagal'
    //         ], 404);
    //         return response()->json([
    //             'status' => 0,
    //             'error' => $e->getMessage(),
    //             'message' => 'Gagal'
    //         ], 500);
    //     }
    // }

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
        if ($request->jenis == 1) {
            $folder = 'GET';
            $imei = "/".$request->imei;
            //untuk log 23-02-2024
            $path_result = "../../../public_html/back_end_mp/" . $request->comp_id . "_config/GETLOG/"; //vps
        } elseif ($request->jenis == 2) {
            $folder = 'POST';
            $imei = "/".$request->imei;
            //untuk log 23-02-2024
            $path_result = "../../../public_html/back_end_mp/" . $request->comp_id . "_config/POSLOG/"; //vps
        } elseif ($request->jenis == 3) {
            $folder = 'DEL';
            $imei = "";
            //untuk log 23-02-2024
            $path_result = "../../../public_html/back_end_mp/" . $request->comp_id . "_config/DELLOG/"; //vps
        }
        if (!file_exists($path_result)) {
            mkdir($path_result, 0777, true);
        }
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $file->move('../../../public_html/back_end_mp/'.$request->comp_id."_config/".$folder."/".$imei,$filename);
        $date= date('Y-m-d H:i:s');
        copy('../../../public_html/back_end_mp/'.$request->comp_id."_config/".$folder.$imei."/".$filename, $path_result.$date.'__'.$filename);
        return response()->json([
            'status' => 1,
            'error' => 0,
            'message' => 'File berhasil di upload',
            'data' => [
                'file' => 'misterkong.com/back_end_mp/'.$request->comp_id."_config/".$folder."/".$imei."/".$filename
            ]
        ]);   
    }

    public function get_json_file_name(Request $request)
    {
        if ($request->jenis == 1) {
            $folder = 'GET';
            $imei = "/".$request->imei;
        } elseif ($request->jenis == 2) {
            $folder = 'POST';
            $imei = "/".$request->imei;
        } elseif ($request->jenis == 3) {
            $folder = 'DEL';
        }
        $dt =  shell_exec("ls /home/misterkong/public_html/back_end_mp/".$request->comp_id."_config/".$folder . $imei);
        if (!empty($dt)) {
            $var = preg_split("#[\r\n]+#", trim($dt));
        } else {
            $var = [];
        }
        return response()->json($var);
    }

    public function delete_file_json(Request $request)
    {
        if ($request->jenis == 1) {
            $folder = 'GET';
        } else {
            $folder = 'POST';
        }
        $path_result = "../../../public_html/back_end_mp/".$request->comp_id."_config/".$folder."/".$request->imei."/".$request->nama_file;
        if (!file_exists($path_result)) {
            return response()->json([
                'status' => 1,
                'error' => 200,
                'message' => 'File tidak ada'
            ]);
        } else {
            unlink("../../../public_html/back_end_mp/".$request->comp_id."_config/".$folder."/".$request->imei."/".$request->nama_file);
            // $cmd_command = shell_exec(unlink("rm -r /home/misterkong/public_html/back_end_mp/".$request->comp_id."_config/".$folder."/".$request->imei."/".$request->nama_file));
            return response()->json([
                'status' => 1,
                'error' => 200,
                'message' => 'Berhasil hapus file'
            ]);
        }
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





    // kong POS dari Robi
    public function version()
    {
        $query = DB::select("SELECT * FROM g_app_version WHERE jenis = 1 ");
        if(!empty($query))
        {
            return response()->json([
                "version" => $query[0]->app_store_version,
                "status" => $query[0]->version_level
            ]);
        }else{
            return response()->json([
                'status' => 200,
                'error' => false,
                'messages' => "Sorry Not Found ",
                'data' => '0'
            ]);
        }
    }

    public function cek_reg(Request $request)
    {
        $hp =  $request->no_hp;
        $nama = $request->nama_usaha;
        $alamat = $request->alamat;

        $query = DB::select("SELECT company_id FROM m_user_company WHERE nama_usaha = ? AND no_telepon = ? AND alamat = ?",["$nama","$hp","$alamat"]);

        $id = $query[0]->company_id;
        $status=0;
        $progress=0;
        $complete=0;
        if (!empty($id)) {
            $sql_cek_data="SELECT * FROM
            (
                SELECT cnt_table+cnt_view+cnt_fp+cnt_rec AS def_data FROM
                (
                    SELECT COUNT(table_name) AS cnt_table FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema='misterkong_comp2020061905541701'
                ) data_table
                CROSS JOIN 
                (
                    SELECT COUNT(*) AS cnt_view FROM INFORMATION_SCHEMA.VIEWS
                    WHERE TABLE_SCHEMA='misterkong_comp2020061905541701'
                ) data_view
                CROSS JOIN
                (
                    SELECT COUNT(*) AS cnt_fp from information_schema.routines
                    WHERE ROUTINE_SCHEMA='misterkong_comp2020061905541701'
                ) data_fp
                CROSS JOIN
                (SELECT COUNT(*) AS cnt_rec FROM misterkong_comp2020061905541701.m_jam_buka_toko) data_rec
            ) def_db
            CROSS join
            (
                SELECT cnt_table+cnt_view+cnt_fp+cnt_rec AS new_data FROM
                (
                    SELECT COUNT(table_name) AS cnt_table FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema='misterkong_$id'
                ) data_table
                CROSS JOIN 
                (
                    SELECT COUNT(*) AS cnt_view FROM INFORMATION_SCHEMA.VIEWS
                    WHERE TABLE_SCHEMA='misterkong_$id'
                ) data_view
                CROSS JOIN
                (
                    SELECT COUNT(*) AS cnt_fp from information_schema.routines
                    WHERE ROUTINE_SCHEMA='misterkong_$id'
                ) data_fp
                CROSS JOIN
                (SELECT COUNT(*) AS cnt_rec FROM misterkong_".$id.".m_jam_buka_toko) data_rec
            ) new_db
            ";
            $data=DB::select($sql_cek_data)[0];
            $progress=$data->new_data;
            $complete=$data->def_data;
            if ($data->new_data >= $data->def_data ) {
                $status=1;
            }
        } else {
            // return response()->json([
            //     'status' => 2
            // ]);
            $status=2;
        }
        $percentage=0;
        if ($complete>0) {
            if ($progress<$complete) {
                $percentage=$progress/$complete *100;
            }else{
                $percentage=100;
            }
        }
        $response=[
            'status'=>$status,
            // 'progress'=>$progress,
            // 'complete'=> $complete,
            'percentage'=> $percentage
        ];
        return response()->json($response, 200);

    }

    public function transaksi(Request $request)
    {
        date_default_timezone_set("Asia/Jakarta");
        $companyid = $request->company_id;
        $startdate = date('Y-m-d', strtotime("-2 day", strtotime(date("Y-m-d"))));
        $endate = date("Y-m-d");

        if(empty($companyid))
        {
            return response()->json([
                'status' => 500,
                'error' => true,
                'messages' => "company id kosong",
                'data' => '0'
            ]);
        }else{
            $query =  DB::select("SELECT t_penjualan.no_transaksi, t_pengiriman.no_penjualan, t_pengiriman.nama_tujuan, 
                t_driver.kode_pin, m_user_company.company_id, m_driver.nama_depan, m_driver.hp1, t_penjualan.id, m_driver.kd_driver
                FROM t_pengiriman 
                INNER JOIN t_penjualan ON t_pengiriman.no_resi= t_penjualan.no_transaksi
                INNER JOIN t_driver ON t_penjualan.no_transaksi = SUBSTRING(t_driver.no_transaksi, 1,20)
                INNER JOIN m_driver ON t_driver.kd_driver = m_driver.kd_driver
                INNER JOIN m_user_company ON t_penjualan.user_id_toko = m_user_company.id
                WHERE m_user_company.company_id = '$companyid' AND date(t_penjualan.tanggal) BETWEEN '$startdate' AND '$endate'
                AND t_penjualan.status_barang = 4 ");
            // echo $query;
            if (empty($query)) {
                return response()->json([
                    'status' => 200,
                    'error' => false,
                    'messages' => "Sorry Not Found ",
                    'data' => '0'
                ]);
            } else {
                foreach($query as $key => $value){
                    $sql = "SELECT t_penjualan_detail.*, m_barang.kd_barang, m_satuan.kd_satuan
                    FROM t_penjualan_detail 
                    INNER JOIN m_barang_satuan ON t_penjualan_detail.item_id = m_barang_satuan.id
                    INNER JOIN m_barang ON m_barang_satuan.barang_id = m_barang.id
                    INNER JOIN m_satuan ON m_barang_satuan.satuan_id = m_satuan.id
                    WHERE t_penjualan_detail.no_transaksi ='".$value->no_transaksi."'";
                    $pesanan = DB::select($sql);                    
                    $data[] = array(
                        'data'  => [
                            'no_transaksi' => $value->no_transaksi,
                            'pembeli'      => $value->nama_tujuan,
                            'pesanan'      => $pesanan,
                            'id_order'     => $value->id,
                            'pin'          => $value->kode_pin,
                            'noHp'         => $value->hp1,
                            'comp_id'      => $value->company_id,
                            'nama_driver'  => $value->nama_depan,
                            'id_driver'    => $value->kd_driver,
                        ]
                    );
                }
                return response()->json($data);
            }
        }
    }

    public function hapusAkun(Request $request)
    {
        $userCompany = DB::select("SELECT company_id, m_userx.kd_user FROM m_user_company INNER JOIN m_userx ON m_user_company.kd_user = m_userx.id WHERE m_userx.no_hp = '$request->no_hp' AND m_user_company.status <> 0");
        $companyid = [];
        $kd_user = [];
        
        foreach ($userCompany as $key => $value) {
            $companyid[] = $value->company_id;
            $kd_user[] = $value->kd_user;
        }

        if(substr($request->no_hp, 0,1) == "0"){
            $hp = '62'.substr(trim($request->no_hp), 1);
        }else{
            $hp = $request->no_hp;
        }

        if (count($companyid) == 1) {
            // print_r($kd_user[0]);
            // status 1 : aktif, -2 banned, 0 : tutup
            if ($request->status == 1) {
                DB::update("UPDATE m_user_company SET status = '1' WHERE company_id = '$request->company_id'");
            } elseif ($request->status == -2) {
                DB::update("UPDATE m_user_company SET status = '-2' WHERE company_id = '$request->company_id'");
            } else {
                DB::beginTransaction();
                try {
                    DB::update("UPDATE m_user_company SET status = '0' WHERE company_id = '$request->company_id'");
                    DB::update("UPDATE m_userx SET status = 0, no_hp = '{$hp}_del' WHERE kd_user = '{$kd_user[0]}'");
                    DB::commit();
                    return response()->json([
                        "status" => 1,
                        "error" => 0,
                        "Pesan" => "Berhasil hapus akun",
                        "status_akun" => 0,
                        "data" => []
                    ], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $status_tutup_toko = DB::select("SELECT `status` FROM m_user_company WHERE company_id='$request->company_id'");
                    $payload = array(
                        'to' => '/topics/kongpos',
                        'priority' => 'high',
                        "mutable_content" => true,
                        'data' => array(
                            "title" => 'Nonaktif',
                            "comp_id" => $request->company_id,
                            "jenis_notif" => '11',
                            "body" => '',
                            "isi" => ''
                        ),
                    );
                    $headers = array(
                        'Authorization:key=AAAAf50odws:APA91bERBP6tLNfAWz_aeNhmXjbOOItI2aZ_bZEy1xNX47SWCr8LbrfNVQfuVJ8xYT7_mCFKRn6pBW7_qO-fG5qFNfIU-8nfWm1-M_zhezLK12dlsIeFi8ZfYeizEhPVQTdIbGj0DtUt', 'Content-Type: application/json',
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    $result = curl_exec($ch);
                    curl_close($ch);

                    return response()->json([
                        "status" => 0,
                        "error" => 404,
                        "Pesan" => "Gagal hapus akun",
                        "status_akun" => $status_tutup_toko[0]->status,
                        "data" => []
                    ], 404);
                    return response()->json([
                        "status" => 0,
                        "error" => 500,
                        "Pesan" => "Gagal hapus akun",
                        "status_akun" => $status_tutup_toko[0]->status,
                        "data" => []
                    ], 500);
                }
            }
        } else {
            // status 1 : aktif, -2 banned, 0 : tutup
            if ($request->status == 1) {
                $verivikasi = DB::update("UPDATE m_user_company SET status = '1' WHERE company_id = '$request->company_id'");
            } elseif ($request->status == -2) {
                $verivikasi = DB::update("UPDATE m_user_company SET status = '-2' WHERE company_id = '$request->company_id'");
            } else {
                $verivikasi = DB::update("UPDATE m_user_company SET status = '0' WHERE company_id = '$request->company_id'");
            }
            $payload = array(
                'to' => '/topics/kongpos',
                'priority' => 'high',
                "mutable_content" => true,
                'data' => array(
                    "title" => 'Nonaktif',
                    "comp_id" => $request->company_id,
                    "jenis_notif" => '11',
                    "body" => '',
                    "isi" => ''
                ),
            );
            $headers = array(
                'Authorization:key=AAAAf50odws:APA91bERBP6tLNfAWz_aeNhmXjbOOItI2aZ_bZEy1xNX47SWCr8LbrfNVQfuVJ8xYT7_mCFKRn6pBW7_qO-fG5qFNfIU-8nfWm1-M_zhezLK12dlsIeFi8ZfYeizEhPVQTdIbGj0DtUt', 'Content-Type: application/json',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $result = curl_exec($ch);
            curl_close($ch);
            return response()->json([
                "status" => 1,
                "error" => 0,
                "Pesan" => "Berhasil hapus akun",
                "status_akun" => 0,
                "data" => []
            ], 200);
        }

    }


    public function aktifkanAkun(Request $request)
    {
        if(substr($request->no_hp, 0,1) == "0"){
            $hp = '62'.substr(trim($request->no_hp), 1);
        }else{
            $hp = $request->no_hp;
        }

        $userCompany = DB::select("SELECT * FROM m_userx WHERE no_hp = '{$hp}_del' AND `status`=0");
        $kd_user = [];
        
        foreach ($userCompany as $key => $value) {
            $kd_user[] = $value->kd_user;
        }

        // print_r($userCompany[0]->kd_user != null);

        if (empty($userCompany)) {
            // status 1 : aktif, -2 banned, 0 : tutup
            DB::update("UPDATE m_user_company SET status = '1' WHERE company_id = '$request->company_id'");
            return response()->json([
                "status" => 1,
                "error" => 0,
                "Pesan" => "Berhasil aktifkan akun",
                "status_akun" => 1,
                "data" => []
            ], 200);
        } else {
            DB::beginTransaction();
            try {
                DB::update("UPDATE m_user_company SET status = '1' WHERE company_id = '$request->company_id'");
                DB::update("UPDATE m_userx SET status = 1, no_hp = '$hp' WHERE kd_user = '{$userCompany[0]->kd_user}'");
                DB::commit();
                return response()->json([
                    "status" => 1,
                    "error" => 0,
                    "Pesan" => "Berhasil aktifkan akun",
                    "status_akun" => 1,
                    "data" => []
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    "status" => 0,
                    "error" => 404,
                    "Pesan" => "Gagal aktifkan akun",
                    "data" => []
                ], 404);
                return response()->json([
                    "status" => 0,
                    "error" => 500,
                    "Pesan" => "Gagal aktifkan akun",
                    "data" => []
                ], 500);
            }
        }

    }

    public function checkStatus(Request $request)
    {
        $sql = DB::select("SELECT `status` FROM m_user_company WHERE company_id='$request->company_id'");
        if ($sql[0]->status == 1) {
            $status = 'Aktif';
        } else {
            $status = 'Nonaktif';
        }
        return response()->json([
            "status" => 1,
            "error" => 0,
            "Pesan" => "Status toko : $status",
            "data" => [
                'status_toko' => $sql[0]->status
            ]
        ], 200);
    }    

    public function showProduct(Request $request)
    {
        if (!empty($request->order_col) && !empty($request->order_type)) {
            $query_order = " ORDER BY $request->order_col $request->order_type";
        } else {
            $query_order = "";
        }

        if (!(empty($request->search))) {
            $query_search = " WHERE nama LIKE '%".$request->search."%' OR kategori LIKE '%".$request->search."%'";
        } else {
            $query_search = "";
        }

        $crud_type='select';
        $sql = DB::select("SELECT * FROM (SELECT m_barang.kd_barang, m_barang.kd_kategori, m_barang.kd_jenis_bahan, m_barang.kd_model, m_barang.kd_merk, m_barang.kd_warna, m_barang.ukuran, m_barang.keterangan, m_barang.status_pinjam, m_barang.pabrik, m_barang.tag, m_barang.nama, m_barang.`status`, m_kategori.nama AS kategori, m_barang_gambar.gambar, m_barang_satuan.harga_jual AS harga, m_satuan.kd_satuan, m_satuan.nama AS satuan FROM misterkong_$request->company_id.m_barang m_barang
        INNER JOIN misterkong_$request->company_id.m_kategori m_kategori ON m_barang.kd_kategori = m_kategori.kd_kategori
        INNER JOIN misterkong_$request->company_id.m_barang_satuan m_barang_satuan ON m_barang.kd_barang = m_barang_satuan.kd_barang
        INNER JOIN misterkong_$request->company_id.m_satuan m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
        LEFT JOIN (SELECT kd_barang, GROUP_CONCAT(gambar) AS gambar FROM misterkong_$request->company_id.m_barang_gambar GROUP BY kd_barang) m_barang_gambar ON m_barang.kd_barang = m_barang_gambar.kd_barang) a $query_search $query_order LIMIT $request->limit, $request->length");
        
        $sql2 = DB::select("SELECT COUNT(*) AS jumlah_record FROM (SELECT m_barang.kd_barang, m_barang.nama, m_barang.`status`, m_kategori.nama AS kategori, m_barang_gambar.gambar, m_barang_satuan.harga_jual AS harga FROM misterkong_$request->company_id.m_barang m_barang
        INNER JOIN misterkong_$request->company_id.m_kategori m_kategori ON m_barang.kd_kategori = m_kategori.kd_kategori
        INNER JOIN misterkong_$request->company_id.m_barang_satuan m_barang_satuan ON m_barang.kd_barang = m_barang_satuan.kd_barang
        INNER JOIN misterkong_$request->company_id.m_satuan m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
        LEFT JOIN (SELECT kd_barang, GROUP_CONCAT(gambar) AS gambar FROM misterkong_$request->company_id.m_barang_gambar GROUP BY kd_barang) m_barang_gambar ON m_barang.kd_barang = m_barang_gambar.kd_barang) a $query_search");
        
        if ($sql && $sql2) {
            return response()->json($this->crudResponses(1,$crud_type,$sql,$sql2[0]->jumlah_record));
        }else{
            return response()->json($this->crudResponses(0,$crud_type));
        }
    }

    public function upload(Request $request)
    {

            $file = $request->file('file');
        
            $path = [];
			foreach ($file as $key => $value) {
                $name = $value->getClientOriginalName();
                $destinationPath = "../../../public_html/back_end_mp/" . $request->company_id . "_config/images/";
                $value->move($destinationPath, $name);
                $filePath = $destinationPath . $name;
                $path[] = $name;
                // $path[] = $value->store('up');
                // $path[] = $value->move("../../../public_html/back_end_mp/".$request->company_id."_config/images/",$name);
                // $path[] = $value->move(public_path('/uploads'),$name);
			}
			return [
				'status' => 1,
				'error' => 200,
				'message' => "Berhasil simpan gambar",
				'data' => [
					"path" => $path
				]
			];
		// } else {
		// 	return [
		// 		'status' => 0,
		// 		'error' => 500,
		// 		'message' => "Gagal simpan gambar",
		// 		'data' => []
		// 	];
		// }

        // $file = $request->file('file');
        // $name = $file->getClientOriginalName(); 
        // $ext = $file->getClientOriginalExtension();

        // if (strcasecmp($ext, 'jpg') == 0 || strcasecmp($ext, 'jpeg') == 0 || strcasecmp($ext, 'bmp') == 0 || strcasecmp($ext, 'png') == 0) {
        //     $file->move("../../../public_html/back_end_mp/".$request->company_id."_config/images/",$name);
        //     return response()->json([
        //         'status' => 1,
        //         'error' => 0,
        //         'message' => 'Berhasil upload gambar',
        //         'data' => [
        //             'path' => $name
        //         ]
        //     ]);
        // } else {
        //     return response()->json([
        //         'status' => 0,
        //         'error' => 500,
        //         'message' => 'Format file harus berextention jpg atau jpeg atau bmp atau png',
        //         'data' => []
        //     ]);
        // }
    }

    public function cudProduct(Request $request)
    {
        $type='';
		$exe='';
		
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save['misterkong_'.$request->company_id.'.m_barang'][]=[
				'kd_barang'=>$request->kd_barang,
				'kd_merk'=>$request->kd_merk,
				'kd_jenis_bahan'=>$request->kd_jenis_bahan,
				'kd_model'=>$request->kd_model,
				'kd_kategori'=>$request->kd_kategori,
				'kd_warna'=>$request->kd_warna,
				'nama'=>$request->nama,
				'status'=>$request->status,
				'keterangan'=>$request->keterangan,
				'ukuran'=>$request->ukuran,
				'status_pinjam'=>$request->status_pinjam,
				'pabrik'=>$request->pabrik,
				'tanggal_daftar'=>$request->tanggal_daftar,
				'tag' => $request->tag
			];

			foreach ($request->mbs as $key_mbs => $value_mbs) {
				$data_save['misterkong_'.$request->company_id.'.m_barang_satuan'][]=[
					'kd_barang'=>$request->kd_barang,
					'kd_satuan'=>$value_mbs['kd_satuan'],
					'jumlah'=>$value_mbs['jumlah'],
					'harga_jual'=>$value_mbs['harga'],
					'status'=>$value_mbs['status'],
					'margin'=>$value_mbs['margin']
				];
			}
			
			foreach ($request->img as $key_gambar => $value_gambar) {
				$data_save['misterkong_'.$request->company_id.'.m_barang_gambar'][]=[
					'kd_barang'=>$request->kd_barang,
					'nomor'=>$value_gambar['nomor'],
					'gambar'=>$value_gambar['gambar'],
					'keterangan'=>'-',
					'ismain'=>1,
					'spesifikasi'=>'-',
					'deskripsi'=>'-',
				];
			}
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				$key['misterkong_'.$request->company_id.'.m_barang'][]=['kd_barang'=>$request->kd_barang];
				foreach ($request->mbs as $key_satuan => $value_satuan) {
					$key['misterkong_'.$request->company_id.'.m_barang_satuan'][]=['kd_barang'=>$request->kd_barang,'kd_satuan'=>$value_satuan['kd_satuan']];
				}
				foreach ($request->img as $key_img => $value_img) {
					$key['misterkong_'.$request->company_id.'.m_barang_gambar'][]=['kd_barang'=>$request->kd_barang];
				}
				$exe=CRUDModel::doBulkUpdateTable($data_save,$key,['misterkong_'.$request->company_id.'.m_barang_gambar','misterkong_'.$request->company_id.'.m_barang_satuan'],'misterkong_'.$request->company_id.'.m_barang');
			}else{
				$crud_type='insert';
				$exe=CRUDModel::doBulkInsertTable($data_save);
			}

            if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET')) {
			$crud_type='select_put';
            $m_barang = DB::select("SELECT kd_barang, kd_kategori, kd_jenis_bahan, kd_model, kd_merk, kd_warna, ukuran, nama, keterangan, `status`, status_pinjam, pabrik, tag FROM misterkong_$request->company_id.m_barang WHERE kd_barang='$request->kd_barang'");
            $mbs = DB::select("SELECT kd_barang, kd_satuan, jumlah, harga_jual, `status`, margin FROM misterkong_$request->company_id.m_barang_satuan WHERE kd_barang='$request->kd_barang'");
            $mbg = DB::select("SELECT kd_barang, nomor, keterangan, gambar FROM misterkong_$request->company_id.m_barang_gambar WHERE kd_barang='$request->kd_barang'");
            $data = [];
            $data['m_barang'] = $m_barang[0];
            $data['m_barang_satuan'] = $mbs;
            $data['m_barang_gambar'] = $mbg;
            if (!empty($data)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}
    }

    public function crudResponses($status,$crud_type,$data=[],$jumlah_record=[])
	{
		$msg_arr=[
			[
				'insert'=>'oops, terjadi kesalahan, data gagal disimpan',
				'update'=>'oops, terjadi kesalahan, perubahan gagal disimpan',
				'delete'=>'data gagal dihapus',
				'select'=>'data tidak tersedia',
				'err_notfound' => 'Data Not Found'
			],
			[
				'insert'=>'Data Berhasil disimpan',
				'update'=>'Perubahan Berhasil disimpan',
				'delete'=>'Data Berhasil dihapus',
				'select'=> count($data)." Data ditemukan",
				'select_put'=> "Data ditemukan"
			]
		];
		if ($crud_type=='err_notfound') {
			$error=404;
		}else{
			if ($status==1) {
				$error=200;
			}else{
				$error=500;
			}
		}

        if ($crud_type=='insert' || $crud_type=='select_put' || $crud_type=='delete' || $crud_type=='update') {
            $response=[
                'status' => $status,
                'error' => $error,
                'message' => $msg_arr[$status][$crud_type],
                'data' => (!empty($data))?$data:[]
            ];
        } else {
            $response=[
                'status' => $status,
                'error' => $error,
                'message' => $msg_arr[$status][$crud_type],
                'jumlah_record' => (!empty($jumlah_record))?$jumlah_record:[],
                'data' => (!empty($data))?$data:[]
            ];
        }
		
		return $response;
	}   

    public function unique_code($param_code)
    {
        $currentDate = Carbon::now()->toDateString();
        $currentTime = Carbon::now()->format('H:i');

        // Parse the date and time
        $dateTime = Carbon::parse("$currentDate $currentTime");

        // Format the DateTime object as per your desired format
        $formattedDateTime = $dateTime->format('ymdHi');

        // Remove any separators from the formatted string
        $code = $param_code . str_replace(['-', ':'], '', $formattedDateTime);
        return $code;
    }

    public function cudKategori(Request $request)
	{
		$type='';
		$exe='';

        $kd_kategori = $this->unique_code('MKAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_kategori'=>$kd_kategori,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_kategori']);
				// $exe=DB::table('m_kategori')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_kategori')->where('kd_kategori',$request->kd_kategori)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_kategori')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_kategori)) {
			$crud_type='select_put';
			$key=['kd_kategori'=>$request->kd_kategori];
			$data_edit=DB::select("SELECT kd_kategori, nama, keterangan, `status` FROM misterkong_$request->company_id.m_kategori WHERE kd_kategori='$request->kd_kategori'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_kategori')->where('kd_kategori',$request->kd_kategori)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
		
	}

    public function cudMerk(Request $request)
	{
		$type='';
		$exe='';

        $kd_merk = $this->unique_code('MMAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_merk'=>$kd_merk,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_merk']);
				// $exe=DB::table('m_merk')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_merk')->where('kd_merk',$request->kd_merk)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_merk')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_merk)) {
			$crud_type='select_put';
			$key=['kd_merk'=>$request->kd_merk];
			$data_edit=DB::select("SELECT kd_merk, nama, keterangan, `status` FROM misterkong_$request->company_id.m_merk WHERE kd_merk='$request->kd_merk'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_merk')->where('kd_merk',$request->kd_merk)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
		
	}

    public function cudSatuan(Request $request)
	{
		$type='';
		$exe='';
		$kd_satuan = $this->unique_code('MSAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_satuan'=>$kd_satuan,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_satuan']);
				// $exe=DB::table('m_satuan')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_satuan')->where('kd_satuan',$request->kd_satuan)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_satuan')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_satuan)) {
			$crud_type='select_put';
			$key=['kd_satuan'=>$request->kd_satuan];
			$data_edit=DB::select("SELECT kd_satuan, nama, keterangan, `status` FROM misterkong_$request->company_id.m_satuan WHERE kd_satuan='$request->kd_satuan'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_satuan')->where('kd_satuan',$request->kd_satuan)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
		
	}

    public function show_master($search = '', $order_col = '', $order_type = '', $limit = '', $length = '', $company_id, $tbl_master, $kode)
    {
        $crud_type='select';
        if (!empty($search)) {
            $sql_search = " WHERE nama LIKE '%$search%'";
        } else {
            $sql_search = "";
        }

        if (!empty($order_col) && !empty($order_type)) {
            $sql_order = " ORDER BY $order_col $order_type";
        } else {
            $sql_order = "";
        }

        if ($limit > -1 && !(empty($length))) {
            $sql_limit = " LIMIT $limit, $length";
        } else {
            $sql_limit = "";
        }
        $sql = DB::select("SELECT $kode, nama, `status`, keterangan FROM misterkong_$company_id.$tbl_master $sql_search $sql_order $sql_limit");
        $sql2 = DB::select("SELECT COUNT(*) AS jumlah_record FROM misterkong_$company_id.$tbl_master");
        
        if ($sql && $sql2) {
			return response()->json($this->crudResponses(1,$crud_type,$sql,$sql2[0]->jumlah_record));
		}else{
			return response()->json($this->crudResponses(0,$crud_type));
		}
    }

    public function show_kategori(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_kategori', 'kd_kategori');
        return $dt;
    }

    public function show_merk(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_merk', 'kd_merk');
        return $dt;
    }

    public function show_satuan(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_satuan', 'kd_satuan');
        return $dt;
    }

    public function show_model(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_model', 'kd_model');
        return $dt;
    }

    public function show_warna(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_warna', 'kd_warna');
        return $dt;
    }

    public function show_jenis_bahan(Request $request)
    {
        $dt = $this->show_master($request->search, $request->order_col, $request->order_type, $request->limit, $request->length, $request->company_id, 'm_jenis_bahan', 'kd_jenis_bahan');
        return $dt;
    }

    public function cudModel(Request $request)
	{
		$type='';
		$exe='';
		$kd_model = $this->unique_code('MMAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_model'=>$kd_model,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_model']);
				// $exe=DB::table('m_model')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_model')->where('kd_model',$request->kd_model)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_model')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_model)) {
			$crud_type='select_put';
			$key=['kd_model'=>$request->kd_model];
			$data_edit=DB::select("SELECT kd_model, nama, keterangan, `status` FROM misterkong_$request->company_id.m_model WHERE kd_model='$request->kd_model'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_model')->where('kd_model',$request->kd_model)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
	}
    
    public function cudWarna(Request $request)
	{
		$type='';
		$exe='';
		$kd_warna = $this->unique_code('MWAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_warna'=>$kd_warna,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_warna']);
				// $exe=DB::table('m_warna')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_warna')->where('kd_warna',$request->kd_warna)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_warna')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_warna)) {
			$crud_type='select_put';
			$key=['kd_warna'=>$request->kd_warna];
			$data_edit=DB::select("SELECT kd_warna, nama, keterangan, `status` FROM misterkong_$request->company_id.m_warna WHERE kd_warna='$request->kd_warna'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_warna')->where('kd_warna',$request->kd_warna)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
	}

    public function cudJenisBahan(Request $request)
	{
		$type='';
		$exe='';
		$kd_jenis_bahan = $this->unique_code('MJAA');
		if ($request->isMethod('POST') || $request->isMethod('PUT')) {
			$data_save=[
				'kd_jenis_bahan'=>$kd_jenis_bahan,
				'nama'=>$request->nama,
				'keterangan'=>$request->keterangan,
				'status'=>$request->status
			];
			
			if ($request->isMethod('PUT')) {
				$crud_type='update';
				unset($data_save['kd_jenis_bahan']);
				// $exe=DB::table('m_jenis_bahan')->insert($data_save);
                $exe = DB::table('misterkong_'.$request->company_id.'.m_jenis_bahan')->where('kd_jenis_bahan',$request->kd_jenis_bahan)->update($data_save);
			}else{
				$crud_type='insert';
				$exe=DB::table('misterkong_'.$request->company_id.'.m_jenis_bahan')->insert($data_save);
			}
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type),200);
			}else{
				return response()->json($this->crudResponses(0,$crud_type),500);
			}
		}elseif ($request->isMethod('GET') && !empty($request->kd_jenis_bahan)) {
			$crud_type='select_put';
			$key=['kd_jenis_bahan'=>$request->kd_jenis_bahan];
			$data_edit=DB::select("SELECT kd_jenis_bahan, nama, keterangan, `status` FROM misterkong_$request->company_id.m_jenis_bahan WHERE kd_jenis_bahan='$request->kd_jenis_bahan'");
            if (!empty($data_edit)) {
                return response()->json($this->crudResponses(1,$crud_type,(array)$data_edit[0]));
            } else {
                return response()->json($this->crudResponses(0,'err_notfound'),404);
            }
		}elseif ($request->isMethod('DELETE')) {
			$crud_type='delete';
			$exe=DB::table('misterkong_'.$request->company_id.'.m_jenis_bahan')->where('kd_jenis_bahan',$request->kd_jenis_bahan)->delete();
			if ($exe) {
				return response()->json($this->crudResponses(1,$crud_type));
			}else{
				return response()->json($this->crudResponses(0,$crud_type));
			}
		}else{
			return response()->json($this->crudResponses(0,'err_notfound'),404);
		}
	}
} 