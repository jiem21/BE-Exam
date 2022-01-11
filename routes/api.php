<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductsController;
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

Route::post('/register', [ AuthController::class, 'register' ] );
Route::post('/login', [ AuthController::class, 'login' ] );
Route::get('/products/{id}', [ ProductsController::class, 'show' ] );

// Authenticated API
Route::group([ 'middleware' => ['auth:sanctum'] ], function () {
    Route::get('/users', [ AuthController::class, 'index' ] );
    Route::post('/logout', [ AuthController::class, 'logout' ] );
    Route::get('/products', [ ProductsController::class, 'index' ] );
    Route::post('/order', [ ProductsController::class, 'order' ] );
});
