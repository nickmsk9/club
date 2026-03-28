<?php

include($rootpath . 'include/bittorrent.php');

dbconn();
loggedinorreturn();

if (get_user_class() >= UC_SYSOP) {
if(isset($_POST["delmp"])) {
    $do="DELETE FROM messages WHERE id IN (" . implode(", ", $_POST[delmp]) . ")";
    $res=sql_query($do);
    }
}
header("Refresh: 0; url=spamko.php");
?>
