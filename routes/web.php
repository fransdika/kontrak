<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Utilites;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/me-alter',[Utilites::class, 'alterDb']);
Route::get('/mambo-generator',[Utilites::class, 'generateMamboCodePage']);

Route::get('/junk',[Utilites::class, 'loadJunk']);