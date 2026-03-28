<?php
require_once("include/bittorrent.php");
dbconn(false);

global $lang;
stdhead('Список аниме торрентов доступных для скачивания , бесплатно и без регистрации');

$res1 = sql_query("SELECT COUNT(*) FROM torrents WHERE modded = 'yes'"); 
$row1 = mysqli_fetch_array($res1); 
$count = $row1[0]; 
    $perpage = 50; 
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?" ); 
    // Initialize output buffer
    $content = '';
    $content .= $pagertop; 

$content .= "
<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\">";
$res = sql_query("SELECT torrents.id, torrents.name FROM torrents ORDER BY id DESC $limit") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) > 0) {
	while ($arr = mysqli_fetch_assoc($res)) {
		$torrname = $arr['name'];
		$content .= "<b><a href=\"details/id".$arr['id']."-".friendly_title($arr['name'])."\" alt=\"".$arr['name']."\" title=\"".$arr['name']."\">".$torrname."</a></b><br><hr>\n";
	}
} else
	$content .= "<b> ".$lang['no_need_seeding']." </b>\n";
$content .= "
</td></tr></table>";
    $content .= $pagerbottom; 
	
begin_main_frame();
print $content;
end_main_frame();
stdfoot();
?>