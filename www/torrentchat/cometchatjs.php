<?php
// Подключение конфигурации и запуск сессии ДО вывода заголовков
include_once(__DIR__ . "/config.php");

if (!defined('DO_NOT_START_SESSION') || DO_NOT_START_SESSION != '1') {
    if (session_status() === PHP_SESSION_NONE) {
        if (defined('SET_SESSION_NAME') && SET_SESSION_NAME !== '') {
            session_name(SET_SESSION_NAME);
        }
        session_start();
        file_put_contents(__DIR__ . '/../logs/cometchat_debug.log', "Session Started ID: " . session_id() . "\n", FILE_APPEND);
    }
}
// === Расширенное логирование загрузки cometchatjs.php ===
$logFile = __DIR__ . '/../logs/cometchat_debug.log';
$logData = "===== Загрузка cometchatjs.php =====\n";
$logData .= "Дата: " . date("Y-m-d H:i:s") . "\n";
$logData .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестно') . "\n";
$logData .= "USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'неизвестно') . "\n";
$logData .= "Запрошен IF_MODIFIED_SINCE: " . ($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? 'не задано') . "\n";
$logData .= "Session ID: " . (session_id() ?: 'нет сессии') . "\n";
$logData .= "CUR_SESSION_VARS:\n";
foreach ($_SESSION ?? [] as $key => $value) {
    $logData .= "  $key => " . (is_scalar($value) ? $value : json_encode($value)) . "\n";
}
$logData .= "CURUSER:\n";
if (isset($CURUSER)) {
    foreach ($CURUSER as $key => $value) {
        $logData .= "  $key => " . (is_scalar($value) ? $value : json_encode($value)) . "\n";
    }
} else {
    $logData .= "  CURUSER не определён\n";
}
$logData .= "Инициализация файлов:\n";
$logData .= "  libraries.php: " . (file_exists(__DIR__ . '/js/libraries.php') ? 'есть' : 'НЕТ') . "\n";
$logData .= "  cometchat.php: " . (file_exists(__DIR__ . '/js/cometchat.php') ? 'есть' : 'НЕТ') . "\n";
file_put_contents($logFile, $logData . "\n", FILE_APPEND);

file_put_contents(__DIR__ . '/../logs/cometchat_log.txt', date("Y-m-d H:i:s") . " Загружен cometchatjs.php от IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестно') . "\n", FILE_APPEND);
// Проверка кеша браузера и корректная отправка заголовка Last-Modified
$lastModifiedTimestamp = filemtime(__FILE__);
$lastModified = gmdate('D, d M Y H:i:s', $lastModifiedTimestamp) . ' GMT';
if (!headers_sent()) {
    header('Last-Modified: ' . $lastModified);
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModifiedTimestamp) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }
}

// Защита от отключения панели
if (defined('BAR_DISABLED') && BAR_DISABLED == 1) {
    exit;
}

// Удалена ошибка: неопределённая переменная
// if ($p_ < 1) exit; // ← УДАЛЕНО

$starttime = microtime(true);

// Определение user-agent
$useragent = $_SERVER["HTTP_USER_AGENT"] ?? '';

// Установка заголовков
if (!headers_sent()) {
    header('Content-type: application/javascript; charset=utf-8');
}

// === Проверка и инициализация переменных, если они не заданы ===
if (!isset($language))  $chat_language = 'en';
if (!isset($trayicon))  $trayicon = [];
if (!isset($plugins))   $plugins = [];
if (!isset($extensions)) $extensions = [];
if (!isset($modules))   $modules = [];
if (!isset($embedcode)) $embedcode = '';
if (!isset($theme))     $theme = 'default';
if (!isset($basedata))  $basedata = '';
if (!isset($messageBeep)) $messageBeep = '1';
if (!isset($autoPopupChatbox)) $autoPopupChatbox = '1';
if (!isset($autoPopupChatboxUsers)) $autoPopupChatboxUsers = '';
if (!isset($hideOffline)) $hideOffline = '0';
if (!isset($startOffline)) $startOffline = '0';
if (!isset($alwaysShow)) $alwaysShow = '1';

if (!isset($basedata) && isset($CURUSER['id'])) {
    $basedata = $CURUSER['id'];
    file_put_contents($logFile, "Установлен basedata из CURUSER['id']: $basedata\n", FILE_APPEND);
}

// Подключение JS-файлов
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/cometchat_debug.log';

try {
    include_once(__DIR__ . "/js/libraries.php");
    file_put_contents($logFile, "✔ libraries.php успешно подключён\n", FILE_APPEND);
} catch (Throwable $e) {
    file_put_contents($logFile, "✘ Ошибка при подключении libraries.php: " . $e->getMessage() . "\n", FILE_APPEND);
}

try {
    include_once(__DIR__ . "/js/cometchat.php");
    file_put_contents($logFile, "✔ cometchat.php успешно подключён\n", FILE_APPEND);
} catch (Throwable $e) {
    file_put_contents($logFile, "✘ Ошибка при подключении cometchat.php: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Загрузка иконок панели
foreach ($trayicon as $icon) {
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $icon['extension'] . DIRECTORY_SEPARATOR . 'init.js')) {
        include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $icon['extension'] . DIRECTORY_SEPARATOR . 'init.js';
    }
}

// Загрузка плагинов
foreach ($plugins as $plugin) {
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'init.js')) {
        include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'init.js';
    }
}

// Загрузка расширений
foreach ($extensions as $extension) {
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'init.js')) {
        include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'init.js';
    }
}
?>

<?php
file_put_contents(__DIR__ . '/../logs/cometchat_debug.log', "[cometchat] Запуск инициализации...\n", FILE_APPEND);
file_put_contents(__DIR__ . '/../logs/cometchat_debug.log', "[cometchat] jQuery загружен\n", FILE_APPEND);
file_put_contents(__DIR__ . '/../logs/cometchat_debug.log', "[cometchat] $.cometchat найден, запускаю...\n", FILE_APPEND);
echo <<<EOT
<script>
if (typeof jQuery !== 'undefined' && typeof $.cometchat !== 'undefined') {
    $(function () {
        $.cometchat();
    });
}
</script>
EOT;
?>
