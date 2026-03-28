<?php
require_once("include/bittorrent.php");
dbconn();
getlang();

if (empty($_GET["l"])) stderr($lang['error'], $lang['invalid_id']);
			$domain = $_SERVER['HTTP_HOST'];
		if ( strtolower( substr($domain, 0, 4) ) == 'www.' )
			$domain = substr($domain, 4);	// Fix the domain to accept domains with and without 'www.'. 
		if ( substr($domain, 0, 1) != '.' )
			$domain = '.'.$domain;	// Add the dot prefix to ensure compatibility with subdomains
	
	setcookie("lang", unesc($_GET["l"]), 0x7fffffff, "/",$domain);
	if ($CURUSER) 
	sql_query("UPDATE users SET language = ".sqlesc($_GET["l"])." WHERE id = ".$CURUSER['id']);

	if (isset($_GET['returnto']))
	header("Location: ".htmlspecialchars($_GET["returnto"]));
	else
	header("Location: index.php");

?>