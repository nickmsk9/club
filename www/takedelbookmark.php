<?php
require_once("include/bittorrent.php");

function bark($msg) {
    global $lang;
    stdhead();
    stdmsg($lang['error'], $msg);
    stdfoot();
    exit;
}

dbconn();
loggedinorreturn();

if (!isset($_POST['delbookmark']) || !is_array($_POST['delbookmark']) || count($_POST['delbookmark']) === 0) {
    bark("Ничего не выбрано");
}

$ids = array_map("intval", $_POST['delbookmark']);
$ids_str = implode(", ", $ids);

$res2 = sql_query("SELECT id, userid FROM bookmarks WHERE id IN ($ids_str)") or sqlerr(__FILE__, __LINE__);

while ($arr = mysqli_fetch_assoc($res2)) {
    if (($arr['userid'] == $CURUSER['id']) || (get_user_class() > 3)) {
        sql_query("DELETE FROM bookmarks WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);
    } else {
        bark("Вы пытаетесь удалить не свою закладку!");
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>