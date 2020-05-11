<?php


namespace App\Repositories\Api\Admin;

use ArrayObject;
use App\Models\User;
use App\Models\Ratting;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RideRepository extends Controller
{
    // get pending ride list
    public function get_pending_ride_list($request)
    {
        $pending_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
                        'driver.mobile_no as driver_mobile'
                    )               
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',0)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($pending_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Pending Rride List', 
                'data'    => $pending_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get running ride list
    public function get_running_ride_list($request)
    {
        $running_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',1)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($running_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Running Rride List', 
                'data'    => $running_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get completed ride list
    public function get_completed_ride_list($request)
    {
        $completed_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.ride_status',3)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($completed_ride_list['data'])
        {
            $list = [];
            foreach($completed_ride_list['data'] as $ride)
            {
                $driver_ratting = $this->get_driver_ratting($ride->id);
                $rider_ratting = $this->get_rider_ratting($ride->id);

                $ride->driver_ratting = $driver_ratting ? $driver_ratting->ratting : 0;
                $ride->rider_ratting = $rider_ratting ? $rider_ratting->ratting : 0;

                $list[] = $ride;
            }
            $completed_ride_list['data'] = $list;
            return response()->json([
                'status'    => true,
                'message'   => 'Completed Rride List', 
                'data'    => $completed_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get no response ride list
    public function get_no_response_ride_list($request)
    {
        $no_response_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                        DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                            'rider.mobile_no as rider_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.is_canceled',1)
                ->where('taxi_request.cancel_by',0)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($no_response_ride_list['data'])
        {

            foreach ($no_response_ride_list['data'] as  $ride) {
                
                $driverArray = explode(',', $ride->rejected_by);

                // get driver data
                $driver_name = DB::table('taxi_users')
                    ->select( 
                        DB::raw('CONCAT(first_name," ",last_name) as rider_name'),
                            'mobile_no as rider_mobile'
                        )
                    ->whereIn('user_id',$driverArray)
                    ->get();
                                        
                // format data
                $driverName = '';
                $driverMobile = '';
                foreach ($driver_name as $value) {
                    if($driverMobile!='')
                    {
                        $driverMobile .= ',';
                    }
                    $driverMobile .= $value->rider_mobile;


                    if($driverName!='')
                    {
                        $driverName .= ', ';
                    }
                    $driverName .= $value->rider_name;
                }

                $ride->driver_name = $driverName;
                $ride->driver_mobile = $driverMobile;

                $ride_list[] = $ride;
            }

            $no_response_ride_list['data'] = $ride_list;

            return response()->json([
                'status'    => true,
                'message'   => 'No Response Rride List', 
                'data'    => $no_response_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get canceled ride list
    public function get_canceled_ride_list($request)
    {
        $canceled_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.is_canceled',1)
                ->whereIn('taxi_request.cancel_by',[1,2])
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($canceled_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Canceled Rride List', 
                'data'    => $canceled_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get no driver available list
    public function get_no_driver_available_list($request)
    {
        $no_driver_available_list = DB::table('taxi_driver_notavailable')
                ->select('taxi_driver_notavailable.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile'
                    )
                ->join('taxi_users as rider', 'taxi_driver_notavailable.rider_id', '=', 'rider.user_id')
                ->orderByRaw('taxi_driver_notavailable.id DESC')
                ->paginate(10)->toArray();

        if($no_driver_available_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'no driver available list', 
                'data'    => $no_driver_available_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get fake ride list
    public function get_fake_ride_list($request)
    {
        $fake_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',4)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($fake_ride_list['data'])
        {

            foreach ($fake_ride_list['data'] as  $ride) {
                
                $rider_reveiw = Ratting::where('request_id',$ride->id)->where('review_by','rider')->value('ratting');
                $driver_reveiw = Ratting::where('request_id',$ride->id)->where('review_by','driver')->value('ratting');
                
                $ride->rider_reveiw = $rider_reveiw != "" ? $rider_reveiw : 0;
                $ride->driver_reveiw = $driver_reveiw != "" ? $driver_reveiw : 0;
                
                $ride_list[] = $ride;
            }
            $fake_ride_list['data'] = $ride_list;

            return response()->json([
                'status'    => true,
                'message'   => 'Fake ride list', 
                'data'    => $fake_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get ride area list
    public function get_ride_area_list($request)
    {
        $ride_area_list = DB::table('taxi_ride_area_coordinates')
                ->select('id','area_name','created_date')
                ->orderByRaw('id DESC')
                ->paginate(10)->toArray();

        if($ride_area_list['data'])
        {

            return response()->json([
                'status'    => true,
                'message'   => 'Rride area list', 
                'data'    => $ride_area_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // view area boundaries
    public function view_area_boundaries($request)
    {
        $view_area_boundaries = DB::table('taxi_ride_area_coordinates')
                ->where('id',$request->id)
                ->first();

        if($view_area_boundaries)
        {
            $result = (array)$view_area_boundaries;
            $areaStr = $center = '';
            $centerLatLng = '53.7267,-127.6476';
            $centerLatLng = explode(',', $centerLatLng);
            $lat = $centerLatLng[0];
            $long = $centerLatLng[1];
            $area = (!empty($result['coordinates'])) ? $result['coordinates'] : '';
            $coordinates = $result['coordinates'];
            
            if (!empty($area)) {
                $areaLatLong = str_replace('"', '', $area);
                $area = str_replace('[', '', $area);
                $area = str_replace(']', '', $area);
                $area = str_replace('{', '', $area);
                $area = str_replace('}', '', $area);
                if(strpos($area, '.')!==false)
                {
                    $area = explode(',', $area);
    
    
                    $aa = '';
                    
                    $i=0;
                    foreach ($area as $value) {
                        $val = explode(':', $value);
    
                        if($aa!='')
                        {
                            $aa .= ',';
    
                        }
                        if($i % 2 == 0)
                        {
                            $aa .= '{';
                        }
                        $aa .= $val[0].':'.$val[1];
                        if($i % 2 != 0)
                        {
                            $aa .= '}';
                        }
                        
                    $i++;
                    }
    
                }
    
                $areaStr = str_replace('{', '(', $areaLatLong);
                $areaStr = str_replace('}', ')', $areaStr);
                $areaStr = str_replace('),(', ')|(', $areaStr);
                $areaStr = str_replace('[', '', $areaStr);
                $areaStr = str_replace(']', '', $areaStr);
                
                $center = explode('|', $areaStr);
                if (!empty($center)) {
                    if (!empty($center[0])) {
    
                        $centerLatLng = str_replace('(lat:', '', $center[0]);
                        $centerLatLng = str_replace('lng:', '', $centerLatLng);
                        $centerLatLng = str_replace(')', '', $centerLatLng);
    
                       $centerLatLng = explode(',', $centerLatLng);
                       $lat = $centerLatLng[0];
                       $long = $centerLatLng[1];
                    }
                }
                
                $areaStr = str_replace('lat', '', $areaStr);
                $areaStr = str_replace('lng', '', $areaStr);
                $areaStr = str_replace(':', '', $areaStr);
                $areaStr = str_replace('"', '', $areaStr);
    
                $array['areaStr'] = $areaStr;
                $array['lat'] = $lat;
                $array['long'] = $long;
                $array['area'] = $aa;
                $array['coordinates'] = $coordinates;
        
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Area Boundaries', 
                'data'    => $array,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // add area boundaries
    public function add_area_boundaries($request)
    {
        $input = $request->all();
        $input['created_date'] = date('Y-m-d H:i:s'); 

        $insert = DB::table('taxi_ride_area_coordinates')->insert($input);

        // $notif = $this->silentNotificationToAllUsers();  // Send Notification To ALL Drivers && Riders

        return response()->json([
            'status'    => true,
            'message'   => 'Area boundaries add successfully', 
            'data'    => $insert,
        ], 200);
    }


    // delete area boundaries
    public function delete_area_boundaries($request, $id)
    {
        $delete_area_boundaries = DB::table('taxi_ride_area_coordinates')->where('id',$id)->delete();

        // $notif = $this->silentNotificationToAllUsers();  // Send Notification To ALL Drivers && Riders        
        if($delete_area_boundaries)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Area boundaries delete successfully', 
                'data'    => array(),
            ], 200);
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'Failed', 
                'data'    => array(),
            ], 200);
        }
    }

    // delete_ride
    public function delete_ride($request)
    {
        $id = explode(',',$request->id);
        $delete_ride = 0;
        if($request['type'] == 'driver_notavailable')
        {
            $delete_ride = DB::table('taxi_driver_notavailable')->whereIn('id',$id)->delete();
        }
        else
        {
            $delete_ride = Request::whereIn('id',$id)->delete();
        }

        if($delete_ride)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Ride delete successfully', 
                'data'    => array(),
            ], 200);
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'Failed', 
                'data'    => array(),
            ], 200);
        }
    }

    // complete ride
    public function complete_ride($request)
    {

        $update['updated_date'] = date('Y-m-d H:i:s');
        $update['status'] = 3;
        $update['ride_status'] = 3;

        $complete_ride = Request::where('id',$request->id)->update($update);

        if($complete_ride)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Ride completed successfully', 
                'data'    => array(),
            ], 200);
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'Failed', 
                'data'    => array(),
            ], 200);
        }
    }


    // Sub Function =================================

    // silent notification
    public function silentNotificationToAllUsers()
    {

        $result = User::all();
        $all_device_token 	= array_column($result, 'device_token');
        $device_tokens 		= str_replace(' ', '', implode(',',$all_device_token));

        $session_user = $this->qb_create_session_with_user();
        $session_data = json_decode($session_user);
        $token = $session_data->session->token;

        /**
        |--------------------------------------------------------
        | This code for iOS, Which is send with 'aps' => $alert
        |--------------------------------------------------------
        */
        $apn = array(
            "title" => '',
            "body" 	=> ''
        );
        $apns = (object)$apn;

        $alerts = array(
            "alert" => $apns,
            'sound' => 'default'
        );
        $alert = (object)$alerts;
        // iOS code End 

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        $ttt = array (
            'message' 	=> 'msg', 
            'type' 		=> 'silent_notification', 
            'body'		=> 'body',
            'title' 	=> 'title',
            'aps' 		=> $alert,
        );
        $a = json_encode($ttt);             // QuickBlox allow only string so conver it into json
        $msggg = base64_encode($a);         // QuickBlox allow base64 encoded string 

        curl_setopt($ch, CURLOPT_URL, "https://api.quickblox.com/events.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"event\": {\"notification_type\": \"push\", \"environment\": \"".environment."\", \"user\": { \"ids\": \"$device_tokens\"}, \"message\": \"$msggg\"}}");
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
               
        return TRUE;
    }

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

    // get driver ratting
    public function get_driver_ratting($request_id)
    {

        $ratting =  Ratting::where('review_by','driver')->where('request_id',$request_id)->first();
        return $ratting;
    }

    // get rider ratting
    public function get_rider_ratting($request_id)
    {
        $ratting =  Ratting::where('review_by','rider')->where('request_id',$request_id)->first();
        return $ratting;
    }
}

