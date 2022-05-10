<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class api_all extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        $data = DB::select('SELECT * FROM misterkong_comp2020110310015601.m_customer_config');
        return response()->json([
            'Data' => $data
        ], 200);
    }

    public function update_m_customer_config(Request $request, $id)
    {
        DB::update('update misterkong_comp2020110310015601.m_customer_config set `status` = '.$request->status.' where kd_customer = ?', [$id]);
        return response()->json([
            'Pesan' => "Berhasil Update",
            'Data' => DB::select("SELECT * FROM misterkong_comp2020110310015601.m_customer_config WHERE kd_customer='.$id.'")
        ], 200);
        return response()->json([
            'Pesan' => "Gagal update"
        ], 404);
        return response()->json([
            'Pesan' => "Gagal update"
        ], 500);
    }
}