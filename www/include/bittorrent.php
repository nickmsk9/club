<?php
//setlocale(LC_ALL, 'ru_RU.UTF-8');
# IMPORTANT: Do not edit below unless you know what you are doing!

// DEFINE IMPORTANT CONSTANTS
define('IN_TRACKER', true);
define("FILE",$_SERVER['PHP_SELF']); //file called by user

/*
### security protection by n-sw-bit ::: ANTIDDOS ###
### Чтобы включить защиту от флуда раскомментируйте этот блок.

$google=false;
if (stripos($_SERVER['HTTP_USER_AGENT'], "googlebot") !== false) $google = true;
if (stripos($_SERVER['HTTP_USER_AGENT'], "yandexbot") !== false) $google = true;

$nn = md5("opapoaopps".$_SERVER['REMOTE_ADDR']);
if (!isset($_COOKIE[$nn]) && FILE!=="/announce.php" && FILE!=="/scrape.php" && $google==false)
{
	if (isset($_POST[$nn])){
		setcookie($nn, "yes");
		header("Location: http://www.animeclub.lv".$_SERVER['REQUEST_URI']);exit;
	}
	echo "<html><body><form id=\"f\" action=\"http://www.animeclub.lv".$_SERVER['REQUEST_URI']."\" method=\"post\"><input type=\"hidden\" name='".$nn."' value='a'><script>document.getElementById('f').submit();</script><input type=\"submit\" value='Continue'></form></body></html>";
	die();
}
### security protection by n-sw-bit ::: ANTIDDOS ###
*/


// SET PHP ENVIRONMENT

@ignore_user_abort(1);
@set_time_limit(0);

define ('ROOT_PATH',dirname(dirname(__FILE__))."/");


function timer() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

// Variables for Start Time
$tstart = timer(); // Start time

// INCLUDE BACK-END
if (empty($rootpath))
	$rootpath = ROOT_PATH;
include($rootpath . 'include/core.php');
### security protection by n-sw-bit ::: ctracker ###
include($rootpath . 'include/ctracker.php');

/* Init logger unit 
include('./include/logger.php');
$_logger = new Logger();
register_shutdown_function(Array($_logger, 'Save'));*/
?>