<?

require "include/bittorrent.php";
global $memcache_obj;
if ($memcache_obj instanceof Memcached) {
    $memcache_obj->delete('bans', 0);
}

dbconn(false);

loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
    die;
$remove = isset($_GET['remove']) ? intval($_GET['remove']) : 0;
if (is_valid_id($remove)) {
    $res = sql_query("SELECT first, last FROM bans WHERE id=$remove") or sqlerr(__FILE__, __LINE__);
    $ip = mysql_fetch_array($res);
    $first = long2ip($ip["first"]);
    $last = long2ip($ip["last"]);
    sql_query("DELETE FROM bans WHERE id=$remove") or sqlerr(__FILE__, __LINE__);
    write_log("Бан IP адреса номер $remove (" . ($first == $last ? $fisrt : "адреса с $first по $last") . ") был убран пользователем $CURUSER[username].", "", "bans");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_MODERATOR) {
    $first = trim($_POST["first"]);
    $last = trim($_POST["last"]);
    $comment = trim($_POST["comment"]);
    if (!$first || !$last || !$comment)
        stderr($lang['error'], $lang['missing_form_data']);
    $first = ip2long($first);
    $last = ip2long($last);
    if ($first == -1 || $last == -1)
        stderr($lang['error'], $lang['invalid_ip']);
    $comment = sqlesc($comment);
    $added = sqlesc(get_date_time());
    sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES($added, $CURUSER[id], $first, $last,$comment)") or sqlerr(__FILE__, __LINE__);
    write_log("IP адреса от " . long2ip($first) . " до " . long2ip($last) . " были забанены пользователем $CURUSER[username].", "", "bans");
    header("Location: $DEFAULTBASEURL$_SERVER[REQUEST_URI]");
    die;
}

gzip();

$res = sql_query("SELECT bans.*, users.username, users.id as uid FROM bans LEFT JOIN users ON bans.addedby = users.id WHERE users.id <> 22 ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

stdhead($lang['bans']);
begin_main_frame();
if (mysqli_num_rows($res) == 0)
    print("<p align=\"center\"><b>" . $lang['nothing_found'] . "</b></p>\n");
else {
    print("<table border=0 align=center cellspacing=0 cellpadding=5>\n");
    // begin_table();
    print("<tr><td class=\"colhead\" colspan=\"7\">Забаненые IP</td></tr>\n");
    print("<tr><td class=\"colhead\">Добавлен</td><td class=\"colhead\" align=\"left\">Первый IP</td><td class=\"colhead\" align=\"left\">Последний IP</td>" .
        "<td class=\"colhead\" align=\"left\">Кем</td><td class=\"colhead\" align=\"left\">Комментарий</td><td class=\"colhead\">До</td><td class=\"colhead\">Снять бан</td></tr>\n");

    while ($arr = mysqli_fetch_assoc($res)) {

        $arr["first"] = long2ip($arr["first"]);
        $arr["last"] = long2ip($arr["last"]);
        if ($arr['until'] == "0000-00-00 00:00:00") $arr['until'] = "&nbsp;";
        print("<tr><td class=\"row1\">$arr[added]</td><td class=\"row1\" align=\"left\">$arr[first]</td><td  class=\"row1\" align=\"left\">$arr[last]</td><td  class=\"row1\" align=\"left\"><a href=\"user/id$arr[addedby]\">$arr[username]" .
            "</a></td><td  class=\"row1\" align=\"left\">" . $arr["comment"] . "</td><td class=\"row1\">$arr[until]</td><td  class=\"row1\"><a href=\"bans.php?remove=$arr[id]\">Снять бан</a></td></tr>\n");
    }
    print("</table>");
}

if (get_user_class() >= UC_MODERATOR) {
    //print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<br />\n");
    print("<form method=\"post\" action=\"bans.php\">\n");
    begin_table();
    print("<tr><td class=\"colhead\" colspan=\"2\">Забанить IP адрес</td></tr>");
    print("<tr><td class=\"rowhead\">Первый IP</td><td class=\"row1\"><input type=\"text\" name=\"first\" size=\"40\"/></td></tr>\n");
    print("<tr><td class=\"rowhead\">Последний IP</td><td class=\"row1\"><input type=\"text\" name=\"last\" size=\"40\"/></td></tr>\n");
    print("<tr><td class=\"rowhead\">Комментарий</td><td class=\"row1\"><input type=\"text\" name=\"comment\" size=\"40\"/></td></tr>\n");
    print("<tr><td class=\"row1\" align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Забанить\" class=\"btn\"/></td></tr>\n");
    end_table();
    print("</form>\n");
}
end_main_frame();
stdfoot();
