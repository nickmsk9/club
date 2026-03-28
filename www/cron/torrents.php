<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/include/bittorrent.php';
dbconn();

$dbname = $mysql_db;
global $torrent_dir, $signup_timeout, $max_dead_torrent_time, $autoclean_interval, $points_per_cleanup, $lang, $dooptimizedb, $dbname;

set_time_limit(0);
ignore_user_abort(true);

do {
    $res = sql_query("SELECT id FROM torrents") or sqlerr(__FILE__, __LINE__);
    $ar = array();
    while ($row = mysqli_fetch_array($res)) {
        $id = $row[0];
        $ar[$id] = 1;
    }

    if (!count($ar)) {
        break;
    }

    $dp = opendir($torrent_dir);
    if (!$dp) {
        break;
    }

    $ar2 = array();
    while (($file = readdir($dp)) !== false) {
        if (!preg_match('/^(\d+)\.torrent$/', $file, $m)) {
            continue;
        }
        $id = $m[1];
        $ar2[$id] = 1;
        if (isset($ar[$id]) && $ar[$id]) {
            continue;
        }
        $ff = $torrent_dir . "/$file";
        unlink($ff);
    }
    closedir($dp);

    if (!count($ar2)) {
        break;
    }

    $delids = array();
    foreach (array_keys($ar) as $k) {
        if (isset($ar2[$k]) && $ar2[$k]) {
            continue;
        }
        $delids[] = $k;
        unset($ar[$k]);
    }
    if (count($delids)) {
        sql_query("DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")") or sqlerr(__FILE__, __LINE__);
    }

    $res = sql_query("SELECT torrent FROM peers GROUP BY torrent") or sqlerr(__FILE__, __LINE__);
    $delids = array();
    while ($row = mysqli_fetch_array($res)) {
        $id = $row[0];
        if (isset($ar[$id]) && $ar[$id]) {
            continue;
        }
        $delids[] = $id;
    }
    if (count($delids)) {
        sql_query("DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")") or sqlerr(__FILE__, __LINE__);
    }

    sleep(5);

    $res = sql_query("SELECT torrent FROM files GROUP BY torrent") or sqlerr(__FILE__, __LINE__);
    $delids = array();
    while ($row = mysqli_fetch_array($res)) {
        $id = $row[0];
        if (!isset($ar[$id]) || !$ar[$id]) {
            $delids[] = $id;
        }
    }
    if (count($delids)) {
        sql_query("DELETE FROM files WHERE torrent IN (" . join(", ", $delids) . ")") or sqlerr(__FILE__, __LINE__);
    }
} while (0);

write_log("Очистка [b]торрентов[/b] была успешно произведена @ " . date("F j, Y, g:i a") . "", "", "system");
?>