<?php
if (!defined('BLOCK_FILE')) {
    header("Location: ../index.php");
    exit;
}
require_once 'include/secrets.php'; // Подключаем файл с соединением

global $CURUSER, $lang, $mcache;
$content = '';
getlang('online');
$blocktitle = $lang['block_online'];

// Получение количества гостей онлайн с кэшированием
if (cache_check("guests_online", 300)) {
    // cache_read может вернуть старый массив, убедимся, что это целое число
    $cached = cache_read("guests_online");
    $guests_count = is_numeric($cached) ? (int)$cached : 0;
} else {
    $res = sql_query("SELECT COUNT(*) AS cnt FROM guests") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $guests_count = (int)$row['cnt'];
    cache_write("guests_online", $guests_count);
}
$guests_online = number_format($guests_count);

if (cache_check("online", 300)) {
    $result = cache_read("online");
} else {
    $title_who = array();

    $dt = sqlesc(time() - 300);

    $result = sql_query("SELECT u.id,
                u.username, 
                u.class,
                u.warned,
                u.gender,
                u.enabled,
                u.parked,
                u.donor FROM users AS u WHERE u.last_access > " . sqlesc(get_date_time(time() - 300)) . " AND hiden = 'no' ORDER BY u.class DESC") or sqlerr(__FILE__, __LINE__);

    $online_cache = array();
    while ($cache_data = mysqli_fetch_assoc($result))
        $online_cache[] = $cache_data;

    cache_write("online", $online_cache);
    $result = $online_cache;
}

$friend = array();
if ($CURUSER) {
    if (false === ($row = $mcache->get_value('user_f_' . $CURUSER['id']))) {
        $res = sql_query("SELECT f.friendid FROM friends AS f WHERE f.userid= " . $CURUSER['id'] . " AND f.status = 'yes'") or sqlerr(__FILE__, __LINE__);
        $cache = array();
        while ($row = mysqli_fetch_assoc($res))
            $cache[] = $row;
        $mcache->cache_value('user_f_' . $CURUSER['id'], $cache, 300);
        $row = $cache;
    }
    foreach ($row as $arr)
        $friend[$arr['friendid']] = $arr;
}

$total = 0;
$title_who = array();
foreach ($result as $arr) {
    list($uid, $uname, $class, $warned, $gender, $enabled, $parked, $donor) = array_values($arr);

    if (!empty($uname) && isset($friend[$uid])) {
        $title_who[] = "<a href=\"user/id" . $uid . "\" class=\"online\"><span style=\"background-color:#e4fcfc;\">" . get_user_class_color($class, $uname) . get_user_icons($arr) . "</span></a>";
    } else {
        $title_who[] = "<a href=\"user/id" . $uid . "\" class=\"online\">" . get_user_class_color($class, $uname) . get_user_icons($arr) . "</a>";
    }

    $total++;
}

if ($total == "")
    $total = 0;

if (count($title_who)) {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\">" . implode(", ", $title_who) . "<hr></td></tr></table>\n";
} else {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\">" . $lang['no_online_users'] . "<hr></td></tr></table>\n";
}
$totalu = $total + $guests_count;

$content .= "<table border=\"0\" width=\"100%\"><tr><td align=\"left\" class=\"embedded\">\n";
$content .= "<span class=\"stats-class-users\">{$lang['class_users']}: {$total}</span>&nbsp;|&nbsp;\n";
$content .= "<span class=\"stats-guests\">{$lang['guests']}: {$guests_online}</span>&nbsp;|&nbsp;\n";
$content .= "<span class=\"stats-total\">{$lang['total']}: {$totalu}</span></td></tr></table>\n";

?>