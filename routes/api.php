<?php

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

// Admin Gust Routes
Route::post('adminLogin','API\Admin\Auth\AdminLoginController@login');
Route::post('adminRegister','API\Admin\Auth\AdminRegisterController@create');


Route::group(['middleware' => 'auth:admin'], function(){
    
    // Admin Auth Routes
    Route::post('getUserList','API\Admin\UserController@get_user_list');
    
});
