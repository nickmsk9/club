<?php
require_once("include/bittorrent.php");

// Инициализация Memcached для кеша закладок (при необходимости)
global $memcache_obj;
if (!isset($memcache_obj) || !$memcache_obj instanceof Memcached) {
    $memcache_obj = new Memcached();
    if (empty($memcache_obj->getServerList())) {
        $memcache_obj->addServer('127.0.0.1', 11211);
    }
}

dbconn(false);
getlang();
loggedinorreturn();

stdhead("Закладки");
begin_main_frame();
$res = $mysqli->query("SELECT COUNT(id) FROM bookmarks WHERE userid = ".sqlesc($CURUSER["id"]));
$row = $res->fetch_array();
$count = $row[0] ?? 0;

if (!$count) {
	stdmsg($lang['error'], "У Вас нету закладок !", 'error');
} else {
?>
<table class="embedded" cellspacing="0" cellpadding="5" width="100%">
<tr><td class="colhead" align="center" colspan="12">Список закладок</td></tr>
<?

$perpage = 25;

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "bookmarks.php?");

$res = $mysqli->query("SELECT bookmarks.id AS bookmarkid, torrents.id, torrents.name, torrents.type, torrents.comments, torrents.leechers, torrents.seeders,torrents.owner, torrents.image1, torrents.owner_name, torrents.owner_class, torrents.tags, torrents.modded,
 torrents.save_as, torrents.numfiles, torrents.added,  torrents.size,
 torrents.times_completed, torrents.category FROM bookmarks INNER JOIN torrents ON bookmarks.torrentid = torrents.id WHERE bookmarks.userid = ".sqlesc($CURUSER["id"])." ORDER BY torrents.added DESC $limit") or sqlerr(__FILE__, __LINE__);

print("<tr><td class=\"index\" colspan=\"12\">");
print($pagertop);
print("</td></tr>");
bookmarktable($res);
print("<tr><td class=\"index\" colspan=\"12\">");
print($pagerbottom);
print("</td></tr>");
print("</table>");
}
end_main_frame();
stdfoot();

?>