<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* НАСТРОЙКИ */


// Не уничтожать сессию при выходе
define('DO_NOT_DESTROY_SESSION', '0');

// Разрешить переключение пользователей
define('SWITCH_ENABLED', '1');

// Подключать jQuery
define('INCLUDE_JQUERY', '0');

// Эмуляция magic quotes
define('FORCE_MAGIC_QUOTES', '1');

// Добавлять активность пользователей
define('ADD_LAST_ACTIVITY', '1');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* НАСТРОЙКИ БАЗЫ ДАННЫХ */

// Подключаем основные файлы трекера
include_once dirname(dirname(__FILE__)) . "/include/bittorrent.php";
include_once dirname(dirname(__FILE__)) . "/include/secrets.php";

// Подключение к БД и загрузка языка
dbconn();
getlang();


define('DB_USERTABLE', 'cometchat_status');
define('TABLE_PREFIX', '');
define('DB_USERTABLE_USERID', 'userid');
define('DB_USERTABLE_NAME', 'username');
define('DB_USERTABLE_USERAVATAR', 'avatar');
define('DB_USERTABLE_USERGENDER', 'gender');
define('DB_USERTABLE_LASTACTIVITY', 'last_access');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ФУНКЦИИ */

// Получить ID текущего пользователя
function getUserID() {
    global $CURUSER;
    $userid = 0;
    if ($CURUSER) {
        $userid = $CURUSER['id'];
    }
    return $userid;
}

// Получить список друзей (для отображения в панели)
function getFriendsList($userid, $time) {
    global $conn;
    $userid = mysqli_real_escape_string($conn, $userid);
    $sql = "SELECT f.friendid AS userid, c_s.userid, c_s.username, c_s.last_access AS lastactivity,
                   c_s.avatar, c_s.message, c_s.gender, c_s.status
            FROM friends AS f
            LEFT JOIN cometchat_status AS c_s ON f.friendid = c_s.userid
            WHERE f.userid = $userid
            ORDER BY username ASC";
    return $sql;
}

// Получить информацию о пользователе
function getUserDetails($userid) {
    global $conn;
    $userid = mysqli_real_escape_string($conn, $userid);
    return "SELECT userid, username, avatar, gender, last_access AS lastactivity, message, status
            FROM cometchat_status WHERE userid = '$userid'";
}

// Получить статус пользователя
function getUserStatus($userid) {
    global $conn;
    $userid = mysqli_real_escape_string($conn, $userid);
    return "SELECT message, status FROM cometchat_status WHERE userid = '$userid'";
}

// Обновить последнюю активность пользователя
function updateLastActivity($userid) {
    global $conn, $CURUSER;
    $userid = mysqli_real_escape_string($conn, $userid);
    $username = mysqli_real_escape_string($conn, $CURUSER["username"]);
    $avatar = mysqli_real_escape_string($conn, $CURUSER["avatar"]);
    $gender = mysqli_real_escape_string($conn, $CURUSER["gender"]);
    $last = time();
    return "UPDATE " . DB_USERTABLE . " SET " .
           DB_USERTABLE_NAME . " = '$username', " .
           DB_USERTABLE_USERAVATAR . " = '$avatar', " .
           DB_USERTABLE_USERGENDER . " = '$gender', " .
           DB_USERTABLE_LASTACTIVITY . " = '$last' " .
           "WHERE " . DB_USERTABLE_USERID . " = '$userid'";
}

// Получить ссылку на профиль
function getLink($link) {
    return "./user/id" . $link;
}

// Получить аватар пользователя
function getAvatar($image) {
    if ($image == "") {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/themes/Anime/images/default_avatar.gif';
    } else {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/timthumb.php?src=' . $image . '&w=20&zc=1&q=100';
    }
}

// Получить иконку пола
function getGender($img_gender) {
    if ($img_gender == 1) return "./pic/ico_m.png";
    if ($img_gender == 2) return "./pic/ico_w.png";
    return "./pic/no_icon.png";
}

// Получить временную метку
function getTimeStamp() {
    return time();
}

// Обработка времени (заглушка)
function processTime($time) {
    return $time;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ХУКИ */

// Обработка обновления статуса
function hooks_statusupdate($userid, $statusmessage) {
    global $conn;
    $userid = mysqli_real_escape_string($conn, $userid);
    $statusmessage = mysqli_real_escape_string($conn, $statusmessage);
    $sql = "UPDATE cometchat_status SET status = '$statusmessage' WHERE userid = '$userid'";
    mysqli_query($conn, $sql);
    echo mysqli_error($conn);
}

// Принудительная дружба (не используется)
function hooks_forcefriends() {}

// Обновление активности (не используется)
function hooks_activityupdate($userid, $status) {}

// Обработка сообщений (не используется)
function hooks_message($userid, $unsanitizedmessage) {}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ПРОЧЕЕ */

// Внутренняя переменная для защиты генератора JS
$p_ = 4;

?>