<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SanctumIsValid;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\HistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function() {
    Route::group(['middleware' => [SanctumIsValid::class]], function() {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::group(['middleware' => [SanctumIsValid::class]], function() {
    Route::group(['prefix' => 'article'], function() {
        Route::get('', [ArticleController::class, 'index']);
        Route::get('/detail/{id}', [ArticleController::class, 'detail']);
        Route::post('/create', [ArticleController::class, 'create']);
        Route::post('/update/{id}', [ArticleController::class, 'update']);
        Route::delete('/delete/{id}', [ArticleController::class, 'delete']);
    });
    Route::group(['prefix' => 'history'], function() {
        Route::get('', [HistoryController::class, 'index']);
        Route::get('/detail/{id}', [HistoryController::class, 'detail']);
        Route::post('/create', [HistoryController::class, 'create']);
    });
});