<?php

include_once dirname(__FILE__) . "/cometchat_init.php";

// Безопасная инициализация переменных
$userid = $userid ?? 0;
$bannedUserIDs = $bannedUserIDs ?? [];
$callback = $_GET['callback'] ?? '';

$return = 0;

// Проверка, авторизован ли пользователь
if ($userid > 0) {
    $return = 1;
}

// Проверка, не забанен ли пользователь
if (!empty($userid) && in_array($userid, $bannedUserIDs)) {
    $return = 0;
}

// Возможность модифицировать результат через хук
if (function_exists('hooks_displaybar')) {
    $return = hooks_displaybar($return);
}

// Вывод результата с поддержкой JSONP
if (!empty($callback)) {
    echo $callback . '(' . json_encode($return) . ')';
} else {
    echo json_encode($return);
}
exit;