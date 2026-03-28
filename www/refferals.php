<?php
require "include/bittorrent.php";
gzip();
dbconn();
loggedinorreturn();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$ref = isset($_GET['ref']) ? (int)$_GET['ref'] : 0;

if ($ref === 0) {
    stdhead('Топ 10 Реффералов');
    begin_main_frame();
    begin_frame('Топ 10 Реффералов');

    echo '<table width="100%" id="no_border" cellspacing="0" cellpadding="8">';
    echo '<tr>'
        . '<td><b>Место</b></td>'
        . '<td><b>Пользователь<font color="red">*</font></b></td>'
        . '<td><b>Реффералов<font color="red">**</font></b></td>';
    if (get_user_class() >= UC_MODERATOR) {
        echo '<td><b>Инфо</b></td>';
    }
    echo '</tr>';

    $cache_time = 3600;
    if (cache_check('ref', $cache_time)) {
        $res = cache_read('ref');
    } else {
        $sql = "SELECT users.id, users.username, users.class, users.donor, users.warned, users.enabled, users.gender, "
             . "(SELECT COUNT(*) FROM referals WHERE users.id = referals.ref) AS num_ref "
             . "FROM users ORDER BY num_ref DESC LIMIT 10";
        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $ref_cache = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ref_cache[] = $row;
        }
        cache_write('ref', $ref_cache);
        $res = $ref_cache;
    }

    $num = 0;
    foreach ($res as $arr) {
        $num++;
        $id = (int)$arr['id'];
        $ref_count = (int)$arr['num_ref'];
        if ($ref_count > 0) {
            echo '<tr>'
                . '<td align="center">' . $num . '.</td>'
                . '<td width="100%"><a href="user/id' . $id . '">'
                  . get_user_class_color($arr['class'], $arr['username'])
                  . get_user_icons($arr)
                  . '</a></td>'
                . '<td align="center">' . number_format($ref_count) . '</td>';
            if (get_user_class() >= UC_MODERATOR) {
                echo '<td><a href="/refferals.php?ref=' . $id . '">смотреть</a></td>';
            }
            echo '</tr>';
        }
    }

    $cache_file = $rootpath . 'cache/ref.cache';
    $times = file_exists($cache_file)
        ? $cache_time - (time() - filemtime($cache_file))
        : 0;
    if ($times < 0) {
        $times = 0;
    }

    if ($cache_time >= 3600*24*24) {
        $t = $times / 86400;
        $unit = 'дней';
    } elseif ($cache_time >= 3600*24) {
        $t = $times / 3600;
        $unit = 'часов';
    } elseif ($cache_time >= 3600) {
        $t = $times / 60;
        $unit = 'минут';
    } else {
        $t = $times;
        $unit = 'секунд';
    }
    $time1 = number_format($t, ($unit === 'секунд' ? 0 : 2), ',', ' ');
    $time = "Статистика обновится через {$time1} {$unit}.";

    echo '<tr>'
       . '<td id="no_border" colspan="8" height="10" align="center">'
       . '<font class="small">'
       . '<b><font color="red">*</font></b> Рефферал — это пользователь, который пригласил других пользователей, передав им свою реферальную ссылку. '
       . 'После перехода по этой ссылке к нам на сайт, реффералу начисляется один поинт. '
       . 'Свою реферальную ссылку вы можете найти в <a href="/my.php">Профиле</a>. '
       . 'Для реффералов создана отдельная система бонусов и поощрений.'
       . '</font>'
       . '</td>'
       . '</tr>';
    echo '<tr>'
       . '<td id="no_border" colspan="8" height="10" align="center">'
       . '<font class="small">'
       . '<b><font color="red">**</font></b> Данные статистичны, динамика обновления — раз в час. '
       . $time
       . '</font>'
       . '</td>'
       . '</tr>';
    echo '</table>';

} else {
    if (get_user_class() < UC_MODERATOR) {
        stderr($lang['error'], $lang['access_denied']);
        exit;
    }
    $id = $ref;
    $link = '<a href="/refferals.php"><u><i>вернуться к Топу</i></u></a>';
    stdhead('Список Реффералов');
    begin_main_frame();
    begin_frame("Список реффералов $link");

    $count = get_row_count("referals WHERE ref = $id");
    $perpage = 25;
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "/refferals.php?ref=$id&");

    if ($count) {
        echo $pagertop;
        echo '<table width="100%" id="no_border" cellspacing="0" cellpadding="10">';
        echo '<tr><td><b>IP Рефферала</b></td><td><b>Сайт откуда пришел</b></td><td><b>Дата</b></td></tr>';
        $res2 = sql_query(
            "SELECT ref, ip, from_url, added FROM referals WHERE ref = $id ORDER BY added DESC $limit"
        ) or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res2)) {
            echo '<tr>'
               . '<td>' . $row['ip'] . '</td>'
               . '<td>' . htmlspecialchars_uni($row['from_url']) . '</td>'
               . '<td>' . $row['added'] . '</td>'
               . '</tr>';
        }
        echo '</table>';
        echo $pagerbottom;
    } else {
        echo 'Нет Данных';
    }

    end_frame();
    end_main_frame();
    stdfoot();
}
?>