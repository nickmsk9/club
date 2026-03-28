<?php
include('include/bittorrent.php');
dbconn();

$gid = (int)$_GET['gid'];
$action = trim($_GET['action']);

$res = sql_query("SELECT * FROM groups WHERE id = ".$gid) or sqlerr(__FILE__,__LINE__);
$arr = mysql_fetch_array($res);

	$grname = htmlspecialchars($arr["name"]);
	$grpriv = ($arr["priv"] == "no" ? "<font color=\"green\"><b>Общедоступная</b></font>" : "<font color=\"red\"><b>Закрытая</b></font>");
	$grdescr = format_comment($arr["descr"]);
	$grrating = (int)$arr["rating"];
	$growner = "<a href=\"$DEFAULTBASEURL/user/id" . $arr["owner"] . "\"> " . get_user_class_color($arr["class"], $arr["username"]) ."</a>";
	$grusers = (int)$arr["users"];
	$grnews = (int)$arr["news"];
	$grcomm = (int)$arr["comm"];
	$grtopic = (int)$arr["topic"];
	$gradded = gmdate("d.m.Y",$arr["added"] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);
	$full_url = "<a href=\"$DEFAULTBASEURL/groups.php\">Группы</a> > ".$grname;

stdhead('Группы -> '.$grname);
begin_main_frame();
?>
<style>
#posts {padding: 10px 10px 10px 10px;} 
#post-list {padding: 0px 10px 10px 10px;}
#post-list .date {color: #92918D; margin-bottom: 2px;}
#post-list .date a{color: #92918D;}
#post-list .date a:hover{text-decoration: none;}
#post-list .title a {color: #BC5349; font-size: 20px; padding-bottom: 7px;}
h2 a.frame, .frame {color: #BC5349; font-size: 16px; padding-bottom: 7px;}
#post-list .text {line-height: 17px;padding: 10px 0;}
#post-list .post-bottom {color: #202020; font-style: italic;}
#post-list .greyb {color: #92918D;}
#post-list .greyb:hover {color: #d82a0e;}
#post-list .cat-btm {color: #92918D; padding-right: 10px;}
#post-list .lnk-btm {margin-left: 20px; color: #6aa100;}
#posts-content .space {margin-top: 40px;}

</style>
<?
begin_frame($full_url);
$h = "
		<div id=\"post-list\" style='float:left;width:100%;'>
		<div class=\"date\"></div>
		<div class=\"text\">".$grdescr."&nbsp;</div>
		<div class=\"post-bottom\">
		<span class=\"cat-btm\">Тип: <b>".$grpriv."</b></span> 
		<span class=\"cat-btm\">Новостей: <b>".$grnews."</b></span> 
		<span class=\"cat-btm\">Форум: <b>".$grtopic."</b></span> 
		<span class=\"cat-btm\">Комм.: <b>".$grcomm."</b></span>
		<span class=\"cat-btm\">Пользователей: <b>".$grusers."</b></span> 
		<span class=\"cat-btm\">Группу основал: <b>". $growner ."</b></span> 
		<span class=\"cat-btm\">Дата: <b>".$gradded."</b></span> 

		</div>
		</div>
		<div style='clear:both;'></div>
		";

	echo $h;	
end_frame();
end_main_frame();
stdfoot();