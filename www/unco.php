<?

require "include/bittorrent.php";
dbconn();
global $mysqli, $memcache_obj;
if (!isset($memcache_obj)) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('localhost', 11211);
}
loggedinorreturn();
stdhead("Не подтвержденные пользователи");
begin_main_frame();
begin_frame("Не подтвержденные пользователи");


if (get_user_class() < UC_ADMINISTRATOR)
die;
$res = $mysqli->query("SELECT * FROM users WHERE status='pending' ORDER BY username");
if (!$res) stderr("Ошибка", "Ошибка базы данных: " . $mysqli->error);
if ($res->num_rows != 0)
{
print'<br /><table width=100% border=1 cellspacing=0 cellpadding=5>';
print'<tr>';
print'<td class=rowhead><center>Пользователь</center></td>';
print'<td class=rowhead><center>eMail</center></td>';
print'<td class=rowhead><center>Зарегистрирован</center></td>';
print'<td class=rowhead><center>Статус</center></td>';
print'<td class=rowhead><center>Подтвердить</center></td>';
print'</tr>';
while ($row = $res->fetch_assoc())
{
$id = $row['id'];
print'<tr><form method=post action=modtask.php>';
print'<input type=hidden name=\'action\' value=\'confirmuser\'>';
print("<input type=hidden name='userid' value='$id'>");
print("<input type=hidden name='returnto' value='unco.php'>");
print'<a href="user/id' . $row['id'] . '"><td><center>' . $row['username'] . '</center></td></a>';
print'<td align=center>&nbsp;&nbsp;&nbsp;&nbsp;' . $row['email'] . '</td>';
print'<td align=center>&nbsp;&nbsp;&nbsp;&nbsp;' . $row['added'] . '</td>';
print'<td align=center><select name=confirm><option value=pending>Не подтвержден</option><option value=confirmed>Подтвержден</option></select></td>';
print'<td align=center><input type=submit value="OK" style=\'height: 20px; width: 40px\'>';
print'</form></tr>';
}
print '</table>';
}
else
{
	print 'Нет не подтвержденных пользователей...';
}

end_frame();
end_main_frame();
stdfoot();
?>