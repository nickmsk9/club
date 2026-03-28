<?php
global $DEFAULTBASEURL;
// Проверяем, определена ли константа BLOCK_FILE
if (!defined('BLOCK_FILE')) {
    // Если константа не определена, перенаправляем на главную страницу
    header("Location: ../index.php");
    exit;
}

// Объявляем глобальные переменные
global $CURUSER, $lang, $mcache, $friendly_title;

// Загружаем языковые настройки для раздела 'rel'
getlang('rel');
$blocktitle = $lang['block_rel'];

// Получаем номер страницы из GET-параметра, по умолчанию 1
$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;

// Проверяем, есть ли данные в кэше
if (false === ($count = $mcache->get_value('release'))) {
    // Если данных нет, выполняем запрос к базе данных
    $res1 = sql_query("SELECT COUNT(*) FROM torrents WHERE category <> 6 AND modded = 'yes'");
    $row1 = mysqli_fetch_array($res1); // Используем mysqli вместо устаревшего mysql
    $count = $row1[0];
    // Кэшируем результат
    $mcache->cache_value('release', $count, 0, rand(1000, 3000));
}

// Количество элементов на странице
$perpage = 7;

// Генерация пагинации
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "index.php?");
$content = $pagertop;
$content .= "<table cellspacing=\"0\" cellpadding=\"5\" width=\"100%\" id=\"no_border\">";

// Инициализация массива для хранения данных
$query = [];

// Выполняем запрос к базе данных
$res = sql_query("SELECT torrents.* FROM torrents WHERE category <> 6 AND modded = 'yes' ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res)) {
    $query[] = $row; // Заполняем массив данными
}

// Обрабатываем каждую запись
foreach ($query as $release) {
    $id = $release["id"];
    // Проверяем, есть ли описание в кэше
    if (false === ($descr = $mcache->get_value('torrent_desc' . $id))) {
        $descr1 = format_comment($release["descr"]);
        $descr2 = explode("<u><b>Техданные:</b></u>", $descr1);
        $descr = $descr2[0];
        // Кэшируем описание
        $mcache->cache_value('torrent_desc' . $id, $descr, rand(1000, 3000));
    }

    $torname = htmlspecialchars_uni($release["name"]);
    $uprow = isset($release["owner_name"]) ? ("<a href=user/id" . $release["owner"] . ">" . get_user_class_color($release["owner_class"], htmlspecialchars_uni($release["owner_name"])) . "</a>") : "<i>Скоро сделаем и будет видно :)</i>";

    $content .= "<tr><td id=\"no_border\">";
    $content .= "<table width=\"100%\" class=\"main\" id=\"no_border\" cellspacing=\"0\" cellpadding=\"10\">";
    $content .= "<a href=\"" . $DEFAULTBASEURL . "/details/id" . $release["id"] . "-" . friendly_title($release['name']) . "\" title=\"Скачать аниме " . $release["name"] . " бесплатно\"><h2>" . $torname . "</h2></a>";
    $content .= "<td>";

    $img1 = !empty($release["image1"]) ? "<img style='border:0; width:230px;' class='latest-posts' src='" . $DEFAULTBASEURL . "/timthumb.php?src=" . $release["image1"] . "&w=230&zc=1&q=90' alt='" . $torname . "'/>" : "";

    $content .= "<div style=\"margin: 3px 2px; float: left;\">$img1";
    $content .= "<br><br><div id=\"releases\" style=\"border: 1px dashed #ddd;  background: #f7f7f7; margin: 0 0 5px 0; padding: 3px;\">
        <b>" . $lang['uped'] . "</b>$uprow<br>
        <b>" . $lang['size'] . "</b>" . mksize($release["size"]) . "<br>
        <b style='color: #0a0;'>" . $lang['seederz'] . "</b>" . $release["seeders"] . "<br>
        <b style='color: #a00;'>" . $lang['leecherz'] . "</b>" . $release["leechers"] . "<br>
        <b>" . $lang['downloaded'] . "</b>" . $release["times_completed"] . " раз(а)</div></div>";

    $content .= "<div style='margin-left:239px;'>" . $descr . "<br></div>
        <a href=\"" . $DEFAULTBASEURL . "/details/id" . $release["id"] . "-" . friendly_title($release['name']) . "\" alt=\"" . $release["name"] . "\" title=\"Скачать аниме " . $release["name"] . " бесплатно\">скачать аниме</a>";
    $content .= "</td></table>";
    $content .= "</td></tr>";
}

$content .= "</table>";
$content .= $pagerbottom;
$content .= "<br /><hr />";

// Подключаем функцию для работы с облаком тегов
require_once("include/cloud_func.php");
$content .= cloud('11', '26', true); // Здесь 100% - ширина облака, 190 - высота, 8 и 12 диапазон размеров шрифтов в пикселах

?>