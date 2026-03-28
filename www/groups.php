<?php
include('include/bittorrent.php');
dbconn();
stdhead('Группы ');
begin_main_frame();
?>
<style>
#posts {padding: 10px 10px 10px 10px;} 
#post-list {padding: 0px 10px 10px 10px;}
#post-list .date {color: #92918D; margin-bottom: 2px;}
#post-list .date a{color: #92918D;}
#post-list .date a:hover{text-decoration: none;}
#post-list .title a {color: #BC5349; font-size: 20px; padding-bottom: 7px;}
#post-list .text {line-height: 17px;padding: 10px 0;}
#post-list .post-bottom {color: #202020; font-style: italic;}
#post-list .greyb {color: #92918D;}
#post-list .greyb:hover {color: #d82a0e;}
#post-list .cat-btm {color: #92918D; padding-right: 10px;}
#post-list .lnk-btm {margin-left: 20px; color: #6aa100;}
#posts-content .space {margin-top: 40px;}

</style>
<?
$res = sql_query("SELECT COUNT(*) FROM groups")or die(mysql_error());  
$arr = mysql_fetch_array($res);
$count = $arr[0];
$perpage = 7; 

    if ($addparam != "") {
 if ($pagerlink != "") {
  if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
    $addparam = $addparam . "&" . $pagerlink;
  } else {
    $addparam = $addparam . $pagerlink;
  }
 }
    } else {
 $addparam = $pagerlink;
    }
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "groups.php?". $addparam); 

echo $pagertop;

$res = sql_query("SELECT gr.* , u.id AS uid , u.username, u.class FROM groups AS gr LEFT JOIN users AS u ON gr.owner = u.id ORDER BY rating DESC $limit") or sqlerr(__FILE__,__LINE__);
while ($arr = mysql_fetch_array($res)){
	$grid = (int)$arr["id"];
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
	$full_url = "<a href=$DEFAULTBASEURL/groups_id.php?gid=". $grid .">".$grname."</a>";
	
	
		$h = "<table id=\"posts\" align=\"center\" width=\"100%\"><tbody><tr>
		<td valign=\"top\" border=\"0px\">
		<div id=\"post-list\" style='float:left;width:100%;'><div class=\"title\" >". $full_url . "</div>
		
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
		</td>
	  </tr>
	</tbody></table>";

	echo $h;	
}

echo $pagerbottom;

end_main_frame();
stdfoot();