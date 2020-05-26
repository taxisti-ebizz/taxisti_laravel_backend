<?php

namespace App\Repositories\Api\App\Auth;

use App\Models\User;
use App\Models\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Api\App\AppCommonRepository;

class AppLoginRepository extends Controller
{
    protected $appCommon;

    public function __construct()
    {
        $this->appCommon = new AppCommonRepository;
    }

    // Login
    public function login($request)
    {
        if($user = User::where(['mobile_no' => $request['phone'],'password' => md5($request['password'])])->first()){ 

            Auth::login($user);

            if($user->user_type == 1)
            {
                $check_ride_start_or_not = Request::where('status',1)->where('driver_id',$user->user_id)->first();
                if($check_ride_start_or_not)
                {
                    return response()->json([
                        'status'    => false,
                        'message'   => "Your Ride is already runnig so you can't login now.", 
                        'data'    => array(),
                    ], 200);   
                }
            }
            else 
            {
                $check_ride_start_or_not = Request::where('status',1)->where('rider_id',$user->user_id)->first();
                if($check_ride_start_or_not)
                {
                    return response()->json([
                        'status'    => false,
                        'message'   => "Your Ride is already runnig so you can't login now.", 
                        'data'    => array(),
                    ], 200);   
                }

            }

            $this->appCommon->silentNotificationToOldDevice($user->device_token,$user->device_type,$user->user_id);
            $this->appCommon->qb_delete_old_subscription($user->device_token);

            $inpute['device_token'] = $request['device_token'];
            $inpute['device_type'] = $request['device_type'];
            $inpute['updated_date'] = date('Y-m-d H:i:s');

            // update device inform 
            User::where('user_id',$user->user_id)->update($inpute);


            // get user data
            $user_data =  User::where('user_id',$user->user_id)->first();
            $user_data->profile_pic = $user_data->profile_pic != '' ? env('AWS_S3_URL').$user_data->profile_pic : '';

            $success['token'] =  $user_data->createToken('Texi_App')->accessToken; 
            $success['data'] = $user_data; 
    
            
            return response()->json([
                'status'    => true,
                'message'   => 'Login successfully', 
                'data'    => $success,
            ], 200);
        } 
        else{ 
            return response()->json([
                'status'    => false,
                'message'   => "Password don't match", 
                'errors'    => '',
            ], 200);
        }
    }
    
}
