<?php

	include_once('Config.Inc.php');
	include_once("DB.php");
	$db = new DB();
	
	$para=array();
	$para["deviceid"]=$_REQUEST['deviceToken'];
		
	$result =array();
	$result= $db->show("notificationandroid", $para);
	
	$json=json_encode($result);
	echo $json;

	$db->Remove("notificationandroid",$para);
?>