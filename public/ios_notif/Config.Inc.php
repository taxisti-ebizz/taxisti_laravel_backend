<?php
ob_start();
session_start();
//error_reporting(0);
$dir = dirname(__FILE__);
//echo $dir."<hr>";
#DATABASE CONSTANT
if (!defined('DATABASE_TYPE')) define('DATABASE_TYPE','mysql');
if (!defined('DATABASE_HOST')) define('DATABASE_HOST','localhost');
if (!defined('DATABASE_USER')) define('DATABASE_USER','taxisti');
if (!defined('DATABASE_PASSWORD')) define('DATABASE_PASSWORD','7rA${!dHPub4mS:^');
if (!defined('DATABASE_NAME')) define('DATABASE_NAME','taxisti');
if (!define("GOOGLE_API_KEY")) define("GOOGLE_API_KEY", "AIzaSyAlN84WM8MaPgO_JPRKvLi1bFvWyI_DT1A");
?>		
