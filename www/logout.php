<?php
require_once("include/bittorrent.php");
dbconn();
logoutcookie();
header("Location: $DEFAULTBASEURL/");
exit;