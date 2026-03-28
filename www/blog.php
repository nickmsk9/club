<?php
require "include/bittorrent.php"; 
dbconn(false); 

function bark($msg) {
	global $tracker_lang;
	stdhead($tracker_lang['error']);
	stdmsg($tracker_lang['error'], $msg);
	stdfoot();
	exit;
}

// Безопасно получаем bid и action
$bid = isset($_GET['bid']) ? (int)$_GET['bid'] : 0;
$action = isset($_GET['action']) ? trim($_GET['action']) : '';


if($bid && $action == ''){
if($CURUSER)
visitorsHistory($bid,5);

    $sql = sql_query("SELECT *, u.id, u.username, u.class, u.avatar FROM blogs LEFT JOIN users u ON blogs.uid = u.id WHERE bid = ".sqlesc($bid)) or sqlerr(__FILE__,__LINE__);
    $row = mysqli_fetch_assoc($sql); 
	if (empty($bid) || mysqli_num_rows($sql) < 1)
	 bark("Неверный идентификатор ! <a href=\"javascript:history.go(-1);\">Назад</a>");
	
$bid = isset($_GET['bid']) ? (int)$_GET['bid'] : 0;
    $moderate = "  <a href=\"/blog.php?bid=".$bid."&amp;action=edit\">Редакт.</a> | <a href=\"/blog.php?bid=".$bid."&amp;action=delete\">Удалить</a>";
                
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
		$bid = (int)$row['bid'];
		$userid = (int)$row['uid'];
		$date = get_blog_time($row['p_added']);
		$username = get_user_class_color($row['class'],$row['username']);
		$postname = htmlspecialchars(trim($row['subject']));
		$text =  str_replace("[more]","",$row['txt']);
		$text = format_comment($text);
		$tags = htmlspecialchars($row['tags']);
		$tags = blogtags($tags);
		$com_count = (int)$row['comments'];
		$views_count = (int)$row['views'];
		$voteup = $row['up'];
		$votedown = $row['down'];
		stdhead("Блог ".htmlspecialchars($row['username'])." - ".$postname);
		
?>
<link rel="stylesheet" href="<?=$DEFAULTBASEURL?>/css/voteup.css" type="text/css"> 
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/voteup.js"></script>
<style>
#post div.text {margin:15px 0;}
#post div.pod-text {padding-top: 15px; font-style: italic; color: #2a1d1d; border-top: 1px dashed gray;}
#post div.pod-text a {color: #2a1d1d;}
#post div.pod-text a:hover {text-decoration: none;}
#post {padding: 10px 10px 10px 10px; margin-right: 50px;}
#post .date {color: #92918D; }
#post .date a {text-decoration:none; }
#post .title {font-size: 20px; padding-bottom: 7px;}
#post .cat-btm {color: #92918D; margin-right: 20px;}
#post p {margin-bottom:10px;} 
</style>
<?php
begin_main_frame();
blog_menu();
begin_frame("<a href='/blogs.php'>Блоги</a> > <a href=\"/myblog.php?bid=". $userid ."\">". $username ."</a> > ".$postname);
		$h = "<div id=\"post\">
			<div class=\"date\">Написал <a href=\"/user/id". $userid ."\">". $username ."</a>, ". $date . $moderate ."</div>
			<div class=\"text\"><p>".$text."</p></div>  
			<div class=\"box1\">
			<div class='up'>
		<a href='blogs.php#' class=\"vote\" id=\"".$bid."\" act=\"up\" name=\"up\">".$voteup."</a></div>
		<div class='down'>
		<a href='blogs.php#' class=\"vote\" id=\"".$bid."\" act=\"down\" name=\"down\">".$votedown."</a></div>
			</div>
		<div class=\"pod-text\">
		<span class=\"cat-btm\">Метки: <b>".$tags."</b></span><br />
		<span class=\"cat-btm\">Просмотров: <b>".$views_count."</b></span>
		<span class=\"cat-btm\">Комментариев: <b>".$com_count."</b></span>
		</div>
		</div>";
		echo $h;
	if ($CURUSER["id"] != $row["uid"])
	sql_query("UPDATE LOW_PRIORITY blogs SET views = views + 1 WHERE bid = ".sqlesc($bid));
	end_frame();	  
	
	if($row["comment"] == "no")
	{ print ("<h3 align=\"center\">Комментарии к этой записи отключены ! </h3>");
	} else{
	# Including comments JavaScript code
	echo "<script type=\"text/javascript\" src=\"/js/blog_comm.js\"></script>\n";
       $subres = sql_query("SELECT COUNT(*) FROM blog_comments WHERE bid = $bid");
        $subrow = mysqli_fetch_array($subres);
        $count = $subrow[0];

        $limited = 10;
	
	if (!$count && $CURUSER) {

		 echo "<div id=\"comments_list\">\n";
         print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
         print("<tr><td width=\"100%\" class=\"brd\" align=\"center\" >");
         print("<form name=\"comment\" id=\"comment\">");
         print("<center><table border=\"0\"><tr><td class=\"brd\">");
         print("<div align=\"center\">". commbbcode("comment","text","", 1) ."</div>");
         print("</td></tr></table></center></form>");
         print("</td></tr><tr><td class=\"brd\" style=\"text-align:center;\" colspan=\"2\">");
         print("<input type=\"button\" class=btn value=\"Разместить комментарий\" onClick=\"BL_SendComment('{$bid}')\" id=\"send_comment\" />
		 		 <input type=\"button\" class=btn value=\"Смайлы\" onClick=\"javascript:winop()\" />
		 <input type=\"button\" class=btn value=\"Смайлы2\" onClick=\"javascript:winop2()\" />");
         print("</td></tr></table>");
  echo "</div>\n";

    } else {

                list($pagertop, $pagerbottom, $limit) = pager($limited, $count, "blog.php?bid=$bid&amp;", array(lastpagedefault => 1));

                $subres = sql_query("SELECT c.id, c.ip, c.text,c.bid AS blogid, c.user, c.added, c.editedby, c.editedat ,u.avatar, u.warned, ".
                  "u.username, u.title, u.class, u.donor, u.downloaded, u.uploaded, u.gender, u.last_access, e.username AS editedbyname 
				  FROM blog_comments AS c LEFT JOIN users AS u ON c.user = u.id LEFT JOIN users AS e ON c.editedby = e.id WHERE bid = " .
                  "$bid ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);
                $allrows = array();
                while ($subrow = mysqli_fetch_array($subres))
                        $allrows[] = $subrow;

echo "<div id=\"comments_list\">\n";
         print("<table class=main cellspacing=\"0\" cellPadding=\"5\" width=\"100%\" >");
		 print("<tr><td class=\"brd\">");
         print($pagertop);
         print("</td></tr>");

         print("<tr><td class=\"brd\">");
                 blogtable_ajax($allrows);
         print("</td></tr>");
         print("<tr><td class=\"brd\">");
         print($pagerbottom);
         print("</td></tr>");

         print("</table>");

if($CURUSER){
         print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
         print("<tr><td width=\"100%\" align=\"center\" class=\"brd\">");
         print("<form name=\"comment\" id=\"comment\">");
         print("<center><table border=\"0\"><tr><td class=\"brd\">");
         print("<div align=\"center\">". commbbcode("comment","text","", 1) ."</div>");
         print("</td></tr></table></center></form>");
         print("</td></tr><tr><td class=\"brd\" style=\"text-align:center;\" colspan=\"2\">");
         print("<input type=\"button\" class=btn value=\"Разместить комментарий\" onClick=\"BL_SendComment('{$bid}')\" id=\"send_comment\" />
		 <input type=\"button\" class=btn value=\"Смайлы\" onClick=\"javascript:winop()\" />
		 <input type=\"button\" class=btn value=\"Смайлы2\" onClick=\"javascript:winop2()\" />");
         print("</td></tr></table>");
		 }
  echo "</div>\n";

        } }
	begin_frame();
	if($CURUSER)
		print(visitorsList("Сейчас эту страницу просматривают : <div id=\"visitors\">[VISITORS]</div>\n", $VISITORS));
	end_frame();
	end_main_frame();
	stdfoot();

} elseif ($action == "edit"){
loggedinorreturn(); 
if ($_SERVER["REQUEST_METHOD"] == "POST")
{ 
  if (isset($_POST["edit"]))
            {
			////// МОД ТЭГОВ [by merdox] //////

$replace = array(", ", " , ", " ,");
$tags = trim(str_replace($replace, ",", mb_convert_case(unesc($_POST["tags"]), MB_CASE_LOWER, $mysql_charset)));
$oldtags = unesc($_POST["oldtags"]);

$un = array_diff(explode(",", $tags), explode(",", $oldtags));
$un2 = array_diff(explode(",", $oldtags), explode(",", $tags));

$ret = array();
$res = sql_query("SELECT name FROM blogtags");
while ($row = mysqli_fetch_array($res))
	$ret[] = $row["name"];

$union = array_intersect($ret, $un);
$ununion = array_diff($un, $ret);

foreach ($union as $tag) {
		@sql_query('UPDATE blogtags SET howmuch=howmuch+1 WHERE name LIKE CONCAT(\'%\', '.sqlesc($tag).', \'%\')') or sqlerr(__FILE__, __LINE__);
	}

foreach ($un2 as $tag) {
		@sql_query('UPDATE blogtags SET howmuch=howmuch-1 WHERE name LIKE CONCAT(\'%\', '.sqlesc($tag).', \'%\')') or sqlerr(__FILE__, __LINE__);
	}

foreach ($ununion as $tag) {
		@sql_query("INSERT INTO blogtags ( name, howmuch) VALUES ( ".sqlesc($tag).", 1)") or sqlerr(__FILE__, __LINE__);
	}

////// МОД ТЭГОВ [by merdox] //////
			
			
                sql_query("UPDATE blogs SET subject=" . sqlesc($_POST["name"]) . ", tags=".sqlesc($_POST["tags"])." ,privat=".sqlesc($_POST["privat"]).",comment=".sqlesc($_POST["comment"]).", txt=" . sqlesc($_POST["txt"]) . " WHERE bid=".sqlesc($_POST["bid"])) or sqlerr(__FILE__, __LINE__);
                header("Refresh: 2; url=" . $DEFAULTBASEURL . "/blog.php?bid=" .(int)$_POST["bid"]);
                
				stdsucc("Успешно", "Информация обновлена");
            }

            else
            {
                stderr("Ошибка", "Нет доступа ! <a href=\"javascript:history.go(-1);\">Назад</a>");
                die();
            }
			} else {
    $sql = sql_query("SELECT * FROM blogs WHERE bid=".sqlesc($bid)) or sqlerr(__FILE__,__LINE__);
	$row = mysqli_fetch_assoc($sql);
	
	if ($CURUSER["id"] == $row["uid"] || get_user_class() >= UC_MODERATOR)
                        $owner = 1;
                else
                        $owner = 0;
    if (empty($bid) || mysqli_num_rows($sql) < 1 || $owner == 0)
	bark("Неверный идентификатор ! <a href=\"javascript:history.go(-1);\">Назад</a>");
	
	$subject = htmlspecialchars($row['subject']);
	$tags = htmlspecialchars($row['tags']);
	$title = "Редактирование записи - ".$subject;
	$privat = $row['privat'] == 'yes';
	$comment = $row['comment'] == 'yes';
	stdhead("Блог - ".$title);
	?>
	<script type="text/javascript" src="markitup/jquery.markitup.pack.js"></script>
<script type="text/javascript" src="markitup/sets/bbcode/set.js"></script>
<link rel="stylesheet" type="text/css" href="markitup/skins/simple/style.css" />
<link rel="stylesheet" type="text/css" href="markitup/sets/bbcode/style.css" />
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('#txt').markItUp(mySettings);			
});
</script>
<?	begin_main_frame();

	begin_frame($title, true);
                    print("<form name=\"blogs\"  method=\"post\" action=\"blog.php?bid=".$bid."&amp;action=edit\" enctype=\"multipart/form-data\">\n");
                    print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
                    tr("Заголовок ", "<input type=\"text\" name=\"name\" size=\"76\" value=\"" . $subject . "\">", 1);
                    print("<input type=\"hidden\" name=\"edit\">");
                    print("<input type=\"hidden\" name=\"bid\" value=\"" . $bid . "\">");
                    print("<tr align=\"right\" valign=\"top\">\n<td><b>Текст </b></td>\n<td align=\"left\">");
                    print("<textarea name=txt id=txt  cols=86 rows=18>".htmlspecialchars($row['txt'])."</textarea>");
                    print("</td>\n</tr>\n");
					tr("Приватность","Показывать запись - <input type=\"radio\" name=\"privat\" value=\"yes\" " . ($privat ? " checked" : "") . ">Только друзьям <input type=\"radio\" name=\"privat\" value=\"no\" " . (!$privat ? " checked" : "") . ">Всем", 1);
					tr("Комментировани","Разрешить комментарии - <input type=\"radio\" name=\"comment\" value=\"no\" " . (!$comment ? " checked" : "") . ">Нет <input type=\"radio\" name=\"comment\" value=\"yes\" " . ($comment ? " checked" : "") . ">Да", 1);
					tr("<b>Метки</b> ","<input type=\"hidden\" name=\"oldtags\" value=\"" . htmlspecialchars($tags) . "\"><input type=\"text\" name=\"tags\" size=\"76\" value=\"" . $tags . "\">",1);
                    print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Изменить\"> <input type=\"button\" onclick=\"window.location.href ='javascript:history.go(-1);'\" value=\"Назад\"/></td></tr>\n");
                    print("</table></form>\n");
                    end_frame();
					end_main_frame();
					stdfoot();
}
} elseif ($action == "add"){
loggedinorreturn(); 
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
			if (isset($_POST["add"]))
            {
						////// МОД ТЭГОВ [by merdox] //////

$replace = array(", ", " , ", " ,");
$tags = trim(str_replace($replace, ",", mb_convert_case(unesc($_POST["tags"]), MB_CASE_LOWER, $mysql_charset)));
$oldtags = "";
  if (isset($_POST["oldtags"])) {
      $oldtags = unesc($_POST["oldtags"]);
  }
$un = array_diff(explode(",", $tags), explode(",", $oldtags));

$ret = array();
$res = sql_query("SELECT name FROM blogtags");
while ($row = mysqli_fetch_array($res))
	$ret[] = $row["name"];

$union = array_intersect($ret, $un);
$ununion = array_diff($un, $ret);

foreach ($union as $tag) {
		@sql_query('UPDATE blogtags SET howmuch=howmuch+1 WHERE name LIKE CONCAT(\'%\', '.sqlesc($tag).', \'%\')') or sqlerr(__FILE__, __LINE__);
	}



foreach ($ununion as $tag) {
		@sql_query("INSERT INTO blogtags (name, howmuch) VALUES (".sqlesc($tag).", 1)") or sqlerr(__FILE__, __LINE__);
	}

////// МОД ТЭГОВ [by merdox] //////
sql_query("INSERT INTO blogs (uid, privat, comment, tags, p_added, subject, txt, comments, views, up, down, editedat) VALUES (...)");
}
            else
            {
                stderr("Ошибка", "Нет доступа ! <a href=\"javascript:history.go(-1);\">Назад</a>");
                die();
            }
			} else {

stdhead("Добавить запись в блог ");
?>
	<script type="text/javascript" src="markitup/jquery.markitup.pack.js"></script>
<script type="text/javascript" src="markitup/sets/bbcode/set.js"></script>
<link rel="stylesheet" type="text/css" href="markitup/skins/simple/style.css" />
<link rel="stylesheet" type="text/css" href="markitup/sets/bbcode/style.css" />
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('#txt').markItUp(mySettings);			
});
</script>
<?
begin_main_frame();
 $tags = "";
begin_frame("Добавить запись в блог", true);
                    print("<form name=\"addform\" id=\"addform\" method=\"post\" action=\"blog.php?action=add\" enctype=\"multipart/form-data\">\n");
                    print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
                    tr("Заголовок ", "<input type=\"text\" name=\"name\" size=\"76\">", 1);
                    print("<tr align=\"right\" valign=\"top\">\n<td><b>Сообщение</b></td>\n<td align=\"left\"><br /> Для того что бы поделить пост используйте тэг <b>[more]</b> .<br /> Все что будет написано после этого тэга , не будет выводиться на главной блога . ");
                    print("<input type=\"hidden\" name=\"add\">");
                    print("<textarea name=txt id=txt  cols=86 rows=18></textarea>");
                    print("</td>\n</tr>\n");
					tr("Приватность","Показывать запись - <input type=\"radio\" name=\"privat\" value=\"yes\">Только друзьям <input type=\"radio\" name=\"privat\" value=\"no\" checked>Всем", 1);
					tr("Комментировани","Разрешить комментарии - <input type=\"radio\" name=\"comment\" value=\"no\">Нет <input type=\"radio\" name=\"comment\" value=\"yes\" checked>Да", 1);
					tr("<b>Метки</b> ","<input type=\"text\" name=\"tags\" size=\"76\" value=\"" . $tags . "\"><br />Метки пишуться через запятую !",1);
                    print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Добавить\"> <input type=\"button\" onclick=\"window.location.href ='javascript:history.go(-1);'\" value=\"Назад\"/></td></tr>\n");
                    print("</table></form>\n");
                    end_frame();
					end_main_frame();
					stdfoot();


}
} elseif ($action == "delete"){
loggedinorreturn(); 
	$sql = sql_query("SELECT * FROM blogs WHERE bid = ".sqlesc($bid));
	$row = mysqli_fetch_assoc($sql);
	if ($CURUSER["id"] == $row["uid"] || get_user_class() >= UC_MODERATOR)
                        $owner = 1;
                else
                        $owner = 0;
	if (empty($bid) || mysqli_num_rows($sql) < 1 || $owner == 0)
	bark("Неверный идентификатор ! <a href=\"javascript:history.go(-1);\">Назад</a>");
 
    $sure = (int)$_GET["sure"];

    if (!$sure)
    {
      stderr("Удалить Тему", "Вы уверены что хотите удалить Тему ?\n" .
      "Нажмите <a href=blog.php?bid=".$bid."&amp;action=delete&amp;sure=1>да</a> если уверены .",false);
	}
	
    sql_query("DELETE FROM blogs WHERE bid=".sqlesc($bid)) or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM blog_comments WHERE bid=".sqlesc($bid)) or sqlerr(__FILE__, __LINE__);
    header("Location: $BASEURL/blogs.php");

    die;
} else
                stderr("Ошибка", "Нет доступа ! <a href=\"javascript:history.go(-1);\">Назад</a>");
                die();
?>