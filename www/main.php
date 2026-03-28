<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("include/bittorrent.php");
require_once("themes/Anime/template.php");

dbconn();
header ("Content-Type: text/html; charset=" . $lang['language_charset']);
global $lang , $CURUSER ,$maxusers ,$rootpath , $cache_check , $cache_read , $cache_write , $begin_frame, $begin_table , $end_frame, $end_table , $show_news;
getlang();
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] == 'POST')
{

    $act = (string)$_POST["act"];

    if (empty($act))
    	die("Ошибка");

    echo("<link rel=\"stylesheet\" href=\"css/main.css\" type=\"text/css\">\n");


////////////////////начало///////////////////	

if ($act == "reliases")
{
    getlang('rel');
?>
<script language="javascript" type="text/javascript" src="js/features.js"></script>
<?
global $mysqli;
echo"<table cellspacing=\"0\" cellpadding=\"5\" width=\"100%\" id=\"no_border\">";  
$res = sql_query("SELECT torrents.*, users.username FROM torrents LEFT JOIN users ON torrents.owner = users.id WHERE visible = 'yes' ORDER BY added DESC LIMIT 5") or sqlerr(__FILE__, __LINE__);  

    $content = "";
    $num_results = 0;
    while ($release = mysqli_fetch_assoc($res)) {
        $num_results++;

                $torname = $release["name"];   
        $descr=$release["descr"];  
        $uprow = (isset($release["username"]) ? ("<a href=user/id" . $release["owner"] . ">" . htmlspecialchars($release["username"]) . "</a>") : "<i>Скоро сделаем и будет видно :)</i>");  
      //  if (strlen($descr) > 1500)   
        //    $descr = substr($descr, 0, 1500)."...";  
        echo"<tr><td id=\"no_border\">";  
        echo"<table width=\"100%\" class=\"main\" id=\"no_border\" cellspacing=\"0\" cellpadding=\"5\">";  
        echo"<tr>";  
        echo"<td class=\"colhead\" colspan=\"2\" align=center><p align=left>";  
                //////////////////////////////////////////////////////////////////////////////// 
        echo"<h2>".$torname."</h2>";  
        echo"</td>";  
        echo"</tr>";  

                  if ($release["image1"] != "")  
                    $img1 = "<img style='border:0;' src='".$DEFAULTBASEURL."/timthumb.php?src=".$release["image1"]."&w=230&zc=1&q=90' width='230px' alt='".$release['name']."'/>";  
        echo"<tr valign=\"top\"><td align=\"center\" width=\"230\">";  
            echo"$img1";  
        echo"</td>";  
        echo"<td><p align=\"left\"> 
            ".format_comment($descr)." <br> 
            <br><div id=\"releases\" style=\"border: 1px dashed #ddd;  background: #f7f7f7; margin: 0 0 5px 0; padding: 3px;\">  
            <b>".$lang['uped']."</b>$uprow<br>  
            <b>".$lang['size']."</b>".mksize($release["size"])."<br>  
            <b style='color: #0a0;'>".$lang['seederz']."</b>".$release["seeders"] ."<br>
            <b style='color: #a00;'>".$lang['leecherz']."</b>".$release["leechers"]." <br>
            <b>".$lang['downloaded']."</b>".$release["times_completed"] ."раз(а) 
            </div>
            <br> 
            </p><p align=\"right\"> ";
	
		echo"[<a href=\"details/id".$release["id"]."&tocomm=1\"><b>".$lang['comment']."</b><b>".$release["comments"]."</b></a>] [<a href=\"download.php?id=".$release["id"]."&name=".$release["filename"]."\" alt=\"".$release["name"]."\" title=\"".$release["name"]."\"><b>".$lang['download']."</b></a>]";
		
		echo"[<a href=\"details/id".$release["id"]."\" alt=\"".$release["name"]."\" title=\"".$release["name"]."\"><b>".$lang['readfull']."</b></a>]</p></td>";  
        echo"</tr>";  
        echo"</table>";  
        echo"</td></tr>";  
    }   
   // echo"<tr><td>";  
   // echo$pagerbottom;  
   // echo"</td></tr>";  
echo"</table>";
die();
}

/////////////////////////////////////////////

elseif ($act == "stats") {
getlang('stats');
$cache_time = 120;
if (!cache_check("stats", $cache_time)) {
$registered = number_format(get_row_count("users", " WHERE enabled = 'yes'"));
$male = number_format(get_row_count("users"," WHERE gender = '1' AND enabled = 'yes'"));
$female = number_format(get_row_count("users"," WHERE gender = '2' AND enabled = 'yes'"));
$torrents = number_format(get_row_count("torrents"));
$seeders = get_row_count("peers", "WHERE seeder='yes'");
$leechers = get_row_count("peers", "WHERE seeder='no'");
$naty = number_format(get_row_count("peers", "WHERE connectable='yes'"));
$natn = number_format(get_row_count("peers", "WHERE connectable='no'"));
$uploaders = number_format(get_row_count("users", "WHERE class = ".UC_UPLOADER));
$vip = number_format(get_row_count("users", "WHERE class = ".UC_VIP));
$vip_p = number_format(get_row_count("users", "WHERE class = ".UC_VIP_P));
if ($leechers == 0)
  $ratio = 0;
else
  $ratio = round($seeders / $leechers * 100);
$peers = number_format($seeders + $leechers);
$seeders = number_format($seeders);
$leechers = number_format($leechers);
$res = sql_query("SELECT SUM(size) FROM torrents") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res) or die("ошибка доступа к БД ");
$result = sql_query("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul FROM users") or sqlerr(__FILE__, __LINE__); 
$row = mysqli_fetch_assoc($result); 
$stats['totaldownloaded'] = $row["totaldl"]; 
$stats['totaluploaded'] = $row["totalul"]; 
$test = mksize($stats['totaluploaded'] + $stats['totaldownloaded']);
	$stats = array(
		"registered" => $registered,
		"torrents" => $torrents,
		"uploaders" => $uploaders,
		"male" => $male,
		"female" => $female,
		"vip" => $vip,
		"vip_p" => $vip_p,
		"ratio" => $ratio,
		"peers" => $peers,
		"naty" => $naty,
		"natn" => $natn,
		"seeders" => $seeders,
		"leechers" => $leechers,
		"arr" => $arr,
		"test" => $test,
	);
	cache_write("stats", $stats);
} else {
	$stats = cache_read("stats");
	$registered = $stats["registered"];
	$torrents = $stats["torrents"];
	$male = $stats["male"];
	$female = $stats["female"];
	$uploaders = $stats["uploaders"];
	$vip = $stats["vip"];
	$vip_p = $stats["vip_p"];
	$ratio = $stats["ratio"];
	$peers = $stats["peers"];
	$naty = $stats["naty"];
	$natn = $stats["natn"];
	$seeders = $stats["seeders"];
	$leechers = $stats["leechers"];
	$arr = $stats["arr"];
	$test = $stats["test"];
}
$res2 = sql_query("SELECT id FROM users WHERE status = 'confirmed' AND UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP(added) < UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP('" . date("Ymd000000") . "')") or sqlerr(__FILE__, __LINE__); 
$arr2 = mysqli_num_rows($res2);
$reg = '<b>(+ '.$arr2.')</b>';

echo"	<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" width=\"80%\">
		<tbody>	
	<tr>	
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_users']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$registered."&nbsp;".$reg."</b></div></td>
	
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_files']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$torrents."</b></div></td>
	</tr>
	
	
		
		<tr>	
	<td class=\"rowhead\"><div align=\"right\">Парней</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$male."</b></div></td>

	<td class=\"rowhead\"><div align=\"right\">".$lang['st_peers']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$peers."</div></td>
	</tr>
	
	<tr>
	<td class=\"rowhead\"><div align=\"right\">Девушек</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$female."</b></div></td>
	
	<td class=\"rowhead\"><div align=\"right\" >".$lang['st_seeders']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b><font color=green>".$seeders."</font></b></div></td>

	</tr>

	<tr>
	<td class=\"rowhead\"><div align=\"right\"><font color=\"#fb780f\">".$lang['st_users_uploaders']."</font></div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$uploaders."</b></div></td>

	<td class=\"rowhead\"><div align=\"right\">".$lang['st_leechers']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b><font color=red>".$leechers."</font></b></div></td>
	</tr>
		<tr>
	<td class=\"rowhead\"><div align=\"right\"><span style=\"color:#9504fb\">VIP</span></div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$vip."</b> (+ ".$vip_p.")</div></td>

	<td class=\"rowhead\"><div align=\"right\">Порты Открыты / Закрыты</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b><font color=green>".$naty."</font> / <font color=red>".$natn."</font></b></div></td>
	</tr>
	
	<tr>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_total_trafic']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$test."</div></td>
	
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_total_size']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".mksize($arr['SUM(size)'])."</div></td>

	</tr>";	
	$times = $cache_time - date(gmtime() - filemtime($rootpath . "cache/stats.cache"));

if ($cache_time >= 3600*60*24 ){
$time = $times / 60 / 60 / 24;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." дней.";
} elseif ($cache_time >= 3600*60 ) {
$time = $times / 60 / 60;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." часов";
} elseif ($cache_time >= 3600 ) {
$time = $times / 60;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." минут";
} elseif ($cache_time >= 60 ) {
$time = $times;
$time1 = number_format($time, 0,',',' ');
	$time = "Статистика обновится через ".$time1." секунд.";
} 
echo"
		<tr>
			<td colspan=\"8\" height=\"10\" align=\"center\"><font class=small><b>".$time."</b></font></td>
		</tr>

"; 
echo"</tbody></table><br><br>";
//visited today
if (get_user_class() >= UC_UPLOADER){

$con = null;
global $mysqli;
$con = sql_query("SELECT userid FROM peers GROUP BY userid") or sqlerr(__FILE__, __LINE__);
$connected = mysqli_num_rows($con);
$avgload = floatval(get_server_load());
if (strtolower(substr(PHP_OS, 0, 3)) != 'win') {
    $percent = $avgload * 4.0;
} else {
    $percent = $avgload;
}
if ($percent <= 50) $pic = "loadbargreen.gif";
elseif ($percent <= 70) $pic = "loadbaryellow.gif";
else $pic = "loadbarred.gif";
    $width = (int)round($percent * 4.0);
echo"<center>
<table class=\"main\" border=\"0\" width=\"70%\"><tr><td style=\"padding: 0px; background-repeat: repeat-x\" title=\"Нагрузка: $percent%, Средняя (LA): $avgload\">"
."<img height=\"15\" width=\"$width\" src=\"pic/$pic\" alt=\"Нагрузка: $percent%, Средняя (LA): $avgload\" title=\"Нагрузка: $percent%, Средняя (LA): $avgload\">"
."</td></tr></table>"
."<b>Нагрузка: $percent%, Средняя (LA): $avgload<br /> Всего к трекеру подключено уникальных $connected пользователей.</b></center>";
}
die();
}

/////////////////////////////////////////////


  elseif ($act == "news") {
///////////////////////////////////////////////////
if (cache_check("news", 600))
    $res = cache_read("news");
else {
$res = sql_query("SELECT id ,added, subject FROM news ORDER BY id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$news_cache = array();
    while ($cache_data = mysqli_fetch_assoc($res))
        $news_cache[] = $cache_data;

    cache_write("news", $news_cache);
    $res = $news_cache;
    }
    echo"<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\" id=\"no_border\">\n";

foreach($res as $arr) 
	{	
	$newsid = $arr["id"];
	$subject = $arr["subject"];
	$added = $arr["added"];
	echo "<a class=\"news_show\" href=\"/news_view.php?newsid=".$newsid."\" title=\"".$subject."\">".$subject."</a> - <i>".$added."</i><hr>\n";
	}
	echo "</td></tr></table>\n";

/////////////////////////////////////////////////////////

die();
}

/////////////////////////////////////////////


  elseif ($act == "topten")
{            
  function _torrenttable($res)
  {
    global $frame_caption;
    begin_frame($frame_caption, true);
	begin_table();
?>
<tr>
<td class=colhead align=center>Место</td>
<td class=colhead align=left>Название</td>
<td class=colhead align=right>Раздающих</td>
<td class=colhead align=right>Качающих</td>

</tr>
<?
    $num = 0;
    while ($a = mysqli_fetch_assoc($res))
    {
      ++$num;
      if ($a["leechers"])
      {
        $r = $a["seeders"] / $a["leechers"];
        $ratio = "<font color=" . get_ratio_color($r) . ">" . number_format($r, 2) . "</font>";
      }
      else
        $ratio = "Inf.";
      echo("<tr><td class=embedded align=center>$num</td><td class=embedded align=left><a href=details/id" . $a["id"] . "><b>" .
        $a["name"] . "</b></a></td><td class=embedded align=right>" . number_format($a["seeders"]) .
        "</td><td class=embedded align=right>" . number_format($a["leechers"]) . "</td></tr>\n");
    }
	end_table();
    end_frame();
  }
		  $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT 10") or sqlerr(__FILE__, __LINE__);
		  _torrenttable($r);

die();
} elseif ($act == "last24") {

$todayactive = "";
$usersactivetoday = 0;

if (cache_check("record", 600))
$res = cache_read("record");
else {
$res = sql_query("SELECT id, gender, username, class FROM users WHERE UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP('" .date("Ymd000000"). "') AND hiden = 'no'") or sqlerr(__FILE__, __LINE__);
    $record_cache = array();
    while ($cache_data = mysqli_fetch_assoc($res))
        $record_cache[] = $cache_data;
    cache_write("record", $record_cache);
    $res = $record_cache;
    }

foreach ($res as $arr)
{
    if ($todayactive)
        $todayactive .= ", ";

        $todayactive .= "<a href=user/id" . $arr["id"] . ">".get_user_class_color($arr["class"], $arr["username"])."</a></a>";
   
    $female = $arr["gender"] == "2";
     if ($female){
$todayactive .= "<img alt=\"Девушка\" src=\"pic/ico_f.gif\">";
} 
    $male = $arr["gender"] == "1"; 
     if ($male){
$todayactive .= "<img alt=\"Парень\" src=\"pic/ico_m.gif\">"; 
}
    $usersactivetoday++;
} 
echo "<br><center>
<table class=\"main\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\" width=\"100%\"><tr><td class=\"embedded\">"
."<h3>".$usersactivetoday." человек посетили сегодня наш сайт .</h3> <hr>"
."<div align='left'>".$todayactive."</div><hr>"
."</td></tr></table></center>";

die();
} elseif ($act == "forum") {
    // Кеширование на 5 минут
    $cache_key = 'main_forum';
    if (cache_check($cache_key, 300)) {
        echo cache_read($cache_key);
        die();
    }

    $content = "";
    /// FIRST WE MAKE THE HEADER (NON-LOOPED) ///
    $content .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" width=\"100%\">
            <tbody>	<tr>".
    "<td width=\"100%\" align=\"center\"><b>Тема</b></td>".
    "<td  align=\"center\"><b>Ответов</b></td>".
    "<td  align=\"center\"><b>Просмотров</b></td>".
    "<td  align=\"center\"><b>Посл.&nbsp;Сообщение</b></td>".
    "</tr>";
    $for = sql_query("SELECT topics.*, (SELECT COUNT(*) FROM posts WHERE posts.topicid = topics.id) as posts_c , forums.id AS fid , forums.name AS fname , users.id AS uid , users.username AS uname , users.class AS uclass FROM forums ,topics , posts  LEFT JOIN users ON posts.userid = users.id WHERE topics.lastpost= posts.id  AND topics.forumid = forums.id ORDER BY lastpost DESC LIMIT 20");

    while ($topicarr = mysqli_fetch_assoc($for))
    {
        $topicid = $topicarr["id"];
        $topic_title = $topicarr["subject"];
        $topic_userid = $topicarr["userid"];
        $topic_views = $topicarr["views"];
        $views = number_format($topic_views);
        $replies = $topicarr['posts_c'];
        $userid = $topicarr['uid'];
        $username = $topicarr['uname'];
        $userclass = $topicarr['uclass'];

        $subject = "<a href=\"/forum/view/topic/id$topicid\"><b>" . encodehtml($topicarr["subject"]) . "</b></a>";
        $subject_forum = "<a href=\"/forum/view/forum/id{$topicarr["fid"]}\">{$topicarr["fname"]}</a>";
        $content .= "<tr>
<td style='padding-right: 5px'>$subject -> $subject_forum</td>".
"<td align=\"center\">$replies</td>".
"<td align=\"center\">$views</td>".
"<td align=\"center\">".get_user_class_color($userclass,$username)."</td>";
        $content .= "</tr>";
    }
    $content .= "</tbody></table>";
    echo $content;
    cache_write($cache_key, $content);
}

/////////////////////////////////////////////
elseif ($act == "topusers") {
    $content = "";
    global $mysqli;
    if (!cache_check('top10', 1200)) {
        $content .= "<table width=\"100%\" align=\"center\" class=\"main\" border=\"0px\" cellspacing=\"0\" cellpadding=\"2\">";
        // По заливающим
        $content .= "<td width=\"25%\" align=\"center\" valign=\"top\"><table class=main width='100%' cellspacing=0 cellpadding=5><tr><td colspan=\"2\"><center><b>Раздающие</b></center></td></tr><tr><td class=\"colhead\"><b>Имя</b></td><td class=\"colhead\"><b>Раздал</b></td></tr>";
        $torrents = mysqli_query($mysqli, "SELECT * FROM users ORDER BY uploaded DESC LIMIT 10");
        while($upload = mysqli_fetch_assoc($torrents)){
            $uid = (int)$upload['id'];
            $username = get_user_class_color($upload["class"], $upload["username"]) . get_user_icons($upload);
            $uploaded = mksize($upload["uploaded"]);
            $content .= "<tr><td align=\"left\" width=\"65%\"><a href='/user/id".$uid."'>".$username."</a></td><td align=\"center\">".$uploaded."</td></tr>";
        }
        // По благодарностям
        $content .= "</table><td width=\"25%\" align=\"center\" valign=\"top\"><table class=main width='100%' cellspacing=0 cellpadding=5><tr><td colspan=\"2\"><center><b>Благодарности</b></center></td></tr><tr><td class=\"colhead\"><b>Имя</b></td><td class=\"colhead\"><b>Спасибок</b></td></tr>";
        $thank = mysqli_query($mysqli, "SELECT u.id, u.username, u.class, u.warned, u.gender, u.enabled, u.parked, u.donor, COUNT(t.touserid) as thanks FROM users as u, thanks as t WHERE u.id=t.touserid GROUP BY u.id ORDER BY thanks DESC LIMIT 10");
        while($thank2 = mysqli_fetch_assoc($thank)){
            $useridt = (int)$thank2["id"];
            $userth = get_user_class_color($thank2["class"], $thank2["username"]) . get_user_icons($thank2);
            // Считаем количество благодарностей одним запросом
            $thanks1 = mysqli_query($mysqli, "SELECT COUNT(*) FROM thanks WHERE touserid='".$useridt."'");
            $arr = mysqli_fetch_row($thanks1);
            $thanks3 = (int)$arr[0];
            $content .= "<tr><td align=\"left\" width=\"65%\"><a href='/user/id".$useridt."'>".$userth."</a></td><td align=\"center\">".$thanks3."</td></tr>";
        }
        // По аплоадерам
        $content .= "</table><td width=\"25%\" align=\"center\" valign=\"top\"><table class=main width='100%' cellspacing=0 cellpadding=5><tr><td colspan=\"2\"><center><b>Аплоадеры</b></center></td></tr><tr><td class=\"colhead\"><b>Имя</b></td><td class=\"colhead\"><b>Торрентов</b></td></tr>";
        $torrents = mysqli_query($mysqli, "SELECT u.id, u.username, u.class, u.warned, u.gender, u.enabled, u.parked, u.donor, COUNT(t.owner) AS torrents FROM users AS u, torrents AS t WHERE u.id=t.owner GROUP BY u.id ORDER BY torrents DESC LIMIT 10");
        while($torr = mysqli_fetch_assoc($torrents)){
            $uid = (int)$torr["id"];
            $username = get_user_class_color($torr["class"], $torr["username"]) . get_user_icons($torr);
            // Считаем количество торрентов одним запросом
            $torrents1 = mysqli_query($mysqli, "SELECT COUNT(*) FROM torrents WHERE owner='$uid'");
            $arr = mysqli_fetch_row($torrents1);
            $torrents2 = (int)$arr[0];
            $content .= "<tr><td align=\"left\" width=\"65%\"><a href='/user/id".$uid."'>".$username."</a></td><td align=\"center\">".$torrents2."</td></tr>";
        }
        // По донейтам
        $content .= "</table>";
        $content .="</tr></table>";
        cache_write('top10', $content);
    } else {
        $content = cache_read('top10');
    }
    echo $content;
}
/////////////////////конец//////////////////
  else
        die("Косяк !!!");

}
else
    die("Прямой доступ запрещен");