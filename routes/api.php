<?php

use App\Http\Controllers\GoalController;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
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

Route::post('login', [UserController::class, 'login']);
Route::post('signin', [UserController::class, 'store']);
Route::get('dashboard/{user_id?}', [UserController::class, 'dashboard']);
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/edit/{id?}', [UserController::class, 'edit']);
        Route::post('/ubahPassword/{id?}', [UserController::class, 'ubahPassword']);
    });
});


Route::group(['prefix' => 'jenis'], function () {
    Route::get('/{user_id?}', [JenisController::class, 'index']);
    Route::post('tambah', [JenisController::class, 'tambah']);
    Route::post('edit', [JenisController::class, 'edit']);
    Route::post('hapus/{user_id?}/{jenis_id?}', [JenisController::class, 'hapus']);

    //
    Route::post('tambahWarna', [JenisController::class, 'tambahWarna']);
    Route::post('hapusIcon/{id?}', [JenisController::class, 'hapusIcon']);

    //warna
    Route::post('warnaTambah', [JenisController::class, 'warnaTambah']);
    Route::post('hapusWarna/{id?}', [JenisController::class, 'hapusWarna']);
});

Route::group(['prefix' => 'transaksi'], function () {
    Route::get('/{user_id?}', [TransaksiController::class, 'indexUser']);
    Route::post('tambah', [TransaksiController::class, 'tambah']);
    Route::post('tambahgoal', [TransaksiController::class, 'tambahgoal']);
    Route::post('tambahhutang', [TransaksiController::class, 'tambahhutang']);
    Route::post('edit', [JenisController::class, 'edit']);
    Route::post('hapus/{user_id?}/{jenis_id?}', [JenisController::class, 'hapus']);
});


Route::group(['prefix' => 'goal'], function () {
    Route::get('/{user_id?}', [GoalController::class, 'indexUser']);
    Route::post('tambah', [GoalController::class, 'tambah']);
    Route::post('bagi', [GoalController::class, 'bagi']);
    Route::post('update', [GoalController::class, 'update']);
    Route::post('hapus/{jenis_id?}', [JenisController::class, 'hapus']);
});
