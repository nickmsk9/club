<?php
if (!defined('IN_TRACKER'))
    die('Hacking attempt!');

// Подключаем файл с настройками доступа к БД
require_once(__DIR__ . '/secrets.php');

function docleanup()
{
    global $mysqli, $torrent_dir, $signup_timeout, $max_dead_torrent_time, $autoclean_interval, $points_per_cleanup, $lang, $dooptimizedb;

    @set_time_limit(0);
    @ignore_user_abort(1);

    $deadtime = deadtime();
    $mysqli->query("DELETE FROM peers WHERE last_action < $deadtime");

    $deadtime -= $max_dead_torrent_time;

    $torrents = array();
    $res = $mysqli->query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
    while ($row = $res->fetch_assoc()) {
        $key = ($row["seeder"] == "yes") ? "seeders" : "leechers";
        $torrents[$row["torrent"]][$key] = $row["c"];
    }

    $res = $mysqli->query("SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
    while ($row = $res->fetch_assoc()) {
        $torrents[$row["torrent"]]["comments"] = $row["c"];
    }

    $fields = explode(":", "comments:leechers:seeders");
    $res = $mysqli->query("SELECT id, seeders, leechers, comments FROM torrents");
    while ($row = $res->fetch_assoc()) {
        $id = $row["id"];
        $torr = isset($torrents[$id]) ? $torrents[$id] : array();
        foreach ($fields as $field) {
            if (!isset($torr[$field]))
                $torr[$field] = 0;
        }
        $update = array();
        foreach ($fields as $field) {
            if ($torr[$field] != $row[$field])
                $update[] = "$field = " . $torr[$field];
        }
        if (count($update))
            $mysqli->query("UPDATE torrents SET " . implode(", ", $update) . " WHERE id = $id");
    }

    $secs = 5 * 86400;
    $dt = sqlesc(time() - $secs);
    $mysqli->query("DELETE FROM shoutbox WHERE date < $dt");

    // Очистка временных банов
    $mysqli->query("DELETE FROM bans WHERE until<>'0000-00-00 00:00:00' AND until < NOW()");

    // Пересчёт количества тэгов
    $mysqli->query('UPDATE tags AS t SET t.howmuch = (SELECT COUNT(*) FROM torrents AS ts WHERE ts.tags LIKE CONCAT(\'%\', t.name, \'%\') AND ts.category = t.category)');
    $mysqli->query('DELETE FROM tags WHERE howmuch = 0');

    // Пересчёт постов и топиков на форуме
    $forums = $mysqli->query("SELECT id FROM forums");
    while ($forum = $forums->fetch_assoc()) {
        $postcount = 0;
        $topiccount = 0;
        $topics = $mysqli->query("SELECT id FROM topics WHERE forumid=" . $forum['id']);
        while ($topic = $topics->fetch_assoc()) {
            $res = $mysqli->query("SELECT count(*) FROM posts WHERE topicid=" . $topic['id']);
            $arr = $res->fetch_row();
            $postcount += $arr[0];
            ++$topiccount;
        }
        $mysqli->query("UPDATE forums SET postcount=$postcount, topiccount=$topiccount WHERE id=" . $forum['id']);
    }

    // Проверка и оптимизация таблиц запускаются только во время cleanup,
    // а не при каждом подключении файла. Это убирает сильные тормоза
    // при первом открытии сайта после старта контейнера.
    if (!empty($dooptimizedb)) {
        $alltables = $mysqli->query("SHOW TABLES");
        while ($table = $alltables->fetch_assoc()) {
            foreach ($table as $tablename) {
                $tablename = trim((string)$tablename);
                if ($tablename === '') {
                    continue;
                }

                $sql = "REPAIR TABLE `" . $mysqli->real_escape_string($tablename) . "`";
                @$mysqli->query($sql) or die("<b>Что-то пошло не так!</b><br />Запрос: $sql<br />Ошибка: (" . $mysqli->errno . ") " . htmlspecialchars($mysqli->error));
            }
        }
        $alltables->free();

        $alltables = $mysqli->query("SHOW TABLES");
        while ($table = $alltables->fetch_assoc()) {
            foreach ($table as $tablename) {
                $tablename = trim((string)$tablename);
                if ($tablename === '') {
                    continue;
                }

                $sql = "OPTIMIZE TABLE `" . $mysqli->real_escape_string($tablename) . "`";
                @$mysqli->query($sql) or die("<b>Что-то пошло не так!</b><br />Запрос: $sql<br />Ошибка: (" . $mysqli->errno . ") " . htmlspecialchars($mysqli->error));
            }
        }
        $alltables->free();
    }
}

// Проверка и оптимизация таблиц
write_log("Очистка системы была успешно произведена @ " . date("F j, Y, g:i a"), "green", "system");
