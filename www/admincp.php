<?php
require "include/bittorrent.php";
dbconn(false);
ob_start();
//httpauth();
if (get_user_class() < UC_ADMINISTRATOR) die('Access denied, u\'re not sysop'); 
stdhead("Панель администратора");
define("ADMIN_FILE", 1);
$admin_file = "admincp";
begin_main_frame();
$frame_caption = "Панель администратора";
begin_frame($frame_caption, true);
include_once("ad_min/admin.php");
end_frame();
end_main_frame();
stdfoot();
?>