<?php
include "include/bittorrent.php";
global $memcache_obj;
dbconn();
gzip();
loggedinorreturn();

stdhead("Анкеты участниц конкурса \"Мисс AnimeClub.Lv 2011\"");
begin_main_frame();
?>
<link rel="stylesheet" type="text/css" href="fancybox/fancybox.css"/>
<script type="text/javascript" src="fancybox/fancybox.js"></script>
<script>
jQuery(document).ready(function() {
 jQuery("a.screen").fancybox({
 'overlayShow' : false,
 });
 });

</script>
<?
/*
	begin_frame("Анкеты участниц конкурса \"Мисс AnimeClub.Lv 2011\"", true);
	$mem_get_r = $memcache_obj->get('konkurs');
if ($mem_get_r === false) {
$res1 = sql_query("SELECT COUNT(*) FROM konkurs"); 
$row1 = mysql_fetch_array($res1); 
$count = $row1[0]; 
$memcache_obj->set('konkurs', $count, 0, 3600); 
} else $count = $mem_get_r;
 
	$perpage = 9;
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?" ); 

	echo $pagertop; 
	$res = sql_query("SELECT * FROM konkurs $limit") or sqlerr(__FILE__, __LINE__);
	$perrow = 3; // Количество картинок в ряду
	$i = 0;
	echo '<table width="100%" cellspacing="5" cellpadding="5"><tr>';
	while($row=mysql_fetch_array($res)){

	echo "<td valign='center' align='center' class='brd' style='width:30%;padding:5px;'><noindex><a class=\"screen\" href=\"".$row['photo1']."\"  >
	<img border=0 style='max-width:200px; max-height:160px' src=\"".$row['photo1']."\" /></a></noindex><br>
	 ". htmlspecialchars_uni($row["name"]). " (".$row['age']." лет)";
	 	
	 echo " <br /> <a href='/anketa.php?id=".$row['id']."'>Смотреть</a>";
	 echo "</td>";

$i++;
    if ($i == $perrow)
      {
        echo "</tr><tr>";
        $i = 0;
      }

}
echo '</tr></table>';
    echo $pagerbottom; 
end_frame();

*/
begin_frame("Таблица лидеров голосования");

print "<table width=\"100%\" id=\"no_border\" cellspacing=\"0\" cellpadding=\"8\"><tr><td><b>Место</b></td><td><b>Участница</b></td><td><b>Голосов</b></td>";


 $cache_time = 600;
 if (cache_check("vote", $cache_time))
    $res = cache_read("vote");
	else {
$res = sql_query("SELECT konkurs.id, konkurs.uid, konkurs.name, konkurs.age, konkurs.from , (SELECT COUNT(*) FROM vote WHERE konkurs.id = vote.anketid) as count ,
 users.id as uids, users.username, users.class FROM `konkurs` LEFT JOIN users ON konkurs.uid = users.id ORDER BY count DESC")or sqlerr(__FILE__, __LINE__);
    $vote_cache = array();
    while ($cache_data = mysql_fetch_array($res))
        $vote_cache[] = $cache_data;

    cache_write("vote", $vote_cache);
    $res = $vote_cache;
    }
	
	$num = 0;
foreach($res as $arr)  {
    ++$num;
    $id = $arr["id"];
    $uid = $arr["uids"];
	$name = $arr["name"];
	$age = $arr["age"];
	$from = $arr["from"];
    $ref = number_format($arr["count"]);
	if($ref > 0){
    print "<tr><td align=\"center\">$num.</td><td width=100%><a href=/anketa.php?id=$id>$name ($age)</a> / <a href=\"user/id$uid\">".get_user_class_color($arr["class"], $arr["username"])."</a> | $from</td><td align=\"center\">$ref</td>"; 
	
	print"</tr>";
}
}
print"</table>";

end_frame();


end_main_frame();
stdfoot();

?>