<?php
require "include/bittorrent.php"; 
dbconn(); 

global $lang, $mysqli, $CURUSER;

header("Content-Type: text/html; charset=" . ($lang['language_charset'] ?? 'UTF-8'));

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {

    $ip = $_SERVER['REMOTE_ADDR']; 
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $act = $_REQUEST['act'] ?? '';
    $userid = $CURUSER['id'] ?? 0;

    if ($id === 0 || $userid === 0) {
        die("Invalid ID or user not logged in");
    }

    $resid = mysqli_query($mysqli, "SELECT * FROM voting_id WHERE blog_id = " . sqlesc($id) . " AND userid = " . sqlesc($userid) . " AND ip_add = " . sqlesc($ip)) or die(mysqli_error($mysqli));
    $count = mysqli_num_rows($resid);

    if ($act === 'up') {

        if ($count == 0) {
            mysqli_query($mysqli, "UPDATE blogs SET up = up + 1 WHERE bid = " . sqlesc($id)) or die(mysqli_error($mysqli));
            mysqli_query($mysqli, "INSERT INTO voting_id (blog_id, ip_add, userid, act) VALUES (" . sqlesc($id) . ", " . sqlesc($ip) . ", " . sqlesc($userid) . ", " . sqlesc($act) . ")") or die(mysqli_error($mysqli));
            echo "<script>alert('Спасибо за голос !');</script>";
        } else {
            echo "<script>alert('Вы уже оценили этот пост !');</script>";
        }

        $result = mysqli_query($mysqli, "SELECT up FROM blogs WHERE bid = " . sqlesc($id)) or die(mysqli_error($mysqli));
        $row = mysqli_fetch_array($result);
        echo (int)($row[0] ?? 0);

    } else {

        if ($count == 0) {
            mysqli_query($mysqli, "UPDATE blogs SET down = down + 1 WHERE bid = " . sqlesc($id)) or die(mysqli_error($mysqli));
            mysqli_query($mysqli, "INSERT INTO voting_id (blog_id, ip_add, userid, act) VALUES (" . sqlesc($id) . ", " . sqlesc($ip) . ", " . sqlesc($userid) . ", " . sqlesc($act) . ")") or die(mysqli_error($mysqli));
            echo "<script>alert('Спасибо за голос !');</script>";
        } else {
            echo "<script>alert('Вы уже оценили этот пост !');</script>";
        }

        $result = mysqli_query($mysqli, "SELECT down FROM blogs WHERE bid = " . sqlesc($id)) or die(mysqli_error($mysqli));
        $row = mysqli_fetch_array($result);
        echo (int)($row[0] ?? 0);
    }

} else {
    die("Ошибочка! Загляните попозже!");
}
?>