<?php
require_once("include/bittorrent.php");
GLOBAL $memcache_obj;
dbconn();

loggedinorreturn();

// Initialize memcached client if not already set
if (empty($memcache_obj) || !($memcache_obj instanceof Memcached)) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('127.0.0.1', 11211);
}

header ("Content-Type: text/html; charset=" . $lang['language_charset']);
$torrent = (int) $_POST["torrent"];
if (empty($torrent))
    die("Операция невозможна");

$memcache_obj->delete('torrent_'.$torrent, 0);
@sql_query("UPDATE torrents SET checkcomm = 'no' WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
print "<b>вот и не буду</b>";

?>