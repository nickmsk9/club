<?php
// Защита от прямого доступа
if (!defined("IN_TRACKER")) {
    die("Hacking attempt!");
}

// === ПОДКЛЮЧЕНИЕ ОСНОВНЫХ ФАЙЛОВ ===
require_once($rootpath . 'include/functions.php');         // Основные функции
require_once($rootpath . 'include/init.php');              // Инициализация БД
require_once($rootpath . 'include/global.php');            // Глобальные переменные
require_once($rootpath . 'include/config.php');            // Конфигурация трекера
require_once($rootpath . 'include/blocks.php');            // Система блоков
require_once($rootpath . 'include/secrets.php');           // Пароли/ключи

// === КЛАССЫ ===
require_once($rootpath . 'include/class/class.cache.php');
require_once($rootpath . 'include/class/class.bbcode.php');
require_once($rootpath . 'include/class/class.bookmarks.php');
require_once($rootpath . 'include/class/class.torrenttable.php');
require_once($rootpath . 'include/class/class.timeago.php');
require_once($rootpath . 'include/class/class.subscribetable.php');
require_once($rootpath . 'include/class/class.commentable.php');
require_once($rootpath . 'include/class/class.commentable_ajax.php');
require_once($rootpath . 'include/class/class.mcache.php');

// Инициализация кеша
$mcache = new CACHE();

// === КОНСТАНТЫ СИСТЕМЫ ===
define("BETA", 0); // Режим beta
define("BETA_NOTICE", "<br />Внимание: Данный билд — Release Candidate 0! Возможны ошибки.");
define("DEBUG_MODE", 1); // Показывать SQL-запросы внизу страницы
define("VERSION", "<a href=\"/\" target=\"_blank\" style=\"cursor: help;\" title=\"WNE BTTS Beta 2009\" class=\"copyright\">WNE BTTS</a> v 1.830 | <a class=\"copyright\" href=\"{$DEFAULTBASEURL}/sitemap.php\" title='Карта сайта'>Карта сайта</a>");
define("TBVERSION", "<noindex><a href=\"http://www.tbdev.net\" target=\"_blank\" style=\"cursor: help;\" title=\"Общедоступная OpenSource база использованная для этого движка\" class=\"copyright\">TBDev</a></noindex> v2.0 | " . VERSION);

// === УДАЛЕНИЕ УСТАРЕВШИХ PHP ГЛОБАЛОВ ===
unset($HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES);

// === Обработка входящих данных — рекомендуем обрабатывать данные там, где они используются, а не глобально ===
// Если нужна глобальная фильтрация, сделайте свою функцию очистки, например:
function sanitize_input(array &$data): void {
    foreach ($data as $key => &$value) {
        if (is_array($value)) {
            sanitize_input($value);
        } else {
            // Лучше не использовать addslashes, а делать htmlspecialchars для вывода в HTML
            // или подготовленные запросы для БД
            $value = trim($value);
        }
    }
}

sanitize_input($_GET);
sanitize_input($_POST);
sanitize_input($_COOKIE);
?>