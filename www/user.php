<?php
// Prevent browser caching for AJAX-loaded tab content
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}
ob_start();
require_once("include/bittorrent.php");
// Ensure private message inbox constant is defined
if (!defined('PM_INBOX')) {
    define('PM_INBOX', 0);
}
dbconn();
global $lang;
header ("Content-Type: text/html; charset=" . $lang['language_charset']);

if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] == 'POST')
{
    $id = (int)$_POST["user"];
    $act = (string)$_POST["act"];

    if (!is_valid_id($id) || empty($act))
    	die("Ошибка");

    print("<link rel=\"stylesheet\" href=\"css/user.css\" type=\"text/css\">\n");

    function maketable($res)
    {
        global $lang;
        $ret = "<table class=\"tt\">\n
            <tr><td class=\"tt\" style=\"padding:0px;margin:0px;width:50px;\" align=\"center\"><img src=\"pic/genre.gif\" title=\"Категория\" alt=\"\" /></td><td class=\"tt\"><img src=\"pic/release.gif\" title=\"Название\" alt=\"\" /></td><td class=\"tt\" align=\"center\"><img src=\"pic/mb.gif\" title=\"Размер\" alt=\"\" /></td><td class=\"tt\" width=\"30\" align=\"center\"><img src=\"pic/seeders.gif\" title=\"Раздают\" alt=\"\" /></td><td class=\"tt\" width=\"30\" align=\"center\"><img src=\"pic/leechers.gif\" title=\"Качают\" alt=\"\" /></td><td class=\"tt\" align=\"center\"><img src=\"pic/uploaded.gif\" title=\"Раздал\" alt=\"\" /></td>\n
            <td class=\"tt\" align=\"center\"><img src=\"pic/downloaded.gif\" title=\"Скачал\" alt=\"\" /></td><td class=\"tt\" align=\"center\"><img src=\"pic/ratio.gif\" title=\"Рейтинг\" alt=\"\" /></td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res))
        {
            if ($arr["downloaded"] > 0)
            {
                $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
                $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
            }
            else
            {
                if ($arr["uploaded"] > 0)
                    $ratio = "Inf.";
                else
                    $ratio = "---";
            }
            $catid = $arr["catid"];
        	$catimage = htmlspecialchars($arr["image"]);
        	$catname = htmlspecialchars($arr["catname"]);
        	$size = str_replace(" ", "&nbsp;", mksize($arr["size"]));
        	$uploaded = str_replace(" ", "&nbsp;", mksize($arr["uploaded"]));
        	$downloaded = str_replace(" ", "&nbsp;", mksize($arr["downloaded"]));
        	$seeders = number_format($arr["seeders"]);
        	$leechers = number_format($arr["leechers"]);
			$datum = gmdate('Y-m-d H:i',$arr['added'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);      

            $ret .= "
                <tr>\n
                <td rowspan=\"2\" style=\"padding:0px;margin:0px;width:50px;\"><a href=\"browse.php?cat=$catid\"><img src=\"pic/cats/$catimage\" title=\"$catname\" alt=\"\" border=\"0\"/></a></td>\n
                <td colspan=\"7\"><a href=details/id$arr[torrent]><b>" . $arr["torrentname"] ."</b></a></td>\n
                </tr>\n
                <tr>\n
                <td align=\"left\"><font color=\"#808080\" size=\"1\">" . $datum . "</font></td>\n
                <td align=\"center\">$size</td>\n
                <td align=\"center\">$seeders</td>\n
                <td align=\"center\">$leechers</td>\n
                <td align=\"center\">$uploaded</td>\n
        		<td align=\"center\">$downloaded</td>\n
                <td align=\"center\">$ratio</td>\n
                </tr>\n";
        }
        $ret .= "</table>\n";
        return $ret;
    }

    $res = @sql_query("SELECT * FROM users WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_array($res) or die("Неверный идентификатор");

    print("<style>\n");
    print("table.main td {border:1px solid #cecece;margin:0;}\n");
    print("table.main a {color:#266C8A;font-family:tahoma;}\n");
    print("</style>\n");

    if ($act == "info")
    {
        if (empty($user['info']))
            print("<div class=\"tab_error\">Пользователь не сообщил эту информацию.</div>\n");
        else
            print(format_comment($user['info']));
        die();
    }
    elseif ($act == "friends")
    {
        $res = sql_query("SELECT f.friendid as id, u.username AS name, u.class, u.avatar, u.gender, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE f.userid=$id AND f.status = 'yes' ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0)
        {
            print("<div id=\"friends\">\n");
			$perrow = 3; // Количество картинок в ряду
			$i = 0;
			echo '<table width="100%" cellspacing="5" cellpadding="5"><tr>';

            while ($row = mysqli_fetch_array($res))
            {
                if (empty($row['avatar']))
                    $avatar = "./pic/default_avatar.gif";
                else
                    $avatar = $row['avatar'];
                $dt = get_date_time(gmtime() - 300);
                if ($row['last_access'] > $dt)
                    $status = "<font color=\"#008000\">Онлайн</font>";
                else
                    $status = "<font color=\"#FF0000\">Оффлайн</font>";
                if ($row["gender"] == "1")
                    $gender = "<img src=\"pic/male.gif\" alt=\"Парень\" title=\"Парень\" />";
                else
                    $gender = "<img src=\"pic/female.gif\" alt=\"Девушка\" title=\"Девушка\" />";
                print("<td valign='center' align='center' class='brd' style='width:33%;padding:5px;'><div class=\"friend\">\n");
                print("<div class=\"avatar\"><a href=\"./user/id" . $row['id'] . "\"><img src=\"$avatar\" style=\"max-width: 80px; max-height:80px;\" /></a></div>\n");
                print("<div class=\"finfo\" align='left'>\n");
                print("<p><b>Имя:</b>&nbsp;<a href=\"./user/id" . $row['id'] . "\">" . get_user_class_color($row['class'], $row['name']) . "</a></p>\n");
                print("<p><b>Пол:</b>&nbsp;$gender</p>\n");
                print("<p><b>Класс:</b>&nbsp;" . get_user_class_name($row['class']) . "</p>\n");
                print("<p><b>Статус:</b>&nbsp;$status</p>\n");
                print("</div>\n");
                print("<div class=\"actions\">\n");
                print("<p><a href=\"./message.php?action=sendmessage&receiver=" . $row['id'] . "\">Отправить ЛС</a></p>\n");
                if ($CURUSER['id'] == $id)
                    print("<p><a href=\"./friends.php?action=delete&type=friend&targetid=" . $row['id'] . "\">Убрать из друзей</a></p>\n");
                print("</div>\n");
                print("<div style=\"clear:both;\"></div>\n");
                print("</div></td>\n");
				
				$i++;
				if ($i == $perrow)
				{
				echo "</tr><tr>";
				$i = 0;
				}
            }
			echo '</tr></table>';

            print("</div>\n");
        
		}
		
        else
            print("<div class=\"tab_error\">У пользователя нет друзей.</div>\n");
        die();
    }
    elseif ($act == "downloaded")
    {
        $res = sql_query("SELECT snatched.torrent AS id, snatched.uploaded, snatched.seeder, snatched.downloaded, categories.name AS catname, categories.image AS catimage, categories.id AS catid, torrents.name, torrents.seeders, torrents.leechers FROM snatched JOIN torrents ON torrents.id = snatched.torrent JOIN categories ON torrents.category = categories.id WHERE snatched.finished='yes' AND userid = $id ORDER BY torrent") or sqlerr(__FILE__,__LINE__);
        if (mysqli_num_rows($res) > 0)
        {
            print "<table class=\"tt\">\n
            <tr>
            <td class=\"tt\" style=\"padding:0;margin:0;width:50px;\" align=\"center\"><img src=\"pic/genre.gif\" title=\"Категория\" alt=\"\" /></td>
            <td class=\"tt\"><img src=\"pic/release.gif\" title=\"Название\" alt=\"\" /></td>
            <td class=\"tt\" align=\"center\"><img src=\"pic/seeders.gif\" title=\"Раздают\" alt=\"\" /></td>
            <td class=\"tt\" align=\"center\"><img src=\"pic/leechers.gif\" title=\"Качают\" alt=\"\" /></td>
            <td class=\"tt\" align=\"center\"><img src=\"pic/uploaded.gif\" title=\"Раздал\" alt=\"\" /></td>
            <td class=\"tt\" align=\"center\"><img src=\"pic/downloaded.gif\" title=\"Скачал\" alt=\"\" /></td>
            <td class=\"tt\" align=\"center\"><img src=\"pic/ratio.gif\" title=\"Скачал\" alt=\"\" /></td>";
            while ($row = mysqli_fetch_array($res))
            {
                if ($row["downloaded"] > 0)
                {
                    $ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
                    $ratio = "<font color=\"" . get_ratio_color($ratio) . "\">$ratio</font>";
                }
                else
                {
            	    if ($row["uploaded"] > 0)
                        $ratio = "Inf.";
            	    else
            		    $ratio = "---";
                }
                $uploaded = mksize($row["uploaded"]);
                $downloaded = mksize($row["downloaded"]);
                if ($row["seeder"] == 'yes')
            	    $seeder = "<font color=\"green\">Да</font>";
                else
            	    $seeder = "<font color=\"red\">Нет</font>";
            	$cat = "<a href=\"browse.php?cat=$row[catid]\"><img src=\"pic/cats/$row[catimage]\" alt=\"$row[catname]\" border=\"0\" /></a>";
                print "<tr><td style=\"padding:0;margin:0;width:50px;\" rowspan=\"2\">$cat</td><td colspan=\"9\"><a href=\"details/id" . $row["id"] . "\"><b>" . $row["name"] . "</b></a></td></tr>" .
                  "<tr><td align=\"left\" width=400></td><td align=\"center\">$row[seeders]</td><td align=\"center\">$row[leechers]</td><td align=\"center\"><nobr>$uploaded</nobr></td>
				  <td align=\"center\"><nobr>$downloaded</nobr></td><td align=\"center\">$ratio</td>
				  \n";
            }
            print "</table>";
        }
        else
            print("<div class=\"tab_error\">Пользователь не скачивал торрентов.</div>");
        die();
    }
    elseif ($act == "uploaded")
    {
        $res = sql_query("SELECT t.id, t.name, t.seeders, t.added, t.leechers, t.category, c.name AS catname, c.image AS catimage, c.id AS catid FROM torrents AS t LEFT JOIN categories AS c ON t.category = c.id WHERE t.owner = $id ORDER BY t.name") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0)
        {
            print("<table class=\"tt\">\n" .
            "<tr><td class=\"tt\" style=\"padding:0;margin:0;width:50px;\" align=\"center\"><img src=\"pic/genre.gif\" title=\"Категория\" alt=\"\" /></td><td class=\"tt\"><img src=\"pic/release.gif\" title=\"Название\" alt=\"\" /></td><td class=\"tt\" width=\"30\" align=\"center\"><img src=\"pic/seeders.gif\" title=\"Раздают\" alt=\"\" /></td><td class=\"tt\" width=\"30\" align=\"center\"><img src=\"pic/leechers.gif\" title=\"Качают\" alt=\"\" /></td></tr>\n");
            while ($row = mysqli_fetch_assoc($res))
            {
						$datum = gmdate('Y-m-d H:i',$row['added'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);      
		        $cat = "<a href=\"browse.php?cat=$row[catid]\"><img src=\"pic/cats/$row[catimage]\" alt=\"$row[catname]\" border=\"0\" /></a>";
                print("<tr><td rowspan=\"2\" style=\"padding:0;margin:0;\">$cat</td><td colspan=\"3\"><a href=\"details/id" . $row["id"] . "\"><b>" . $row["name"] . "</b></a></td></tr>\n");
                print("<tr><td><font color=\"#808080\" size=\"1\">" . $datum . "</font></td><td align=\"center\">$row[seeders]</td><td align=\"center\">$row[leechers]</td></tr>\n");
            }
            print("</table>");
        }
        else
            print("<div class=\"tab_error\">Пользователь не загружал торрентов.</div>");
        die();
    }
    elseif ($act == "downloading")
    {
        $res = sql_query("SELECT torrent, added, uploaded, downloaded, torrents.name AS torrentname, categories.name AS catname, categories.id AS catid, size, image, category, seeders, leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id WHERE userid = $id AND seeder='no'") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0)
            print(maketable($res));
        else
            print("<div class=\"tab_error\">Пользователь ничего не качает сейчас.</div>");
        die();
    }
    elseif ($act == "uploading")
    {
        $res = sql_query("SELECT torrent, added, uploaded, downloaded, torrents.name AS torrentname, categories.name AS catname, categories.id AS catid, size, image, category, seeders, leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id WHERE userid = $id AND seeder='yes'") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0)
            print(maketable($res));
        else
            print("<div class=\"tab_error\">Пользователь ничего не раздает сейчас.</div>");
        die();
    }
        elseif ($act == "reputation")
    {
	
	echo  "<table class=\"tt\">\n
            <tr style='color:#FFFFFF;'><td class=\"tt\" width='120px' align=\"center\">Дата</td><td width='130px' class=\"tt\">Пользователь</td><td class=\"tt\" width='30px' align=\"center\">+ / -</td><td class=\"tt\" align=\"center\">Сообщение</td>\n
            </tr>\n";
        $res = sql_query("SELECT k.*, u.id,u.username,u.class FROM karma AS k LEFT JOIN users AS u ON k.fromid = u.id WHERE userid = $id ORDER by added DESC LIMIT 15") or sqlerr(__FILE__, __LINE__);
        
		while ($arr = mysqli_fetch_array($res)){
		echo  "<tr>\n
                <td align=\"center\">".gmdate('Y-m-d H:i',$arr['added'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60) ."</td>\n
                <td align=\"center\"><a href='/user/id".$arr['id']."'>".get_user_class_color($arr['class'], $arr['username']) ."</a></td>\n
                <td align=\"center\">".$arr['type']."</td>\n
                <td align=\"center\">".format_comment($arr['descr'])."</td>\n
                </tr>\n";
		}
		echo "</table>";
        die();
    }

    elseif ($act == "statistics")
    {
        $comments = get_row_count("comments", "WHERE user = $id");
        $seeder = get_row_count("peers", "WHERE userid = $id AND seeder = 'yes'");
        $leecher = get_row_count("peers", "WHERE userid = $id AND seeder = 'no'");
        $torrents = get_row_count("torrents", "WHERE owner = $id");
        $snatched = get_row_count("snatched", "WHERE userid = $id");
        $thanks = get_row_count("thanks", "WHERE userid = $id");
      //  $ratings = get_row_count("ratings", "WHERE user = $id");
      //  $bookmarks = get_row_count("bookmarks", "WHERE userid = $id");
        $friends = get_row_count("friends", "WHERE userid = $id");
      //  $invites = get_row_count("invites", "WHERE inviter = $id");
        print("<h2>Статистика</h2>\n");
        print("<table width=\"100%\" cellpadding=\"5\">\n");
        print("<tr>\n");
        print("<td><b>Комментариев:</b> $comments</td>\n");
        print("<td><b>Качает:</b> $leecher</td>\n");
        print("<td><b>Раздает:</b> $seeder</td>\n");
		print("</tr>\n");
        print("<tr>\n");
        print("<td><b>Загрузил:</b> $torrents</td>\n");
        print("<td><b>Скачал:</b> $snatched</td>\n");

        print("<td><b>Спасибо:</b> $thanks</td>\n");
      //  print("<td><b>Оценил:</b> $ratings</td>\n");
   //     print("<td><b>Закладок:</b> $bookmarks</td>\n");
    //    print("<td><b>Пригласил:</b> $bookmarks</td>\n");
     //   print("<td><b>Друзей:</b> $friends</td>\n");
        print("</tr>\n");
        print("</table>\n");
        die();
    }

    elseif ($act === 'addtofriends') {
        global $memcache_obj;
        $type = $_POST['type'] ?? '';
        if (!in_array($type, ['add','delete'], true) || empty($CURUSER['id'])) {
            die("Прямой доступ закрыт");
        }
        if ($type === 'add') {
            // Check for existing friendship or request
            $res = sql_query(
                "SELECT id, status 
                 FROM friends 
                 WHERE userid = ".sqlesc($CURUSER['id'])." 
                   AND friendid = $id"
            ) or sqlerr(__FILE__, (string)__LINE__);
            if ($row = mysqli_fetch_assoc($res)) {
                if ($row['status'] === 'yes') {
                    die("<div class=\"error\">Пользователь уже ваш друг.</div>");
                }
                if ($row['status'] === 'pending') {
                    die("<div class=\"error\">Вы уже отправили запрос. Пожалуйста, дождитесь решения пользователя.</div>");
                }
                // Remove previous denial
                sql_query("DELETE FROM friends WHERE id = ".sqlesc($row['id'])) or sqlerr(__FILE__, (string)__LINE__);
            }
            // Insert pending request
            sql_query(
                "INSERT INTO friends (userid, friendid, status) 
                 VALUES (".sqlesc($CURUSER['id']).", $id, 'pending')"
            ) or sqlerr(__FILE__, (string)__LINE__);
            $newId = mysqli_insert_id($mysqli);
            $dt = sqlesc(get_date_time());
            $msg = sqlesc(
                "Пользователь [url=user/id{$CURUSER['id']}]{$CURUSER['username']}[/url] желает добавить Вас в друзья. " .
                "[url=friends.php?act=accept&id={$newId}&user={$CURUSER['id']}]Принять[/url] " .
                "[url=friends.php?act=surrender&id={$newId}&user={$CURUSER['id']}]Отклонить[/url]"
            );
            $subj = sqlesc("Предложение дружбы.");
            // Send notification into inbox
            sql_query(
                "INSERT INTO messages 
                 (sender, receiver, subject, msg, added, saved, location, spam, unread) 
                 VALUES ("
                    . sqlesc($CURUSER['id']) . ", $id, $subj, $msg, $dt, "
                    . sqlesc('no') . ", " . PM_INBOX . ", 0, " . sqlesc('yes') .
                ")"
            ) or sqlerr(__FILE__, (string)__LINE__);
            if (isset($memcache_obj)) {
                $memcache_obj->delete('messages_' . $id);
            }
            die("<div class=\"success\">Запрос отправлен. Дождитесь ответа пользователя.</div>");
        }
        // Handle removal of friendship/request
        if ($type === 'delete') {
            sql_query(
                "DELETE FROM friends 
                 WHERE (userid = ".sqlesc($CURUSER['id'])." AND friendid = $id) 
                    OR (userid = $id AND friendid = ".sqlesc($CURUSER['id']).")"
            ) or sqlerr(__FILE__, (string)__LINE__);
            $dt = sqlesc(get_date_time());
            $msg = sqlesc(
                "Пользователь [url=user/id{$CURUSER['id']}]{$CURUSER['username']}[/url] удалил Вас из друзей."
            );
            $subj = sqlesc("Отмена дружбы.");
            sql_query(
                "INSERT INTO messages 
                 (sender, receiver, subject, msg, added, saved, location, spam, unread) 
                 VALUES ("
                    . sqlesc($CURUSER['id']) . ", $id, $subj, $msg, $dt, "
                    . sqlesc('no') . ", " . PM_INBOX . ", 0, " . sqlesc('yes') .
                ")"
            ) or sqlerr(__FILE__, (string)__LINE__);
            if (isset($memcache_obj)) {
                $memcache_obj->delete('messages_' . $id);
            }
            die("<div class=\"success\">Пользователь удален из друзей.</div>");
        }
    }
?> 
<div align="center">
<STYLE TYPE="text/css" >
.smilies {display:inline-block;width:98px;height:98px;background:#ecf3fd;border:1px solid #b8d6fb;margin:2px;-moz-border-radius: 3px;-khtml-border-radius: 3px;-webkit-border-radius: 3px;border-radius: 3px;cursor:pointer;}
.smilies:hover {background:#c2dcfc;border:1px solid #7da2ce;}
.smilies div{height:98px;}
.smilies img{max-height:90px;max-width:90px;margin-top:5px;-moz-box-shadow: 1px 1px 3px 1px #96a6b9;-khtml-box-shadow: 1px 1px 3px 1px #96a6b9;-webkit-box-shadow: 1px 1px 3px 1px #96a6b9;box-shadow: 1px 1px 3px 1px #96a6b9;}
.podarki {background:#ecf3fd;border:1px solid #b8d6fb;margin:3px;-moz-border-radius: 3px;-khtml-border-radius: 3px;-webkit-border-radius: 3px;border-radius: 3px;}
.podarki:hover {background:#c2dcfc;border:1px solid #7da2ce;}
.bro input {height:25px;width:100%}
</STYLE>
<link rel="stylesheet" type="text/css" href="<?=$DEFAULTBASEURL?>/fancybox/fancybox.css"/>
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/fancybox/fancybox.js"></script>
<script>
	jQuery(document).ready(function() {
		 	 jQuery("a.pod").fancybox({
			'overlayShow' : false,
			});
	});
		

</script>

<?
$res = sql_query ( "
SELECT 
podarok.id, 
podarok.podarokid,  
(SELECT podarki.pic FROM podarki WHERE podarki.id=podarok.podarokid) AS picp , podarok.text
FROM podarok WHERE userid = '{$id}' ORDER BY date DESC LIMIT 7" ) or sqlerr ( __FILE__, __LINE__ );
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
print ( "<div class=\"smilies\" ><a href=\"".$BASEURL.$arr['picp']."\" title=\"".$arr['text']."\" class=\"pod\"><img src=\"".$BASEURL.$arr['picp']."\" /></a></div>" );
}
?> 
</div><br/>
<table width=100%><tr><td align="left">
<a class="podarki" href="podarok.php?id=<?=$id?>" title="Подарить подарок">Сделать подарок</a>
</td><td align="right">
<a class="podarki" href="podarok.php?userid=<?=$id?>" title="Все подарки">Все подарки пользователя</a>
</td></tr></table>
<?
	
	}elseif ($act == "guests"){ 
	$res = sql_query("SELECT prof_guest.*, users.id, users.username, users.avatar, users.class FROM prof_guest LEFT JOIN users ON prof_guest.uid = users.id WHERE profid = $id ORDER BY time DESC LIMIT 10") or sqlerr(__FILE__,__LINE__);
		echo '<table width="100%" cellspacing="5" cellpadding="5"><tr>';
	$perrow = 5; // Количество картинок в ряду
	$i = 0;
	while ( $arr = mysqli_fetch_assoc( $res ) ) {
	                if (empty($arr['avatar']))
                    $avatar = "./pic/default_avatar.gif";
                else
                    $avatar = $arr['avatar'];
					
				$datum = gmdate("d.m.Y H:i:s",$arr["time"] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);

		echo "<td  class='brd' style='width:95px;padding:5px;text-align:center;'>
	<img border=0 style='max-width:80px; max-height:80px' src=\"".$avatar."\" /></a><br>
	 <a href=user/id".$arr['id'].">" .get_user_class_color($arr["class"], htmlspecialchars_uni($arr["username"]))."</a><br>
	 ".$datum ."<br>" . get_elapsed_time($arr['time'])." назад</td>";
	 
	 $i++;
    if ($i == $perrow)
      {
        echo "</tr><hr><tr>";
        $i = 0;
      }

}
	
	echo '</tr></table>';
	}
    else
        die("Прямой доступ запрещен");
ob_end_flush();