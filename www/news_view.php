<?php
include('include/bittorrent.php');
global $show_news, $memcache_obj, $mysqli;

// Подключение к БД и включение gzip
dbconn();
gzip();

// Получаем ID новости и убеждаемся, что это целое число
$newsid = isset($_GET["newsid"]) ? (int)$_GET["newsid"] : 0;

// Инициализируем Memcached при необходимости
if (!isset($memcache_obj)) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('localhost', 11211);
}

// Пытаемся получить новость из Memcached
$mem_get_news = $memcache_obj->get('news_' . $newsid);

if ($mem_get_news === false) {
    // Если нет в кэше — берём из БД
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, subject, body, added FROM news WHERE id = ?");
    $stmt->bind_param("i", $newsid);
    $stmt->execute();
    $res = $stmt->get_result();
    $arr = $res->fetch_assoc();
    $stmt->close();

    if (!$arr) {
        stderr("Ошибка", "Новость не найдена.");
    }

    // Сохраняем в кэш на 1 час
    $memcache_obj->set('news_' . $newsid, $arr, 3600);
} else {
    $arr = $mem_get_news;
}

// Подготовка данных к отображению
$newsid = $arr["id"];
$body = $arr["body"];
$subject = $arr["subject"];
$added = $arr["added"] . " GMT (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"])) . " назад)";

// Отображение страницы
stdhead("Новости - " . $subject);
begin_main_frame();
begin_frame($subject);

print format_comment($body) . "<br /><div align='right'><i>Добавлена $added</i></div>\n";
print "<br /><br /><tr><td class='embeded'><b>Другие новости</b><br />";
show_news();
print "</td></tr>";

end_frame();
end_main_frame();
stdfoot();
?>