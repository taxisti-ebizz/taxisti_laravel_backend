<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

    $regId = $result[$i]['devicetoken'];
    $message = $_REQUEST["txtMessage"];
    		
    include_once './GCM.php';
    
    $gcm = new GCM();

    $registatoin_ids = array($regId);
    $message = array("price" => $message);

    $result = $gcm->send_notification($registatoin_ids, $message);

   // echo $result;
	


?>
