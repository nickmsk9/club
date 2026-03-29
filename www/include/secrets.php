<?php
require_once(__DIR__ . '/class/class.memcached_stub.php');

$mysql_host = "db";
$mysql_user = "root";
$mysql_pass = "root";
$mysql_db = "555";
$mysql_charset = "utf8";
$cookie_domain = 'animeclub.local'; // или '127.0.0.1', или 'animeclub.local' домен для кукиз - печеньки

// Подключение к MySQL
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if($mysqli->connect_error) {
    die("MySQL connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset($mysql_charset);

// Подключение к Memcached внутри докер-сети (имя контейнера и стандартный порт 11211)
$memcached_host = 'memcached';
$memcached_port = 11211;

$memcached = new Memcached();
$memcached->addServer($memcached_host, $memcached_port);


// Включение ошибок как можно раньше
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);



///////////////SMARTY//////////////////////




////////////////////////////////////////////



?>
