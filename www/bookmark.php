<?php
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();

function bark($msg, $error = true) {
global $lang;
stdhead(($error ? $lang['error'] : $lang['success']));
	begin_main_frame();
    stdmsg(($error ? $lang['error'] : $lang['success']), $msg, ($error ? 'error' : 'success'));
	end_main_frame();
stdfoot();
exit;
}

$id = (int) $_GET["torrent"];

if (empty($id)){
    bark("Торрент не выбран ! ");
	header("Refresh: 3; url=browse.php");
}
$res = sql_query("SELECT name FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_array($res);
if(empty($arr["name"]))
       bark("Торрент не существует ! ");


if ((get_row_count("bookmarks", "WHERE userid = $CURUSER[id] AND torrentid = $id")) > 0)
       bark("Торрент \"".$arr['name']."\" уже в закладках.");

sql_query("INSERT INTO bookmarks (userid, torrentid) VALUES ($CURUSER[id], $id)") or sqlerr(__FILE__,__LINE__);

header("Refresh: 3; url=".$DEFAULTBASEURL."/details/id".$id);

bark("Торрент \"".$arr['name']."\" успешно добавлен !",false);

?>