<?


require "include/bittorrent.php";
dbconn();
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);
  if (!$username || !$password)
    stderr($lang['error'], "Заполните форму корректно.");
  $res = sql_query("SELECT * FROM users WHERE username=" . sqlesc($username) .
  " AND passhash=md5(concat(secret,concat(" . sqlesc($password) . ",secret)))") or sqlerr(__FILE__, __LINE__);
  if (mysql_num_rows($res) != 1)
    stderr($lang['error'], "Неверное имя пользователя или пароль. Проверьте введеную информацию.");
  $arr = mysql_fetch_assoc($res);

  $id = $arr['id'];
  $res = sql_query("DELETE FROM users WHERE id = $id") or sqlerr(__FILE__, __LINE__);
  sql_query("DELETE FROM messages WHERE receiver = $id") or sqlerr(__FILE__,__LINE__);
  sql_query("DELETE FROM friends WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
  sql_query("DELETE FROM friends WHERE friendid = $id") or sqlerr(__FILE__,__LINE__);
  sql_query("DELETE FROM blocks WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
  sql_query("DELETE FROM blocks WHERE blockid = $id") or sqlerr(__FILE__,__LINE__);
  sql_query("DELETE FROM peers WHERE userid = $id") or sqlerr(__FILE__,__LINE__);
  if (mysql_affected_rows() != 1)
    stderr($lang['error'], "Невозможно удалить аккаунт.");
  stderr($lang['success'], "Аккаунт удален.");
}
stdhead("Удалить аккаунт");
?>
<h1></h1>
<table border="1" cellspacing="0" cellpadding="5">
<form method="post" action="delacct.php">
<tr><td class="colhead" colspan="2">Удалить аккаунт</td></tr>
<tr><td class="rowhead">Пользователь</td><td><input size="40" name="username"></td></tr>
<tr><td class="rowhead">Пароль</td><td><input type="password" size="40" name="password"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" class="btn" value="Удалить"></td></tr>
</form>
</table>
<?
stdfoot();
?>