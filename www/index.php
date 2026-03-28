<?php
// Включение всех ошибок, включая предупреждения и Notice
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '0'); // Отключаем логирование, если хотим видеть на экране
ini_set('html_errors', '1');
require "include/bittorrent.php";
dbconn(true);

stdhead($lang['homepage']);
stdfoot();
?>