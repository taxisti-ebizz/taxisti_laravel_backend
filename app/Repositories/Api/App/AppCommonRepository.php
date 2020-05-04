<?php


namespace App\Repositories\Api\App;

use App\GCM;
use App\Models\User;
use App\Models\Driver;
use App\Models\Ratting;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AppCommonRepository extends Controller
{

    // update profile
    public function update_profile($request)
    {
        $input['first_name'] = $request['first_name']; 
        $input['last_name'] = $request['last_name']; 
        $input['mobile_no'] = $request['phone']; 
        $input['date_of_birth'] = $request['dob']; 
        $input['user_type'] = $request['user_type']; 
        $input['device_type'] = $request['device_type']; 
        $input['device_token'] = $request['device_token']; 
        $imageName = '';

        // profile_pic handling 
        if ($request->file('profile_pic')) {
    
            // delete files
            $user_data = User::where('user_id',$request['user_id'])->first();
            Storage::disk('s3')->exists($user_data->profile_pic) ? Storage::disk('s3')->delete($user_data->profile_pic) : '';

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/users/' . time() . '.' . $profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
        }

        // update data
        User::where('user_id',$request['user_id'])->update($input);

        $user_data = User::where('user_id',$request['user_id'])->first();
        $user_data->profile_pic = $user_data->profile_pic != '' ? env('AWS_S3_URL').$user_data->profile_pic : '';

        if($user_data->user_type == 1)
        {
            $user_data->driver_detail = $this->get_driver_detail($user_data->user_id);
        }
        
        return response()->json([
            'status'    => true,
            'message'   => 'Profile update successfully', 
            'data'    => $user_data,
        ], 200);
    }

    // admin setting
    public function admin_setting($request)
    {

        $setting_data = DB::table('taxi_option')->get();
        $data = [];
        foreach ($setting_data as $value) {
            $data[$value->option_name] = $value->option_value;
        }

        $data['ios_app_start_time'] = $data['app_start_time'];
        $data['ios_app_close_time'] = $data['app_close_time'];

        if(isset($data['app_start_time']) && $data['app_start_time']!='' && json_encode($data['app_close_time']) && $data['app_close_time']!='')
        {
            $data['app_status']=false;
            $startTime=strtotime($data['app_start_time']);
            $endTime=strtotime($data['app_close_time']);
            $timestamp = strtotime(date("h:i:s")) + (60*60*8);
            $time = date('H:i:s', $timestamp);
            $currentTime=strtotime($time);
            if($currentTime>=$startTime && $currentTime<=$endTime){
                $data['app_status']=true;
            }
        }
        if(isset($data['status']) && $data['status']!='')
        {
            if($data['status']==1)
            {
                $data['app_status']=true;
            }elseif($data['status']==0)
            {
                $data['app_status']=false;
            }
        }

        $coordinates = DB::table('taxi_ride_area_coordinates')->get();
        $temp_array = [];
        foreach ($coordinates as  $value) {
            
            $temp = json_decode($value->coordinates);  
            $temp_array[] = $temp;
        
        }
        $data['coordinates']=$temp_array;


        $setting_data = $data;
        
        return response()->json([
            'status'    => true,
            'message'   => 'Admin setting data', 
            'data'    => $setting_data,
        ], 200);
    }

    // add user promotion
    public function add_user_promotion($request)
    {
        $msg = [];
        $result = DB::table('taxi_promotion')
            ->where('code',$request['code'])
            ->where('type',$request['type'])->first();

        if($result->start_date <= date('Y-m-d') && date('Y-m-d') <= $result->end_date)
        {
            if($result->user_limit != $result->limit_usage)
            {
                $checkPromo = DB::table('taxi_user_promotion')
                    ->where('user_id',$request['user_id'])
                    ->where('promotion_id',$result->id)->first();

                if(!$checkPromo)
                {	
                    $input['user_id'] = $request['user_id'];
                    $input['promotion_id'] = $result->id;
                    $input['type'] = $result->type;
                    $input['created_at'] = date('Y-m-d H:m:d');


                    $insert = DB::table('taxi_user_promotion')->insert($input);
                    if($insert)
                    {
                        $limit_usage = $result->limit_usage + 1;
                
                        $result->promo_image = $result->promo_image != '' ? env('AWS_S3_URL').$result->promo_image : '' ;
                        $result->user_limit = $result->user_limit - $limit_usage;
                    

                        $update['limit_usage'] = $limit_usage;
                        $update['updated_at'] = date('Y-m-d H:m:d');

                        DB::table('taxi_promotion')->where('id',$result->id)->update($update);

                        $msg['status'] = true;
                        $msg['message'] = 'Success';
                        $msg['data'] = $result;
                    }
                }
                else
                {
                    $msg['status'] = false;
                    $msg['message'] = 'Promo code already used';
                    $msg['message_ar'] = 'لقد تم استخدام الرمز سابقا';
                    $msg['data'] = array();
                }
            }
            else
            {
                $msg['status'] = false;
                $msg['message'] = 'Promo code limit reached. Try another one!';
                $msg['message_ar'] = 'بلغ العرض الحد الاقصى من الاستخدامات ';
                $msg['data'] = array();
            }
        }	
        else
        {
            $msg['status'] = false;
            $msg['message'] = 'Promo code is expired. Try another one!';
            $msg['message_ar'] = 'الرمز الذي أدخلته منتهي الصلاحية';
            $msg['data'] = array();
        }

        return response()->json($msg, 200);
    } 

    // apply promotion
    public function apply_promotion($request)
    {
        $promotion_data = DB::table('taxi_promotion')
            ->where('code',$request['code'])
            ->where('type',$request['type'])->first();

        if($promotion_data)
        {
            $promotion_data->promo_image = $promotion_data->promo_image != '' ? env('AWS_S3_URL').$promotion_data->promo_image : '' ;

            return response()->json([
                'status'    => true,
                'message'   => 'Promotion data', 
                'data'    => $promotion_data,
            ], 200);
        }
        else{

            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // auto logout
    public function auto_logout($request)
    {
        $auto_logout = User::where('user_id',$request['user_id'])->where('device_token',$request['device_token'])->first();

        if($auto_logout)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Logged In', 
                'login_flag'    => 1,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Not Logged In', 
                'login_flag'    => 2,
            ], 200);
        }
    }

    // check phone
    public function check_phone($request)
    {
        $check_phone = User::where('mobile_no',$request['phone'])->first();
        if($check_phone)
        {
            if($check_phone->password != '')
            {
                $msg['status']=true;
                $msg['message']='Phone already exist.';
                $msg['message_ar'] = "رقم الهاتف مسجل";
                $msg['set_password'] = 1;
            }
            else
            {
                $msg['status']=true;
                $msg['message']='Phone already exist.';
                $msg['message_ar'] = "رقم الهاتف مسجل";
                $msg['set_password'] = 0;
            }
        }	
        else{
            $msg['status']=false;
            $msg['message']='Phone Number do Not Exist.';
            $msg['message_ar'] = "رقم الهاتف غير مسجل";
        }

        return response()->json($msg, 200);

    }

    // check promotion status
    public function check_promotion_status($request)
    {
        $promotion_data = DB::table('taxi_user_promotion')
            ->join('taxi_promotion','taxi_promotion.id','taxi_user_promotion.promotion_id')
            ->where('taxi_user_promotion.user_id',$request['user_id'])
            ->where('taxi_user_promotion.type',$request['type'])
            ->where('taxi_user_promotion.is_deleted',0)
            ->where('taxi_user_promotion.redeem',0)->first();


        if($promotion_data)
        {
            $promotion_data->promo_image = $promotion_data->promo_image != '' ? env('AWS_S3_URL').$promotion_data->promo_image : '' ;

            return response()->json([
                'status'    => true,
                'message'   => 'Promotion data', 
                'data'    => $promotion_data,
            ], 200);
        }
        else{

            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // check login
    public function check_login($request)
    {
        $old_device_token = User::where('user_id',$request['user_id'])->first();

        if($old_device_token)
        {
            if($old_device_token->device_token != '')
            {
                if($request['fcm'] != $old_device_token->device_token)
                {
                    User::where('user_id',$request['user_id'])->update(['device_token'=>'']);
    
                    $msg['status']=true;
                    $msg['message']='You are logout';
                    $msg['data']['user_id'] = $request['user_id'];
                }
                else
                {
                    $update['device_token'] = $request['fcm'];
                    $update['device_type'] = $request['device_type'];
                    User::where('user_id',$request['user_id'])->update($update);
                    
                    $msg['status']=false;
                    $msg['message']='You login successfully';
                    $msg['data'] = array();
                }
            }
            else
            {
                $update['device_token'] = $request['fcm'];
                $update['device_type'] = $request['device_type'];
                User::where('user_id',$request['user_id'])->update($update);
    
                $msg['status']=false;
                $msg['message']='You login successfully';
                $msg['data'] = array();
            }
        }
        else
        {
            $msg['status']=true;
            $msg['message']='User not exist';
            $msg['data'] = array();
        }

        return response()->json($msg, 200);

    }

    // contact us
    public function contact_us($request)
    {
        date_default_timezone_set("Africa/Tripoli");

        $input['user_id'] =  $request['user_id'];
        $input['message'] =  $request['message'];
        $input['status'] =  1;
        $input['created_date'] =  date('Y-m-d H:i:s');

        $contact_us = DB::table('taxi_contact_us')->insert($input);
        
        if($contact_us)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => array(),
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Faild', 
                'data'    => array(),
            ], 200);
        }
    }

    // delete promotion
    public function delete_promotion($request, $id)
    {
        if($id != '')
        {
            $input['is_deleted'] = 1;
            $input['updated_at'] = date('Y-m-d H:i:s');
            $update = DB::table('taxi_user_promotion')->where('id',$id)->update($input);
            if($update)
            {
                $msg['status'] = true;
                $msg['message'] = 'Promo code deleted successfully';
                $msg['message_ar'] = 'تم مسح العرض بنجاح';
            }
            else
            {
                $msg['status'] = false;
                $msg['message'] = 'Failed to deleted promo code';
                $msg['message_ar'] = 'حدث خطأ في مسح العرض';
            }
        }

        return response()->json($msg, 200);

    }

    // get cms page
    public function get_cms_page($request)
    {

        $taxi_pages = DB::table('taxi_pages')->where('page_title',$request['page_title'])->first();
        
        if($taxi_pages)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $taxi_pages,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // get ratting
    public function get_ratting($request)
    {

        if($request['type'] == 'rider')
        {
            $ratting = Ratting::select(
                DB::raw('coalesce(AVG(ratting),0) as avgrating, count(review) as countreview'))
            ->where('review_by','driver')
            ->where('rider_id',$request['id'])->first();

        }
        elseif($request['type'] == 'driver')
        {
            $ratting = Ratting::select(
                DB::raw('coalesce(AVG(ratting),0) as avgrating, count(review) as countreview'))
            ->where('review_by','rider')
            ->where('driver_id',$request['id'])->first();

        }
        
            
        if($ratting)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $ratting,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }


    // get request_detail
    public function get_request_detail($request)
    {

        $request_detail = Request::where('id',$request['request_id'])->first();
            
        if($request_detail)
        {
            $request_detail['driver_detail'] = $this->get_driver($request_detail['driver_id']);
            $request_detail['rider_detail'] = $this->get_rider($request_detail['rider_id']);

            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $request_detail,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // logout
    public function logout($request)
    {

        $user_data = User::where('user_id',$request['user_id'])->first();
            
        if($user_data)
        {
            $update['device_type'] = '';
            $update['device_token'] = '';
            $update['updated_date'] = date('Y-m-d H:m:s');

            User::where('user_id',$request['user_id'])->update($update);

            return response()->json([
                'status'    => true,
                'message'   => 'Logout successfully', 
                'data'    => array(),
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Logout unsuccessfully', 
                'data'    => array(),
            ], 200);
        }
    }

    // get request list
    public function get_request_list($request)
    {
        $request_list = [];
        if($request['rider_id'] != '')
        {
            $request_list = Request::where('rider_id',$request['rider_id'])->where('status','!=',4)->where('ride_status_text','Ride Completed')->get();

        }
        elseif($request['driver_id'] != '')
        {
            $request_list = Request::where('driver_id',$request['driver_id'])->where('status','!=',4)->where('ride_status_text','Ride Completed')->get();

        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'driver_id or rider_id is required', 
                'data'    => array(),
            ], 200);            
        }

            
        if($request_list)
        {
            $list = [];
            foreach ($request_list as  $value) {
                
                if($request['rider_id'] != '')
                {
                    $user_detail = $this->get_driver($value['driver_id']);
                }
                elseif($request['driver_id'] != '')
                {
                    $user_detail = $this->get_driver($value['driver_id']);
                }
                
                $value['request_id'] = $value['id'];   
                $value['profile_pic'] = $user_detail['profile_pic'];  
                $value['first_name'] = $user_detail['first_name'];   
                $value['last_name'] = $user_detail['last_name'];   
                $value['ratting'] = $user_detail['ratting'];
                $list[] = $value; 

            }

            $request_list = $list;

            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $request_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // add review
    public function add_review($request)
    {

        $input = $request->except(['review_type']);
        $input['created_date'] = date('Y-m-d H:m:d');
            
        if($request['review_type'] == 'byrider')
        {
            $input['review_by'] = 'rider';
        }
        else
        {
            $input['review_by'] = 'driver';
        }

        $review = Ratting::insert($input);

        if($review)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Review add successfully', 
                'data'    => array(),
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Review add Failed', 
                'data'    => array(),
            ], 200);
        }
    }








    
    // sub function --------------------------


    // get rider 
    public function get_rider($rider_id)
    {
        $rider  = User::where('user_id',$rider_id)->first();
        $rider['profile_pic'] = $rider['profile_pic'] != '' ? env('AWS_S3_URL').$rider['profile_pic'] : '';
        $rider['ratting'] = $this->get_rider_ratting($rider_id);

        return $rider; 
    }

    // get driver 
    public function get_driver($driver_id)
    {
        $driver  = User::where('user_id',$driver_id)->first();
        $driver['ratting'] = $this->get_driver_ratting($driver_id);
        $driver['driver_detail'] = $this->get_driver_detail($driver_id);

        return $driver; 
    }

    // get driver detail
    public function get_driver_detail($driver_id)
    {
        $driver_detail = Driver::where('driver_id',$driver_id)->first();

        if($driver_detail)
        {
            $driver_detail['profile'] = $driver_detail['profile'] != '' ? env('AWS_S3_URL').$driver_detail['profile'] : '';
            $driver_detail['licence'] = $driver_detail['licence'] != '' ? env('AWS_S3_URL').$driver_detail['licence'] : '';

        }
        else {
            $driver_detail = array();
        }

        return $driver_detail;

    }

    // get driver ratting
    public function get_driver_ratting($driver_id)
    {
        $ratting = Ratting::select(
            DB::raw('coalesce(AVG(ratting),0) as avgrating, count(review) as countreview'))
        ->where('review_by','rider')
        ->where('driver_id',$driver_id)->first();

        return $ratting;
    }

    // get rider ratting
    public function get_rider_ratting($rider_id)
    {
        $ratting =  Ratting::select(
            DB::raw('coalesce(AVG(ratting),0) as avgrating, count(review) as countreview'))
        ->where('review_by','driver')
        ->where('rider_id',$rider_id)->first();

        return $ratting;
    }

    // silent Notification To Old Device
    public function silentNotificationToOldDevice($device_token,$device_type,$user_id)
    {
           
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
    
        $msg = 'You logout from app.';
    
        if ($device_type == 'A')
        {
            $fields = array(
                    'to' => $device_token,
                    "content_available"=> true,
                    'data' => array('message' => $msg ,'type' => 'silent_logout_notification','body'=>$user_id,'title' => 'Logout'),          
                );
        }
        else if ($device_type == 'I')
        {
            $fields = array(
                    'to' => $device_token,
                    "content_available"=> true,
                    'notification' => array('title' => 'silent_logout_notification','body'=>$msg),
                    'data' => array('message' => $msg ,'type' => 'silent_logout_notification','body'=>$user_id,'title' => 'Logout')
                );
            //APNS====================================================
            
            
            // include_once '../../../public/ios_notif/GCM.php';
            
            $message = 'You logout from app.';
            $body1=json_decode('{"alert":"'.$message.'","sound":"default","badge":1,"user_id":'.$user_id.',"type":"silent_logout_notification"}');
            $deviceToken =  $device_token;
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
            stream_context_set_option($ctx, 'ssl', 'passphrase', '1');
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            $body['aps'] =$body1; 
            // Encode the payload as JSON
            $payload = json_encode($body);
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            fwrite($fp, $msg, strlen($msg));
    
            $regId = $device_token;
            $gcm = new GCM();
            $registatoin_ids = array($regId);
            $message = array("price" => $message);
            $a=$gcm->send_notification($registatoin_ids, $message);
        }
        else 
        {
            $fields = array(
                    'to' => $device_token,
                    "content_available"=> true,
                    'notification' => array('title' => 'Ride Request','body'=>$msg),
                    'data' => array('message' => $msg ,'type' => 'silent_logout_notification','body'=>$user_id,'title' => 'Logout'),
                    
                );
        }
        
        $headers = array(
            'Authorization:key=AIzaSyAlN84WM8MaPgO_JPRKvLi1bFvWyI_DT1A',
            'Content-Type:application/json'
        );       
        $type='silent_logout_notification';
        
        $this->store_notification($user_id,$type,$fields['data']);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        //$data['body'] = $fields['data']['body'];
        // return $data;
        return true;

    }

    // store notification
    public function store_notification($user_id,$type,$msg)
    {

        $input['type'] = $type;
        $input['message'] = json_encode($msg);
        $input['user_id'] = $user_id;
        $input['datetime'] = date('Y-m-d H:m:s');

        DB::table('taxi_notification')->insert($input);
        return true;
    }
    
    // qb create session with user
    public function qb_create_session_with_user ()
    {
        DEFINE('APPLICATION_ID', 69589);
        DEFINE('AUTH_KEY', "YKnMcUtfn792W-e");
        DEFINE('AUTH_SECRET', "fUDgC4R4qmGzwNr");

        // User credentials
        DEFINE('USER_LOGIN', "Taxisti");                  // Your Project Name in QuickBlox
        DEFINE('USER_PASSWORD', "Taxisti2016libya");      // QuickBlox Password

        // Quickblox endpoints
        DEFINE('QB_API_ENDPOINT', "https://api.quickblox.com");
        DEFINE('QB_PATH_SESSION', "session.json");

        // Generate signature
        $nonce = rand();
        $timestamp = time(); // time() method must return current timestamp in UTC but seems like hi is return timestamp in current time zone
        $signature_string = "application_id=".APPLICATION_ID."&auth_key=".AUTH_KEY."&nonce=".$nonce."&timestamp=".$timestamp."&user[login]=".USER_LOGIN."&user[password]=".USER_PASSWORD;

        $signature = hash_hmac('sha1', $signature_string , AUTH_SECRET);

        // Build post body
        $post_body = http_build_query(array(
                        'application_id' => APPLICATION_ID,
                        'auth_key' => AUTH_KEY,
                        'timestamp' => $timestamp,
                        'nonce' => $nonce,
                        'signature' => $signature,
                        'user[login]' => USER_LOGIN,
                        'user[password]' => USER_PASSWORD
                        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_SESSION); // Full path is - https://api.quickblox.com/session.json
        curl_setopt($curl, CURLOPT_POST, true); // Use POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body); // Setup post body
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Receive server response

        // Execute request and read responce
        $responce = curl_exec($curl);

        // Check errors
        if ($responce) {
                // echo $responce . "\n";
        } else {
                $error = curl_error($curl). '(' .curl_errno($curl). ')';
                echo $error . "\n";
        }

        // Close connection
        curl_close($curl);

        return $responce;
    }

    // qb delete old subscription
    public function qb_delete_old_subscription ($old_device_token) 
    {
        $session_user = $this->qb_create_session_with_user();
        $session_data = json_decode($session_user);
        $token = $session_data->session->token;
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.quickblox.com/users/$old_device_token.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");


        $headers = array();
        $headers[] = "Quickblox-Rest-Api-Version: 0.1.0";
        $headers[] = "Qb-Token: $token";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        // return $result;
        return true;

    }


}