<?php

use App\Http\Controllers\API\api_all;
use App\Http\Controllers\API\api_testing;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KongPosController;
use App\Http\Controllers\API\LaporanController;
use App\Http\Controllers\API\LaporanTableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('login', ['as' => 'login', 'uses' => 'App\Http\Controllers\API\AuthController@login_response']);
Route::post('index', [api_all::class, 'index']);

// get data
Route::post('customer_contract', [api_all::class, 'customer_contract']);
Route::post('compare_supplier', [api_all::class, 'compare_supplier']);
Route::post('supplier_response_contract', [api_all::class, 'supplier_response_contract']);
Route::post('m_supplier', [api_all::class, 'm_supplier']);
Route::post('selected_contracted', [api_all::class, 'selected_contracted']);
Route::post('get_list_supplier_item', [api_all::class, 'get_list_supplier_item']);
Route::post('get_supplier_contracted', [api_all::class, 'get_supplier_contracted']);
Route::post('get_list_item_contracted', [api_all::class, 'get_list_item_contracted']);

// post data
Route::post('post_request_contract', [api_all::class, 'post_request_contract']);
Route::post('post_compare_supplier_data', [api_all::class, 'post_compare_supplier_data']);
Route::post('post_customer_respons_contract', [api_all::class, 'post_customer_respons_contract']);
Route::post('post_do_payment', [api_all::class, 'post_do_payment']);
Route::post('prepare_order', [api_all::class, 'procedure_prepare_kontrak']);
Route::post('upload_image', [api_all::class, 'upload_image']);
Route::post('postBarangSatuan', [api_all::class, 'postBarangSatuan']);
Route::post('laporan/penjualan', [LaporanController::class, 'getLaporanPenjualan']);
Route::post('laporan/pembelian', [LaporanController::class, 'getLaporanPembelian']);
Route::post('laporan/hutang', [LaporanController::class, 'getLaporanHutang']);
Route::post('laporan/piutang', [LaporanController::class, 'getLaporanPiutang']);
Route::post('laporan/stok', [LaporanController::class, 'getLaporanStok']);
Route::post('laporan/biaya', [LaporanController::class, 'getLaporanBiaya']);
Route::post('laporan/pendapatan', [LaporanController::class, 'getLaporanPendapatan']);

// data table
Route::post('laporan/penjualan_dt', [LaporanTableController::class, 'laporanp_priode']);
Route::post('laporan/pembelian_dt', [LaporanTableController::class, 'laporanpem_priode']);
Route::post('laporan/hutang_dt', [LaporanTableController::class, 'hutang']);
Route::post('laporan/piutang_dt', [LaporanTableController::class, 'piutang']);
Route::post('laporan/stok_dt', [LaporanTableController::class, 'inventory']);
Route::get('testing', [api_testing::class, 'testing']);

// kong pos
Route::post('/pos/cek_status_order', [KongPosController::class, 'status_pesanan']);
Route::post('/pos/siap', [KongPosController::class, 'tandaisiap']);
Route::post('/pos/cek_status', [KongPosController::class, 'getLastStatus']);

Route::post('login_get_cid', [AuthController::class, 'loginGetCid']);
Route::post('login_company', [AuthController::class, 'loginCompany']);

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth',

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});
Route::post('regisman', [ManagerController::class, 'daftar']);