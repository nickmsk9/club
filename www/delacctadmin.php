<?

require "include/bittorrent.php";
dbconn();

if (get_user_class() < UC_ADMINISTRATOR)
stderr($lang['error'], "Нет доступа.");

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST")
{
$username = trim($_POST["username"]);

if (!$username)
  stderr($lang['error'], "Пожалуста заполняйте форму корректно.");

$res = sql_query("SELECT * FROM users WHERE username=" . sqlesc($username)) or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) != 1)
  stderr($lang['error'], "Неверное имя пользователя. Проверьте введеные данные.");
$arr = mysql_fetch_assoc($res);

$id = $arr['id'];
$res = sql_query("DELETE FROM users WHERE id = $id") or sqlerr(__FILE__, __LINE__);
if (mysql_affected_rows() != 1)
  stderr($lang['error'], "Невозможно удалить аккаунт.");
sql_query("DELETE FROM messages WHERE receiver = $id") or sqlerr(__FILE__,__LINE__);
sql_query("DELETE FROM friends WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
sql_query("DELETE FROM friends WHERE friendid = $id") or sqlerr(__FILE__,__LINE__);
sql_query("DELETE FROM blocks WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
sql_query("DELETE FROM blocks WHERE blockid = $id") or sqlerr(__FILE__,__LINE__);
sql_query("DELETE FROM peers WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
stderr($lang['success'], "Аккаунт <b>$username</b> удален.");
}
stdhead("Удалить аккаунт");
?>
<h1>Удалить аккаунт</h1>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=delacctadmin.php>
<tr><td class=rowhead>Пользователь</td><td><input size=40 name=username></td></tr>

<tr><td colspan=2><input type=submit class=btn value='Удалить'></td></tr>
</form>
</table>
<?
stdfoot();
?>