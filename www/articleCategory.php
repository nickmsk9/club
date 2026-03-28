<?php

# +
# +---------------------------------+
# +   articles mod by qwertzuiop    |
# +---------------------------------+
# +

ob_start();
require_once("include/bittorrent.php");
dbconn(false);
getlang();
loggedinorreturn();
if (get_user_class() < UC_SYSOP) {
die($tracker_lang['access_denied']);
}
stdhead("Категории статей");
begin_main_frame();
print("<h1>Категории статей</h1>\n");
print("</br>");
print("<table width=70% border=1 cellspacing=0 cellpadding=2><tr><td align=center>\n");

///////////////////// D E L E T E C A T E G O R Y \\\\\\\\\\\\\\\\\\\\\\\\\\\\

$sure = $_GET['sure'];
if($sure == "yes") {
$delid = (int) $_GET['delid'];
$query = "DELETE FROM article_categories WHERE id=" .sqlesc($delid) . " LIMIT 1";
$sql = sql_query($query);
echo("Категория успешно удалена!<br />[ <a href='articleCategory.php'>Назад</a> ]");
end_frame();
end_main_frame();
stdfoot();
die();
}
$delid = (int) $_GET['delid'];
$name = htmlspecialchars($_GET['cat']);
if($delid > 0) {
echo("Вы действителньо хотите удалить эту категорию? ($name) ( <strong><a href=\"". $_SERVER['PHP_SELF'] . "?delid=$delid&cat=$name&sure=yes\">Да</a></strong> / <strong><a href=\"". $_SERVER['PHP_SELF'] . "\">Нет</a></strong> )");
end_frame();
end_main_frame();
stdfoot();
die();

}

///////////////////// E D I T A C A T E G O R Y \\\\\\\\\\\\\\\\\\\\\\\\\\\\
$edited = $_GET['edited'];
if($edited == 1) {
$id = (int) $_GET['id'];
$cat_name = htmlspecialchars($_GET['cat_name']);
$query = "UPDATE article_categories SET
name = ".sqlesc($cat_name)." WHERE id=".sqlesc($id);
$sql = sql_query($query);
if($sql) {
echo("<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<tr><td><div align='center'>Ваша категория отредактирована <strong>успешно!</strong><br />[ <a href='articleCategory.php'>Назад</a> ]</div></tr>");
echo("</table>");
end_frame();
end_main_frame();
stdfoot();
die();
}
}

$editid = (int) $_GET['editid'];
$name = htmlspecialchars($_GET['name']);
$img = htmlspecialchars($_GET['img']);
if($editid > 0) {
echo("<form name='form1' method='get' action='" . $_SERVER['PHP_SELF'] . "'>");
echo("<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<div align='center'><input type='hidden' name='edited' value='1'>Редактирование категории <strong>&quot;$name&quot;</strong></div>");
echo("<br />");
echo("<input type='hidden' name='id' value='$editid'<table class=main cellspacing=0 cellpadding=5 width=50%>");
echo("<tr><td>Название: </td><td align='right'><input type='text' size=50 name='cat_name' value='$name'></td></tr>");
echo("<tr><td></td><td><div align='right'><input type='Submit' value='Редактировать'></div></td></tr>");
echo("</table></form>");
end_frame();
end_main_frame();
stdfoot();
die();
}

///////////////////// A D D A N E W C A T E G O R Y \\\\\\\\\\\\\\\\\\\\\\\\\\\\
$add = $_GET['add'];
if($add == 'true') {
$cat_name = htmlspecialchars($_GET['cat_name']);
$query = "INSERT INTO article_categories (name) VALUES (".sqlesc($cat_name).")";
$sql = sql_query($query) or die(mysql_error());
if($sql) {
$success = TRUE;
} else {
$success = FALSE;
}
}
print("<br />");
echo("<table class=main cellspacing=0 cellpadding=5 width=50%>");
print("<tr><td colspan=2 align=center><strong>Добавить новую категорию</strong></td></tr>");
echo("<form name='form1' method='get' action='" . $_SERVER['PHP_SELF'] . "'>");
echo("<tr><td>Название: </td><td align='right'><input type='text' size=50 name='cat_name'><input type='hidden' size=50 value=true name='add'></td></tr>");
echo("<tr><td colspan=2><div align='right'><input type='Submit' value='Создать категорию'></div></td></tr>");
echo("</table>");
if($success == TRUE) print("<strong><font color=green>Категория удачно добавлена!</font></strong><br />");
elseif($success != TRUE && $add && $cat_name) print("<strong><font color=red>Не получилось добавить категорию</font></strong><br />");
echo("<br />");
echo("</form>");

///////////////////// E X I S T I N G C A T E G O R I E S \\\\\\\\\\\\\\\\\\\\\\\\\\\\

print("<strong>Существующие категории:</strong>");
print("<br />");
print("<br />");
echo("<table class=main cellspacing=0 cellpadding=5>");
echo("<td>ID</td><td>Название</td><td>Просмотр категории</td><td>Редактировать</td><td>Удалить</td>");
$query = "SELECT * FROM article_categories WHERE 1=1 ORDER BY id";
$sql = sql_query($query) or sqlerr(__FILE__, __LINE__);
while ($row = mysql_fetch_array($sql)) {
$id = (int) $row['id'];
$name = $row['name'];
echo("<tr><td><strong>$id</strong> </td> <td><strong>$name</strong></td> <td><div align='center'><a href='articles.php?category=$id'><img src='$DEFAULTBASEURL/pic/viewnfo.gif' border='0' class=special /></a></div></td> <td><a href='articleCategory.php?editid=$id&name=$name&img=$img&sort=$sort'><div align='center'><img src='$DEFAULTBASEURL/pic/multipage.gif' border='0' class=special /></a></div></td> <td><div align='center'><a href='articleCategory.php?delid=$id&cat=$name'><img src='$DEFAULTBASEURL/pic/warned2.gif' border='0' class=special align='center' /></a></div></td></tr>");
}

end_frame();
end_frame();
end_main_frame();
stdfoot();

?>