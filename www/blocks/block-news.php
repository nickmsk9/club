<?php
if (!defined('BLOCK_FILE')) {
    header("Location: ../index.php");
    exit;
}

global $lang, $show_news;
getlang('news');

// Определение заголовка блока с учетом прав администратора
$blocktitle = $lang['block_news'];
if (get_user_class() >= UC_ADMINISTRATOR) {
    $blocktitle .= "<font class=\"small\"> - [<a class=\"altlink\" href=\"news.php\"><b>{$lang['n_create']}</b></a>]</font>";
}

// Проверка кэша для основного контента
if (!cache_check('news_main', 1200)) {
    $content = generateNewsContent();
    cache_write('news_main', $content);
} else {
    $content = cache_read('news_main');
}

/**
 * Генерация контента для блока новостей.
 *
 * @return string
 */
function generateNewsContent(): string {
    $content = '';

    // Получаем до 11 новостей (1 последнюю + 10 для списка) с кэшированием
    $allNews = getCachedData('news_block', 600, function () {
        $res = sql_query(
            "SELECT id, added, subject, body FROM news ORDER BY added DESC LIMIT 11"
        ) or sqlerr(__FILE__, __LINE__);
        return fetchAll($res);
    });

    // Формирование контента для последней новости
    if (!empty($allNews)) {
        $latest = array_shift($allNews);
        $date = date("d.m.Y", strtotime($latest['added']));
        $subject = htmlspecialchars($latest['subject']);
        $body = format_comment($latest['body']);
        $content .= "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\" id=\"no_border\">\n";
        $content .= "<span>{$date} - <b>{$subject}</b></span>\n"
            . "<span id=\"ss{$latest['id']}\" style=\"display: block;\">{$body}</span>"
            . "<br /><hr />";
        $content .= "</td></tr></table>\n";
    } else {
        $content .= "<p>Новостей пока нет.</p>\n";
    }

    // Формирование контента для списка оставшихся новостей
    if (!empty($allNews)) {
        $content .= "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\" id=\"no_border\">\n";
        foreach ($allNews as $news) {
            $nid = (int)$news['id'];
            $subject = htmlspecialchars($news['subject']);
            $added = htmlspecialchars($news['added']);
            $content .= "<a class=\"news_show\" href=\"/news_view.php?newsid={$nid}\" title=\"{$subject}\">{$subject}</a>"
                . " - <i>{$added}</i><hr>\n";
        }
        $content .= "</td></tr></table>\n";
    }

    return $content;
}

/**
 * Получение данных из кэша или их генерация.
 *
 * @param string $cacheKey Ключ кэша
 * @param int $cacheTime Время жизни кэша
 * @param callable $dataGenerator Функция для генерации данных, если кэш отсутствует
 * @return array
 */
function getCachedData(string $cacheKey, int $cacheTime, callable $dataGenerator): array {
    if (cache_check($cacheKey, $cacheTime)) {
        return cache_read($cacheKey);
    }

    $data = $dataGenerator();
    cache_write($cacheKey, $data);
    return $data;
}

/**
 * Получение всех строк результата запроса.
 *
 * @param mysqli_result $result Результат SQL-запроса
 * @return array
 */
function fetchAll(mysqli_result $result): array {
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
?>