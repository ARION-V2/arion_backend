<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourierController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PartnerController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SaController;

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
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('checkUsername', [AuthController::class, 'checkUsername']);
    Route::post('changePassword', [AuthController::class, 'changePassword']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('getMyUser', [UserController::class, 'getMyUser']);
        Route::post('update', [UserController::class, 'updateProfile']);
        Route::post('updatePhotoProfile', [UserController::class, 'updatePhotoProfile']);
    });

    Route::group(['prefix' => 'auth'], function () {
        Route::delete('logout', [AuthController::class, 'logout']);
    });

    Route::group(['prefix' => 'partner'], function () {
        Route::get('all', [PartnerController::class, 'all']);
        Route::post('update', [PartnerController::class, 'update']);
        Route::delete('delete', [PartnerController::class, 'delete']);
    });

    Route::group(['prefix' => 'courier'], function () {
        Route::get('all', [CourierController::class, 'all']);
        Route::post('update', [CourierController::class, 'update']);
        Route::delete('delete', [CourierController::class, 'delete']);
    });
    Route::group(['prefix' => 'product'], function () {
        Route::get('all', [ProductController::class, 'all']);
        Route::post('update', [ProductController::class, 'update']);
        Route::delete('delete', [ProductController::class, 'delete']);
    });

    Route::group(['prefix' => 'delivery'], function () {
        Route::get('all', [DeliveryController::class, 'all']);
        Route::post('update', [DeliveryController::class, 'update']);
        Route::delete('delete', [DeliveryController::class, 'delete']);
    });

    Route::group(['prefix' => 'checkMapping'], function () {
        Route::get('all', [SaController::class, 'sa_tsp']);
    });

});
