<?php
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// Ensure Memcached client from secrets.php is available
global $memcached;
if (empty($memcached) || !($memcached instanceof Memcached)) {
    $memcached = new Memcached();
    $memcached->addServer('animeclub-memcache', 11211);
}

  function torrenttable($res, $variant = "index") {
		global $pic_base_url, $CURUSER, $use_ttl, $ttl_days, $lang, $addtags, $category,
             $DEFAULTBASEURL, $cat_name, $seourl, $mysqli, $memcached;
        // Prevent undefined index warnings for GET parameters
        $_GET['sort'] = $_GET['sort'] ?? '';
        $_GET['type'] = $_GET['type'] ?? '';
        $_GET['d']    = $_GET['d'] ?? '';
        // Default to list view (table) if no browse mode specified
        // Initialize view and pagination variables
        $_COOKIE['browsemode'] = $_COOKIE['browsemode'] ?? '';
        $link3 = $link4 = $link5 = $link7 = $link8 = $link9 = $link10 = '';
        $peerlink = false;
print("<tr>\n");
if (isset($_COOKIE['browsemode']) && $_COOKIE['browsemode'] === 'thumbs')
{
  $perrow = 4; // Количество картинок в ряду
  print "<script language=\"JavaScript\" src=\"".$DEFAULTBASEURL."/js/overlib.js\" type=\"text/javascript\"></script>";

  $i = 0;
	if(is_array($res))
	foreach($res as $row)
  {

    print ("<td style=\"width:134px;border:0;vertical-align:top;\" align=\"center\"><a class=\"readtorrent\" href=\"".$DEFAULTBASEURL."/details/id".$row["id"]."-".friendly_title($row['name'])."\"><img src=\"".$row["image1"]."\" width=\"134px\" height=\"188px\" style=\"border: 1px solid #999;margin-bottom: 5px;\" onmouseover=\"return overlib('<div style=\'padding: 5px; background-color:#FFFFFF;\'><table id=\'thumbs\'><tr style=\'background: #f0f0f0;\'><td colspan=\'2\'><pre>".$row["name"]."</pre></td></tr><td>Категория</td><td>".cat_name($row["category"],true)."</td></tr><tr><td>Загрузил</td><td>" . (isset($row['owner_name']) 
      ? '<b>' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</b>' 
      : '<i>(unknown)</i>') . "</td></tr><tr style=\'background: #f0f0f0;\'><td>Добавлен</td><td><pre style=\'font-weight:normal;\'>".gmdate('Y-m-d',$row['added'])."</pre></td></tr><tr><td>Комментариев</td><td>".$row["comments"]."</td></tr><tr style=\'background: #f0f0f0;\'><td>Файлов</td><td>".$row["numfiles"]."</td></tr><tr><td>Размер</td><td>".mksize($row["size"])."</td></tr></table></div>');\" onmouseout=\"return nd();\" alt=\"\" /></a><br /><div align=\"center\"><img src=\"pic/ardown.gif\" title=\"Качают\" /> ".$row['leechers']."  <img src=\"pic/arup.gif\" title=\"Раздают\" /> ".$row['seeders']."  <img src=\"pic/snat.gif\" title=\"Скачан\" /> ".$row['times_completed']."</div></td>");

    $i++;
    if ($i == $perrow)
      {
        print ("</tr><tr>");
        $i = 0;
      }
  }
}
 elseif (isset($_COOKIE['browsemode']) && $_COOKIE['browsemode'] === 'full') {

 	if(is_array($res))
	foreach($res as $row)
  {
		global $memcached;
		$id = $row['id'];
		$cacheKey = 'torrent_desc' . $id;
		$descr = $memcached->get($cacheKey);
		if ($memcached->getResultCode() !== Memcached::RES_SUCCESS || $descr === false) {
			$descr = format_comment($row['descr'] ?? '');
			$memcached->set($cacheKey, $descr, 3600);
		}
		$descr = explode("<u><b>Техданные:</b></u>", $descr);
		$descr = $descr[0];
		$torname = $row["name"];   
        $uprow = (isset($row["owner_name"]) ? ("<a href=".$DEFAULTBASEURL."/user/id" . $row["owner"] . ">" . get_user_class_color($row["owner_class"], htmlspecialchars_uni($row["owner_name"])) . "</a>") : "<i>Скоро сделаем и будет видно :)</i>");   
        
		echo "<tr><td id=\"no_border\">";  
        echo "<table width=\"100%\" class=\"main\" id=\"no_border\" cellspacing=\"0\" cellpadding=\"10\">";  
        echo "<h2><a href=\"details/id".$row["id"]."-".friendly_title($torname)."\" alt=\"".$row["name"]."\" title=\"Скачать аниме ".$row["name"]." бесплатно\">".$torname."</a></h2>";  
		echo "<td>";
		if ($row["image1"] != "") 
		$img1 = "<img style='border:0;' src='".$DEFAULTBASEURL."/timthumb.php?src=".$row["image1"]."&w=230&zc=1&q=90' width='230px' alt='".$row['name']."'/>"; 
		
            echo "<div style=\"margin: 3px 5px; float: right;\">$img1";  
        echo "<br><br><div id=\"rows\" style=\"border: 1px dashed #ddd;  background: #f7f7f7; margin: 0 0 5px 0; padding: 3px;\">  
            <b>".$lang['size']."</b>".mksize($row["size"])."<br>  
            <b style='color: #0a0;'>Раздают:</b>".$row["seeders"] ."<br>
            <b style='color: #a00;'>Качают:</b>".$row["leechers"]." <br>
            <b>Скачан:</b>".$row["times_completed"] ."раз(а) 
            </div></div>";
			
        echo "<p align=\"left\"><noindex>".$descr."</noindex><br></p>";  
        echo "</td>";  
        echo "</table>";  
        echo "</td></tr>";  
		
		}
		
}
else
{
        // Initialize oldlink for building query strings
        $oldlink = '';
?>

<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/wz_tooltip.js"></script>
<?
// sorting by MarkoStamcar

$count_get = 0;

foreach ($_GET as $get_name => $get_value) {

$get_name = mysqli_real_escape_string($mysqli, strip_tags(str_replace(array("\"","'"), array("", ""), $get_name)));

$get_value = mysqli_real_escape_string($mysqli, strip_tags(str_replace(array("\"","'"), array("", ""), $get_value)));

if ($get_name != "sort" && $get_name != "type") {
if ($count_get > 0) {
$oldlink = $oldlink . "&" . $get_name . "=" . $get_value;
} else {
$oldlink = $oldlink . $get_name . "=" . $get_value;
}
$count_get++;
}

}

if ($count_get > 0) {
$oldlink = $oldlink . "&";
}

if ($_GET['sort'] == "3") {
if ($_GET['type'] == "desc") {
$link3 = "asc";
} else {
$link3 = "desc";
}
}

if ($_GET['sort'] == "4") {
if ($_GET['type'] == "desc") {
$link4 = "asc";
} else {
$link4 = "desc";
}
}

if ($_GET['sort'] == "5") {
if ($_GET['type'] == "desc") {
$link5 = "asc";
} else {
$link5 = "desc";
}
}

if ($_GET['sort'] == "7") {
if ($_GET['type'] == "desc") {
$link7 = "asc";
} else {
$link7 = "desc";
}
}

if ($_GET['sort'] == "8") {
if ($_GET['type'] == "desc") {
$link8 = "asc";
} else {
$link8 = "desc";
}
}

if ($_GET['sort'] == "9") {
if ($_GET['type'] == "desc") {
$link9 = "asc";
} else {
$link9 = "desc";
}
}

if ($_GET['sort'] == "10") {
if ($_GET['type'] == "desc") {
$link10 = "asc";
} else {
$link10 = "desc";
}
}

if ($link3 == "") { $link3 = "desc"; }
if ($link4 == "") { $link4 = "desc"; }
if ($link5 == "") { $link5 = "desc"; }
if ($link7 == "") { $link7 = "desc"; }
if ($link8 == "") { $link8 = "desc"; }
if ($link9 == "") { $link9 = "desc"; }
if ($link10 == "") { $link10 = "desc"; }

?>
<td  align="center"><img src="<?=$DEFAULTBASEURL?>/pic/browse/genre.gif" alt="<?=$lang['type'];?>" border="0px"/></td>
<td  align="left" width="50%"><img src="<?=$DEFAULTBASEURL?>/pic/browse/release.gif" alt="<?=$lang['name'];?>" border="0px"/></td>
<?

if ($variant == "mytorrents")
	print("<td align=\"center\">".$lang['visible']."</td>\n");

?><noindex>
<td align="center"><a href="browse?<? print $oldlink; ?>sort=3&type=<? print $link3; ?>" class="altlink_white"><img src="<?=$DEFAULTBASEURL?>/pic/browse/comments.gif" alt="<?=$lang['comments'];?>" border="0px" /></a></td>

<td align="center"><a href="browse?<? print $oldlink; ?>sort=5&type=<? print $link5; ?>" class="altlink_white"><img src="<?=$DEFAULTBASEURL?>/pic/browse/mb.gif" alt="<?=$lang['size'];?>" border="0px" /></a></td>

<td align="center"><a href="browse?<? print $oldlink; ?>sort=7&type=<? print $link7; ?>" class="altlink_white"><img src="<?=$DEFAULTBASEURL?>/pic/browse/seeders.gif" alt="<?=$lang['seeds'];?>" border="0px" /></a>|
<a href="browse?<? print $oldlink; ?>sort=8&type=<? print $link8; ?>" class="altlink_white"><img src="<?=$DEFAULTBASEURL?>/pic/browse/leechers.gif" alt="<?=$lang['leechers'];?>" border="0px" /></a></td>
<?

if ($variant == "index" )
	print("<td  align=\"center\"><a href=\"browse?{$oldlink}sort=9&type={$link9}\" class=\"altlink_white\"><img src=\"".$DEFAULTBASEURL."/pic/browse/upped.gif\" alt=\"".$lang['uploadeder']."\" border=\"0px\" /></a></td>\n");


print("</noindex></tr>\n");

print("<tbody>");
// Инициализация переменной для хранения предыдущей даты
$prevdate = '';

	if(is_array($res))
	foreach($res as $row)
	{
		
/** Make some date variables **/
// Получаем часовой пояс и летнее время, если пользователь не авторизован — используем 0
$timezone = isset($CURUSER['timezone']) ? $CURUSER['timezone'] : 0;
$dst      = isset($CURUSER['dst'])      ? $CURUSER['dst']      : 0;
$day_added = $row['added'] + ($timezone + $dst) * 60;
$thisdate  = gmdate('Y-m-d', $day_added);

/** If date already exist, disable $cleandate varible **/
if($thisdate==$prevdate){
$cleandate = '';

/** If date does not exist, make some varibles **/
}else{
// Используем ранее вычисленные $timezone и $dst для корректного смещения
$day_header = gmdate('l d M', $row['added'] + ($timezone + $dst) * 60);
$day_added = '<p align="justify"><b>Торренты за ' . $day_header . '</b></p>';
$cleandate = "<tr><td colspan=15 id=no_border><b>$day_added</b></td></tr>\n";
}
/** Prevent that "torrents added..." wont appear again with the same date **/
$prevdate = $thisdate;

$man = array(
    'Jan' => 'Января',
    'Feb' => 'Февраля',
    'Mar' => 'Марта',
    'Apr' => 'Апреля',
    'May' => 'Мая',
    'Jun' => 'Июня',
    'Jul' => 'Июля',
    'Aug' => 'Августа',
    'Sep' => 'Сентября',
    'Oct' => 'Октября',
    'Nov' => 'Ноября',
    'Dec' => 'Декабря'
);

foreach($man as $eng => $rus){
    $cleandate = str_replace($eng, $rus,$cleandate);
}

$dag = array(
    'Mon' => 'Понедельник',
    'Tues' => 'Вторник',
    'Wednes' => 'Среду',
    'Thurs' => 'Четверг',
    'Fri' => 'Пятницу',
    'Satur' => 'Субботу',
    'Sun' => 'Воскресенье'
);

foreach($dag as $eng => $rus){
    $cleandate = str_replace($eng.'day', $rus.'',$cleandate);
}
/** If torrents not listed by added date **/
if(!$_GET['sort'] && !$_GET['d']){
   echo $cleandate."\n";
}




if ($row["modded"] == "no" && get_user_class() < UC_MODERATOR && $row["owner"] != $CURUSER["id"])
            print "";
        else {
		$id = $row["id"];
		print("<tr>\n");
				print("<td align=\"center\" rowspan=2 width=1% style=\"padding: 0px\">");
		
			print("<a href=\"".$DEFAULTBASEURL."/browse/cat" . $row["category"] . "\">");
			
            echo cat_name($row["category"]);
			
			print("</a>");
		
			
		print("</td>\n");
        if($row["image1"])
        {
          $img_tor = $row["image1"];
        } 
		$dispname = $row["name"];

                print("<td colspan=\"10\" align=\"left\"><a " .($img_tor ? "onmouseover=\"Tip('<img  src=".$DEFAULTBASEURL."/timthumb.php?src=".$img_tor."&w=230&zc=1&q=90 width=230px>', 300, 600, PADDING, 1, 'red', 'red');\" onmouseout=\"UnTip();\" " : ""). "  href=\"details/");
			        unset($img_tor);  

		print("id$id-".friendly_title($dispname));

		print("\"><b>$dispname</b></a> \n");
					
print("</td></tr><tr>");

		print('<td class="small">');
print('<noindex><font size="1" color="#bc5349"> Тэги: '.addtags($row["tags"],0).'</font></noindex>');
	print("</td>\n");

		if ($variant == "mytorrents") {
			print("<td align=\"center\">");
			if ($row["visible"] == "no")
				print("<font color=\"red\"><b>".$lang['no']."</b></font>");
			else
				print("<font color=\"green\">".$lang['yes']."</font>");
			print("</td>\n");
		}
		
		if (!$row["comments"])
			print("<td align=\"center\">" . $row["comments"] . "</td>\n");
		else {
				print("<td align=\"center\"><b><a href=\"./details/id$id-".friendly_title($dispname)."&amp;page=0\">" . $row["comments"] . "</a></b></td>\n");
		}

		print("<td align=\"center\">" . str_replace(" ", "&nbsp;", mksize($row["size"])) . "</td>\n");

		print("<td align=\"center\">");

		if ($row["seeders"]) {
			if ($variant == "index")
			{
			   if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
				print("<b><font color=" .
				  get_slr_color($ratio) . ">" . $row["seeders"] . "</font></b>\n");
			}
			else
				print("<b><span class=\"" . linkcolor($row["seeders"]) . "\">" .
				  $row["seeders"] . "</span></b>\n");
		}
		else
			print("<span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span>");

		print(" | ");

		if ($row["leechers"]) {
			if ($variant == "index")
				print("<b>" .
				   number_format($row["leechers"]) . ($peerlink ? "" : "") .
				   "</b>\n");
			else
				print("<b><span class=\"" . linkcolor($row["leechers"]) . "\" >" .
				  $row["leechers"] . "</span></b>\n");
		}
		else
			print("0\n");
if (get_user_class() >= UC_MODERATOR)
		print("&nbsp;(".$row["times_completed"].")");
		
		print("</td>");
		
		if ($variant == "index")
			print("<td align=\"center\">" . (isset($row["owner_name"]) ? ("<a href=\"./user/id" . $row["owner"] . "\"><b>" . get_user_class_color($row["owner_class"], htmlspecialchars_uni($row["owner_name"])) . "</b></a>") : "<i>(unknown)</i>") . "</td>\n");




	print("</tr>\n");
}
	}
	print("</tbody>");

}
	return is_array($res) ? count($res) : 0;
}
?>