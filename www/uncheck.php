<?php
require_once("include/bittorrent.php");
global $memcache_obj;
dbconn();
loggedinorreturn();

header ("Content-Type: text/html; charset=" . $lang['language_charset']);
$torrent = (int) $_POST["torrent"];
if (empty($torrent))
    die("Операция невозможна");

if(get_user_class() < UC_MODERATOR)
    die("Нет доступа");

$res = sql_query("SELECT modded,  modby ,owner FROM torrents WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
$row = mysql_fetch_assoc($res);
$moder = $row['modby'];
if ($row["modded"] == "no")
    die("Раздача уже отправлена на проверку повторно !");
$memcache_obj->delete('torrent_'.$torrent, 0);
sql_query("UPDATE torrents SET modded = 'no', modby = ' ', modname = ' ', modtime = ' ' WHERE id = ".sqlesc($torrent)) or sqlerr(__FILE__,__LINE__);
sql_query("UPDATE users SET moderated = moderated - 1 WHERE id = ".sqlesc($moder)) or sqlerr(__FILE__,__LINE__);

print "Раздача отправлена на проверку !";


?>