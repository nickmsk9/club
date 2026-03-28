<?php

if (!defined('BLOCK_FILE')) {
header("Location: ../index.php");
exit;
}
global $lang, $ss_uri, $maxusers ,$rootpath;
getlang(stats);
$blocktitle = $lang['block_stats'];
$cache_time = 600;
if (!cache_check("stats", $cache_time)) {
$registered = number_format(get_row_count("users"));
$male = number_format(get_row_count("users"," WHERE gender = '1'"));
$female = number_format(get_row_count("users"," WHERE gender = '2'"));
$torrents = number_format(get_row_count("torrents"));
$seeders = get_row_count("peers", "WHERE seeder='yes'");
$leechers = get_row_count("peers", "WHERE seeder='no'");
$uploaders = number_format(get_row_count("users", "WHERE class = ".UC_UPLOADER));
$preuploaders = number_format(get_row_count("users", "WHERE class = ".UC_PREUPLOADER));
if ($leechers == 0)
  $ratio = 0;
else
  $ratio = round($seeders / $leechers * 100);
$peers = number_format($seeders + $leechers);
$seeders = number_format($seeders);
$leechers = number_format($leechers);
$res = mysql_query("SELECT SUM(size)FROM torrents;") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res) or die("ошибка доступа к БД ");
$result = mysql_query("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul FROM users") or sqlerr(__FILE__, __LINE__); 
$row = mysql_fetch_assoc($result); 
$stats['totaldownloaded'] = $row["totaldl"]; 
$stats['totaluploaded'] = $row["totalul"]; 
$test = mksize($stats['totaluploaded'] + $stats['totaldownloaded']);
	$stats = array(
		"registered" => $registered,
		"torrents" => $torrents,
		"uploaders" => $uploaders,
		"male" => $male,
		"female" => $female,
		"preuploaders" => $preuploaders,
		"ratio" => $ratio,
		"peers" => $peers,
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
	$preuploaders = $stats["preuploaders"];
	$ratio = $stats["ratio"];
	$peers = $stats["peers"];
	$seeders = $stats["seeders"];
	$leechers = $stats["leechers"];
	$arr = $stats["arr"];
	$test = $stats["test"];
}

$content .= "	<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">
		<tbody id=\"collapseobj_showstats\" style=\"\">	
	<tr>	
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_users']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$registered."</b></div></td>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_files']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$torrents."</b></div></td>
	</tr><tr>
		<tr>	
	<td class=\"rowhead\"><div align=\"right\">Парней</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$male."</b></div></td>
	<td class=\"rowhead\"><div align=\"right\">Девушек</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$female."</b></div></td>
	</tr><tr>
	
	<td class=\"rowhead\"><div align=\"right\" >".$lang['st_seeders']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b><font color=green>".$seeders."</font></b></div></td>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_leechers']."</div></td>
	<td class=\"rowhead\"><div align=\"right\"><b><font color=red>".$leechers."</font></b></div></td>
	</tr>
	
	<tr>

	<td class=\"rowhead\"><div align=\"right\">".$lang['st_peers']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$peers."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_reting']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$ratio."</div></td>

	</tr>
	<tr>
	<td class=\"rowhead\"><div align=\"right\"><font color=\"#fb780f\">".$lang['st_users_uploaders']."</font></div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$uploaders."</b></div></td>

	<td class=\"rowhead\"><div align=\"right\"><font color=\"#bb96ba\">".$lang['st_users_preaploaders']."</font></div></td>
	<td class=\"rowhead\"><div align=\"right\"><b>".$preuploaders."</b></div></td>
	</tr><tr>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_total_trafic']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$test."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".$lang['st_total_size']."</div></td>
	<td class=\"rowhead\"><div align=\"right\">".mksize($arr['SUM(size)'])."</div></td>

	</tr>";	
	$times = $cache_time - date(gmtime() - filemtime($rootpath . "cache/stats.cache"));

if ($cache_time >= 3600*24*24 ){
$time = $times / 60 / 60 / 24;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." дней.";
} elseif ($cache_time >= 3600*24 ) {
$time = $times / 60 / 60;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." часов";
} elseif ($cache_time >= 3600 ) {
$time = $times / 60;
$time1 = number_format($time, 2,',',' ');
	$time = "Статистика обновится через ".$time1." минут";
} elseif ($cache_time >= 300 ) {
$time = $times;
$time1 = number_format($time, 0,',',' ');
	$time = "Статистика обновится через ".$time1." секунд.";
} 

$content .= "
		<tr>
			<td colspan=\"8\" height=\"10\" align=\"center\"><font class=small><b>".$time."</b></font></td>
		</tr>

"; 


$content .= "</tbody></table><br><br>";
//visited today
if (get_user_class() >= UC_UPLOADER){

global $lang;
$con = mysql_query("SELECT userid FROM peers GROUP by userid");
$connected = mysql_num_rows($con);
$blocktitle = $tracker_lang['server_load'];
$avgload = get_server_load();
if (strtolower(substr(PHP_OS, 0, 3)) != 'win')
	$percent = $avgload * 4;
else
	$percent = $avgload;
if ($percent <= 50) $pic = "loadbargreen.gif";
elseif ($percent <= 70) $pic = "loadbaryellow.gif";
else $pic = "loadbarred.gif";
	$width = $percent * 4;
$content .= "<center>
<table class=\"main\" border=\"0\" width=\"402\"><tr><td style=\"padding: 0px; background-repeat: repeat-x\" title=\"Нагрузка: $percent%, Средняя (LA): $avgload\">"
."<img height=\"15\" width=\"$width\" src=\"pic/$pic\" alt=\"Нагрузка: $percent%, Средняя (LA): $avgload\" title=\"Нагрузка: $percent%, Средняя (LA): $avgload\">"
."</td></tr></table>"
."<b>Всего к трекеру подключено уникальных $connected пользователей.</b></center>";

//visited today

if (cache_check("record", 600))
$res = cache_read("record");
else {
$res = mysql_query("SELECT id, gender, username, class FROM users WHERE UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(" . get_dt_num() . ") - UNIX_TIMESTAMP(" .date("Ymd000000"). ") AND hiden = 'no'") or sqlerr(__FILE__, __LINE__); 
    $record_cache = array();
    while ($cache_data = mysql_fetch_array($res))
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

$content .= "<center>
<table class=\"main\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\" width=\"100%\"><tr><td class=\"embedded\">"
."<b><font color=red>".$usersactivetoday."</font> Пользователей посетило трекер сегодня:</b><hr>"
."<div align='left'>".$todayactive."</div><hr>"
."</td></tr></table></center>";  

}

?>