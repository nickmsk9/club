<?php
require "include/bittorrent.php";
gzip();
dbconn();
loggedinorreturn();

if (get_user_class() < UC_SYSOP)
    stderr("Error", "Permission denied.");

$id = 0 + $_GET['id'];
// DELETE FORUM ACTION
if ($_GET['action'] == "del") {


if (!$id) { header("Location: $BASEURL/forummanage.php"); die();}

$result = sql_query ("SELECT * FROM topics where forumid = '".$_GET['id']."'");
if ($row = mysql_fetch_array($result)) {
do {
sql_query ("DELETE FROM posts where topicid = '".$row["id"]."'") or sqlerr(__FILE__, __LINE__);
} while($row = mysql_fetch_array($result));
}
sql_query ("DELETE FROM topics where forumid = '".$_GET['id']."'") or sqlerr(__FILE__, __LINE__);
sql_query ("DELETE FROM forums where id = '".$_GET['id']."'") or sqlerr(__FILE__, __LINE__);

header("Location: $BASEURL/forummanage.php");
die();
}

//EDIT FORUM ACTION
if ($_POST['action'] == "editforum") {

$name = htmlspecialchars($_POST['name']);
$desc = htmlspecialchars($_POST['desc']);

if (!$name && !$desc && !$id) { header("Location: $BASEURL/forummanage.php"); die();}

sql_query("UPDATE forums SET sort = '" . $_POST['sort'] . "', name = " . sqlesc($_POST['name']). ", description = " . sqlesc($_POST['desc']). ", forid = ".sqlesc(($_POST['overforums']))." where id = '".$_POST['id']."'") or sqlerr(__FILE__, __LINE__);
header("Location: $BASEURL/forummanage.php");
die();
}

//ADD FORUM ACTION
if ($_POST['action'] == "addforum") {

$name = htmlspecialchars($_POST['name']);
$desc = htmlspecialchars($_POST['desc']);

if (!$name && !$desc) { header("Location: $BASEURL/forummanage.php"); die();}

sql_query("INSERT INTO forums (sort, name,  description, forid) VALUES(" . $_POST['sort'] . ", " . sqlesc($_POST['name']). ", " . sqlesc($_POST['desc']). ", ".sqlesc(($_POST['overforums'])).")") or sqlerr(__FILE__, __LINE__);

header("Location: $BASEURL/forummanage.php");
die();
}

// SHOW FORUMS WITH FORUM MANAGMENT TOOLS
stdhead("Редактирование форумов");
begin_main_frame();
 begin_frame("Форумы");
?>
<script language="JavaScript">
<!--
function confirm_delete(id)
{
   if(confirm('Вы уверены что хотите удалить форум ?'))
   {
      self.location.href='<? $_SERVER["PHP_SELF"]; ?>?action=del&id='+id;
   }
}
//-->
</script>
<?
echo '<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="0">';
echo "<tr><td class=colhead align=left>Название</td><td class=colhead>Категория</td><td class=colhead>Действия</td></tr>";
$result = sql_query ("SELECT  * FROM forums ORDER BY sort ASC");
if ($row = mysql_fetch_array($result)) {
do {
$forid = (int)$row['forid'];
$res2 = sql_query("SELECT name FROM overforums WHERE id=$forid");
$arr2 = mysql_fetch_array($res2);
$name = htmlspecialchars($arr2['name']);


echo "<tr><td><a href=forum.php?action=viewforum&forumid=".$row["id"]."><b>".$row["name"]."</b></a><br>".$row["description"]."</td>";
echo "<td>".$name."</td><td align=center nowrap><b><a href=\"". $_SERVER["PHP_SELF"]."?action=editforum&id=".$row["id"]."\">Редакт</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('".$row["id"]."');\"><font color=red>Удалить</font></a></b></td></tr>";


} while($row = mysql_fetch_array($result));
} else {print "<tr><td>Sorry, no records were found!</td></tr>";}
echo "</table>";
?>
<br><br>
<form method=post action="<?=$_SERVER["PHP_SELF"];?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>Создать новый</td>
  </tr>
  <tr>
    <td><b>Название</td>
    <td><input name="name" type="text" size="20" maxlength="60"></td>
  </tr>
  <tr>
    <td><b>Описание</td>
    <td><input name="desc" type="text" size="30" maxlength="200"></td>
  </tr>
  <tr>
    <td><b>Категория</td>
    <td>
    <select name=overforums>
    <?

            $forid = (int)$row["forid"];
            $res = sql_query("SELECT * FROM overforums");
             while ($arr = mysql_fetch_array($res)) {

             $name = htmlspecialchars($arr["name"]);
             $i = (int)$arr["id"];

            print("<option value=$i" . ($forid == $i ? " selected" : "") . ">$prefix" . $name . "\n");
            }
?>
        </select>
    </td>
  </tr>

    <tr>
    <td><b>Сортировка</td>
    <td>
    <select name=sort>
    <?
$res = sql_query ("SELECT sort FROM forums");
$nr = mysql_num_rows($res);
            $maxclass = $nr + 1;
          for ($i = 0; $i <= $maxclass; ++$i)
            print("<option value=$i>$i \n");
?>
        </select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="addforum"><input type="submit" name="Submit" value="Создать" class=btn></td>
  </tr>
</table>

<?

print("<tr><td align=center colspan=1><form method=\"get\" action=\"forumcats.php#add\"></form><form method=\"get\" action=\"forumcats.php#add\"><input type=\"submit\" value=\"Создание Категорий\" class=\"btn\" /></form></td></tr>\n");
end_frame(); 
end_main_frame();?>
<? if ($_GET['action'] == "editforum") {

//EDIT PAGE FOR THE FORUMS

$id = (int)($_GET["id"]);
begin_frame("Редактирование");
$result = sql_query ("SELECT * FROM forums where id = ".sqlesc($id));
if ($row = mysql_fetch_array($result)) {

// Get OverForum Name - To Be Written

do {
?>

<form method=post action="<?=$_SERVER["PHP_SELF"];?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>Редактирование: <?=$row["name"];?></td>
  </tr>

    <td><b>Название</td>
    <td><input name="name" type="text" size="20" maxlength="60" value="<?=$row["name"];?>"></td>
  </tr>
  <tr>
    <td><b>Описание  </td>
    <td><input name="desc" type="text" size="30" maxlength="200" value="<?=$row["description"];?>"></td>
  </tr>


    <tr>
    <td><b>Категория </td>
    <td>
    <select name=overforums>
    <?

            $forid = (int)$row["forid"];
            $res = sql_query("SELECT * FROM overforums");
             while ($arr = mysql_fetch_array($res)) {

             $name = htmlspecialchars($arr["name"]);
             $i = $arr["id"];

            print("<option value=$i" . ($forid == $i ? " selected" : "") . ">$prefix" . $name . "\n");
            }


?>
        </select>
    </td>
  </tr>


    <tr>
    <td><b>Сортировка </td>
    <td>
    <select name=sort>
    <?
$res = sql_query ("SELECT sort FROM forums");
$nr = mysql_num_rows($res);
            $maxclass = $nr + 1;
          for ($i = 0; $i <= $maxclass; ++$i)
            print("<option value=$i" . ($row["sort"] == $i ? " selected" : "") . ">$i \n");
?>
        </select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="editforum"><input type="hidden" name="id" value="<?=$id;?>"><input type="submit" name="Submit" value="Готово" class="btn"></td>
  </tr>
</table>

<?
} while($row = mysql_fetch_array($result));
} else {print "Sorry, no records were found!";}

print("<tr><td align=center colspan=1><form method=\"get\" action=\"forummanage.php#add\"><input type=\"submit\" value=\"Вернуться\" class=\"btn\" /></form></td></tr>\n");
end_frame();
}

stdfoot();
?>