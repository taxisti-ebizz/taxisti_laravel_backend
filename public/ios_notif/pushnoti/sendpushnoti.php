<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php
include_once 'GCM.php';
			         
            //echo "iPhone";
            //include_once("sendpush.php?device_token=" . $result[$i]['device_token'] . "&msg=" . $_REQUEST["txtMessage"] . "&notytext=1");
            //include_once("sendpush.php");
            $device_token="85a6d5640f091be0a1ac36f7c664c9f94d78bef1f9bab524173bedc65fe20a0b";
			$message="hello";
            $passphrase  = 'minmaxx';

            $ctx         = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            
            if (!$fp) {
                return ("Failed to connect: $err $errstr" . PHP_EOL);
			}
            
            //echo 'Connected to APNS' . PHP_EOL;
            
            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'badge' => 1,
//                'notification_text' => $_REQUEST['notytext']
            );
            
            // Encode the payload as JSON
            $payload = json_encode($body);
            
            $msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;
            
            fwrite($fp, $msg, strlen($msg));
            
            /*	if (!$result)
            echo 'Message not delivered' . PHP_EOL;
            else
            echo 'Message successfully delivered' . PHP_EOL; */
            
            // Close the connection to the server
            fclose($fp);
         
		
	
?>
<form id="form1" name="form1" method="post">
<table width="200" border="1">
<tbody>
<tr>
<td>Message</td>
<td>
<label><textarea name="txtMessage" id="txtMessage" cols="45" rows="5"></textarea>  </label>
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td><div align="right">  <input name="sendnoti" id="sendnoti" value="Send Notification" type="submit"></div>
</td></tr>
</tbody></table>
</form>
</body>
</html>