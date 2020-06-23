<?php


namespace App\Repositories\Api\Admin;

use File;
use ArrayObject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Controller
{

    //get user list
    public function get_user_list($request)
    { 
        $user_list = array();

        if($request['type'] == 'currentWeek' && $request['sub_type'] == '')
        {
            $list = 'CurrentWeek';

            $previous_week = strtotime("0 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week);
            $end_week = strtotime("next friday",$start_week);
            $start_current_week = date("Y-m-d H:i:s",$start_week);
            $end_current_week = date("Y-m-d 23:59:00",$end_week);

            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->with('avgRating')
            ->where('user_type', 0)
            ->whereBetween('created_date', [$start_current_week, $end_current_week])
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }
        elseif($request['type'] == 'lastWeek' && $request['sub_type'] == '')
        {
            $list = 'LastWeek';

            $previous_week1 = strtotime("-1 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week1);
            $end_week = strtotime("next friday",$start_week);
            $start_last_week = date("Y-m-d H:i:s",$start_week);
            $end_last_week = date("Y-m-d 23:59:00",$end_week);

            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->with('avgRating')
            ->where('user_type', 0)
            ->whereBetween('created_date', [$start_last_week, $end_last_week])
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }
        elseif($request['sub_type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                if($request['type'] == 'currentWeek')
                {
                    $list = 'Filter currentWeek';

                    $previous_week = strtotime("0 week +1 day");
                    $start_week = strtotime("last saturday midnight",$previous_week);
                    $end_week = strtotime("next friday",$start_week);
                    $start_current_week = date("Y-m-d H:i:s",$start_week);
                    $end_current_week = date("Y-m-d 23:59:00",$end_week);
        
                    $query = User::withCount('complate_ride','cancel_ride','total_review')
                    ->with('avgRating')
                    ->where('user_type', 0)
                    ->whereBetween('created_date', [$start_current_week, $end_current_week]);
        
                }
                elseif($request['type'] == 'lastWeek')
                {
                    $list = 'Filter lastWeek';

                    $previous_week1 = strtotime("-1 week +1 day");
                    $start_week = strtotime("last saturday midnight",$previous_week1);
                    $end_week = strtotime("next friday",$start_week);
                    $start_last_week = date("Y-m-d H:i:s",$start_week);
                    $end_last_week = date("Y-m-d 23:59:00",$end_week);
        
                    $query = User::withCount('complate_ride','cancel_ride','total_review')
                    ->with('avgRating')
                    ->where('user_type', 0)
                    ->whereBetween('created_date', [$start_last_week, $end_last_week]);
                }
                else
                {
                    $list = 'Filter all';

                    $query = User::withCount('complate_ride','cancel_ride','total_review')
                        ->with('avgRating')
                        ->where('user_type', 0);

                }

                if(!empty($filter->username)) // username filter
                {
                    $username = explode(' ',$filter->username);
                    if(count($username) > 1)
                    {
                        $query->where(function ($q) use ($username) {
                            $q->where('first_name', 'LIKE', '%'.$username[0].'%')->orWhere('last_name', 'LIKE', '%'.$username[1].'%');
                        });
                    }
                    else
                    {
                        $query->where(function ($q) use ($filter) {
                            $q->where('first_name', 'LIKE', '%'.$filter->username.'%')->orWhere('last_name', 'LIKE', '%'.$filter->username.'%');
                        });
                    }
                }
                
                if(!empty($filter->mobile)) // mobile filter 
                {
                    $query->where('mobile_no', 'LIKE', '%'.$filter->mobile.'%');
                }
                
                if(!empty($filter->dob)) // date_of_birth filter
                {
                    $query->whereBetween('date_of_birth',explode(' ',$filter->dob));
                }
                
                if(!empty($filter->dor)) // date_of_register
                {
                    $query->whereBetween('created_date',explode(' ',$filter->dor));
                }
                
                if(!empty($filter->device_type)) // device_type filter
                {
                    $device_type = explode(',',$filter->device_type);
                    if(count($device_type) > 1)
                    {
                        $query->whereBetween('device_type',$device_type);
                    }
                    else
                    {
                        $query->where('device_type',$device_type[0]);
                    }
                }
                
                if(!empty($filter->verify)) // verify filter
                {
                    
                    $verify = explode(',',$filter->verify);
                    if(count($verify) > 1)
                    {
                        $query->whereBetween('verify',[0,1]);
                    }
                    else
                    {
                        $verify = $verify[0] == 2 ? 0 : 1; 
                        $query->where('verify',$verify);
                    }
                }
                
                if(!empty($filter->complete_ride)) // complete_ride filter
                {
                    $complete_ride = explode('-',$filter->complete_ride);
                    $query = $query->where(function($q) use ( $complete_ride ){
                        $q->has('complate_ride','>=',$complete_ride[0]);
                        $q->has('complate_ride','<=',$complete_ride[1]);
                    });

                }
                
                if(!empty($filter->cancelled_ride)) // cancel_ride filter
                {
                    $cancel_ride = explode('-',$filter->cancelled_ride);
                    $query = $query->where(function($q) use ( $cancel_ride ){
                        $q->has('cancel_ride','>=',$cancel_ride[0]);
                        $q->has('cancel_ride','<=',$cancel_ride[1]);
                    });
                }
                
                if(!empty($filter->total_review)) // total_review filter
                {
                    $total_review = explode('-',$filter->total_review);
                    $query = $query->where(function($q) use ( $total_review ){
                        $q->has('total_review','>=',$total_review[0]);
                        $q->has('total_review','<=',$total_review[1]);
                    });

                }
                
                if(!empty($filter->average_ratting)) // average_ratting filter
                {
                    $average_ratting = explode('-',$filter->average_ratting);
                    $query->whereHas('avg_rating' , function ($q) use ( $average_ratting ) {
                        $q->havingRaw('AVG(taxi_ratting.ratting) >= '.$average_ratting[0]);
                        $q->havingRaw('AVG(taxi_ratting.ratting) <= '.$average_ratting[1]);
                    });
                    
                }

                $user_list = $query->orderBy('user_id', 'DESC')->paginate(10)->toArray();
            }
            else
            {
                return response()->json([
                    'status'    => false,
                    'message'   => 'filter parameter is required',
                    'data'    => new ArrayObject,
                ], 200);
            }

        }
        else{
            
            $list = 'All';
            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->with('avgRating')
            ->where('user_type', 0)
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }

        if($user_list['data'])
        {
            // add base url in profile_pic
            foreach ($user_list['data'] as $user) {
                $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';

                $data[] = $user;
            }
            $user_list['data'] = $data;

            return response()->json([
                'status'    => true,
                'message'   => $list.' user list',
                'data'    => $user_list,
            ], 200);

        } 
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No user found',
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get user detail
    public function get_user_detail($request)
    {
        $user = User::where('user_id', $request->user_id)->first();
        if ($user) {
            $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';
            return response()->json([
                'status'    => true,
                'message'   => 'user detail',
                'data'    => $user,
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'No user found',
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // edit user detail
    public function edit_user_detail($request)
    {
        $input = $request->except(['user_id']);
        $input['updated_date'] = date('Y-m-d H:i:s');

        // profile_pic handling 
        if ($request->file('profile_pic')) {

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/users/' . time() . '.' . $profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
        }

        // update data
        User::where('user_id', $request['user_id'])->update($input);

        // get user 
        $user = User::where('user_id', $request->user_id)->get()->first();
        $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';


        return response()->json([
            'status'    => true,
            'message'   => 'update successfull',
            'data'    => $user,
        ], 200);
    }

    // edit user status
    public function edit_user_status($request)
    {
        $input = $request->except(['user_id']);
        $input['updated_date'] = date('Y-m-d H:i:s');

        // update status
        User::where('user_id', $request['user_id'])->update($input);
        $user =  User::where('user_id', $request['user_id'])->first();

        if($request['verify']==1)
		{
			$message='Hi '.$user['first_name'].", Your Account Is verified By Administrator.";
		}
        else
        {
			$message='Hi '.$user['first_name'].", Your Account Is Deactivated By Administrator.";
        }
        
        $device_type = $user['device_type'];
		$device_token = $user['device_token'];
        $type = 'account_verify';
        
		if($this->sentNotificationOnVerified($message,$device_token,$type, $device_type))
		{
            $this->store_notification($request['user_id'],'verify_user',$message);
        }

        // get user details
        $get_user_detail = $this->get_user_detail($request);

        return response()->json([
            'status'    => true,
            'message'   => 'update successfull',
            'data'    => $get_user_detail->original['data'],
        ], 200);
    }

    // delete user
    public function delete_user($request, $user_id)
    {
        $user = User::where('user_id', $user_id)->first();
        $image_path = $user['profile_pic'];

        // delete profile_pic
        Storage::disk('s3')->exists($user['profile_pic']) ? Storage::disk('s3')->delete($user['profile_pic']) : '';

        User::where('user_id', $user_id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'user deleted',
            'data'    => '',
        ], 200);
    }

    // get rider reviews
    public function get_rider_reviews($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter all';

                $query = User::select(
                    DB::raw('CONCAT(first_name," ",last_name) as rider_name'),
                    'mobile_no as rider_mobile','user_id as rider_id'
                )
                ->withCount('total_review')
                ->withCount([
                    'avg_rating' => function ($query) {
                        $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                    }
                ])
                ->where('user_type', 0);
       

                if(!empty($filter->name)) // name filter
                {
                    $name = explode(' ',$filter->name);
                    if(count($name) > 1)
                    {
                        $query->where(function ($q) use ($name) {
                            $q->where('first_name', 'LIKE', '%'.$name[0].'%')->orWhere('last_name', 'LIKE', '%'.$name[1].'%');
                        });
                    }
                    else
                    {
                        $query->where(function ($q) use ($filter) {
                            $q->where('first_name', 'LIKE', '%'.$filter->name.'%')->orWhere('last_name', 'LIKE', '%'.$filter->name.'%');
                        });
                    }

                }
                
                if(!empty($filter->mobile)) // mobile filter 
                {
                    $query->where('mobile_no', 'LIKE', '%'.$filter->mobile.'%');
                }
                
                if(!empty($filter->review)) // review filter
                {
                    $total_review = explode('-',$filter->review);
                    $query = $query->where(function($q) use ( $total_review ){
                        $q->has('total_review','>=',$total_review[0]);
                        $q->has('total_review','<=',$total_review[1]);
                    });

                }
                
                if(!empty($filter->avg_rating)) // avg_rating filter
                {
                    $average_ratting = explode('-',$filter->avg_rating);
                    $query->whereHas('avg_rating' , function ($q) use ( $average_ratting ) {
                        $q->havingRaw('AVG(taxi_ratting.ratting) >= '.$average_ratting[0]);
                        $q->havingRaw('AVG(taxi_ratting.ratting) <= '.$average_ratting[1]);
                    });
                }

                $rider_reviews = $query->orderBy('user_id', 'DESC')->paginate(10)->toArray();
            }
            else
            {
                return response()->json([
                    'status'    => false,
                    'message'   => 'filter parameter is required',
                    'data'    => new ArrayObject,
                ], 200);
            }

        }
        else
        {
            $list = 'All';

            $rider_reviews = User::select(
                DB::raw('CONCAT(first_name," ",last_name) as rider_name'),
                'mobile_no as rider_mobile','user_id as rider_id'
            )
            ->withCount('total_review')
            ->withCount([
                'avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                }
            ])
            ->where('user_type', 0)
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }


        if ($rider_reviews['data']) 
        {
            return response()->json([
                'status'    => true,
                'message'   => $list.' Rider reviews',
                'data'    => $rider_reviews,
            ], 200);
        } 
        else 
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available',
                'data'    => new ArrayObject,
            ], 200);
        }
    }


    // view rider reviews
    public function view_rider_reviews($request)
    {
        $rider_reviews = DB::table('taxi_ratting')
            ->select('taxi_ratting.*','driver_id','rider_id',
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as driver_name'),
                'taxi_users.mobile_no as driver_mobile' 
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->join('taxi_users','taxi_request.rider_id','taxi_users.user_id')
            ->where('taxi_request.rider_id',$request->rider_id)
            ->where('review_by','driver')
            ->get();

        if($rider_reviews)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Rider reviews', 
                'data'    => $rider_reviews,
            ], 200);   
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }

    }

    //get specific user list
    public function get_specific_user_list($request)
    {
        $user_list = User::select('user_id as id',
            DB::raw('CONCAT(first_name," ",last_name) as user_name'),
            'user_type','mobile_no as mobile'
        )
        ->where('verify', 1)
        ->orderBy('user_id', 'DESC')
        ->paginate(10)->toArray();


        return response()->json([
            'status'    => true,
            'message'   => 'All user list',
            'data'    => $user_list,
        ], 200);
    }

    

    // Sub Function

    // user ratting review
    public function user_ratting_review($user_id)
    {
        $ratting_review = DB::table('taxi_ratting')->select(
                DB::raw('count(taxi_ratting.id) as total_review, ROUND(coalesce(avg(ratting),0),1) as avg_ratting')
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->where('taxi_request.rider_id',$user_id)
            ->where('taxi_ratting.review_by','driver')
            ->first();

        return $ratting_review;

    }

    public function sentNotificationOnVerified($msg,$device_token,$type, $device_type)
    {	
        $session_user = $this->qb_create_session_with_user();
        $session_data = json_decode($session_user);
        $token = $session_data->session->token;

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        if ($device_type == 'A')
        {
            $fields = array(
                'to' => $device_token,
                'data' => array('message' => "success" ,'type' => $type,'title' => $msg)
            );
            $headers = array(
                'Authorization:key=AAAAv86JdJU:APA91bGTSn2bpxOBqeGNBpLEnoGl7c8Mj851op9lMdzcWKxTS4_K3U-f-lycbUVIRJ1kI2ar1doINcP9J6lVAFcdAOrfZRvzzTLkoOyTCUmorwCSEjpzMoA7AyN2yDjrvsD-tJIRA_78',
                'Content-Type:application/json'
            );		
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
            $data = json_encode($fields['data']);
            return json_encode($result).json_encode($fields);
        }
        else if ($device_type == 'I')
        {
            $apnsServer = 'ssl://gateway.push.apple.com:2195';
            //$apnsServer = 'ssl://gateway.sandbox.push.apple.com:2195';
            $privateKeyPassword = '1';
            $deviceToken =$device_token;
            $pushCertAndKeyPemFile = 'pushcert.pem';
            $stream = stream_context_create();
            stream_context_set_option($stream,'ssl','passphrase',$privateKeyPassword);
            stream_context_set_option($stream,'ssl','local_cert',$pushCertAndKeyPemFile);

            $connectionTimeout = 20;
            $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $connection = stream_socket_client($apnsServer,$errorNumber,$errorString,$connectionTimeout,$connectionType,$stream);
            if (!$connection){
                // echo 0;
                // exit;
            } else {
                // echo 1;
            }
            $messageBody['aps'] = array(
                    'alert' => array(
                        'title' => "Notification from Taxisti",
                        'body' => $msg
                    ),
                    "type" => 'admin_notification',
                    "badge" => +1,
                    "sound" => 'default',
                );
            $payload = json_encode($messageBody);
            $notification = chr(0) .
            pack('n', 32) .
            pack('H*', $deviceToken) .
            pack('n', strlen($payload)) .
            $payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            fclose($connection);
            if (!$wroteSuccessfully){
                return 0;
            }
            else {
                return 1;
            }
        }
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
        $input['datetime'] = date('Y-m-d H:i:s');

        DB::table('taxi_notification')->insert($input);
        return true;
    }
    

    
}
