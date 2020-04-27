<?php
$msg['status']=false;
$msg['message']='failed';

include_once './GCM.php';
if(isset($_POST["sendnoti"])) {
    $body1=json_decode($_POST['body1']);
	
    $passphrase = '1';
    $message = $body1->alert;
    $deviceToken =  $_POST['devicetoken'];
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$fp){
        exit("Failed to connect: $err $errstr" . PHP_EOL);
    }
    $body['aps'] =$body1; 
    
    // Encode the payload as JSON
    $payload = json_encode($body);
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
    fwrite($fp, $msg, strlen($msg));
    
    if (!$result){
        $msg['message']='Message not delivered';
    }
    else{
        $msg['status']=true;
        $msg['message']='Message successfully delivered';
    }
    
    $regId = $_POST['devicetoken'];
    $gcm = new GCM();
    $registatoin_ids = array($regId);
    $message = array("price" => $message);
    $gcm->send_notification($registatoin_ids, $message);   
	
	$myMessgae['status'] = true;
	$myMessgae['message'] = "sent";
	echo json_encode($myMessgae);	
}else{
	$myMessgae['status'] = false;
	$myMessgae['message'] = "not sent";
	echo json_encode($myMessgae);
}

?>
