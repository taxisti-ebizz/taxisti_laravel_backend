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

class DriverRepository extends Controller
{
    
    // delete driver car image
    public function delete_car_image($request ,$id)
    {
        $image = DB::table('taxi_car_images')->where('id',$id)->first();

        $car_image_path = $image->image; 

        // delete files
        Storage::disk('s3')->exists($car_image_path) ? Storage::disk('s3')->delete($car_image_path) : '';

        DB::table('taxi_car_images')->where('id',$id)->delete();

        $image_list = $this->car_images($image->driver_detail_id);

        return response()->json([
            'status'    => true,
            'message'   => 'Car image deleted', 
            'data'    => $image_list,
        ], 200);   
    }

    // get driver car image
    public function get_car_image($request)
    {
        $driver = Driver::where('driver_id',$request['user_id'])->first();

        if($driver)
        {
            $car_image = $this->car_images($driver->id);

            return response()->json([
                'status'    => true,
                'message'   => 'Car Image', 
                'data'    => $car_image,
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

    // get driver status
    public function get_driver_status($request)
    {
        $driver = User::where('user_type',1)->where('user_id',$request['driver_id'])->first();

        if($driver)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $driver['verify'],
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Driver not exist', 
                'data'    => array(),
            ], 200);
        }
    }

    // driver detail
    public function driver_detail($request)
    {
        if($request['type'] =='add_driver')
        {

            $check_driver = Driver::where('driver_id',$request['user_id'])->first();

            if(!$check_driver)
            {
                // profile_pic handling 
                if($request->file('profile_pic')){

                    $profile_pic = $request->file('profile_pic');
                    $imageName = 'uploads/driver_images/'.time().'.'.$profile_pic->getClientOriginalExtension();
                    $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');

                    $input['profile_pic'] = $imageName;
                    $driver['profile'] = $imageName;
                                            
                }

                // licence handling 
                if($request->file('licence')){

                    $licence = $request->file('licence');
                    $imageName = 'uploads/licence_images/'.time().'.'.$licence->getClientOriginalExtension();
                    $img = Storage::disk('s3')->put($imageName, file_get_contents($licence), 'public');

                    $driver['licence'] = $imageName;
                                            
                }

                $driver['driver_id'] = $request['user_id'];
                $driver['car_brand'] = $request['car_brand'] != '' ? $request['car_brand'] : "";
                $driver['car_year'] = $request['car_year'] != '' ? $request['car_year'] : "";
                $driver['plate_no'] = $request['plate_no'] != '' ? $request['plate_no'] : "";
                $driver['availability'] = $request['availability'] != "" ? $request['availability'] : 0;
                $driver['current_location'] = $request['current_location'] !="" ? $request['current_location'] : "";
                $driver['latitude']=$request['lat'] !="" ? $request['lat'] : "";
                $driver['longitude']=$request['long'] !="" ? $request['long'] : "";
                $driver['car_pic'] = '';
                $driver['created_datetime'] = date('Y-m-d h:i:s');
        
                $add_driver_detail = Driver::insert($driver);
                
                if($add_driver_detail)
                {
                    
                    $input['user_type'] = 1;
                    $input['status'] = 0;
                    $input['updated_date'] = date('Y-m-d h:i:s');
                    isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                    isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                    isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';
                    isset($request['password']) ? $input['password'] = md5($request['password']) : '';

                    $update_user = User::where('user_id',$request['user_id'])->update($input);
                     
                    $msg['message']="Driver Details Added Successfully.";
                    $msg['message_ar'] = "تم إضافة معلومات السائق بنجاح";
                    $msg['status']=1;

                }
                else{
        
                    $msg['message']="Failed.";
                    $msg['status']=2;
                }
                
            }
            else{
                $msg['message']="User Allready Exists.";
                $msg['message_ar'] = "تم تسجيل السائق مسبقا";
                $msg['status']=2;
            }
        
        }
        elseif($request['type']=='add_car_images')
        {
            
            if(isset($request['driver_detail_id']) && $request['driver_detail_id']!='')
            {
                // car_image handling 
                if($request->file('car_pic'))
                {
                    
                    foreach ($request->file('car_pic') as  $car_image) {
    
                        // $car_image = $request->file('car_image');
                        $imageName = 'uploads/car_images/'.time().'.'.$car_image->getClientOriginalExtension();
                        $img = Storage::disk('s3')->put($imageName, file_get_contents($car_image), 'public');
    
                        $car['driver_detail_id'] = $request['driver_detail_id'];  
                        $car['image'] = $imageName;  
                        $car['datetime'] = date('Y-m-d H:m:s');  
                        $car_image = DB::table('taxi_car_images')->insert($car);
                    }

                    $msg['message']="Car Image Added Successfully.";
                    $msg['message_ar'] = "تم تحميل صورة السيارة بنجاح";
                    $msg['status']=1;
        
                }
                else
                {
                    $msg['message']="Failed.";
                    $msg['status']=2;
                }
                
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        elseif($request['type']=='edit_driver')
        {
            $driver_detail = Driver::where('driver_id',$request['user_id'])->first();

            // profile_pic handling 
            if($request->file('profile_pic')){

                // delete files
                Storage::disk('s3')->exists($driver_detail->profile) ? Storage::disk('s3')->delete($driver_detail->profile) : '';

                $profile_pic = $request->file('profile_pic');
                $imageName = 'uploads/driver_images/'.time().'.'.$profile_pic->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');

                $input['profile_pic'] = $imageName;
                $driver['profile'] = $imageName;
                                        
            }

            // licence handling 
            if($request->file('licence')){

                // delete files
                Storage::disk('s3')->exists($driver_detail->licence) ? Storage::disk('s3')->delete($driver_detail->licence) : '';

                $licence = $request->file('licence');
                $imageName = 'uploads/licence_images/'.time().'.'.$licence->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($licence), 'public');

                $driver['licence'] = $imageName;
                                        
            }

            isset($request['user_id']) ? $driver['driver_id'] = $request['user_id'] : '';
            isset($request['car_brand']) ? $driver['car_brand'] = $request['car_brand'] : '';
            isset($request['car_year']) ? $driver['car_year'] = $request['car_year'] : '';
            isset($request['plate_no']) ? $driver['plate_no'] = $request['plate_no'] : '';
            isset($request['availability']) ? $driver['availability'] = $request['availability'] : '';
            isset($request['current_location']) ? $driver['current_location'] = $request['current_location'] : '';
            isset($request['lat']) ? $driver['latitude'] = $request['lat'] : '';
            isset($request['long']) ? $driver['longitude'] = $request['long'] : '';
            $driver['last_update'] = date('Y-m-d h:i:s');
    
            $update_driver_detail = Driver::where('driver_id',$request['user_id'])->update($driver);
            
            if($update_driver_detail)
            {
                
                $input['user_type'] = 1;
                $input['status'] = 0;
                $input['updated_date'] = date('Y-m-d h:i:s');
                isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';

                $update_user = User::where('user_id',$request['user_id'])->update($input);
                    
                $msg['message']="Driver Details Updated.";
                $msg['message_ar'] = "تم تحميل معلومات السائق بنجاح";
                $msg['status']=1;
    

            }
            else{
    
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        
        }
        elseif($request['type']=='get_driver')
        {
            $driver = DB::table('taxi_driver_detail')
            ->select('taxi_driver_detail.*','taxi_users.*')
            ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
            ->where('taxi_driver_detail.driver_id',$request['user_id'])
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->car_images($driver->id);

                $msg['status']=1;
                $msg['message']="Success";
                $msg['data']=$driver;
        
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        elseif($request['type']=='get_driver_all_detail')
        {
            $driver = DB::table('taxi_driver_detail')
            ->select('taxi_driver_detail.*','taxi_users.*')
            ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
            ->where('taxi_driver_detail.driver_id',$request['user_id'])
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->car_images($driver->id);

                $msg['status']=1;
                $msg['message']="Success";
                $msg['data']=$driver;
        
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        
        return response()->json($msg, 200);

    }

    // request_action
    public function request_action($request)
    {
        $request_data = Request::where('id',$request['request_id'])->first();

        if($request['status'] == 1)
        {
            if($request_data->status == 0)
            {
                $update['status'] = 1;
                $update['updated_date'] = date('Y-m-d H:m:s');
                $update_status = Request::where('id',$request['request_id'])->update($update);
                $rider = $this->get_rider($request_data['rider_id']);

                $msg['status']=true;
                $msg['message']='Success';
                $msg['data'] = $rider;
            }
            elseif($request['status']!=0)
            {
                $msg['status']=false;
                $msg['message']='Action already taken for this Request';
            }
        }
        elseif($request['status'] == 2)
        {          
            if($request_data['status']==0)
            {

                $rider = $this->get_rider($request_data['rider_id']);
                $msg['data'] = $rider;
                if($request_data['all_driver']!='')
                {
                    $cur_driver_id = $request_data['driver_id'];
                    $cur_all_driver_id = explode(",",$request_data['all_driver']);
                    $cur_rej_driver_id = array();
                    if(strpos($request_data['rejected_by'],","))
                    {
                        $cur_rej_driver_id = explode(",",$request_data['rejected_by']);
                    }
                    else{
                        $cur_rej_driver_id[] = $request_data['rejected_by'];
                    }

                    $new_all_driver=array();
                    foreach($cur_all_driver_id as $cad)
                    {
                        if($cur_driver_id!=$cad)
                        {
                            $new_all_driver[]=$cad;
                        }
                        else
                        {
                            $cur_rej_driver_id[]=$cur_driver_id;
                        }
                    }
                    
                    if(empty($cur_rej_driver_id))
                    {
                        $cur_rej_driver_id[]=$cur_driver_id;
                    }
                    
                    $not_avlbl_drive_ids = [];
                    $new_driver = '';
                    foreach ($new_all_driver as $new_driver_id) 
                    {
                        if ($this->check_driver_availablity($new_driver_id))
                        {
                            $new_driver=$new_driver_id;
                            break;
                        }
                        else 
                        {
                            $not_avlbl_drive_ids[]  = $new_driver_id;
                            $cur_rej_driver_id[] = $new_driver_id;   
                        }
                    }
                    
                    if (!empty($not_avlbl_drive_ids))
                    {
                        $new_all_driver = array_diff($new_all_driver, $not_avlbl_drive_ids);
                    }

                    $new_all_d='';
                    foreach($new_all_driver as $nad)
                    {

                        if($new_all_d!='')
                        {
                            $new_all_d.=',';
                        }
                        $new_all_d.=$nad;
                    }

                    $new_rej='';
                    foreach($cur_rej_driver_id as $crd)
                    {
                        if($new_rej!='')
                        {
                            $new_rej.=',';
                        }
                        $new_rej.=$crd;
                    }

                    if($new_driver!='')
                    {
                        $update['status'] = 0;
                        $update['driver_id'] = $new_driver;
                        $update['rejected_by'] = $new_rej;
                        $update['all_driver'] = $new_all_d;
                        $update['updated_date'] = date('Y-m-d H:m:s');
                        $update_status = Request::where('id',$request['request_id'])->update($update);
        

                        $driver_data = $this->get_driver($request['driver_id']);
                        $this->send_request_notification_to_driver($driver_data['device_token'],$request['request_id'],$new_driver,$request_data['rider_id'],$driver_data['device_type']);

                        $msg['status']=true;
                        $msg['message']='Request submitted Successfully';		

                    }
                    else
                    {
                        $update['status'] = 2;
                        $update['rejected_by'] = $new_rej;
                        $update['all_driver'] = $new_all_d;
                        $update['updated_date'] = date('Y-m-d H:m:s');
                        $update_status = Request::where('id',$request['request_id'])->update($update);

                        $msg['status']=true;
                        $msg['message']='Request submitted Successfully';	
                    }
                }
                else{

                    //update request status =2

                    /*$msg['notification']=sendreqnotitouser_onreject($request_data['device_token'],$request_data['id'],$con,$request_data['rider_id']);*/


                    $msg['status']=true;
                    $msg['message']='Success.';

                }
            }else{
                $msg['status']=false;
                $msg['message']='Action already taken for this Request';
            }
        }
        elseif($request['status'] == 3)
        {
            $update['status'] = 3;
            $update['end_datetime'] = date('Y-m-d H:m:s');
            $update['updated_date'] = date('Y-m-d H:m:s');
            $update_status = Request::where('id',$request['request_id'])->update($update);


            if(isset($request['driver_id']) && $request['driver_id']!='')
            {
    
                $type='driver';
    
                $driver_data = $this->get_driver($request['driver_id']);
                $rider_data = $this->get_rider($request_data['rider_id']);
      
                $msg['rider'] = $rider_data;
                $msg['driver'] = $driver_data;
        
            }
            if(isset($request['rider_id']) && $request['rider_id']!='')
            {   
    
                $type='rider';

                $update['cancel_by'] = 2;
                $update['end_datetime'] = date('Y-m-d H:m:s');
                $update['updated_date'] = date('Y-m-d H:m:s');
                $update_status = Request::where('id',$request['request_id'])->update($update);
       
                $driver_data = $this->get_driver($request_data['driver_id']);
                $rider_data = $this->get_rider($request['rider_id']);

                $msg['driver'] = $driver_data; 
                $msg['rider'] = $rider_data; 
    
            }
            $msg['status']=true;
            $msg['message']='Success.';
        }
        else
        {
            $msg['status']=false;
            $msg['message']='Failed';
        }

        return response()->json($msg, 200);
    }







    
    // Sub Function =====================


    // get driver car image
    public function car_images($driver_detail_id)
    {
        $image_list = DB::table('taxi_car_images')
            ->select('image','id')
            ->where('driver_detail_id',$driver_detail_id)
            ->get();
        
        $list = [];
        foreach ($image_list as  $value) {

            $image['id'] = $value->id;
            $image['image'] = env('AWS_S3_URL').$value->image;
            $list[] = $image;
        }

        return $list;
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

    // get rider 
    public function get_rider($rider_id)
    {
        $rider  = User::where('user_id',$rider_id)->first();
        $rider['profile_pic'] = $rider['profile_pic'] != '' ? env('AWS_S3_URL').$rider['profile_pic'] : '';
        $rider['ratting'] = $this->get_rider_ratting($rider_id);

        return $rider; 
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

    // check driver availablity
    public function check_driver_availablity($id)
    {
        $driver = Driver::where('driver_id',$id)->where('availability',1)->first();
        if($driver)
        {
            $check_request = Request::where('driver_id',$id)->where('driver_id',$id)->where('status',1)->where('is_canceled','!=',1)->where('ride_status','!=',3)->first();
            if($check_request)
            {
                return true;
            }
            else
            {
                return false;
            }     
        }
        else{
            return false;
        }
    }

    // send request notification to driver
    public function send_request_notification_to_driver($device_token,$req_id,$user_id,$rider_id, $device_type = null)
    {       
        $user = $this->get_driver($user_id);
        $rider = $this->get_rider($rider_id);
        $request_data = Request::where('id',$req_id)->first();
        
        if(!empty(explode(',',$request_data['rejected_by'])) && $request_data['rejected_by']!='')
        {
            $request_data['rejected_by']=explode(',',$request_data['rejected_by']);
        }
        elseif($request_data['rejected_by']==null)
        {
            $request_data['rejected_by']=array();
        }
        $msg='You have Ride Request from '.$rider['first_name'].' '.$rider['last_name'];
        $type = 'ride_request';

        // $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        $session_user = $this->qb_create_session_with_user();
        $session_data = json_decode($session_user);
        $token = $session_data->session->token;

        /**
         |--------------------------------------------------------
        | This code for iOS, Which is send with 'aps' => $alert
        |--------------------------------------------------------
        */
        $apn = array(
            "title" => 'Ride Request',
            "body"  => $msg
        );
        $apns = (object)$apn;

        $alerts = array(
            "alert" => $apns,
        );
        $alert = (object)$alerts;
        // iOS code End 

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        $ttt = array (
            'message'   => $msg,
            'type' => 'ride_request',
            'body'=>json_encode($request_data),
            'title' => 'Ride Request',
            'aps'       => $alert,
        );
        $a = json_encode($ttt);             // QuickBlox allow only string so conver it into json
        $msggg = base64_encode($a);         // QuickBlox allow base64 encoded string 
        define("environment","production");
        curl_setopt($ch, CURLOPT_URL, "https://api.quickblox.com/events.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"event\": {\"notification_type\": \"push\", \"environment\": \"".environment."\", \"user\": { \"ids\": \"$device_token\"}, \"message\": \"$msggg\"}}");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Quickblox-Rest-Api-Version: 0.1.0";
        $headers[] = "Qb-Token: $token";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

        $this->store_notification($user_id,$type,json_encode($ttt));

        return $result;
    }

    public function qb_create_session_with_user()
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

}