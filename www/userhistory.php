<?


require "include/bittorrent.php";

gzip();

dbconn(false);

loggedinorreturn();

$userid = (int)$_GET["id"];

if (!is_valid_id($userid)) stderr($lang['error'], "Invalid ID");

if (get_user_class()< UC_POWER_USER || ($CURUSER["id"] != $userid && get_user_class() < UC_MODERATOR))
	stderr($lang['error'], "Нет доступа");

$page = $_GET["page"];

$action = $_GET["action"];

//-------- Global variables

$perpage = 25;


//-------- Action: View comments

if ($action == "viewcomments")
{
	$select_is = "COUNT(*)";

	// LEFT due to orphan comments
	$from_is = "comments AS c LEFT JOIN torrents as t
	            ON c.torrent = t.id";

	$where_is = "c.user = $userid";
	$order_is = "c.id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	$arr = mysqli_fetch_row($res) or stderr($lang['error'], "Комментарии не найдены");

	$commentcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, $_SERVER["PHP_SELF"] . "?action=viewcomments&id=$userid&");

	//------ Get user data

	$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

	if (mysqli_num_rows($res) == 1)
	{
		$arr = mysqli_fetch_assoc($res);

	  $subject = "<a href=user/id$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
	}
	else
	  $subject = "unknown[$userid]";

	//------ Get comments

	$select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

	if (mysqli_num_rows($res) == 0) stderr($lang['error'], "Комментарии не найдены");

	stdhead("История комментариев");

	print("<h1>История комментариев для $subject</h1>\n");

	if ($commentcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysqli_fetch_assoc($res))
	{

		$commentid = $arr["id"];

	  $torrent = $arr["name"];

    // make sure the line doesn't wrap
	  if (strlen($torrent) > 55) $torrent = substr($torrent,0,52) . "...";

	  $torrentid = $arr["t_id"];

	  //find the page; this code should probably be in details.php instead

	  $subres = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = $torrentid AND id < $commentid")
	  	or sqlerr(__FILE__, __LINE__);
	  $subrow = mysqli_fetch_row($subres);
    $count = $subrow[0];
    $comm_page = floor($count/20);
    $page_url = $comm_page?"&page=$comm_page":"";

	  $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " назад)";

	  print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>".
	  "$added&nbsp;---&nbsp;<b>Торрент:&nbsp;</b>".
	  ($torrent?("<a href=details/id$torrentid&tocomm=1>$torrent</a>"):" [Удален] ").
	  "&nbsp;---&nbsp;<b>Комментарий:&nbsp;</b>#<a href=details/id$torrentid&tocomm=1$page_url>$commentid</a>
	  </td></tr></table></p>\n");

	  begin_table(true);

	  $body = format_comment($arr["text"]);

	  print("<tr valign=top><td class=comment>$body</td></tr>\n");

	  end_table();
	}

	end_frame();

	end_main_frame();

	if ($commentcount > $perpage) echo $pagerbottom;

	stdfoot();

	die;
}
if ($action == "vf")
{
        $select_is = "COUNT(DISTINCT p.id)";

        $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";

        $where_is = "p.userid = $userid";

        $order_is = "p.id DESC";

        $query = "SELECT $select_is FROM $from_is WHERE $where_is";

        $res = sql_query($query) or sqlerr(__FILE__, __LINE__);

        $arr = mysqli_fetch_row($res) or stderr("Ошибка", "Постов не найденно");

        $postcount = $arr[0];

        list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, $_SERVER["PHP_SELF"] . "?action=viewforumposts&id=$userid&");

        $res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($res) == 1)
        {
          $arr = mysqli_fetch_assoc($res);

          $subject = "<a href=user/id$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
        }
        else
            $subject = "unknown[$userid]";

        $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";

        $select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";

        $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

        $res = sql_query($query) or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($res) == 0) stderr("Ошибка", "Постов не найденно");

        stdhead("История постов");

        print("<h1>История постов пользователя $subject</h1>\n");

        if ($postcount > $perpage) echo $pagertop;

        begin_main_frame();

        begin_frame();

        while ($arr = mysqli_fetch_assoc($res))
        {
            $postid = $arr["id"];

            $posterid = $arr["userid"];

            $topicid = $arr["t_id"];

            $topicname = $arr["subject"];

            $forumid = $arr["f_id"];

            $forumname = $arr["name"];

            $dt = (get_date_time(gmtime() - $READPOST_EXPIRY));

            $newposts = 0;

            if ($arr['added'] > $dt)
                $newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;

            $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " назад)";

            print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
            $added&nbsp;--&nbsp;<b>Раздел:&nbsp;</b>
            <a href=forum.php?action=viewforum&forumid=$forumid>$forumname</a>
            &nbsp;--&nbsp;<b>Тема:&nbsp;</b>
            <a href=forum.php?action=viewtopic&topicid=$topicid>$topicname</a>
      &nbsp;--&nbsp;<b>Номер поста:&nbsp;</b>
      #<a href=forum.php?action=viewtopic&topicid=$topicid&page=p$postid#$postid>$postid</a>" .
      ($newposts ? " &nbsp;<b>(<font color=red>Новое!</font>)</b>" : "") .
            "</td></tr></table></p>\n");

            begin_table(true);

            $body = format_comment($arr["body"]);

            if (is_valid_id($arr['editedby']))
            {
                $subres = sql_query("SELECT username FROM users WHERE id=$arr[editedby]");
                if (mysqli_num_rows($subres) == 1)
                {
                    $subrow = mysqli_fetch_assoc($subres);
                    $body .= "<p><font size=1 class=small>Last edited by <a href=user/id$arr[editedby]><b>$subrow[username]</b></a> at $arr[editedat] GMT</font></p>\n";
                }
            }

            print("<tr valign=top><td class=comment>$body</td></tr>\n");

            end_table();
        }

        end_frame();

        end_main_frame();

        if ($postcount > $perpage) echo $pagerbottom;

        stdfoot();

        die;
}

//-------- Handle unknown action

if ($action != "")
	stderr($lang['error'], "Неизвестное действие.");

//-------- Any other case

stderr($lang['error'], "Неверный или отсутствующий запрос.");

?>