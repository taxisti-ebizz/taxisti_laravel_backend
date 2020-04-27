<?php

include_once('Config.Inc.php');
include_once("DB.php");
$db = new DB();

$result =array();
$result= $db->show("usermaster");
$flag=true;

for($i=0; $i < count($result); $i++) {
	if($_REQUEST['devicetoken']==$result[$i]['devicetoken'])
	{	  
	  $flag=false;
	  break;
	}
}

if($flag==false)
{
	$DeleteWherearr = array();
	$DeleteWherearr["devicetoken"]=$_REQUEST['devicetoken'];
	//Deleted Old things
	$db->Remove("usermaster",$DeleteWherearr);
		
}

$arr = array();

$arr["firstname"] = $_REQUEST['firstname'];
$arr["lastname"] = $_REQUEST['lastname'];
$arr["title"] = $_REQUEST['title'];
$arr["street"] = $_REQUEST['street'];
$arr["cityname"] = $_REQUEST['cityname'];
$arr["statename"] = $_REQUEST['statename'];
$arr["zipno"] = $_REQUEST['zipno'];
$arr["phoneno"] = $_REQUEST['phoneno'];
$arr["faxno"] = $_REQUEST['faxno'];
$arr["dealno"] = $_REQUEST['dealno'];
$arr["npino"] = $_REQUEST['npino'];
$arr["email"] = $_REQUEST['email'];
$arr["passcode"] = $_REQUEST['passcode'];
$arr["devicetoken"] = $_REQUEST['devicetoken'];
$arr["deviceType"] = $_REQUEST['deviceType'];

$db->Insert($arr,"usermaster");

$arrRes=array();
$arrRes["msgCode"]="1";
$arrRes["msgText"]="Successful";
$json=json_encode($arrRes);
echo $json;

?>