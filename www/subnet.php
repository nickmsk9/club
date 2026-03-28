<?

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
function ratios($up,$down, $color = True)
{
if ($down > 0)
{
$r = number_format($up / $down, 2);
if ($color)
$r = "<font color=".get_ratio_color($r).">$r</font>";
}
else
if ($up > 0)
$r = "Inf.";
else
$r = "---";
return $r;
}
$mask = "255.255.255.0";
$tmpip = explode(".",$CURUSER["ip"]);
$ip = $tmpip[0].".".$tmpip[1].".".$tmpip[2].".0";
$regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
if (substr($mask,0,1) == "/")
{
$n = substr($mask, 1, strlen($mask) - 1);
if (!is_numeric($n) or $n < 0 or $n > 32)
{
stdmsg($lang['error'], "Неверная маска подсети.");
stdfoot();
die();
}
else
$mask = long2ip(pow(2,32) - pow(2,32-$n));
}
elseif (!preg_match($regex, $mask))
{
stdmsg("Оишбка", "Неверная маска подсети.");
stdfoot();
die();
}
$res = sql_query("SELECT id, username, class, last_access, added, uploaded, downloaded FROM users WHERE enabled='yes' AND status='confirmed' AND id <> $CURUSER[id] AND INET_ATON(ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res)){
stdhead("Сетевые соседи");
begin_main_frame();
begin_frame("::Сетевые соседи ::");
begin_table();

print("<tr><td colspan=8>Эти пользователи ваши сетевые соседи, что означает что вы получите от них скорость выше.</td></tr>");
print("<tr><td class=colhead align=left>Пользователь</td>
<td class=colhead>Раздал</td><td class=colhead>Скачал</td>
<td class=colhead>Рейтинг</td><td class=colhead>Зарегистрирован</td>
<td class=colhead>Последний доступ</td><td class=colhead align=left>Класс</td>
<td class=colhead>IP</td></tr>\n");
while($arr=mysqli_fetch_assoc($res)){
print("<tr><td align=left><b><a href=user/id$arr[id]>".get_user_class_color($arr["class"], $arr["username"])."</a></b></td>
<td>".mksize($arr["uploaded"])."</td>
<td>".mksize($arr["downloaded"])."</td>
<td>".ratios($arr["uploaded"],$arr["downloaded"])."</td>
<td>$arr[added]</td><td>$arr[last_access]</td>
<td align=left>".get_user_class_name($arr["class"])."</td>
<td>".$tmpip[0].".".$tmpip[1].".".$tmpip[2].".*</td></tr>\n");
}

end_table();
end_frame();
end_main_frame();
stdfoot();}
else
stderr("Информация","Сетевых соседей не обнаружено.");
?>