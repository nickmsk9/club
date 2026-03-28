<?php
define ('IN_ANNOUNCE', true);
require_once('./include/functions_announce.php');
global $gzip;
gzip();
dbconn(false);

$r = 'd5:files';


if (!isset($_GET["info_hash"]))
die("fak off");
	if (get_magic_quotes_gpc())
		$hash = bin2hex(stripslashes($_GET["info_hash"]));
	else
		$hash = bin2hex($_GET["info_hash"]);
	if (strlen($_GET["info_hash"]) != 20)
		err("Invalid info-hash (".strlen($_GET["info_hash"]).")");

//$memcache_obj = new Memcache;
//$memcache_obj->connect('127.0.0.1', 11211);

//if (false === ($row = $memcache_obj->get('scrape_'.$hash))) {
$res = mysql_query("SELECT SQL_NO_CACHE info_hash, times_completed, seeders, leechers FROM torrents WHERE info_hash = " . sqlesc($hash)) or err(mysql_error());
$row = mysql_fetch_assoc($res);
//$memcache_obj->set('scrape_'.$hash, $row, 0, rand(100 , 300 ));	  
//} 
	
	$r .= 'd20:'.pack('H*', $row['info_hash'])."d8:completei{$row['seeders']}e10:downloadedi{$row['times_completed']}e10:incompletei{$row['leechers']}eeee";


header('Content-Type: text/plain; charset=UTF-8');
header("Pragma: no-cache");
echo $r;

?>