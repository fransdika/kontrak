<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\api_all;
use App\Http\Controllers\API\AuthController;
use App\Models\api_m;

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

Route::post('index', [api_all::class, 'index']);
Route::post('request_contract', [api_all::class, 'post_request_contract']);
Route::post('compare_supplier_data', [api_all::class, 'compare_supplier_data']);
Route::post('customer_respons_contract', [api_all::class, 'customer_respons_contract']);
Route::post('do_payment', [api_all::class, 'do_payment']);

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});