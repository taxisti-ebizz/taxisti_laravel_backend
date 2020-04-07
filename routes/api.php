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
Route::post('adminLogin','Api\Admin\Auth\AdminLoginController@login');
Route::post('adminRegister','Api\Admin\Auth\AdminRegisterController@create');


// Admin Auth Routes
Route::group(['middleware' => 'auth:admin'], function(){
    

    // USER
    Route::post('getUserList','Api\Admin\UserController@get_user_list');
    Route::post('getUserDetail','Api\Admin\UserController@get_user_detail');
    Route::post('editUserDetail','Api\Admin\UserController@edit_user_detail');
    Route::delete('deleteUser/{user_id}','Api\Admin\UserController@delete_user');

    // DRIVER
    Route::post('getDriverList','Api\Admin\DriverController@get_driver_list');



    
});
