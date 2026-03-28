<?php
require_once("include/bittorrent.php");

$logFile = ROOT_PATH . "logs/login_debug.log";
function write_debug_log($message) {
    global $logFile;
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, "$timestamp $message\n", FILE_APPEND);
}

// 1. Проверка данных формы
if (!mkglobal("username:password")) {
    write_debug_log("Ошибка: данные формы username/password отсутствуют.");
    die("Недопустимый запрос");
}

dbconn();
$password = $_POST['password'];
$username = $_POST['username'];

function bark($text = "Имя пользователя или пароль неверны") {
    write_debug_log("Остановка авторизации: $text");
    stderr("Ошибка входа", $text);
}

// 2. Memcached
$memcachedAvailable = class_exists('Memcached') && isset($memcache_obj) && $memcache_obj instanceof Memcached;
$userCacheKey = 'user_auth_' . md5($username);
$row = null;

if ($memcachedAvailable) {
    $row = $memcache_obj->get($userCacheKey);
    write_debug_log("Поиск пользователя в Memcached: " . ($row ? "Найден" : "Не найден"));
}

// 3. Запрос из базы
if (!$row) {
    $res = sql_query("SELECT id, passhash, secret, enabled, status, last_login, language, ip FROM users WHERE username = " . sqlesc($username));
    $row = mysqli_fetch_assoc($res);
    write_debug_log($row ? "Пользователь найден в БД: ID {$row['id']}" : "Пользователь не найден в БД");

    if ($row && $memcachedAvailable) {
        $memcache_obj->set($userCacheKey, $row, 300);
        write_debug_log("Пользователь закеширован в Memcached");
    }
}

if (!$row) {
    bark("Вы не зарегистрированы в системе.");
}

// 4. Очистка кеша
if ($memcachedAvailable) {
    $memcache_obj->delete('users_' . $row['id']);
    $memcache_obj->delete($userCacheKey);
    write_debug_log("Удаление кеша пользователя");
}

// 5. Проверка частоты логина
sql_query("UPDATE users SET last_login=NOW() WHERE id = {$row['id']} LIMIT 1");
$ip = getip();
$msg = "Попытка входа с IP $ip, пароль: [" . htmlspecialchars($password) . "]";
write_debug_log("IP: $ip | Статус: {$row['status']} | Enabled: {$row['enabled']}");

if (time() - strtotime($row["last_login"]) < 5) {
    sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject) VALUES(0, 0, {$row['id']}, '" . get_date_time() . "', " . sqlesc($msg) . ", 'Повторный вход')");
    bark("Последняя попытка входа менее 5 секунд назад.");
}

// 6. Статус
if ($row["status"] == 'pending') {
    bark("Аккаунт не активирован.");
}

// 7. Проверка пароля
$expected = md5($row["secret"] . $password . $row["secret"]);
write_debug_log("Ожидаемый passhash: $expected | Из базы: {$row['passhash']}");

if ($row["passhash"] !== $expected) {
    sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject) VALUES(0, 0, {$row['id']}, '" . get_date_time() . "', " . sqlesc($msg) . ", 'Неверный пароль')");
    bark("Неверный пароль.");
}

// 8. Отключён?
if ($row["enabled"] == "no") {
    bark("Этот аккаунт отключен.");
}

// 9. Проверка IP и пиров
$peers = sql_query("SELECT COUNT(*) FROM peers WHERE userid = {$row['id']}");
$num = mysqli_fetch_row($peers);
write_debug_log("Пиров: {$num[0]} | IP в БД: {$row['ip']}");

if ($num[0] > 0 && $row['ip'] !== $ip && $row['ip']) {
    bark("Пользователь уже активен с другого IP.");
}

// 10. Очистка старых куки
setcookie('uid', '', time() - 3600, '/');
setcookie('pass', '', time() - 3600, '/');
setcookie('lang', '', time() - 3600, '/');
write_debug_log("Старые куки удалены");

// 11. Установка новых куки
logincookie($row['id'], $row['passhash'], $row['language']);
write_debug_log("Куки установлены: uid={$row['id']}, lang={$row['language']}");

// 12. Успешный логин
write_debug_log("Успешный вход: пользователь ID {$row['id']} ($username)");

// 13. Редирект
if (!empty($_POST["returnto"])) {
    header("Location: $DEFAULTBASEURL/" . $_POST["returnto"]);
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location:" . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: $DEFAULTBASEURL/");
}
exit;
?>