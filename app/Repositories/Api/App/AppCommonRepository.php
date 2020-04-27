<?php


namespace App\Repositories\Api\App;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AppCommonRepository extends Controller
{

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
            
            
            include_once '../../../public/ios_notif/GCM.php';
            
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