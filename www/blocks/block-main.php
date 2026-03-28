<?php
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $lang, $show_news;

$content = "<link rel=\"stylesheet\" href=\"css/main.css\" type=\"text/css\">\n";
$content .= "<script language=\"JavaScript\" src=\"js/main.js\" type=\"text/javascript\"></script>\n";
$content .= "<div id=\"tabs\">\n";
$content .= "<span class=\"tab active\" id=\"news\">Новости</span>\n";
$content .= "<span class=\"tab\" id=\"reliases\">Новинки</span>\n";
$content .= "<span class=\"tab\" id=\"topusers\">Герои</span>\n";
$content .= "<span class=\"tab\" id=\"forum\">Форум</span>\n";

if (get_user_class() >= UC_UPLOADER) {
    $content .= "<span class=\"tab\" id=\"stats\">Статистика</span>\n";
    $content .= "<span class=\"tab\" id=\"last24\">За 24 часа</span>\n";
}
$content .= "<span class=\"tab\" id=\"topten\">Топ 10</span>\n";
$content .= "<span id=\"loading\"></span>\n";
$content .= "<div id=\"body\">\n";

///////////////////////////////////////////////////

// Подключение к базе данных
include 'include/secrets.php'; // Убедитесь, что путь правильный
if (!isset($mysqli)) {
    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
}

if ($mysqli->connect_error) {
    error_log("Ошибка подключения к базе данных: " . $mysqli->connect_error);
    return;
}

// Проверка кеша
if (cache_check("news", 600)) {
    $res = cache_read("news");
} else {
    // Выполнение запроса к базе данных
    $query = "SELECT id, added, subject FROM news ORDER BY id DESC LIMIT 10";
    $result = $mysqli->query($query);

    if (!$result) {
        error_log("Ошибка запроса к news: " . $mysqli->error);
        return;
    }

    $news_cache = [];
    while ($cache_data = $result->fetch_assoc()) {
        $news_cache[] = $cache_data;
    }

    // Сохранение данных в кеше
    cache_write("news", $news_cache);
    $res = $news_cache;
}

$content .= "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\" id=\"no_border\">\n";

foreach ($res as $arr) {
    $newsid = (int)$arr["id"];
    $subject = $arr["subject"];
    $subject = htmlspecialchars($subject);
    $added = htmlspecialchars($arr["added"]);
    $content .= "<a class=\"news_show\" href=\"/news_view.php?newsid=" . $newsid . "\" title=\"" . $subject . "\">" . $subject . "</a> - <i>" . $added . "</i><hr>\n";
}

$content .= "</td></tr></table>\n";

$content .= "<div id=\"reliases_body\" style=\"display:none\"></div>";

/////////////////////////////////////////////////////////
$content .= "</div>\n";
$content .= "</div>\n";

// Закрытие соединения с базой данных
$mysqli->close();


?>