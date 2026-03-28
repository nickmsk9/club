<?
require "include/bittorrent.php";

dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR) die('Access denied, u\'re not sysop'); 

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] != "POST")
 stderr($lang['error'], "Шутник!");

if (get_user_class() < UC_MODERATOR)
stderr($lang['error'], $lang['access_denied']);

$sender_id = ($_POST['sender'] == 'system' ? 0 : $CURUSER['id']);
$dt = sqlesc(get_date_time());
$msg = $_POST['msg'];
if (!$msg)
stderr($lang['error'],"Пожалуста, введите сообщение!");

$subject = $_POST['subject'];
if (!$subject)
stderr($lang['error'],"Пожалуста, введите тему!");

$clases = $_POST['clases'];
if (!$_POST['clases'])
	stderr($lang['error'],"Выберите 1 или более классов для отправки сообщения.");

/*$query = sql_query("SELECT id FROM users WHERE class IN (".implode(", ", array_map("sqlesc", $clases)).")");

while ($dat=mysql_fetch_assoc($query)) {
	sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES ($sender_id, $dat[id], '" . get_date_time() . "', " . sqlesc($msg) .", " . sqlesc($subject) .")") or sqlerr(__FILE__,__LINE__);
}*/

write_log("Массовое сообщение от пользователя $CURUSER[username]","FFAE00","tracker");

sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) SELECT $sender_id, id, NOW(), ".sqlesc($msg).", ".sqlesc($subject)." FROM users WHERE class IN (".implode(", ", array_map("sqlesc", $clases)).")") or sqlerr(__FILE__,__LINE__);
$counter = mysql_affected_rows();

header("Refresh: 2; url=staffmess.php");

stderr("Успешно", "Отправлено $counter сообщений.");

?>