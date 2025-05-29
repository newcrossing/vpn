<?php

use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::controller(App\Http\Controllers\Api\UserAuthController::class)->group(function(){
    Route::post('v1/login', 'login');
    Route::post('v1/register', 'register');
});

Route::middleware(['auth:sanctum'])->controller(UserAuthController::class)->group(function(){
    Route::get('v1/logout', 'logout');
    Route::get('v1/user', 'user');
    Route::post('v1/user-edit', 'user_edit');
    Route::post('v1/change-password', 'change_password');
});