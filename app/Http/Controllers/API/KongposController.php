<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KongPosController extends Controller
{
    public function status_pesanan(Request $request)
    {
        $status = $request->status;
        $company_id = $request->company_id;
        $order = $request->no_order;
        $result = DB::Table('misterkong_' . $company_id . '.t_penjualan_order')->selectRaw('status')->where('no_order', '=', $order)->first();
        if (!empty($result)) {
            $response = [
                'status' => 500,
                'key' => 0,
                'message' => 'error server',
            ];
            if ($result->status == 0) {
                $update = DB::statement("UPDATE misterkong_" . $company_id . ".t_penjualan_order SET status = '$status' WHERE no_order = '$order'");
                if ($status == 3) {
                    $response = [
                        'status' => 200,
                        'key' => 1,
                        'message' => 'baru di terima',
                    ];
                } elseif ($status == 4) {
                    $response = [
                        'status' => 200,
                        'key' => 3,
                        'message' => 'baru di tolak',
                    ];
                }

            } elseif ($result->status == '3') {
                $response = [
                    'status' => 200,
                    'key' => 2,
                    'message' => 'sudah di terima',
                ];
            } elseif ($result->status == '4') {
                $response = [
                    'status' => 200,
                    'key' => 4,
                    'message' => 'sudah di tolak',
                ];
            } elseif ($result->status == 6) {
                $response = [
                    'status' => 200,
                    'key' => 0,
                    'message' => 'pin sudah dimasukan',
                ];
            }
        } else {
            $response = [
                'status' => 500,
                'key' => -1,
                'message' => 'data belum tersedia',
            ];
        }

        return response()->json($response, 200);
    }

    public function tandaisiap(Request $request)
    {
        $status = $request->status;
        $company_id = $request->company_id;
        $order = $request->no_order;
        $result = DB::Table('misterkong_' . $company_id . '.t_penjualan_order')->selectRaw('status')->where('no_order', '=', $order)->first();
        $response = [
            'status' => 500,
            'key' => 0,
            'message' => 'error server',
        ];

        if ($result->status != 6) {
            $update = DB::statement("UPDATE misterkong_" . $company_id . ".t_penjualan_order SET status = '$status' WHERE no_order = '$order'");
            if ($status == 5) {
                $response = [
                    'status' => 200,
                    'key' => 5,
                    'message' => 'berhasil di update',
                ];
            } elseif ($status == 4) {
                $response = [
                    'status' => 200,
                    'key' => 4,
                    'message' => 'berhasil di batalkan',
                ];
            }
        } else {
            $response = [
                'status' => 200,
                'key' => 0,
                'message' => 'pin sudah dimasukan',
            ];
        }
        return response()->json($response, 200);

    }
}