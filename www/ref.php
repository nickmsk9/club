<?php
require "include/bittorrent.php";
gzip();
dbconn(false);

$re = $_GET['ref'] ?? '';
if (!$re) {
    die("Не передан ref ID");
}

$ref = base64_decode($re);
$ip = getip();
$uref = !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'Не определено';
$date = get_date_time();

setcookie("ref", $ref, time() + 60*60*24*365, "/"); // кука на 1 год

sql_query("INSERT INTO referals (ref,ip,from_url,added) VALUES (" .
    sqlesc($ref) . "," .
    sqlesc($ip) . "," .
    sqlesc($uref) . "," .
    sqlesc($date) . ")") or sqlerr(__FILE__, __LINE__);

header("Location: index.php");
exit;
?>