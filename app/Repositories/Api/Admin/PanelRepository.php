<?php

namespace App\Repositories\Api\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PanelRepository extends Controller
{
    // get promotion list
    public function get_promotion_list($request)
    {
        $promotion_list = DB::table('taxi_promotion')
            ->orderByRaw('id DESC')
            ->paginate(10)->toArray();

        if($promotion_list['data'])
        {
            $list = [];
            foreach ($promotion_list['data'] as $promotion) {
                
                $promotion->promo_image = $promotion->promo_image != "" ?  env('AWS_S3_URL') . $promotion->promo_image : '';
                $list[] = $promotion;
            }
            
            $promotion_list['data'] = $list;

            return response()->json([
                'status'    => true,
                'message'   => 'Promotion list', 
                'data'    => $promotion_list,
            ], 200);

        }
        else
        {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }        
    }

    // update promotion detail
    public function update_promotion_detail($request)
    {

        $input = $request->except(['id']);
        $input['updated_at'] = date('Y-m-d H:m:s');

        // promo_image handling 
        if ($request->file('promo_image')) {

            // delete promo_image
            $promotion = DB::table('taxi_promotion')->where('id',$request['id'])->first();
            Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

            $promo_image = $request->file('promo_image');
            $imageName = 'uploads/promo_image/' . time() . '.' . $promo_image->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($promo_image), 'public');
            $input['promo_image'] = $imageName;
        }

      
        $promotion = DB::table('taxi_promotion')
            ->where('id',$request['id'])
            ->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion updated', 
            'data'    => array(),
        ], 200);

    }

    // delete promotion
    public function delete_promotion($request, $id)
    {
        // delete promo_image
        $promotion = DB::table('taxi_promotion')->where('id',$id)->first();
        Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

        DB::table('taxi_promotion')->where('id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion deleted', 
            'data'    => array(),
        ], 200);

    }

    // add promotion
    public function add_promotion($request)
    {

        $input = $request->all();
        $input['created_at'] = date('Y-m-d H:m:s');

        // promo_image handling 
        if ($request->file('promo_image')) {

            $promo_image = $request->file('promo_image');
            $imageName = 'uploads/promo_image/' . time() . '.' . $promo_image->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($promo_image), 'public');
            $input['promo_image'] = $imageName;
        }

        $promotion = DB::table('taxi_promotion')->insert($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion add successfully', 
            'data'    => array(),
        ], 200);

    }

    // get user promotion list
    public function get_user_promotion_list($request)
    {
        $user_promotion_list = DB::table('taxi_user_promotion')
        ->select('taxi_user_promotion.id',
            'taxi_user_promotion.redeem','taxi_promotion.description','taxi_user_promotion.created_at', 
            DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                'taxi_users.mobile_no','taxi_users.profile_pic'
            )
        ->join('taxi_users', 'taxi_user_promotion.user_id', '=', 'taxi_users.user_id')
        ->join('taxi_promotion', 'taxi_user_promotion.promotion_id', '=', 'taxi_promotion.id')
        ->orderByRaw('taxi_user_promotion.id DESC')
        ->paginate(10)->toArray();

        if($user_promotion_list['data'])
        {
            $list = [];
            foreach ($user_promotion_list['data'] as $promotion) {
                
                $promotion->profile_pic = $promotion->profile_pic != "" ?  env('AWS_S3_URL') . $promotion->profile_pic : '';
                $list[] = $promotion;
            }
            
            $user_promotion_list['data'] = $list;

            return response()->json([
                'status'    => true,
                'message'   => 'User promotion list', 
                'data'    => $user_promotion_list,
            ], 200);

        }
        else
        {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }        
    }

    // redeem promotion
    public function redeem_promotion($request)
    {

        $input = $request->except(['id']);
        $input['redeem'] = 1; 
        $input['updated_at'] = date('Y-m-d H:m:s');

        $redeem = DB::table('taxi_user_promotion')->where('id',$request['id'])->update($input);

        $promotion = DB::table('taxi_user_promotion')
        ->select('taxi_promotion.code','taxi_users.device_type','taxi_users.device_token','taxi_users.user_id')
        ->join('taxi_users', 'taxi_user_promotion.user_id', '=', 'taxi_users.user_id')
        ->join('taxi_promotion', 'taxi_user_promotion.promotion_id', '=', 'taxi_promotion.id')
        ->where('taxi_user_promotion.id',$request['id'])
        ->first();

        // $msg = "The offer for ".$promotion->code." has been successfully served";
        // $noti_type = "promotion_redeem";
        // $send = sendNotiToUser($con,$promotion->user_id,$promotion->device_token,$promotion->device_type,$msg,$noti_type);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion redeem', 
            'data'    => array(),
        ], 200);

    }

    // get options
    public function get_options($request)
    {

        $options = DB::table('taxi_option')->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Options list', 
            'data'    => $options,
        ], 200);

    }

    // update options
    public function update_options($request)
    {
        $input = $request->all();
        foreach ($input as $key => $value) {
            $options = DB::table('taxi_option')->where('option_name',$key)->update(['option_value' => $value]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Options updated', 
            'data'    => array(),
        ], 200);

    }

    // get contact us list
    public function get_contact_us_list($request)
    {
        $contact_us_list = DB::table('taxi_contact_us')
            ->select('taxi_contact_us.*',
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                    'taxi_users.mobile_no'
                )
            ->join('taxi_users', 'taxi_contact_us.user_id', '=', 'taxi_users.user_id')
            ->orderByRaw('taxi_contact_us.id DESC')
            ->paginate(10)->toArray();

        if ($contact_us_list) {

            return response()->json([
                'status'    => true,
                'message'   => 'Contact us list', 
                'data'    => $contact_us_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }        

    }

    // view contact us message
    public function view_contact_us_message($request)
    {
        $contact_us = DB::table('taxi_contact_us')->where('id',$request['id'])->first();
        return response()->json([
            'status'    => true,
            'message'   => 'Contact us detail', 
            'data'    => $contact_us,
        ], 200);

    }

    // delete contact us
    public function delete_contact_us($request,$id)
    {
        $contact_us = DB::table('taxi_contact_us')->where('id',$id)->delete();
        return response()->json([
            'status'    => true,
            'message'   => 'Contact us deleted', 
            'data'    => array(),
        ], 200);

    }
    

    // Sub function ===========================================

    // send notification to user
    public function sendNotiToUser($con,$user_id,$device_token,$device_type,$msg,$type)
    {
        if($device_type=='A')
        {
            $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
    
            $fields = array(
                'to' => $device_token,
                //'notification' => array('title' => 'Notification from Taxisti','body'=>$msg),
                'data' => array('message' => $msg, 'type' => $type,'title' => 'Notification from Taxisti', 'body' => "success"),
            );
            
    
            $headers = array(
                /*'Authorization:key=AIzaSyAlN84WM8MaPgO_JPRKvLi1bFvWyI_DT1A', */
                'Authorization:key=AIzaSyBHZX8zi36hoodNoZLjrZxbgtTV9OwoyPw',
                'Content-Type:application/json'
            );		////AIzaSyD1kfwetZt8WINTkC65qwWW6eV9oj95cPA 
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    
            $result = curl_exec($ch);
    
            // print_r($result); die;
            
            curl_close($ch);
    
            if($result)
            {
                $insertData = json_encode($fields['data']);
                
                $message = $insertData;
                
                $qry_update=$con->query("INSERT INTO taxi_notification SET user_id=".$user_id.",message='".$message."',datetime=NOW(),type='".$type."'");
                
                if( $qry_update ) 
                {
                    $notificationmsg = '1';
    
                } else {
                    $notificationmsg = '1';
                }
                $notificationmsg = '1';
            }
    
        }
        elseif($device_type=='I')
        {
            
            $apnsServer = 'ssl://gateway.push.apple.com:2195';
            $privateKeyPassword = '1';
            $message = $msg;
            $deviceToken = $device_token;
            $pushCertAndKeyPemFile = 'dis_taxisti_push.pem';
            $stream = stream_context_create();
            stream_context_set_option($stream,'ssl','passphrase',$privateKeyPassword);
            stream_context_set_option($stream,'ssl','local_cert',$pushCertAndKeyPemFile);
    
            $connectionTimeout = 20;
            $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $connection = stream_socket_client($apnsServer,$errorNumber,$errorString,$connectionTimeout,$connectionType,$stream);
    
            // echo "stream ====>>".$stream.PHP_EOL;
            // echo "connectionType ====>>".$connectionType.PHP_EOL;
            // echo "connectionTimeout ====>>".$connectionTimeout.PHP_EOL;
            // echo "errorString ====>>".$errorString.PHP_EOL;
            // echo "errorNumber ====>>".$errorNumber.PHP_EOL;
               // echo "apnsServer ====>>".$apnsServer.PHP_EOL;
            // echo "connectionType ====>>".$connectionType.PHP_EOL;
            // die;
            
    
            if (!$connection){
                //echo "Failed to connect to the APNS server. Error no = $errorNumber<br/>";
                // exit;
            } 
            else {
               //echo "Successfully connected to the APNS. Processing...</br>";
            }
           // die;
            $messageBody['aps'] = array(
                'alert' => array(
                    'title' => "Notification from Taxisti",
                    'body' => $message
                ),
                "user_id" => $user_id,
                "type" => $type,
                "badge" => +1,
                "sound" => 'default'
            );
            $payload = json_encode($messageBody);
            $notification = chr(0) .pack('n', 32) .pack('H*', $deviceToken) .pack('n', strlen($payload)) .$payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            if (!$wroteSuccessfully){
                $result=0;
            }
            else {
                $result=1;
            }
            fclose($connection);
            if($result)
            {
                $datas = json_encode($messageBody['aps']);
                $datas='{"alert":{"title":"Notification from Taxisti","body":"'.$message.'"},"user_id":"'.$user_id.'","type":"admin_notification","badge":1,"sound":"default"}';
                /*
                echo 'message:'.$message."-------";
                echo $datas;
                echo "insert into taxi_notification(user_id,message,datetime,type) values(".$user_id.",'".$datas."',NOW(),'".$type."')";die;
                */        
                
                $qry_update=$con->query("insert into taxi_notification(user_id,message,datetime,type) values(".$user_id.",'".$datas."',NOW(),'".$type."')");
                
                
                if( $qry_update ) 
                {
                    $notificationmsg = '1';
                } 
                else {
                    $notificationmsg = '1';
                }
                $notificationmsg = '1';
            }
        }
        
        return $notificationmsg;
    }
}