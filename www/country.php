<?
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
 {
 stderr($lang['error'], $lang['access_denied']);
die();
}
stdhead("Редактирование стран");
$edited = $_GET['edited'];
if($edited == 1) {
$id = $_GET['id'];
$country_name = $_GET['country_name'];
$country_id = $_GET['country_id'];
$country_flag = $_GET['country_flag'];
$query = "UPDATE countries SET
name = '$country_name',
flagpic = '$country_flag' WHERE id=".sqlesc($id);
$sql = sql_query($query);
if($sql) {
echo("<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<tr><td><div align='center'><strong>Успешно изменено! </strong>[ <a href='countryadd.php'>На главную</a> ]</div></tr>");
echo("</table>");
end_frame();
stdfoot();
die();
}
}

$editid = $_GET['editid'];
$id = $_GET['id'];
$name = $_GET['name'];
$flag = $_GET['flagpic'];

if($editid > 0) {
echo("<form name='form1' method='get' action='" . $_SERVER['PHP_SELF'] . "'>");
echo("<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<div align='center'><input type='hidden' name='edited' value='1'>Вы редактируете страну <strong> $name</strong></div>");
echo("<br>");
echo("<input type='hidden' name='id' value='$editid'<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<tr><td>Страна:</td><td align='left'><input type='text' size=60 name='country_name' value='$name'></td></tr>");
echo("<tr><td>Флаг:</td><td align='left'><input type='text' size=60 name='country_flag' value='$flag'></td></tr>");
echo("<tr><td colspan=\"2\"><div align='center'><input type='Submit'></div></td></tr>");
echo("</table></form>");
end_frame();
stdfoot();
die();
}

$sure = $_GET['sure'];
if($sure == "yes") {
$delid = $_GET['delid'];
$query = "DELETE FROM countries WHERE id=" .sqlesc($delid) . " LIMIT 1";
$sql = mysql_query($query);
echo("<strong>Страна успешно была удалёна! </strong>[ <a href='country.php'>На главную</a> ]");
end_frame();
stdfoot();
die();
}
$delid = $_GET['delid'];
$name = $_GET['name'];
if($delid > 0) {
echo("Вы уверены что хотите удалить этот страну? (<strong>$name</strong>) ( <strong><a href='". $_SERVER['PHP_SELF'] . "?delid=$delid&name=$name&sure=yes'>Да!</a></strong> / <strong><a href='". $_SERVER['PHP_SELF'] . "'>Нет!</a></strong> )");
end_frame();
stdfoot();
die();

}

$res = sql_query("SELECT COUNT(*) FROM countries") or die(mysql_error());
$row = mysql_fetch_array($res);
$count = $row[0];
$perpage = 100;
$limit = 100;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] ."?" . "&" );
print($pagertop);

echo("<table width=\"100%\" class=main cellspacing=0 cellpadding=5>");
echo("<td>ID</td><td>Страна</td><td>Флаг</td><td>Редактировать</td><td>Удалить</td>");
$query = "SELECT * FROM countries WHERE 1=1 ORDER BY ID DESC $limit";
$sql = sql_query($query);
while ($row = mysql_fetch_array($sql)) {
$ID = $row['id'];
$name = $row['name'];
$country_city = "<img src=/pic/flag/$row[flagpic] alt=\"$ct_a[name]\" style='margin-left: 8pt'>\n";

echo("<tr><td><strong>$ID</strong></td><td>$name</td><td>$country_city</td><td><a href='" . $PHP_SELF['$_SERVER'] . "countryadd.php?editid=$ID&name=$name&flagpic=$row[flagpic]'><div align='center'><img src='$BASEURL/pic/multipage.gif' border='0' class=special /></a></div></td> <td><div align='center'><a href='" . $PHP_SELF['$_SERVER'] . "countryadd.php?delid=$ID&name=$name'><img src='$BASEURL/pic/warned2.gif' border='0' class=special align='center' /></a></div></td></tr>");
}
print("</table></table>");
stdfoot();
?>