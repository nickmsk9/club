<?php

# IMPORTANT: Do not edit below unless you know what you are doing!
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');



function subscribetable($res) {
		global $pic_base_url, $CURUSER, $use_wait, $use_ttl, $ttl_days, $lang, $DEFAULTBASEURL;

 
?>
<script type="text/javascript" src="js/wz_tooltip.js"></script>
<?
// sorting by MarkoStamcar

// Сортировка по MarkoStamcar
$count_get = 0;
$oldlink = "";

foreach ($_GET as $get_name => $get_value) {
    // Экранируем и очищаем имя и значение
    $get_name = mysqli_real_escape_string($GLOBALS['mysqli'], strip_tags(str_replace(array("\"", "'"), array("", ""), $get_name)));
    $get_value = mysqli_real_escape_string($GLOBALS['mysqli'], strip_tags(str_replace(array("\"", "'"), array("", ""), $get_value)));

    // Пропускаем параметры sort и type
    if ($get_name != "sort" && $get_name != "type") {
        $oldlink .= ($count_get > 0 ? "&" : "") . "$get_name=$get_value";
        $count_get++;
    }
}

if ($count_get > 0) {
    $oldlink .= "&";
}

// Безопасно получаем параметры сортировки
$sort = $_GET['sort'] ?? '';
$type = $_GET['type'] ?? '';

// Определяем направление сортировки
$link3  = ($sort == "3"  && $type == "desc") ? "asc" : "desc";
$link4  = ($sort == "4"  && $type == "desc") ? "asc" : "desc";
$link5  = ($sort == "5"  && $type == "desc") ? "asc" : "desc";
$link7  = ($sort == "7"  && $type == "desc") ? "asc" : "desc";
$link8  = ($sort == "8"  && $type == "desc") ? "asc" : "desc";
$link9  = ($sort == "9"  && $type == "desc") ? "asc" : "desc";
$link10 = ($sort == "10" && $type == "desc") ? "asc" : "desc";

?>
<td  align="center"><img src="pic/browse/genre.gif" alt="<?=$lang['type'];?>" border="0px"/></td>
<td  align="left" width="50%"><img src="pic/browse/release.gif" alt="<?=$lang['name'];?>" border="0px"/></td>
<?



?>
<td align="center"><a href="browse.php?<? print $oldlink; ?>sort=3&type=<? print $link3; ?>" class="altlink_white"><img src="pic/browse/comments.gif" alt="<?=$lang['comments'];?>" border="0px" /></a></td>

<td align="center"><a href="browse.php?<? print $oldlink; ?>sort=5&type=<? print $link5; ?>" class="altlink_white"><img src="pic/browse/mb.gif" alt="<?=$lang['size'];?>" border="0px" /></a></td>

<td align="center"><a href="browse.php?<? print $oldlink; ?>sort=7&type=<? print $link7; ?>" class="altlink_white"><img src="pic/browse/seeders.gif" alt="<?=$lang['seeds'];?>" border="0px" /></a>|<a href="browse.php?<? print $oldlink; ?>sort=8&type=<? print $link8; ?>" class="altlink_white"><img src="pic/browse/leechers.gif" alt="<?=$lang['leechers'];?>" border="0px" /></a></td>
<?


	print("<td  align=\"center\"><a href=\"browse.php?{$oldlink}sort=9&type={$link9}\" class=\"altlink_white\"><img src=\"pic/browse/upped.gif\" alt=\"".$lang['uploadeder']."\" border=\"0px\" /></a></td>\n");

print("<td  align=\"center\">Удалить</td>\n");

print("</tr>\n");

print("<tbody>");

$variant = $_GET['variant'] ?? ''; // Добавить до цикла while


	while ($row = mysqli_fetch_assoc($res)) {
print ("<form method=\"post\" action=\"sub.php?act=del\">");


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

                print("<td colspan=\"10\" align=\"left\"><a " .($img_tor ? "onmouseover=\"Tip('<img src=" .$img_tor. " width=200>', 300, 600, PADDING, 1, 'red', 'red');\" onmouseout=\"UnTip();\" " : ""). "  href=\"details/");
			        unset($img_tor);  
		if ($variant == "mytorrents")
			print("returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;");
		print("id$id");


		print("\"><b>$dispname</b></a> \n");
					

print("</td></tr><tr>");

		print('<td class="small">');
print('<noindex><font size="1" color="#bc5349"> Тэги: '.addtags($row["tags"],0).'</font></noindex>');
	print("</td>\n");




		
			print("<td align=\"center\">" . $row["comments"] . "</td>\n");
	

		print("<td align=\"center\">" . str_replace(" ", "&nbsp;", mksize($row["size"])) . "</td>\n");

		print("<td align=\"center\">");


			print("<b><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></b>");

		print(" | ");

	
				print("<b><span class=\"" . linkcolor($row["leechers"]) . "\" >" .
				  $row["leechers"] . "</span></b>\n");
		
if (get_user_class() >= UC_MODERATOR)
		print("&nbsp;(".$row["times_completed"].")");
		
		print("</td>");

			print("<td align=\"center\">" . (isset($row["owner_name"]) ? ("<a href=\"user/id" . $row["owner"] . "\"><b>" . get_user_class_color($row["owner_class"], htmlspecialchars_uni($row["owner_name"])) . "</b></a>") : "<i>(unknown)</i>") . "</td>\n");
print ("<td align=\"center\"><input type=\"checkbox\" name=\"delsub[]\" value=\"" . $row['subscribeid'] . "\" /></td>");
	print("</tr>\n");

	}
}
	print("</tbody>");
		print("<tr><td colspan=\"12\" align=\"right\"><input type=\"submit\" value=\"Удалить\"></td></tr>\n");


			print("</form>\n");

	return;
}

?>