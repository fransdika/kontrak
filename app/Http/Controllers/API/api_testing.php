<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class api_testing extends Controller
{
    public function testing()
    {
        // $data = 'soto';
            // 'harga' => '10000'
        $bakso = [
            'id' => 1,
            'nama' => 'Bakso',
            'harga' => '20000'
        ];
        $soto = [
            'id' => 2,
            'nama' => 'Soto',
            'harga' => '15000',
        ];
        $data = [
            $bakso, $soto
            // 'bakso', 'soto'
            // 'id' => 1,
            // 'nama' => 'Bakso',
            // 'harga' => '20000'
        ];
       
        return response()->json([
            // 'Status' => 200,
            'data' => $data
            // 'data' => $bakso
            // $bakso = [
            //     'id' => 1,
            //     'nama' => 'Bakso',
            //     'harga' => '20000'
            // ];
            // 'data' => $data
        ], 200);
    }
}