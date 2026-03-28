<?php
require_once("include/bittorrent.php");
global $memcache_obj;
dbconn();
loggedinorreturn();

// Initialize memcached client if not already set
if (empty($memcache_obj) || !($memcache_obj instanceof Memcached)) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('127.0.0.1', 11211);
}

header ("Content-Type: text/html; charset=" . $lang['language_charset']);
// Use isset to avoid undefined index notice
$torrent = isset($_POST['torrent']) ? (int)$_POST['torrent'] : 0;
if (empty($torrent))
    die("Операция невозможна");

if(get_user_class() < UC_MODERATOR)
    die("Нет доступа");

$res = sql_query("SELECT modded, owner FROM torrents WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
$row = mysqli_fetch_assoc($res);

if ($row["modded"] == "yes")
    die("Раздача уже проверена");
$memcache_obj->delete('torrent_'.$torrent,0);
sql_query("UPDATE torrents SET modded = 'yes', modby = ".sqlesc($CURUSER["id"]).", modname = ".sqlesc($CURUSER["username"]).", modtime = '" . get_date_time() . "', added = ".sqlesc(time())." WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
sql_query("UPDATE users SET moderated = moderated + 1 WHERE id = ".sqlesc($CURUSER["id"])) or sqlerr(__FILE__,__LINE__);
$res = sql_query("SELECT modby, modname FROM torrents WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
$row = mysqli_fetch_assoc($res);
$modby   = (int)$row['modby'];
$modname = htmlspecialchars($row['modname'], ENT_QUOTES);
print "<b>Проверен? </b> - <a href=\"user/id{$modby}\">{$modname}</a>";

?>