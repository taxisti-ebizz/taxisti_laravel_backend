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
    Route::post('updateUserStatus','Api\Admin\UserController@edit_user_status');
    Route::delete('deleteUser/{user_id}','Api\Admin\UserController@delete_user');

    // DRIVER
    Route::post('getDriverList','Api\Admin\DriverController@get_driver_list');
    Route::post('getDriverDetail','Api\Admin\DriverController@get_driver_detail');
    Route::post('editDriverDetail','Api\Admin\DriverController@edit_driver_detail');
    Route::post('updateDriverStatus','Api\Admin\DriverController@edit_driver_status');
    Route::delete('deleteDriver/{driver_id}','Api\Admin\DriverController@delete_driver');

    // RIDE
    Route::post('getPendingRideList','Api\Admin\RideController@get_pending_ride_list');
    Route::post('getRunningRideList','Api\Admin\RideController@get_running_ride_list');
    Route::post('getCompletedRideList','Api\Admin\RideController@get_completed_ride_list');
    Route::post('getNoResponseRideList','Api\Admin\RideController@get_no_response_ride_list');
    Route::post('getCanceledRideList','Api\Admin\RideController@get_canceled_ride_list');
    Route::post('getNoDriverAvailableList','Api\Admin\RideController@get_no_driver_available_list');
    Route::post('getFakeRideList','Api\Admin\RideController@get_fake_ride_list');

    // REVIEW
    Route::post('getDriverReviews','Api\Admin\DriverController@get_driver_reviews');
    Route::post('viewDriverReviews','Api\Admin\DriverController@view_driver_reviews');
    Route::post('getRiderReviews','Api\Admin\UserController@get_rider_reviews');
    Route::post('viewRiderReviews','Api\Admin\UserController@view_rider_reviews');

    // RIDE AREA SETTING LIST
    Route::post('getRideAreaList','Api\Admin\RideController@get_ride_area_list');
    Route::post('viewAreaBoundaries','Api\Admin\RideController@view_area_boundaries');

    
    
});
