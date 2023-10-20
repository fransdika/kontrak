<?php

use App\Http\Controllers\API\Api_all;
use App\Http\Controllers\API\api_testing;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KongPosController;
use App\Http\Controllers\API\LaporanController;
use App\Http\Controllers\API\LaporanTableController;
use App\Http\Controllers\API\MkController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\API\SinkronisasiController;
use App\Http\Controllers\API\DatabaseGeneratorController;
use App\Http\Controllers\API\SolidReportController;
use App\Http\Controllers\API\Utilites;
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
Route::post('index', [Api_all::class, 'index']);

// get data
Route::post('customer_contract', [Api_all::class, 'customer_contract']);
Route::post('compare_supplier', [Api_all::class, 'compare_supplier']);
Route::post('supplier_response_contract', [Api_all::class, 'supplier_response_contract']);
Route::post('m_supplier', [Api_all::class, 'm_supplier']);
Route::post('selected_contracted', [Api_all::class, 'selected_contracted']);
Route::post('get_list_supplier_item', [Api_all::class, 'get_list_supplier_item']);
Route::post('get_supplier_contracted', [Api_all::class, 'get_supplier_contracted']);
Route::post('get_list_item_contracted', [Api_all::class, 'get_list_item_contracted']);
Route::post('get_barang', [Api_all::class, 'get_barang']);
Route::post('get_satuan', [Api_all::class, 'get_satuan']);
Route::post('query-all', [Utilites::class, 'query_all_db']);
Route::post('do-generate-mambo', [Utilites::class, 'doGenerateMamboCode']);

// post data
Route::post('post_request_contract', [Api_all::class, 'post_request_contract']);
Route::post('post_compare_supplier_data', [Api_all::class, 'post_compare_supplier_data']);
Route::post('post_customer_respons_contract', [Api_all::class, 'post_customer_respons_contract']);
Route::post('post_do_payment', [Api_all::class, 'post_do_payment']);
Route::post('prepare_order', [Api_all::class, 'procedure_prepare_kontrak']);
Route::post('upload-image', [Api_all::class, 'upload_image']);
Route::post('postBarangSatuan', [Api_all::class, 'postBarangSatuan']);
Route::post('laporan/penjualan', [LaporanController::class, 'getLaporanPenjualan']);
Route::post('laporan/pembelian', [LaporanController::class, 'getLaporanPembelian']);
Route::post('laporan/hutang', [LaporanController::class, 'getLaporanHutang']);
Route::post('laporan/piutang', [LaporanController::class, 'getLaporanPiutang']);
Route::post('laporan/stok', [LaporanController::class, 'getLaporanStok']);
Route::post('laporan/biaya', [LaporanController::class, 'getLaporanBiaya']);
Route::post('laporan/pendapatan', [LaporanController::class, 'getLaporanPendapatan']);
Route::post('submit_validate', [Api_all::class, 'submit_validate']);

Route::post('laporan/penjualan_order', [LaporanController::class, 'getPenjualanOrder']);
Route::post('laporan/penjualan_retur', [LaporanController::class, 'getPenjualanRetur']);
Route::post('laporan/pembelian_order', [LaporanController::class, 'getPembelianOrder']);
Route::post('laporan/pembelian_retur', [LaporanController::class, 'getPembelianRetur']);
Route::post('laporan/penjualan-newBorn', [LaporanController::class, 'getPenjualanNewBorn']);
Route::post('laporan/pembelian-newBorn', [LaporanController::class, 'getPembelianNewBorn']);
Route::post('laporan/produk', [LaporanController::class, 'produk']);
Route::post('laporan/mutasi-kas', [LaporanController::class, 'mutasi_kas']);
Route::post('laporan/biaya-newBorn', [LaporanController::class, 'getLaporanBiayaNewBorn']);
Route::post('laporan/pendapatan-newBorn', [LaporanController::class, 'getLaporanPendapatanNewBorn']);


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
Route::post('login_pos',[AuthController::class, 'login_pos']);
Route::post('hapus-akun',[Api_all::class, 'hapusAkun']);
Route::post('aktifkan-akun',[Api_all::class, 'aktifkanAkun']);
Route::post('check-status-akun',[Api_all::class, 'checkStatus']);


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
Route::post('pencarian', [ManagerController::class, 'pencarian']);
Route::post('login_mp', [ManagerController::class, 'login']);

Route::post('info_piutang',[Api_all::class, 'info_piutang']);
Route::post('info_cicilan_piutang', [Api_all::class, 'info_cicilan_piutang']);
Route::post('insert_piutang', [Api_all::class, 'create_piutang']);
Route::post('update_piutang', [Api_all::class, 'update_piutang']);
Route::post('delete_piutang', [Api_all::class, 'delete_piutang']);

Route::post('info_hutang',[Api_all::class, 'info_hutang']);
Route::post('info_cicilan_hutang', [Api_all::class, 'info_cicilan_hutang']);
Route::post('insert_hutang', [Api_all::class, 'create_hutang']);
Route::post('update_hutang', [Api_all::class, 'update_hutang']);
Route::post('delete_hutang', [Api_all::class, 'delete_hutang']);

Route::post('status_toko', [Api_all::class, 'status_buka_tutup_toko']);

Route::post('laba_rugi', [Api_all::class, 'laba_rugi']);
Route::post('mutasi_stok', [Api_all::class, 'mutasi_stok']);
Route::post('kartu_stok', [Api_all::class, 'kartu_stok']);


// backend POS
Route::post('upload-barang', [Api_all::class, 'upload_file']);
Route::post('upload-json', [Api_all::class, 'up_file_json']);
Route::post('list-json', [Api_all::class, 'get_json_file_name']);
Route::post('hapus-file-json', [Api_all::class, 'delete_file_json']);

//route perubahan dari ci_api_vps
Route::get('del_rec', [Api_all::class, 'deleteData']);


//route perubahan dari back_end_mp
Route::post('get-json-pos/{company_id}/{imei}', [SinkronisasiController::class, 'convert_to_json_mode2']);
Route::post('send-json-pos/{company_id}/{imei}', [SinkronisasiController::class, 'jsonPosExecutor']);
Route::post('syncDelete', [SinkronisasiController::class, 'syncDelete']);
Route::get('getFirstMaster/{company_id}/{act}', [SinkronisasiController::class, 'getFirstMaster']);
Route::any('generateDB', [DatabaseGeneratorController::class, 'executeSql']);


//route perubahan dari Api_2
Route::get('getCompanyProfile/{company_id}', [SinkronisasiController::class, 'getCompanyProfile']);
Route::any('totalanStruk/{company_id}', [SinkronisasiController::class, 'totalanStruk']);


// testing testing

// routes kongPOS dari Robi
Route::post('version', [Api_all::class, 'version']);
Route::post('cek_reg', [Api_all::class, 'cek_reg']);
Route::post('pesanan', [Api_all::class, 'transaksi']);


//testing ja
Route::post("mk_dir",[MkController::class,"mk_dir"]);
Route::any("cekPhone",[MkController::class,"cekPhone"]);
Route::get("getBank",[MkController::class,"get_bank"]);
Route::get("getProv",[MkController::class,"get_prov"]);
Route::get("getKat",[MkController::class,"get_category_store"]);
Route::get("getKec/{city}",[MkController::class,"get_subdistrict"]);
Route::get("getKab/{prov}",[MkController::class,"get_district"]);
Route::any("newToko",[MkController::class,"open_new_store"]);
Route::any("regPos",[MkController::class,"registration_pos_ph"]);
Route::post("emailResend",[MkController::class,"send_email_again"]);
Route::post("otpPassResVer",[MkController::class,"verify_reset_password_otp"]);
Route::post("otpRePhone",[MkController::class,"resend_otp_phone"]);
Route::post("check_ph",[MkController::class,"check_ph"]);
Route::post("notifPos",[MkController::class,"notifPos"]);



// Solid Report
Route::post('info-toko', [SolidReportController::class, 'get_info_toko']);
Route::post('info-outlet', [SolidReportController::class, 'get_outlet']);
Route::post('info-jam-operasional', [SolidReportController::class, 'jamOperasional']);
Route::put('update-status-toko', [SolidReportController::class, 'changeStoreStatus']);
Route::post('get-buka-tutup-toko', [SolidReportController::class, 'getBukaTutupToko']);
Route::put('update-buka-tutup-toko', [SolidReportController::class, 'updateJadwalBukaTutupToko']);
Route::put('update-tags', [SolidReportController::class, 'updateTags']);
Route::post('update-gambar-company', [SolidReportController::class, 'updateGambar']);

// baru dika
Route::post('getStatusDb/{company_id}', [SinkronisasiController::class, 'getStatusGenerateDb']);
Route::post('get-first-sync/{company_id}', [SinkronisasiController::class, 'getFirstSync']);
// resetKontrak
Route::post('reset-kontrak', [SinkronisasiController::class, 'resetKontrak']);

Route::post('random-up', [Api_all::class, 'upRandom']);


// Adopsi saribuana
Route::post('produk/show', [Api_all::class, 'showProduct']);
