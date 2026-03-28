<?php
// Srinivas Tamada http://9lessons.info
// Load latest update 
require_once 'include/bittorrent.php';

dbconn();
include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';
include_once 'include/htmlcode.php';
include_once 'include/textlink.php';

$Wall = new Wall_Updates();

if (isset($_POST['msg_id'])) {
    global $mysqli;
    $msg_id = mysqli_real_escape_string(
        $mysqli,
        filter_input(INPUT_POST, 'msg_id', FILTER_SANITIZE_STRING)
    );
    $x = 0;
    include_once('load_comments.php');
}
?>