<?php
require_once("include/bittorrent.php");

gzip();
dbconn(false);

$CURUSER = $CURUSER ?? null;
$lang = $lang ?? null;
$guest_download = $guest_download ?? null;
$mcache = $mcache ?? null;
$DEFAULTBASEURL = $DEFAULTBASEURL ?? null;
$DEFAULTBASEURL = $DEFAULTBASEURL ?? null;
global $CURUSER, $lang, $guest_download, $mcache, $DEFAULTBASEURL;
if (!isset($mcache) || !($mcache instanceof Memcached)) {
    $mcache = new Memcached();
    $mcache->addServer('localhost', 11211);
}
$p = $unp = $p1 = $tags = $keepget = $img1 = '';
$id = (int)$_GET["id"];
$mem_get_d = $mcache->get_value('torrent_'.$id);
if ($mem_get_d === false) {
     $res = sql_query("SELECT torrents.*, ".time()." - torrents.last_action AS lastseed FROM torrents WHERE torrents.id = $id") or sqlerr(__FILE__, __LINE__);
     $row = mysqli_fetch_array($res);
     $date = $row;
    $mcache->cache_value('torrent_'.$id, $date, 0); } else  $row = $mem_get_d;

sql_query("UPDATE LOW_PRIORITY torrents SET views = views + 1 WHERE id = $id");
$owned = 0;
$moderator = 0;
$currentUserId = isset($CURUSER['id']) ? (int)$CURUSER['id'] : 0;

if ($currentUserId > 0 && get_user_class() >= UC_MODERATOR) {
    $owned = 1;
    $moderator = 1;
} elseif ($currentUserId > 0 && isset($row['owner']) && $currentUserId === (int)$row['owner']) {
    $owned = 1;
}

if (!empty($row) && isset($row["modded"]) && $row["modded"] === "no" && !$owned)
stderr("Ошибка", "Раздача ожидает проверки");
  function hex_esc($matches) { return sprintf("%02x", ord($matches[0])); }
		
if (!$row || (isset($row["banned"]) && $row["banned"] === "yes" && !$moderator)) {
header("HTTP/1.0 404 Not Found"); 
stderr($lang['error'], $lang['no_torrent_with_such_id']); 
}
else {
               if (isset($_GET["tocomm"]) && $_GET["tocomm"]) {
    header("Location: $DEFAULTBASEURL/details/id$id-".friendly_title($row['name'])."&amp;page=0");
    exit();
}

       
                stdhead("Скачать аниме " . $row["name"] . " торрент  бесплатно ",'all');
				  echo "<script type=\"text/javascript\" src=\"".$DEFAULTBASEURL."/js/comments.js\"></script>\n";
				print("<link rel=\"stylesheet\" href=\"".$DEFAULTBASEURL."/css/torrent.css\" type=\"text/css\">\n");
				print("<script language=\"JavaScript\" src=\"".$DEFAULTBASEURL."/js/torrent.js\" type=\"text/javascript\"></script>\n");
?> 
<style type="text/css">
/*<![CDATA[*/

/* Плавающая панель */
#socializ {
    background: #fff;
    border: 1.5px solid #E5E5E5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    border-radius: 6px;
    padding: 8px 12px 0 12px;
    margin: 0 auto;
    display: inline-block;
}
#socializ a {
background:#F6F6F6 no-repeat scroll 0 0;
display:block;
float:left;
height:32px;
margin:0 3px 6px;
width:32px;
}
#socializ img {
border : 0px;
}
/* конец Плавающая панель */

/*]]>*/
</style>

<script language="javascript" type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/ajax.js"></script>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  {lang: 'ru'}
</script>
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/socializ.js"></script>

<?

               /* if ($CURUSER["id"] == $row["owner"] || $CURUSER["modcateg"] == $row["category"] || get_user_class() >= UC_MODERATOR)
                        $owned = 1;
                else
                        $owned = 0;
*/
                $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

                $prive = "";
                $s=$row["name"];
			
if ($CURUSER){

    $can_not_thanks = false;
    $thanksby = '';
				begin_main_frame();
				
				$do = " - [<a href=".$DEFAULTBASEURL."/bookmark.php?torrent=$id><span style='color:#63236e;'>Добавить в Закладки</span></a>] 
				[<a href=".$DEFAULTBASEURL."/sub.php?torid=$id&amp;act=add><span style='color:#236a39;'>Подписаться</span></a>]";
                begin_frame($lang['torrent_details'] . $do);
                $p .= ( get_user_class() >= UC_MODERATOR ? "<span id=\"moderated\"><b>Проверен? </b> - ".($row["modded"] == "no" ? "<a onclick=\"javascript: check(".$row["id"].")\">Нет</a>" : "<a href=\"".$DEFAULTBASEURL."/user/id".$row["modby"]."\">".$row["modname"]."</a>")."</span>" : "")."";
				   if (get_user_class() >= UC_MODERATOR)
				$unp .= ($row["modded"] == "yes" ? "<span id=\"unmoderated\">[<a onclick=\"javascript: uncheck(".$row["id"].")\">Отправить на проверку !</a>]</span>":"");
				if	($row["checkcomm"] == "yes")
                $p1 .= "<b>Оповещать о комментариях ? </b> - <span id=\"communcheck\"><a onclick=\"javascript: comm_uncheck(".$row["id"].")\">Нет</a></span>";
				else
                $p1 .= "<b>Оповещать о комментариях ? </b> - <span id=\"commcheck\"><a onclick=\"javascript: comm_check(".$row["id"].")\">Да !</a></span>";

				
				
			 $url = "edit.php?id=" . $row["id"];
                if (isset($_GET["returnto"])) {
                        $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
                        $url .= $addthis;
                        $keepget .= $addthis;
                }
				$userTimezone = isset($CURUSER['timezone']) ? (int)$CURUSER['timezone'] : 0;
				$userDst = isset($CURUSER['dst']) ? (int)$CURUSER['dst'] : 0;
				$datum = gmdate('Y-m-d H:i', $row['added'] + ($userTimezone + $userDst) * 60);
				$editlink = "<p class=\"download\"><a href=\"".$url."\" class=\"sublink\">";
				$info = "<br /><b>".$lang['info_hash']."</b> - ". $row['info_hash']."
				<br /><b>".$lang['size']."</b> - ". mksize($row["size"]) . " (" . number_format($row["size"]) . " ".$lang['bytes'].")
				<br /><b>".$lang['added']."</b> - ".$datum." <br> <b>Категория</b> - <a href='".$DEFAULTBASEURL."/browse/cat{$row["category"]}' title='".cat_name($row["category"],true)."'>".cat_name($row["category"],true)."</a><br />".$p." ".$unp."";
                $s = "<p class=\"download\"> -> <a class=\"h3\" href=\"download.php?id=$id&amp;name=" . rawurlencode($row["filename"]) . "\">" . $row["name"] . "</a> <- </p>";
				if ($owned)
                $s .= " $spacer ".$editlink."[".$lang['edit']."]</a></p>";
				$s .= " ".$info."<br />";
				if ($owned)
				$s .= $p1;

				print ("<a href=\"#moded\" ></a>
						<div class=\"layout\">
			<div class=\"mini donwloadtor\">
        <fieldset style=\"border-color:#C2EFC2;\"><legend><b>Download/Скачать</b></legend>
				
				<div style=float:left;>$s</div>
				</fieldset>
			</div>
		</div>");

              ?>
			  <div id="loading-layer" style="display:none;font-family: Verdana;font-size: 11px;width:200px;height:50px;background:#FFF;padding:10px;text-align:center;border:1px solid #000">
     <div style="font-weight:bold" id="loading-layer-text">Загрузка. Пожалуйста, подождите...</div><br />
     <img src="<?=$DEFAULTBASEURL?>/pic/loading.gif" border="0" />
</div>
			  <?
				
			end_frame();
			end_main_frame(); 
			} else {
			begin_main_frame();
            begin_frame($lang['torrent_details']);
				

				$info = "<br /><b>".$lang['info_hash']."</b> - ". $row['info_hash']."
				<br /><b>".$lang['size']."</b> - ". mksize($row["size"]) . " (" . number_format($row["size"]) . " ".$lang['bytes'].")
				<br /><b>".$lang['added']."</b> - ". gmdate('Y-m-d H:i',$row['added'] )." <br> <b>Категория</b> - <a href='".$DEFAULTBASEURL."/browse/cat".$row["category"]."' title='".cat_name($row["category"],true)."'>".cat_name($row["category"],true)."</a><br />".$p."";
                $s = "<b>" . $row["name"] . "</b>";
				$s .= " ".$info."<br />";
				$s .= "<h2>Что бы скачать этот торрент , Вам необходимо <a href=\"".$DEFAULTBASEURL."/signup.php\"><b><u>ЗАРЕГИСТРИРОВАТЬСЯ</u></b></a> на сайте !</h2>";
				print ("<div style=\"float:left;\">
				<div style=float:left;>$s</div>");
			end_frame();
			end_main_frame(); 
			}
			begin_main_frame();		
            begin_frame(htmlspecialchars($row["name"]));


		if (false === ($descr = $mcache->get_value('torrent_desc'.$id)))
		{
		$descr = format_comment($row["descr"]);
		$mcache->cache_value('torrent_desc'.$id, $descr, rand(1000 , 3000 )); 
		} 
		/*
		if (empty($row['descr_parsed']) || md5($row['descr']) != $row['descr_hash']) {
		$descr_parsed = format_comment($row['descr']);
		$descr_hash = md5($row['descr']);
		sql_query('REPLACE INTO torrents_parsed (torrent, descr_parsed, hash) VALUES ('.implode(', ', array_map('sqlesc', array($row['id'], $descr_parsed, $descr_hash))).')') or sqlerr(__FILE__,__LINE__);
				} else
		$descr_parsed = $row['descr_parsed'];

      */

		 echo "<table width=\"100%\" class=\"brd\" cellspacing=\"0\" cellpadding=\"5\">";  
		if ($row["image1"] != "") 
		$img1 = "<img style='border:0;' src='".$DEFAULTBASEURL."/timthumb.php?src=".$row["image1"]."&w=230&zc=1&q=90' width='230px' alt='".$row['name']."'/>"; 
		
        echo "<tr valign=\"top\">";
        echo "<td class=\"brd\"><div>" . $descr . "</div><br><hr></td>";
        echo "<td class=\"brd\" align=\"center\" width=\"230px\">";
        // Постер
        if ($row["image1"] != "") 
            echo "<img style='border:0;' src='".$DEFAULTBASEURL."/timthumb.php?src=".$row["image1"]."&w=230&zc=1&q=90' width='230px' alt='".htmlspecialchars($row['name'])."'/>"; 
        
        // Блок рейтинга строго по ширине постера (230px), без лишних паддингов, в розовой рамке
        include("include/rating_functions.php");
        include("include/secrets.php");
        echo "<div align='center' style='width:230px; margin:10px auto 0 auto; border:2px solid #e754a5; border-radius:7px; box-sizing:border-box;'>";
        if ($currentUserId > 0) {
            echo pullRating($row["id"], true, false, true, null);
        } else {
            echo '<div style="padding:10px; text-align:center;">Оценка доступна после авторизации</div>';
        }
        echo "</div>";
        echo "<div align='center' class=\"skidka\">";
		
		
        // socializ удалён из-под постера
        echo '</noindex></div></td></tr>';
        echo '<tr><td colspan="2" style="padding:25px 0 0 0; text-align:center; background:transparent;">';
        echo '<div id="socializ" style="display:inline-block; margin:0 auto;">';
        echo '<script type="text/javascript">socializ(encodeURIComponent("' . $DEFAULTBASEURL . '/details/id' . $row['id'] . '"),encodeURIComponent("Скачать ' . $row['name'] . '"))</script>';
        echo '</div>';
        echo '</td></tr>';
		// Социальные кнопки — без рамки, в одну строку

        echo "</table>";  
			end_frame();    
			end_main_frame();
			

			
			begin_main_frame();
			begin_frame("Информация");
print("<div id=\"tabs\">\n");
print("<span class=\"tab active\" id=\"stats\">Данные</span>\n");
print("<span class=\"tab\" id=\"screens\">Скриншоты</span>\n");

print("<span class=\"tab\" id=\"peers\">Раздающие</span>\n");
print("<span class=\"tab\" id=\"downloaded\">Скачавшие</span>\n");

print("<span class=\"tab\" id=\"files\">Список Файлов</span>\n");
print("<span id=\"loading\"></span>\n");
print("<div id=\"body\" torrent=\"$id\">\n");

                $towner = (isset($row["owner_name"]) ? ("<a href=".$DEFAULTBASEURL."/user/id" . $row["owner"] . ">" . get_user_class_color($row["owner_class"], htmlspecialchars_uni($row["owner_name"])) . "</a>") : "<i>Аноним</i>");
      
        print("<table width=\"100%\" class=\"tt\" cellpadding=\"5\">\n");
        print("<tr>\n");
        print("<td class=tt><b>Раздал</b></td>\n");
        print("<td class=tt><b>Просмотров</b></td>\n");
        print("<td class=tt><b>Взят</b></td>\n");
        print("<td class=tt><b>Скачен</b></td>\n");
        print("<td class=tt><b>Активность</b></td>\n");
		print("</tr>\n");
        print("<tr>\n");
        print("<td> $towner</td>\n");
		print("<td>".$row['views']."</td>\n");
		print("<td>".$row['hits']."</td>\n");
		print("<td>".$row['times_completed']."</td>\n");
		print("<td>".mkprettytime($row["lastseed"])." назад</td>");
        print("</tr>\n");
        print("</table>\n");
		print("<br />");
		#### Tag Output ####
						foreach(explode(",", $row["tags"]) as $tag)
                $tags .= "<a style=\"font-weight:normal;\" href=\"".$DEFAULTBASEURL."/browse.php?tag=".$tag."\">".$tag."</a>, ";
				if ($tags)
                $tags = substr($tags, 0, -2);
		print("<table width=\"100%\" class=\"tt\" cellpadding=\"5\">\n");
        print("<tr>\n");
		print("<td class=tt><b>Тэги</b></td>\n");
		print("</tr>\n");
		print("<tr>\n");
		print("<td>".$tags."</td>");
        print("</tr>\n");
        print("</table>\n");
		

  
print("</div>\n");
print("</div>\n");

$torrentid = $id;
$user = $currentUserId;

if ($CURUSER){

list($tnx_count) = @mysqli_fetch_row(sql_query("SELECT COUNT(*) FROM thanks WHERE torrentid = $torrentid AND userid = {$currentUserId} LIMIT 1")) or sqlerr(__FILE__,__LINE__);

if ((int)$row['owner'] === $currentUserId || $tnx_count != 0)
     $can_not_thanks = true;
	 
// Вывод кнопки голосования и ссылки на удаление голоса	 	 
$thanksby .= "<div><input type=\"button\" name=\"send_thanks\" id=\"send_thanks\" value=\"Сказать спасибо\" ".($can_not_thanks == true ? " disabled=\"disabled\"" : "")." onClick=\"SE_SayThanks('{$torrentid}')\" />&nbsp;<span id=\"thanks_msg\">".($tnx_count ? "&nbsp;&nbsp;<img src=\"/pic/x-close-2.png\" style=\"position:relative;top:2px;\" />&nbsp;<a href=\"javascript:;\" onClick=\"SE_RemoveThanks('{$torrentid}')\" title=\"Удалить свою благодарность\" alt=\"Удалить свою благодарность\" class=\"ajax-link\">Удалить свою благодарность</a>" : "")."</span></div>\n";
// Вывод списка проголосовавших
$thanksby .= "<div style=\"margin-top:5px;\" id=\"thanks_body\"><a href=\"javascript:;\" class=\"show_thanks\" onClick=\"SE_ShowThanks('{$torrentid}')\">Показать список поблагодаривших </a></div>\n";
?>
<script type="text/javascript"> 
function SE_SayThanks(id) {
	id = parseInt(id);
	jQuery('#send_thanks').get(0).disabled = 'disalbled';
	jQuery.post('/thanks.php',{'do':'send_thanks',tid:id},
		   function(response) {
			   jQuery('#thanks_msg').empty();
			   jQuery('#thanks_msg').html( '&nbsp;&nbsp;<img src=\"<?=$DEFAULTBASEURL?>/pic/x-close-2.png\" style=\"position:relative;top:2px;\" />&nbsp;<a href="javascript:;" onClick="SE_RemoveThanks(\''+id+'\')" class="ajax-link">Удалить свою благодарность</a>' );
			   SE_ShowThanks(''+id+'');
		   },'html');
}

function SE_RemoveThanks(id) {
	id = parseInt(id);
	jQuery.post('/thanks.php',{'do':'remove_thanks',tid:id},
		   function(response) {
			   jQuery('#send_thanks').get(0).disabled = '';
			   jQuery('#thanks_msg').empty();
			   SE_ShowThanks(''+id+'');
		   },'html');	
}

function SE_ShowThanks(id) {
	id = parseInt(id);
	jQuery('#thanks_body').empty();
	jQuery('#thanks_body').html( 'Загрузка...' );
	jQuery.post('/thanks.php',{'do':'show_thanks',tid:id},
		   function(response) {
			   jQuery('#thanks_body').empty();
			   jQuery('#thanks_body').html(response);
		   },'html');	
}

function SE_HideThanks(id) {
	jQuery('#thanks_body').empty();
	jQuery('#thanks_body').html( '<div style="margin-top:5px;" id="thanks_body"><a href="javascript:;" class="show_thanks" onClick="SE_ShowThanks(\''+id+'\')">Показать список поблагодаривших </a></div>' );
}
</script>
<?
       print("<h2>".$lang['said_thanks']."</h2><br>$thanksby");

	   }
end_frame();
			end_main_frame();
			
				

// Подключаем JS комментариев
echo "<script type=\"text/javascript\" src=\"{$DEFAULTBASEURL}/js/comments.js\"></script>\n";

begin_main_frame();

if (false === ($count = $mcache->get_value('comment_count' . $id))) {
    $res1 = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = $id");
    if (!$res1) {
        error_log("SQL ERROR: " . mysqli_error($GLOBALS["mysqli"]) . " | FILE: " . __FILE__ . " | LINE: " . __LINE__);
    }
    $row1 = mysqli_fetch_array($res1);
    $count = $row1[0];
    $mcache->cache_value('comment_count' . $id, $count, rand(1000, 3000));
}

$limited = 7;
echo "<div id=\"comments_list\">\n";

if (!$count) {
    // Нет комментариев
    print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\" border=\"0\">");
    print("<tr><td class=colhead align=\"left\" colspan=\"2\"><div style=\"float:left\"> :: Список комментариев</div></td></tr>");
    print("<tr><td align=\"center\">Комментариев нет.</td></tr>");
    print("</table><br>");
} else {
    list($pagertop, $pagerbottom, $limit_sql) = pager($limited, $count, "details/id{$id}-" . friendly_title($row['name']) . "?", array('lastpagedefault' => 1));

  $sql = "SELECT c.id, c.ip, c.text, c.torrent AS torrentid, c.user, c.added, c.editedby, c.editedat,
                   u.avatar, u.warned, u.username, u.title, u.class, u.donor, u.downloaded, u.uploaded,
                   u.gender, u.last_access, e.username AS editedbyname
            FROM comments AS c
            LEFT JOIN users AS u ON c.user = u.id
            LEFT JOIN users AS e ON c.editedby = e.id
            WHERE c.torrent = $id
            ORDER BY c.id $limit_sql";

    $subres = sql_query($sql);
    if (!$subres) {
        error_log("SQL ERROR (comments list): " . mysqli_error($GLOBALS["mysqli"]) . " | FILE: " . __FILE__ . " | LINE: " . __LINE__);
    }

    $allrows = [];
    while ($subrow = mysqli_fetch_array($subres)) {
        // Расчёт рейтинга пользователя с защитой от деления на ноль и неинициализированных значений
        $uploaded = isset($subrow["uploaded"]) ? (float)$subrow["uploaded"] : 0;
        $downloaded = isset($subrow["downloaded"]) ? (float)$subrow["downloaded"] : 0;
        if ($downloaded > 0) {
            $ratio = number_format($uploaded / $downloaded, 2);
        } elseif ($uploaded > 0) {
            $ratio = "Inf.";
        } else {
            $ratio = "---";
        }
        // Можно добавить $ratio в $subrow, если нужно отображать
        $subrow['ratio'] = $ratio;
        $allrows[] = $subrow;
    }

    print("<table class='main' cellspacing='0' cellpadding='5' width='100%' border='0'>");
    print("<tr><td class='colhead' align='center'><div style='float:left'> :: Список комментариев</div></td></tr>");
    print("<tr><td>{$pagertop}</td></tr>");
    print("<tr><td>");
    commenttable_ajax($allrows);
    print("</td></tr>");
    print("<tr><td>{$pagerbottom}</td></tr>");
    print("</table>");
}

// Форма добавления комментария
if ($CURUSER) {
    echo "<table style='margin-top: 2px;' cellpadding='5' width='100%'>";
    echo "<tr><td class='colhead' align='left' colspan='2'><b>:: Добавить комментарий</b></td></tr>";
    echo "<tr><td align='center'>";
    echo "<form name='comment' id='comment'><center>";
    echo textbbcode("comment", "text", "", 1);
    echo "</center></form>";
    echo "</td></tr><tr><td align='center' colspan='2'>";
    echo "<input type='button' class='btn' value='Разместить комментарий' onClick=\"SE_SendComment('{$id}')\" id='send_comment' />";
    echo " <input type='button' class='btn' value='Смайлы' onClick='javascript:winop()' />";
    echo " <input type='button' class='btn' value='Смайлы2' onClick='javascript:winop2()' />";
    echo "</td></tr></table>";
}

echo "</div>\n";

end_main_frame();
stdfoot();
        }


// --------- Расчёт ratio для текущего торрента ---------
// Расчёт рейтинга с защитой от деления на ноль и неинициализированных значений
$uploaded = isset($row["uploaded"]) ? (float)$row["uploaded"] : 0;
$downloaded = isset($row["downloaded"]) ? (float)$row["downloaded"] : 0;
if ($downloaded > 0) {
    $ratio = number_format($uploaded / $downloaded, 2);
} elseif ($uploaded > 0) {
    $ratio = "Inf.";
} else {
    $ratio = "---";
}

// Аналогично для расчётов по торренту (пример: sn_up/sn_dn)
$uploaded2 = isset($row["sn_up"]) ? (float)$row["sn_up"] : 0;
$downloaded2 = isset($row["sn_dn"]) ? (float)$row["sn_dn"] : 0;
if ($downloaded2 > 0) {
    $ratio2 = number_format($uploaded2 / $downloaded2, 2);
    $ratio2 = "<font color=" . get_ratio_color($ratio2) . ">$ratio2</font>";
} elseif ($uploaded2 > 0) {
    $ratio2 = "Inf.";
} else {
    $ratio2 = "---";
}
?>