<?php
require "include/bittorrent.php"; 
dbconn(false); 

global $memcache_obj;

if(isset($_GET['stats']))
{
stdhead("Статистика Блогов");
begin_main_frame();
blog_menu();

$res = sql_query("SELECT COUNT(*) FROM blogs")or die(mysql_error());  
$arr = mysqli_fetch_array($res);
$count = $arr[0];
$res2 = sql_query("SELECT COUNT(*) FROM blog_comments") or die(mysql_error());
$arr2 = mysqli_fetch_array($res2);
$com_count = $arr2[0];
echo "<p>Пользователи сделали $count записей , оставили $com_count комментариев .</p>";

begin_frame("Топ по оценкам");
	begin_table(true);
?>
<tr>
<td class="colhead" align="left" width="85%">Название</td>
<td class="colhead" align="right"width="15%">Оценка</td>
</tr>
<?
$res5 = sql_query("SELECT bid,uid,subject, up,down FROM blogs ORDER BY up DESC LIMIT 5") or die(mysql_error());
    while ($arr5 = mysqli_fetch_assoc($res5))
    {
	$count = $arr5["up"]- $arr5["down"];
      echo("<tr><td class='embedded' width=\"95%\" align='left'><a href=blog.php?bid=" . $arr5["bid"] . "><b>" .
        cut_text($arr5["subject"],190) . "</b></a></td><td class=\"brd\" width=\"15%\" align='center'>" . $count .
        "</td></tr>\n");
    }
end_table();
end_frame();

begin_frame("Топ по просмотрам");
	begin_table(true);
?>
<tr>
<td class="colhead" align="left" width="85%">Название</td>
<td class="colhead" align="right"width="15%">Просмотров</td>
</tr>
<?
$res3 = sql_query("SELECT bid,uid,subject, views FROM blogs ORDER BY views DESC LIMIT 5") or die(mysql_error());
    while ($arr3 = mysqli_fetch_assoc($res3))
    {
      echo("<tr><td class='embedded' width=\"95%\" align='left'><a href=blog.php?bid=" . $arr3["bid"] . "><b>" .
        cut_text($arr3["subject"],190) . "</b></a></td><td class=\"brd\" width=\"15%\" align='center'>" . number_format($arr3["views"]) .
        "</td></tr>\n");
    }
end_table();
end_frame();

begin_frame("Топ по комментариям");
begin_table(true);
?>
<tr>
<td class="colhead" align="left" width="85%">Название</td>
<td class="colhead" align="right"width="15%">Коммент.</td>
</tr>
<?
$res4 = sql_query("SELECT bid,uid,subject, comments FROM blogs ORDER BY comments DESC LIMIT 5") or die(mysql_error());
    while ($arr4 = mysqli_fetch_assoc($res4))
    {
      echo("<tr><td class='embedded' width=\"95%\" align='left'><a href=blog.php?bid=" . $arr4["bid"] . "><b>" .
        cut_text($arr4["subject"],190) . "</b></a></td><td class=\"brd\" width=\"15%\" align='center'>" . number_format($arr4["comments"]) .
        "</td></tr>\n");
    }

end_table();
end_frame();

begin_frame("Теги блогов");
$res6 = sql_query("SELECT tags FROM blogs WHERE tags != ''") or die(mysql_error());
while ($arr6 = mysqli_fetch_array($res6)){
		$tags = blogtags($arr6[0]);
		echo $tags;

		}
end_frame();

end_main_frame();
stdfoot();

}else{
$tagstr = isset($_GET["tag"]) ? unesc($_GET["tag"]) : "";
$cleantagstr = htmlspecialchars($tagstr);
if (empty($cleantagstr))
unset($cleantagstr);


 if (isset($cleantagstr))
 {
 $wherein = 'WHERE LOWER(blogs.tags) LIKE "%'.sqlwildcardesc($tagstr).'%"';
 $addparam = "&amp;tag=". urlencode($tagstr) ."";
 }

 /*
$res1 = sql_query("SELECT COUNT(*) FROM blogs $wherein"); 
$row1 = mysql_fetch_array($res1); 
$count = $row1[0]; 
*/
//$mem_get_b = $memcache_obj->get('blogs');
//if ($mem_get_b === false) {
$wherein = ""; // по умолчанию пусто
$res1 = sql_query("SELECT COUNT(*) FROM blogs $wherein")or die(mysql_error());  
$row1 = mysqli_fetch_array($res1); 
$count = $row1[0]; 
//$memcache_obj->set('blogs'.$wherein, $count, 0, 3600); 
//} else $count = $mem_get_b;

$perpage = 7; 
$addparam = "";
$pagerlink = ""; // по умолчанию пусто
    if ($addparam != "") {
 if ($pagerlink != "") {
    if ($addparam[strlen($addparam)-1] != ";") { // & = &amp;
    $addparam = $addparam . "&" . $pagerlink;
  } else {
    $addparam = $addparam . $pagerlink;
  }
 }
    } else {
 $addparam = $pagerlink;
    }
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "blogs.php?". $addparam); 
stdhead("Блоги"); 


begin_main_frame();
?>
<link rel="stylesheet" href="<?=$DEFAULTBASEURL?>/css/voteup.css" type="text/css"> 
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/voteup.js"></script>

<style>
#posts {margin: 10px 15px 10px 15px}; 
#posts .avatar {border: 0px; width: 100px; height: 100px; padding: 1px 1px 1px 1px;}
#post-list {padding: 0px 10px 10px 10px;}
#post-list .date {color: #92918D; margin-bottom: 2px;}
#post-list .date a{color: #92918D;}
#post-list .date a:hover{text-decoration: none;}
#post-list .title {font-size: 20px; padding-bottom: 7px;}
#post-list .text {line-height: 17px;padding: 10px 0;}
#post-list .post-bottom {color: #202020; font-style: italic;}
#post-list .greyb {color: #92918D;}
#post-list .greyb:hover {color: #d82a0e;}
#post-list .cat-btm {color: #92918D; margin-right: 20px;}
#post-list .lnk-btm {margin-left: 20px; color: #6aa100;}
#posts-content .space {margin-top: 40px;}

</style>
<?php


blog_menu();

    $sql = sql_query("SELECT blogs.*,u.id, u.username,u.class,u.avatar FROM blogs LEFT JOIN users u ON blogs.uid = u.id $wherein AND blogs.privat = 'no' ORDER BY bid DESC $limit")or die(mysql_error()); 
    while($row=mysqli_fetch_assoc($sql)){ 
		
		$bid = (int)$row['bid'];
		$avatar = ($row["avatar"] == "" ? "/themes/Anime/images/default_avatar.gif":"".$row['avatar']."") ;
		$userid = (int)$row['uid'];

		$date = get_blog_time($row['p_added']);
		$username = $row['username'];
		$postname = trim($row['subject']);
		$full_url = "/blog.php?bid=$bid";
		$text = explode("[more]", $row['txt']);
		$text = format_comment($text[0]);
		
		$tags = htmlspecialchars($row['tags']);
		$tags = blogtags($tags);
		$com_count = (int)$row['comments'];
		$views_count = (int)$row['views'];
		$voteup = $row['up'];
		$votedown = $row['down'];
		$h = "<table id=\"posts\" width=\"98%\"><tbody><tr>
		<td class=\"avatar\" style=\"border: 0px; width: 100px; height: 200px; padding: 1px 10px 1px 1px;\" valign=\"top\" align=\"center\"><img src=\"".$avatar."\" border=\"0px\" style=\"max-width: 90px; max-height: 90px;\"/>
		<div class=\"box1\">
		<div class='up'>
		<a href='blogs.php#' class=\"vote\" id=\"".$bid."\" act=\"up\" name=\"up\">".$voteup."</a></div>
		<div class='down'>
		<a href='blogs.php#' class=\"vote\" id=\"".$bid."\" act=\"down\" name=\"down\">".$votedown."</a></div>
		</div></td>
		<td valign=\"top\" border=\"0px\">
		<div id=\"post-list\" style='float:left;width:98%;'><div class=\"title\" ><a href=".$full_url.">".$postname."</a></div>
		
		<div class=\"date\">Написал <a href=\"/user/id".$userid."\"><u>".$username."</u></a>, <i>".$date ."</i></div>
		<div class=\"text\">".$text."&nbsp;</div>
		<div class=\"post-bottom\">
		<span class=\"cat-btm\">Метки: ".$tags."</span><br />
		<span class=\"cat-btm\">Комментарии: <b>".$com_count."</b></span>
		<span class=\"cat-btm\">Просмотров: <b>".$views_count."</b></span> 
		
		</div>
		<div style=\"float:right;\"><a href=".$full_url."><b><u> Читать далее... --> </u></b></a></div>
		</div>
		<div style='clear:both;'></div>
		</td>
	  </tr>
	</tbody></table>";

	echo $h;		
     }

		print("<tr><td id=no_border>".$pagerbottom."</td></tr>");
end_main_frame();
stdfoot();
die();
}
?>