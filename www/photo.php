<?php
include "include/bittorrent.php";
global $memcache_obj;
dbconn();
gzip();
loggedinorreturn(); 
stdhead("Фотографии");
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

	begin_frame("Фотографии", true);
	$mem_get_r = $memcache_obj->get('photo');
if ($mem_get_r === false) {
$res1 = sql_query("SELECT COUNT(*) FROM users WHERE photo > '0'"); 
$row1 = mysql_fetch_array($res1); 
$count = $row1[0]; 
$memcache_obj->set('photo', $count, 0, 3600); 
} else $count = $mem_get_r;
 
	$perpage = 16;
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?" ); 
	echo $pagertop; 
	$res = sql_query("SELECT id , username , class, photo FROM users WHERE photo > '0' ORDER BY id ASC $limit") or sqlerr(__FILE__, __LINE__);
	$perrow = 4; // Количество картинок в ряду
	$i = 0;
	echo '<table width="100%" cellspacing="5" cellpadding="5"><tr>';
	while($row=mysql_fetch_array($res)){

	echo "<td  class='brd' style='width:30%;padding:5px;text-align:center;'><noindex><a class=\"screen\" href=\"".$BASEURL."/photo/".$row['photo']."\"  >
	<img border=0 style='max-width:200px; max-height:160px' src=\"/thumb.php?file=/photo/".$row['photo']."&size=160&nocache=1\" /></a></noindex><br>
	 Фото by <a href=user/id".$row['id'].">" .get_user_class_color($row["class"], htmlspecialchars_uni($row["username"]))."</a>";
	 if(get_user_class() >= UC_MODERATOR)
	 echo " - <a href='/foto.php?act=deladmin&id=".$row['id']."'>D</a>";
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
end_main_frame();
stdfoot();

?>