<?php
ob_start();
session_start();
//error_reporting(0);
$dir = dirname(__FILE__);
//echo $dir."<hr>";
#DATABASE CONSTANT
if (!defined('DATABASE_TYPE')) define('DATABASE_TYPE','mysqlt');
if (!defined('DATABASE_HOST')) define('DATABASE_HOST','localhost');
if (!defined('DATABASE_USER')) define('DATABASE_USER','root');
if (!defined('DATABASE_PASSWORD')) define('DATABASE_PASSWORD','latikajn');
if (!defined('DATABASE_NAME')) define('DATABASE_NAME','faxapp');
if (!define("GOOGLE_API_KEY")) define("GOOGLE_API_KEY", "AIzaSyCmbXw0T_y7JNlFnjKQ_jUNPvIBbJ4WuDU");
?>		
