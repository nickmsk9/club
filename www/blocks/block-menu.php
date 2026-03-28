<?php

if (!defined('BLOCK_FILE')) {
header("Location: ../index.php");
exit;
}


// Массив пунктов меню
$menuItems = [
    ['url' => '/', 'label' => 'Главная'],
    ['url' => '/browse.php', 'label' => 'Торренты'],
    ['url' => '/viewrequests.php', 'label' => 'Запросы'],
    ['url' => '/afish.php', 'label' => 'Афиша'],
    ['url' => '/forum/', 'label' => 'Форум'],
    ['url' => 'http://radio.animeshiro.lv:8000/listen.pls', 'label' => 'Аниме Радио', 'class' => 'menu-link--highlight'],
    ['url' => '/allalbum.php', 'label' => 'Фото Галерея'],
    ['url' => '/uploadapp.php', 'label' => 'Стать Аплоудером', 'class' => 'menu-link--highlight'],
    ['url' => '/rules.php', 'label' => 'Правила'],
    ['url' => '/faq.php', 'label' => 'ЧаВо'],
    ['url' => '/topten.php', 'label' => 'Топ 10'],
    ['url' => '/staff.php', 'label' => 'Персонал'],
];

// Генерация HTML меню
$content = '<ul class="menu-list">';
foreach ($menuItems as $item) {
    $url = htmlspecialchars($item['url']);
    $label = htmlspecialchars($item['label']);
    $extraClass = isset($item['class']) ? ' ' . $item['class'] : '';
    $content .= "<li><a href=\"{$url}\" class=\"menu-link{$extraClass}\">{$label}</a></li>";
}
$content .= '</ul>';

?>