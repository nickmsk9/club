<?php
require "include/bittorrent.php";
global $memcache_obj, $CURUSER, $lang;

dbconn(false);
loggedinorreturn();

function puke($text = "Вы забыли что-то?") {
    global $lang;
    stderr($lang['error'], $text);
}

function barf($text = "Пользователь удален") {
    global $lang;
    stderr($lang['success'], $text);
}

if (get_user_class() < UC_MODERATOR) puke($lang['access_denied']);

$action = $_POST['action'] ?? '';

if ($action === "edituser") {
    $userid = (int)($_POST["userid"] ?? 0);
    if (!is_valid_id($userid)) puke("Неверный ID пользователя");

    $res = sql_query("SELECT * FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) puke("Пользователь не найден");

    $updateset = [];
    $modcomment = $arr['modcomment'] ?? '';

    $curclass = (int)$arr["class"];
    $class = (int)($_POST["class"] ?? 0);
    if (!is_valid_user_class($class) || $curclass >= get_user_class() || $class >= get_user_class())
        puke("Недостаточно прав для изменения класса пользователя");

    $title = trim($_POST["title"] ?? '');
    $avatar = trim($_POST["avatar"] ?? '');
    $donate = trim($_POST["donate"] ?? '');
    $bonus = (int)($_POST["bonus"] ?? 0);
    // Гарантируем, что бонус в допустимом диапазоне для INT
    $bonus = max(0, min($bonus, 2147483647));
    $last_upload = trim($_POST["last_upload"] ?? '');
    $enabled = $_POST["enabled"] ?? 'yes';
    $warned = $_POST["warned"] ?? 'no';
    $chat_ban = $_POST["chat_ban"] ?? 'no';
    $warnlength = (int)($_POST["warnlength"] ?? 0);
    // Гарантируем неотрицательное значение warnlength
    $warnlength = max(0, $warnlength);
    $chat_ban_length = (int)($_POST["chat_ban_length"] ?? 0);
    // Гарантируем неотрицательное значение chat_ban_length
    $chat_ban_length = max(0, $chat_ban_length);
    $warnpm = trim($_POST["warnpm"] ?? '');
    $chat_ban_pm = trim($_POST["chat_ban_pm"] ?? '');
    $donor = $_POST["donor"] ?? 'no';
    $upload = $_POST["upload"] ?? 'yes';
    $karma = isset($_POST["karma"]) && $CURUSER["id"] == 22 ? (int)$_POST["karma"] : $arr["karma"];
    // Гарантируем, что karma не превышает диапазон INT
    $karma = max(-2147483648, min($karma, 2147483647));
    $uploadtoadd = (float)($_POST["amountup"] ?? 0);
    $downloadtoadd = (float)($_POST["amountdown"] ?? 0);
    $formatup = $_POST["formatup"] ?? 'gb';
    $formatdown = $_POST["formatdown"] ?? 'gb';
    $mpup = $_POST["upchange"] ?? 'plus';
    $mpdown = $_POST["downchange"] ?? 'plus';
    $modcomm = trim($_POST["modcomm"] ?? '');
    $info = trim($_POST["info"] ?? '');
    $deluser = $_POST["deluser"] ?? '';

    $updateset[] = "enabled = " . sqlesc($enabled);
    $updateset[] = "donor = " . sqlesc($donor);
    $updateset[] = "avatar = " . sqlesc($avatar);
    $updateset[] = "karma = " . sqlesc($karma);
    if (!empty($donate) && $donate !== '0000-00-00 00:00:00' && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $donate) && strtotime($donate) !== false) {
        $updateset[] = "vipuntil = " . sqlesc($donate);
    } else {
        $updateset[] = "vipuntil = " . sqlesc('1970-01-01 00:00:01');
    }
    $updateset[] = "bonus = " . sqlesc($bonus);
    if (!empty($last_upload) && $last_upload !== '0000-00-00 00:00:00' && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $last_upload) && strtotime($last_upload) !== false) {
        $updateset[] = "last_upload = " . sqlesc($last_upload);
    } else {
        $updateset[] = "last_upload = " . sqlesc('1970-01-01 00:00:01');
    }
    $updateset[] = "title = " . sqlesc($title);
    $updateset[] = "class = $class";
    if (!empty($modcomm))
        $modcomment = date("Y-m-d") . " - Заметка от {$CURUSER['username']}: $modcomm\n" . $modcomment;
    $updateset[] = "modcomment = " . sqlesc($modcomment);
    $updateset[] = "info = " . sqlesc($info);

    if (!empty($_POST['resetkey'])) {
        $passkey = md5($CURUSER['username'] . get_date_time() . $CURUSER['passhash']);
        $updateset[] = "passkey = " . sqlesc($passkey);
    }

    sql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

    // Обработка изменения аплоада/даунлоада
    $mult = [
        'mb' => 1048576,
        'gb' => 1073741824
    ];

    // Раздача
    if ($uploadtoadd > 0) {
        $delta = $uploadtoadd * ($mult[$formatup] ?? 1073741824);
        if ($mpup === 'minus') $delta = -$delta;
        sql_query("UPDATE users SET uploaded = uploaded + $delta WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    }

    // Скачка
    if ($downloadtoadd > 0) {
        $delta = $downloadtoadd * ($mult[$formatdown] ?? 1073741824);
        if ($mpdown === 'minus') $delta = -$delta;
        sql_query("UPDATE users SET downloaded = downloaded + $delta WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    }

    if (!isset($memcache_obj)) {
        $memcache_obj = new Memcached();
        $memcache_obj->addServer('localhost', 11211);
    }
    $memcache_obj->delete('users_' . $userid);

    if (!empty($deluser)) {
        $username = $arr["username"];
        sql_query("DELETE FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM messages WHERE receiver = $userid") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM friends WHERE userid = $userid OR friendid = $userid") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM blocks WHERE userid = $userid OR blockid = $userid") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM peers WHERE userid = $userid") or sqlerr(__FILE__, __LINE__);

        write_log("Пользователь $username был удален пользователем {$CURUSER['username']}");
        barf("Пользователь $username удален");
    }

    $returnto = htmlentities($_POST["returnto"] ?? '');
    header("Location: $DEFAULTBASEURL/$returnto");
    die;

} elseif ($action === "confirmuser") {
    $userid = (int)($_POST["userid"] ?? 0);
    $confirm = $_POST["confirm"] ?? '';

    if (!is_valid_id($userid))
        stderr($lang['error'], $lang['invalid_id']);

    $updateset = [
        "status = " . sqlesc($confirm),
        "last_login = " . sqlesc(get_date_time()),
        "last_access = " . sqlesc(get_date_time()),
    ];

    sql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

    $returnto = htmlentities($_POST["returnto"] ?? '');
    header("Location: $DEFAULTBASEURL/$returnto");
    die;
}

puke();