<?php

require_once("include/bittorrent.php");
dbconn();
function bark($msg) {
	global $lang;

	stdmsg($lang['error'], $msg);
	stdfoot();
	exit;
}

function sucs($msg) {
	global $lang;

	stdmsg("Успешно!!!", $msg);
	stdfoot();
	exit;
}
if($_GET[del] >0){

$id = $_GET[del];
$id = floor($id);
$id = 0 + $id;
stdhead("Фотография # ".$id."");
if (!is_valid_id($id))
  bark($lang['invalid_id']);
  $check = sql_query("SELECT userid FROM album WHERE id = $id");
  $row=mysql_fetch_array($check);
  if($row[userid] == $CURUSER[id] || get_user_class() >=UC_MODERATOR){
  $sql = mysql_query("DELETE FROM album WHERE id = $id");
  if($sql){
  	sucs("Удачно удаленно вернутся <a href=allalbum.php>назад</a>");
  }}else{
  		bark("Ты что пытаешся удалить!!! Доступ отказан.");
  }
	die();
}
stdhead("Фотография # ".$id."");
begin_main_frame();

$id = $_GET[id];
$id = floor($id);
$id = 0 + $id;
if (!is_valid_id($id))
  bark($lang['invalid_id']);

$res = sql_query("SELECT album.*, users.username as username,users.class as class FROM album LEFT JOIN users ON users.id = album.userid WHERE album.id = ".sqlesc($id)."") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($res)==0){
	 bark("А фотки то нема!!! Вернутся <a href=allalbum.php>назад</a>.");
}
$row=mysql_fetch_array($res);
$link = $row[link];
$res2 = sql_query("SELECT * FROM album WHERE id < ".sqlesc($id)." ORDER BY `id` DESC LIMIT 2 ") or sqlerr(__FILE__, __LINE__);
$count = mysql_num_rows($res2);
if($count != 2){
$limit = 2-$count;
$limits = 2+$limit;
}else{
	$limits = 2;
}
$res1 = sql_query("SELECT * FROM album WHERE id > '".$id."' LIMIT $limits") or sqlerr(__FILE__, __LINE__);
$count1 = mysql_num_rows($res1);
if($count1 < 2){
$limit = 2-$count1;
$limits = 2+$limit;
$res2 = sql_query("SELECT * FROM album WHERE id < '".$id."'ORDER BY `id` DESC LIMIT $limits") or sqlerr(__FILE__, __LINE__);
}
?>
	<script type="text/javascript" src="fancybox/fancybox.js"></script>
	<script>
	 jQuery("a.screen").fancybox({
			'overlayShow' : false,

			});
	</script>
<link rel="stylesheet" type="text/css" href="fancybox/fancybox.css"/>
<?
begin_frame("Фотография # ".$id."", true);
while($row1=mysql_fetch_array($res1)){
	$after .=" <a href=\"album.php?id=$row1[id]\"><img BORDER=\"0\" src =\"picturesize.php?image=".$row1[link]."&type=1\"></a> ";
if($thing2 != 1){
	$next = "<a href=\"album.php?id=$row1[id]\">Следущая</a>";
	$thing2 = 1;
}
}
while($row2=mysql_fetch_array($res2)){
	$text = " <a href=\"album.php?id=$row2[id]\" ><img BORDER=\"0\" src =\"picturesize.php?image=".$row2[link]."&type=1\"></a> ";
	$before = $text."".$before;
if($thing != 1){
	$pred = "<a href=\"album.php?id=$row2[id]\">Предыдущая</a>";
	$thing = 1;
}
}
function space($num){
	for($i=0; $i<$num; $i++){
		$space .="&#160;";
	}
	return $space;
}
if($pred ==''){
		$pred = "Предыдущая";
}
if($next ==''){
		$next = "Следущая";
}
	if(strlen($row["username"])==0){
		$row["username"] ="Аноним";
		$user = "".get_user_class_color($row["class"], htmlspecialchars_uni($row["username"]))."";
	}else{
			$user = "<a href=album.php?id=$row[userid]>".get_user_class_color($row["class"], htmlspecialchars_uni($row["username"]))."</a>";
	}
$now = "<img BORDER=\"2\" src =\"picturesize.php?image=$link&type=1\">";
print("<table border=1 width=99% cellspacing=0 cellpadding=5>");
print("<tr align=center><td width=50%>$pred </td><td width=50%>$next</td><tr>");
print("<tr align=center><td colspan=2><noindex><a href=\"".$row[link]."\" class=\"screen\"><img src =\"picturesize.php?image=".$link."\" border=0></a></noindex></td><tr>");
print("<tr align=center><td colspan=2><b>Залил:</b> $user</td><tr>");
if(strlen($row[text]) > 0){
print("<tr align=center><td colspan=2><b>Описание:</b> $row[text]</td><tr>");
}
if($CURUSER["id"] == $row['userid'] || get_user_class() >=UC_MODERATOR){
print("<tr align=center><td><b><a href=$link>Скачать</a></b></td><td><b><a href=album.php?del=$id>Удалить</a></b></td><tr>");
}else{
	print("<tr align=center><td colspan=2><b>Скачать</b></td><tr>");
}
print("<tr align=center valign=\"MIDDLE\" ><td align=center colspan=2>".$before."$now".$after."</td><tr>");
print("</table>");


 $subres = mysql_query("SELECT COUNT(*) FROM comments WHERE galary = $id");
if($subres){
        $subrow = mysql_fetch_array($subres);
        $countcom = $subrow[0];
}
        $limited = 10;
      
if (!$countcom) {

  print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
  print("<tr><td class=colhead align=\"left\" colspan=\"2\">");
  print("<div style=\"float: left; width: auto;\" align=\"left\"> :: Список комментариев</div>");
  print("<div align=\"right\"><a href=#comments class=altlink_white>Добавить комментарий</a></div>");
  print("</td></tr><tr><td align=\"center\">");
  print("Комментариев нет. <a href=#comments>Желаете добавить?</a>");
  print("</td></tr></table><br>");


 print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
  print("<tr><td class=colhead align=\"left\" colspan=\"2\">  <a name=comments>&nbsp;</a><b>:: Добавить комментарий к торренту</b></td></tr>");
  print("<tr><td width=\"100%\" align=\"center\" >");
  print("<form id=\"album\" name=album method=\"post\" action=\"comment.php?action=add\">");
  print("<center><table border=\"0\"><tr><td class=\"clear\">");
  print("<div align=\"center\">". textbbcode("album","text","", 1) ."</div>");
  print("</td></tr></table></center>");
  print("</td></tr><tr><td  align=\"center\" colspan=\"2\">");
  print("<input type=\"hidden\" name=\"gid\" value=\"$id\"/>");
  print("<input type=\"submit\" class=btn value=\"Разместить комментарий\" />");
  print("</td></tr></form></table>");

        }
        else {
        	$limit = 5;
                list($pagertop, $pagerbottom, $limit) = pager($limited, $count, "album.php?id=$id&", array(lastpagedefault => 1));

                $subres = sql_query("SELECT c.id, c.ip, c.text, c.user, c.added, c.editedby, c.editedat, u.avatar, u.warned, ".
                  "u.username, u.title, u.class, u.donor, u.downloaded, u.uploaded, u.gender, u.last_access, e.username AS editedbyname FROM comments AS c LEFT JOIN users AS u ON c.user = u.id LEFT JOIN users AS e ON c.editedby = e.id WHERE galary = " .
                  "$id ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);
                $allrows = array();
                while ($subrow = mysql_fetch_array($subres))
                        $allrows[] = $subrow;


         print("<table class=main cellspacing=\"0\" cellPadding=\"5\" width=\"100%\" >");
         print("<tr><td class=\"colhead\" align=\"center\" >");
         print("<div style=\"float: left; width: auto;\" align=\"left\"> :: Список комментариев</div>");
         print("<div align=\"right\"><a href=#comments class=altlink_white>Добавить комментарий</a></div>");
         print("</td></tr>");

         print("<tr><td>");
         print($pagertop);
         print("</td></tr>");
         print("<tr><td>");
                 commenttable($allrows);
         print("</td></tr>");
         print("<tr><td>");
         print($pagerbottom);
         print("</td></tr>");
         print("</table>");

        

 print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
  print("<tr><td class=colhead align=\"left\" colspan=\"2\">  <a name=comments>&nbsp;</a><b>:: Добавить комментарий</b></td></tr>");
  print("<tr><td width=\"100%\" align=\"center\" >");
  print("<form name=comment method=\"post\" id=\"comment\" action=\"comment.php?action=add\">");
  print("<center><table border=\"0\"><tr><td class=\"clear\">");
  print("<div align=\"center\">". textbbcode("comment","text","", 1) ."</div>");
  print("</td></tr></table></center>");
  print("</td></tr><tr><td  align=\"center\" colspan=\"2\">");
  print("<input type=\"hidden\" name=\"gid\" value=\"$id\"/>");
  print("<input type=\"submit\" class=btn value=\"Разместить\" />");
  print("</td></tr></form></table>");
        }
end_frame();
end_main_frame();
stdfoot();
?>
