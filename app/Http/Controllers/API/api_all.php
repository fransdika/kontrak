<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\api_m;
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
            DB::beginTransaction();
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
                    'Pesan' => "Berhasil Insert dan Update"
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
                    "Pesan" => "Berhasil Insert dan Update Data"
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
                    "Pesan" => "Berhasil Insert dan Update Data"
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
            "id_kontrak" => "required",
            "periode" => "required",
            "cid_sumber" => "required",
            "cid_tujuan" => "required",
            "nominal_bayar" => "required",
            "path" => "required"
            
        ]);
        if ($validasi->passes()) {
            $contract = DB::select("SELECT tanggal FROM t_kontrak WHERE id='$request->id_kontrak'");
            $due_date = date('Y-m-d H:i:s', strtotime("+$request->periode month", strtotime(date($contract[0]->tanggal))));
            // print_r($due_date);
            DB::beginTransaction();
            try {
                DB::update("update t_kontrak set tanggal_jatuh_tempo=DATE_ADD(tanggal, INTERVAL $request->periode MONTH) where id='$request->id_kontrak'");
                DB::update("update h_kontrak_request set tanggal_bayar=CURRENT_TIMESTAMP where comp_id_sumber='$request->cid_sumber' and
                    comp_id_tujuan='$request->cid_tujuan' and `status`=-2");
                DB::insert("insert into t_kontrak_pembayaran (kontrak_id, nominal) values ('$request->id_kontrak', '$request->nominal_bayar')");
                DB::insert("insert into t_kontrak_doc (kontrak_id, path_image) values ('$request->id_kontrak', '$request->path')");
                DB::commit();
                return response()->json([
                    "Pesan" => "Berhasil Insert dan Update Data"
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
    public function upload_image(Request $request)
    {

        // $image = $request->file('image');
        print_r($request);
        // if ($request->hasFile('image')) {
        //     $new_name = rand().'.'.$image->getClientOriginalExtension();
        //     $image->move(public_path('/uploads'),$new_name);
        //     return response()->json($new_name);
        // } else {
        //     return response()->json('image null');
        // }
    }
}