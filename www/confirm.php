<?php
require_once("include/bittorrent.php");


$id = intval($_GET["id"]);
$md5 = $_GET["secret"];

if (!$id)
die("error 0");
dbconn();


$res = sql_query("SELECT passhash, editsecret, status FROM users WHERE id = $id");
$row = mysql_fetch_array($res);

if (!$row) 
	die("error 1");

if ($row["status"] != "pending") {
	header("Location: ok.php?type=confirmed");
	exit();
}

$sec = hash_pad($row["editsecret"]);
if ($md5 != md5($sec))
	die("error 2");

sql_query("UPDATE users SET status='confirmed', editsecret='' WHERE id=$id AND status='pending'");

if (!mysql_affected_rows())
	die("error 3");


logincookie($id, $row["passhash"],$default_lang);

header("Location: ok.php?type=confirm");

?>