<?php

# IMPORTANT: Do not edit below unless you know what you are doing! выаыаа
if(!defined('IN_ANNOUNCE'))
  die('Hacking attempt!');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
@ini_set('ignore_repeated_errors', '1');
@ignore_user_abort(1);
@set_time_limit(0);
include_once($rootpath . 'include/benc.php');
include_once($rootpath . 'include/init.php');
require_once($rootpath . 'include/config.php');
require_once($rootpath . 'include/secrets.php');

function err($msg) {
	benc_resp(array("failure reason" => array(type => "string", value => $msg)));
	die();
	exit();
}

function benc_resp($d) {
	benc_resp_raw(benc(array(type => "dictionary", value => $d)));
}

function benc_resp_raw($x) {
	header("Content-Type: text/plain");
	header("Pragma: no-cache");
	echo $x;
}

function get_date_time($timestamp = 0) {
	if ($timestamp)
		return date("Y-m-d H:i:s", $timestamp);
	else
		return date("Y-m-d H:i:s");
}

function gmtime() {
    return strtotime(get_date_time());
}

function strip_magic_quotes($arr) {
	foreach ($arr as $k => $v)
	{
	 if (is_array($v))
	  { $arr[$k] = strip_magic_quotes($v); }
	 else
	  { $arr[$k] = stripslashes($v); }
	}

	return $arr;
}

function mksize($bytes) {
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	else
		return number_format($bytes / 1099511627776, 2) . " TB";
}

function emu_getallheaders() {
   foreach($_SERVER as $name => $value)
	   if(substr($name, 0, 5) == 'HTTP_')
		   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
   return $headers;
}

function portblacklisted($port) {
	if ($port >= 411 && $port <= 413)
		return true;
	if ($port >= 6881 && $port <= 6889)
		return true;
	if ($port == 1214)
		return true;
	if ($port >= 6346 && $port <= 6347)
		return true;
	if ($port == 4662)
		return true;
	if ($port == 6699)
		return true;
	return false;
}

function validip($ip) {
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
				$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

function getip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip_address = '';
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
   return $ip_address;
 }
 
 
function dbconn() {
	global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $mysql_charset, $mysqli;

	$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
	if ($mysqli->connect_error) {
		die("dbconn: connect_error: " . $mysqli->connect_error);
	}
	$mysqli->set_charset($mysql_charset);
}
	 
	 
function sqlesc($value) {
   global $mysqli;
   if (!is_numeric($value)) {
       $value = "'" . $mysqli->real_escape_string($value) . "'";
   }
   return $value;
}

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function unesc($x) {
	return $x;
}

function gzip() {
    global $use_gzip;
    static $already_loaded;
    if (extension_loaded('zlib') && ini_get('zlib.output_compression') != '1' && ini_get('output_handler') != 'ob_gzhandler' && $use_gzip && !$already_loaded) {
        @ob_start('ob_gzhandler');
    } else
        @ob_start();
    $already_loaded = true;
}  
function auto_enter_cheater($userid, $rate, $upthis, $diff, $torrentid, $client, $ip, $last_up)
{
	global $mysqli;
	$mysqli->query(
		"INSERT INTO cheaters (added, userid, client, rate, beforeup, upthis, timediff, userip, torrentid) VALUES(" .
		sqlesc(time()) . ", " . sqlesc($userid) . ", " . sqlesc($client) . ", " . sqlesc($rate) . ", " .
		sqlesc($last_up) . ", " . sqlesc($upthis) . ", " . sqlesc($diff) . ", " . sqlesc($ip) . ", " . sqlesc($torrentid) . ")"
	) or die($mysqli->error);
}

?>