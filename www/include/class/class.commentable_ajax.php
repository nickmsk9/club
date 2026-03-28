<?php
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

function commenttable_ajax($rows, $redaktor = "comment") {
	global $CURUSER, $avatar_max_width, $memcache_obj, $DEFAULTBASEURL;
	$currentUserId = (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['id'])) ? (int)$CURUSER['id'] : 0;
	$currentUserClass = $currentUserId > 0 ? get_user_class() : 0;

	$count = 0;
	foreach ($rows as $row)	{
			    if ($row["downloaded"] > 0) {
			    	$ratio = $row['uploaded'] / $row['downloaded'];
			    	$ratio = number_format($ratio, 2);
			    } elseif ($row["uploaded"] > 0) {
			    	$ratio = "Inf.";
			    } else {
			    	$ratio = "---";
			    }
			     if (strtotime($row["last_access"]) > gmtime() - 600) {
			     	$online = "online";
			     	$online_text = "В сети";
			     } else {
			     	$online = "offline";
			     	$online_text = "Не в сети";
			     }
	   print(" <div id=\"rounded-box-3\"><b class=\"r3\"></b><b class=\"r1\"></b><b class=\"r1\"></b><div class=\"inner-box\">
				<table class=\"maibaugrand\" width=\"95%\" border=\"0px\" align=\"center\" cellspacing=\"0\" cellpadding=\"4\" >");
	   print("<tr><td class=\"colhead\" align=\"left\" border=\"0px\" colspan=\"2\" height=\"24\">");

    if (isset($row["username"]))
		{
			$title = $row["title"];
			if ($title == ""){
				$title = get_user_class_name($row["class"]);
			}else{
				$title = htmlspecialchars_uni($title);
			}
		   print(":: <img src=\"".$DEFAULTBASEURL."/pic/buttons/button_".$online.".gif\" alt=\"".$online_text."\" title=\"".$online_text."\" style=\"position: relative; top: 2px;\" border=\"0\" height=\"14\">"
		       ." <a name=comm". $row["id"]." href=".$DEFAULTBASEURL."/user/id" . $row["user"] . " class=altlink_white><b>". get_user_class_color($row["class"], htmlspecialchars_uni($row["username"])) . "</b></a> ::"
		       .($row["donor"] == "yes" ? "<img src=".$DEFAULTBASEURL."/pic/star.gif alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src=\"".$DEFAULTBASEURL."/pic/warned.gif\" alt=\"Warned\">" : "") . " $title ::\n")
		       ." <b>U</b> ".mksize($row["uploaded"]) ." :: <b>D</b> ".mksize($row["downloaded"])." :: <b>R</b> <font color=\"".get_ratio_color($ratio)."\">$ratio</font> :: ";
	       } else {
			print("<a name=\"comm" . $row["id"] . "\"><i>[Anonymous]</i></a>\n");
	       }
		   
	$avatar =  htmlspecialchars_uni($row["avatar"]);
	if (!$avatar){$avatar = "".$DEFAULTBASEURL."/pic/default_avatar.gif"; }
	
		$id = $row["id"];

			if ($memcache_obj instanceof Memcached && false !== ($cached = $memcache_obj->get('comment'.$id))) {
				$comm_text = $cached;
			} else {
				$comm_text = format_comment($row["text"]);
				if ($memcache_obj instanceof Memcached) {
					$memcache_obj->set('comment'.$id, $comm_text, 600);
				}
			}
		
	
	$text = "<div id=\"comment_text".$row['id']."\" width=\"80%\">".$comm_text."</div>\n";
	if ($row["editedby"]) {
	       $text .= "<p style=float:right><i><font size=1 class=small_com>Последний раз редактировалось <a href=".$DEFAULTBASEURL."/user/id$row[editedby]>$row[editedbyname]</a> в ".display_date_time($row['editedat'])."</font></i></p>\n";
	 }

		print("</td></tr>");
		print("<tr valign=top>\n");
		print("<td style=\"padding: 0px; width: 5%;\" align=\"center\" border=\"0px\"><img src=$avatar width=\"$avatar_max_width\"> </td>\n");
		print("<td width=\"95%\" border=\"0px\" class=\"text\">");
		print("$text</td></tr>\n");
		print("<tr><td class=colhead align=\"center\" colspan=\"2\" border=\"0px\">");
		print "<div style=\"float: left; width: auto;\">"
			.($currentUserId > 0 ? " [<a href=\"javascript:;\" onClick=\"SE_CommentQuote('".$row['id']."','".$row['torrentid']."')\" class=\"altlink_white\">Цитата</a>]" : "")
			.(((int)$row["user"] === $currentUserId) || $currentUserClass >= UC_MODERATOR ? " [<a href=\"javascript:;\" onClick=\"SE_EditComment('".$row['id']."','".$row['torrentid']."')\" class=\"altlink_white\">Изменить</a>]" : "")
		    .($currentUserClass >= UC_MODERATOR ? " [<a href=\"javascript:;\" onClick=\"SE_DeleteComment('".$row['id']."','".$row['torrentid']."')\">Удалить</a>]" : "")

		    .(!empty($row["editedby"]) && $currentUserClass >= UC_MODERATOR ? " [<a href=\"javascript:;\"  onClick=\"SE_ViewOriginal('".$row['id']."','".$row['torrentid']."')\" class=\"altlink_white\">Оригинал</a>]" : "")
		    .($currentUserClass >= UC_MODERATOR ? " IP: ".(!empty($row["ip"]) ? "<a href=\"usersearch.php?ip=$row[ip]\" class=\"altlink_white\">".$row["ip"]."</a>" : "Неизвестен") : "")
		    ."</div>";

		print("<div align=\"right\">Комментарий добавлен: ".display_date_time($row["added"])." </div></td></tr>");
		print("</table></div><b class=\"r1\"></b><b class=\"r1\"></b><b class=\"r3\"></b></div><br />");
  }

}

?>