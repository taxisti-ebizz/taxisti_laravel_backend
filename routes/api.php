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


// Admin Routes -----------------------------------------------------------
Route::prefix('management')->group(function () {
    
    // Admin Gust Routes
    Route::post('adminLogin','Api\Admin\Auth\AdminLoginController@login');
    Route::post('adminRegister','Api\Admin\Auth\AdminRegisterController@create');
    Route::post('distance','Api\Admin\Auth\AdminRegisterController@distance');

    // Admin Auth Routes
    Route::group(['middleware' => 'auth:admin'], function(){
    
        // USER
        Route::post('getUserList','Api\Admin\Users\UserController@get_user_list');
        Route::post('getUserDetail','Api\Admin\Users\UserController@get_user_detail');
        Route::post('editUserDetail','Api\Admin\Users\UserController@edit_user_detail');
        Route::post('updateUserStatus','Api\Admin\Users\UserController@edit_user_status');
        Route::delete('deleteUser/{user_id}','Api\Admin\Users\UserController@delete_user');

        // DRIVER
        Route::post('getDriverList','Api\Admin\Driver\DriverController@get_driver_list');
        Route::post('getDriverDetail','Api\Admin\Driver\DriverController@get_driver_detail');
        Route::post('editDriverDetail','Api\Admin\Driver\DriverController@edit_driver_detail');
        Route::post('updateDriverStatus','Api\Admin\Driver\DriverController@edit_driver_status');
        Route::delete('deleteDriver/{driver_id}','Api\Admin\Driver\DriverController@delete_driver');
        Route::delete('deleteCarImage/{id}','Api\Admin\Driver\DriverController@delete_car_image');


        // RIDE
        Route::post('getPendingRideList','Api\Admin\Ride\PendingRideController@get_pending_ride_list');
        Route::post('getRunningRideList','Api\Admin\Ride\RunningRideController@get_running_ride_list');
        Route::post('getCompletedRideList','Api\Admin\Ride\CompleteRideController@get_completed_ride_list');
        Route::post('getNoResponseRideList','Api\Admin\Ride\NoResponseRideController@get_no_response_ride_list');
        Route::post('getCanceledRideList','Api\Admin\Ride\CanceledRideController@get_canceled_ride_list');
        Route::post('getNoDriverAvailableList','Api\Admin\Ride\NoDriverAvailableRideController@get_no_driver_available_list');
        Route::post('getFakeRideList','Api\Admin\Ride\FakeRideController@get_fake_ride_list');
        Route::post('deleteRide','Api\Admin\Ride\RideCommonController@delete_ride');
        Route::post('completeRide','Api\Admin\Ride\RideCommonController@complete_ride');


        // REVIEW
        Route::post('getDriverReviews','Api\Admin\Review\DriverReviewController@get_driver_reviews');
        Route::post('viewDriverReviews','Api\Admin\Review\DriverReviewController@view_driver_reviews');
        Route::post('getRiderReviews','Api\Admin\Review\UserReviewController@get_rider_reviews');
        Route::post('viewRiderReviews','Api\Admin\Review\UserReviewController@view_rider_reviews');

        // RIDE AREA SETTING LIST
        Route::post('getRideAreaList','Api\Admin\RideArea\RideAreaController@get_ride_area_list');
        Route::post('viewAreaBoundaries','Api\Admin\RideArea\RideAreaController@view_area_boundaries');
        Route::post('addAreaBoundaries','Api\Admin\RideArea\AddRideAreaController@add_area_boundaries');
        Route::delete('deleteAreaBoundaries/{id}','Api\Admin\RideArea\RideAreaController@delete_area_boundaries');
        

        // PROMOTION 
        Route::post('getPromotionList','Api\Admin\Promotion\PromotionController@get_promotion_list');
        Route::post('updatePromotionDetail','Api\Admin\Promotion\PromotionController@update_promotion_detail');
        Route::delete('deletePromotion/{id}','Api\Admin\Promotion\PromotionController@delete_promotion');
        Route::post('addPromotion','Api\Admin\Promotion\AddPromotionController@add_promotion');
        Route::post('getUserPromotionList','Api\Admin\Promotion\PromotionUserController@get_user_promotion_list');
        Route::post('redeemPromotionList','Api\Admin\Promotion\PromotionUserController@redeem_promotion');

        // OPTIONS
        Route::post('getOptions','Api\Admin\Options\OptionsController@get_options');
        Route::post('updateOptions','Api\Admin\Options\OptionsController@update_options');

        // CONTACT US 
        Route::post('getContactUsList','Api\Admin\ContactUs\ContactUsController@get_contact_us_list');
        Route::post('viewContactUsMessage','Api\Admin\ContactUs\ContactUsController@view_contact_us_message');
        Route::delete('deleteContactUs/{id}','Api\Admin\ContactUs\ContactUsController@delete_contact_us');

        // NOTIFICATION    
        Route::post('sendNotification','Api\Admin\Notification\NotificationController@send_notification');
        Route::post('getSpecificUserList','Api\Admin\Notification\NotificationController@get_specific_user_list');

        
        // PAGE
        Route::post('getPageList','Api\Admin\Page\PageController@get_page_list');
        Route::post('addPage','Api\Admin\Page\AddPageController@add_page');
        Route::post('editPage','Api\Admin\Page\PageController@edit_page');
        Route::delete('deletePage/{id}','Api\Admin\Page\PageController@delete_page');
        
        // SUBADMIN
        Route::post('getSubAdminList','Api\Admin\SubAdmin\SubAdminController@get_sub_admin_list');
        Route::post('getSubAdmin','Api\Admin\SubAdmin\SubAdminController@get_sub_admin');
        Route::post('updateSubAdminStatus','Api\Admin\SubAdmin\SubAdminController@update_sub_admin_status');
        Route::delete('deleteSubAdmin/{id}','Api\Admin\SubAdmin\SubAdminController@delete_sub_admin');
        Route::post('addSubAdmin','Api\Admin\SubAdmin\AddSubAdminController@add_sub_admin');

        // DRIVER LOG
        Route::post('getDriverOnlineLog','Api\Admin\DriverOnlineLog\DriverOnlineLogController@get_driver_online_log');

        // ADMIN
        Route::post('updateAdminProfile','Api\Admin\AdminProfile\AdminProfileController@update_admin_profile');

        // DASHBOARD 
        Route::post('getDashboardData','Api\Admin\Dashboard\DashboardController@get_dashboard_data');
    
    });
    
});



// App Routes ---------------------------------------------------------------

// App Common Routes
Route::prefix('common')->group(function () {
    
    // App Gust Routes
    Route::post('appLogin','Api\App\Auth\AppLoginController@login');
    Route::post('appRegister','Api\App\Auth\AppRegisterController@create');
    Route::post('checkPhone','Api\App\AppCommonController@check_phone');
    Route::post('storePassword','Api\App\AppCommonController@storePassword');
    Route::post('forceUpdateAndroid','Api\App\AppCommonController@force_update_android');
    Route::post('forceUpdateIos','Api\App\AppCommonController@force_update_ios');



    // App Auth Routes
    Route::group(['middleware' => 'auth:api'], function(){
        Route::post('updateProfile','Api\App\AppCommonController@update_profile');
        Route::post('adminSetting','Api\App\AppCommonController@admin_setting');
        Route::post('addUserPromotion','Api\App\AppCommonController@add_user_promotion');
        Route::post('applyPromotion','Api\App\AppCommonController@apply_promotion');
        Route::post('autoLogout','Api\App\AppCommonController@auto_logout');
        Route::post('checkPromotionStatus','Api\App\AppCommonController@check_promotion_status');
        Route::post('checkLogin','Api\App\AppCommonController@check_login');
        Route::post('contactUs','Api\App\AppCommonController@contact_us');
        Route::delete('deletePromotion/{id}','Api\App\AppCommonController@delete_promotion');
        Route::post('getCmsPage','Api\App\AppCommonController@get_cms_page');
        Route::post('getRatting','Api\App\AppCommonController@get_ratting');
        Route::post('getRequestDetail','Api\App\AppCommonController@get_request_detail');
        Route::post('logout','Api\App\AppCommonController@logout');
        Route::post('getRequestList','Api\App\AppCommonController@get_request_list');
        Route::post('addReview','Api\App\AppCommonController@add_review');
        Route::post('rideRequestAutomation','Api\App\AppCommonController@ride_request_automation');
        Route::post('switchUser','Api\App\AppCommonController@switch_user');
        Route::post('returnStatus','Api\App\AppCommonController@return_status');
        Route::post('updateFcm','Api\App\AppCommonController@update_fcm');






    });

});


// App Auth Routes
Route::group(['middleware' => 'auth:api'], function(){

    // App Drivers Routes
    Route::prefix('drivers')->middleware('role:drivers')->group(function () {
        Route::delete('deleteCarImage/{id}','Api\App\DriverController@delete_car_image');
        Route::post('getCarImage','Api\App\DriverController@get_car_image');
        Route::post('driverStatus','Api\App\DriverController@get_driver_status');
        Route::post('driverDetail','Api\App\DriverController@driver_detail');
        Route::post('requestAction','Api\App\DriverController@request_action');
        
    });
    
    // App Riders Routes
    Route::prefix('riders')->middleware('role:riders')->group(function () {
        Route::post('getDriver','Api\App\RiderController@get_driver');
        Route::post('requestRide','Api\App\RiderController@request_ride');


    });


});
