<?php
// index.php — пример использования Smarty с шаблоном
require_once __DIR__ . '/include/bittorrent.php';

$smarty = $GLOBALS['smarty'];

$smarty->assign('title', 'Anime Club — Главная');
$smarty->assign('description', 'Добро пожаловать на лучший торрент-трекер аниме!');

// Данные пользователя (заглушка, замени на $CURUSER при интеграции)
$smarty->assign('CURUSER', [
    'id' => 1,
    'username' => 'webnet',
    'class_name' => 'Пользователь',
    'ratio' => '∞',
    'uploaded' => 1024000,
    'downloaded' => 0,
    'pms' => 3
]);

$smarty->assign('baseurl', 'http://' . $_SERVER['HTTP_HOST']);
$smarty->assign('rss_uri', '/rss.php');
$smarty->assign('execution_time', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
$smarty->assign('php_version', phpversion());
$smarty->assign('mysql_version', $mysqli->server_info ?? 'неизвестно');

$smarty->display('header.tpl');

// Здесь может быть основной контент
echo '<div style="padding:20px; font-size:16px;">Добро пожаловать на трекер!</div>';

$smarty->display('footer.tpl');
