<?php
require "include/bittorrent.php";
gzip();
dbconn();
loggedinorreturn();
if (get_user_class() < UC_SYSOP) 
	stderr("Error", "Permission denied.");

//Presets
$act = htmlspecialchars($_GET['act']);
$id = (int)$_GET['id'];

if (!$act) {
$act = "forum";
}

// DELETE FORUM ACTION
if ($act == "del") {

if (!$id) { header("Location: $PHP_SELF?act=forum"); die();}

sql_query ("DELETE FROM overforums WHERE id = $id") or sqlerr(__FILE__, __LINE__);

header("Location: $PHP_SELF?act=forum");
die();
}

//EDIT FORUM ACTION
if ($_POST['action'] == "editforum") {

$name = htmlspecialchars($_POST['name']);

if (!$name && !$desc && !$id) { header("Location: $PHP_SELF?act=forum"); die();}

sql_query("UPDATE overforums SET sort = '" . $_POST['sort'] . "', name = " . sqlesc($_POST['name']). ", forid = 0 WHERE id = '".$_POST['id']."'") or sqlerr(__FILE__, __LINE__);
header("Location: $PHP_SELF?act=forum");
die();
}

//ADD FORUM ACTION
if ($_POST['action'] == "addforum") {

$name = htmlspecialchars($_POST['name']);

if (!$name && !$desc)
{
	header("Location: $PHP_SELF?act=forum");
    die();
}

sql_query("INSERT INTO overforums (sort, name, forid) VALUES(" . $_POST['sort'] . ", " . sqlesc($_POST['name']). ", 1)") or sqlerr(__FILE__, __LINE__);

header("Location: $PHP_SELF?act=forum");
die();
}



stdhead("Категории");
begin_main_frame();



if ($act == "forum")
{

// SHOW FORUMS WITH FORUM MANAGMENT TOOLS
begin_frame("Категории");
?>
<script language="JavaScript">
<!--
function confirm_delete(id)
{
   if(confirm('Вы уверены что хотите удалить категорию ?'))
   {
      self.location.href='<? $PHP_SELF; ?>?act=del&id='+id;
   }
}
//-->
</script>
<?
echo '<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="0">';
echo "<tr><td class=colhead align=left>Название</td><td class=colhead>Действие</td></tr>";
$result = sql_query ("SELECT  * FROM overforums ORDER BY sort ASC");
if ($row = mysql_fetch_array($result)) {
do {


echo "<tr><td><a href=forum.php?action=forumview&forid=".$row["id"]."><b>".htmlspecialchars($row["name"])."</b></a></td>";
echo "<td align=center nowrap><b><a href=\"".$PHP_SELF."?act=editforum&id=".$row["id"]."\">Редактировать</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('".$row["id"]."');\"><font color=red>Удалить</font></a></b></td></tr>";


} while($row = mysql_fetch_array($result));
} else {print "<tr><td>Sorry, no records were found!</td></tr>";}
echo "</table>";
?>
<br><br>
<form method=post action="<?=$PHP_SELF;?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>Создать</td>
  </tr>
  <tr>
    <td><b>Название</td>
    <td><input name="name" type="text" size="20" maxlength="60"></td>
  </tr>
    <tr>
    <td><b>Сортировка </td>
    <td>
    <select name=sort>
    <?
$res = sql_query ("SELECT sort FROM overforums");
$nr = mysql_num_rows($res);
	    $maxclass = $nr + 1;
	  for ($i = 0; $i <= $maxclass; ++$i)
	    print("<option value=$i>$i \n");
?>
	</select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="addforum"><input type="submit" name="Submit" value="Создать"></td>
  </tr>
</table>

<?

print("<tr><td align=center colspan=1><form method=\"get\" action=\"forummanage.php#add\"></form><form method=\"get\" action=\"forummanage.php#add\"><input type=\"submit\" value=\"Форумы\" style='height: 18px' /></form></td></tr>\n");
end_frame(); }?>

<? if ($act == "editforum") {

//EDIT PAGE FOR THE FORUMS
$id = 0+$_GET["id"];
begin_frame("Edit Overforum");
$result = sql_query ("SELECT * FROM overforums where id = '$id'");
if ($row = mysql_fetch_array($result)) {

// Get OverForum Name - To Be Written

do {
?>

<form method=post action="<?=$PHP_SELF;?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>Редактировать : <?=htmlspecialchars($row["name"]);?></td>
  </tr>

    <td><b>Название </td>
    <td><input name="name" type="text" size="20" maxlength="60" value="<?=htmlspecialchars($row["name"]);?>"></td>
  </tr>
    <tr>
    <td><b>Сортировка </td>
    <td>
    <select name=sort>
    <?
$res = sql_query ("SELECT sort FROM overforums");
$nr = mysql_num_rows($res);
	    $maxclass = $nr + 1;
	  for ($i = 0; $i <= $maxclass; ++$i)
	    print("<option value=$i" . ($row["sort"] == $i ? " selected" : "") . ">$i \n");
?>
	</select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="editforum"><input type="hidden" name="id" value="<?=$id;?>"><input type="submit" name="Submit" value="Редактировать"></td>
  </tr>
</table>

<?
} while($row = mysql_fetch_array($result));
} else {print "Sorry, no records were found!";}

print("<tr><td align=center colspan=1><form method=\"get\" action=\"forumcats.php#add\"><input type=\"submit\" value=\"Вернуться\" style='height: 18px' /></form></td></tr>\n");
end_frame(); }?>

<?
end_main_frame();
stdfoot();
?>