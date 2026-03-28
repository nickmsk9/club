<?php

include_once dirname(__FILE__) . "/cometchat_init.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/secrets.php");

$response = array();
$messages = array();

// Проверка языка статусов
$status = [];
$status['available']  = $language[30] ?? 'Доступен';
$status['busy']       = $language[31] ?? 'Занят';
$status['offline']    = $language[32] ?? 'Не в сети';
$status['invisible']  = $language[33] ?? 'Невидим';
$status['away']       = $language[34] ?? 'Отошел';

// Безопасное получение ID
$fetchid = isset($_REQUEST['userid']) ? (int)$_REQUEST['userid'] : 0;

$time = getTimeStamp();
$sql = getUserDetails($fetchid);

if ($guestsMode && $fetchid >= 10000000) {
    $sql = getGuestDetails($fetchid);
}

// Выполнение запроса
$query = mysqli_query($mysqli, $sql);

if (defined('DEV_MODE') && DEV_MODE == '1') {
    echo mysqli_error($mysqli);
}

$chat = mysqli_fetch_array($query);

// Статус пользователя
if ((($time - processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') {
    if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
        $chat['status'] = 'available';
    }
} else {
    $chat['status'] = 'offline';
}

// Если сообщение не задано, берем из статуса
if (empty($chat['message'])) {
    $chat['message'] = $status[$chat['status']] ?? '';
}

// Ссылки и аватар
$link = getLink($chat['userid']);
$avatar = getAvatar($chat['avatar']);

// Форматирование имени
if (function_exists('processName')) {
    $chat['username'] = processName($chat['username']);
}

$response = array(
    'id' => $chat['userid'],
    'n'  => $chat['username'],
    's'  => $chat['status'],
    'm'  => $chat['message'],
    'a'  => $avatar,
    'l'  => $link
);

// Безопасная отправка заголовков
if (!headers_sent()) {
    header('Content-type: application/json; charset=utf-8');
}

// Поддержка JSONP
$callback = $_GET['callback'] ?? '';
if (!empty($callback)) {
    echo $callback . '(' . json_encode($response) . ')';
} else {
    echo json_encode($response);
}

exit;