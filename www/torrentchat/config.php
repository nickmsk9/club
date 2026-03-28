<?php
// Настройки сессии для CometChat в PHP 8
if (session_status() === PHP_SESSION_NONE) {
    session_name('cometchatsession');
    session_start();
}
// Имя сессии для интеграции CometChat
define('SET_SESSION_NAME', session_name());
// Разрешить запуск сессии в cometchatjs.php
define('DO_NOT_START_SESSION', '0');
// === Конфигурация CometChat ===

// Основной URL для скриптов
define('BASE_URL', '/torrentchat/');

// Язык панели чата
$chat_language = 'en';

// Иконки в панели (по умолчанию пусто)
$trayicon = [];

// Плагины, которые будут подключены
$plugins = [];

// Расширения панели
$extensions = [];

// Модули чата
$modules = [];

// Код вставки (если нужен)
$embedcode = '';

// Имя темы
$theme = 'default';

// Данные авторизации (если используются)
$basedata = '';

// Настройки отображения и звуков
$messageBeep = '1';
$autoPopupChatbox = '1';
$autoPopupChatboxUsers = '';
$hideOffline = '0';
$startOffline = '0';
$alwaysShow = '1';

// Подключение кастомной интеграции
include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'integration.php';
?>
