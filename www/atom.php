<?php

require_once("include/bittorrent.php");
require_once("include/functions_global.php");
require_once("include/class/jsend.class.php");
require_once("include/class/class.memchat.php");
$logFile = __DIR__ . '/logs/chat.log';  // <--- добавь сюда путь к логу
// Подключение к БД и gzip
gzip();
dbconn();

// Проверка авторизации
if (!isset($CURUSER)) {
    die('Доступ запрещён.');
}

if ($CURUSER['enabled'] === 'no') {
    die('Ваш аккаунт отключён.');
}

$default_hosts = ['memcached-animeclub', 'memcached', 'localhost', '127.0.0.1'];
$memcache_port = 11211;

$mc = new Memcached();
$connected = false;

foreach ($default_hosts as $host) {
    $mc->addServer($host, $memcache_port);
    $stats = $mc->getStats();
    if (is_array($stats)) {
        foreach ($stats as $server => $data) {
            if (!empty($data['pid'])) {
                $connected = true;
                break 2;
            }
        }
    }
}

if (!$connected) {
    die("Не удалось подключиться к Memcached по ни одному из адресов: " . implode(', ', $default_hosts));
}

$jSEND = new jSEND();
$keep_messages = 25;
$chat = new mc_chat($mc, '2', $keep_messages);

header("Content-Type: text/html; charset=utf-8");


// Функция для логирования с меткой времени
function chat_log(string $message)
{
    global $logFile;
    $time = date('Y-m-d H:i:s');
    // Кодируем в UTF-8 на всякий случай, добавляем новую строку
    file_put_contents($logFile, "[$time] $message\n", FILE_APPEND | LOCK_EX);
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'add') {
    $rawText = $_POST['text'] ?? '';

    // Логируем сырой ввод в hex и как строку (для диагностики)
    chat_log("RAW input (hex): " . bin2hex($rawText));
    chat_log("RAW input (string): " . $rawText);

    // Декодируем с помощью jSEND
    $decodedText = $jSEND->getData($rawText);
    chat_log("After jSEND->getData(): " . $decodedText);

    // Удаляем управляющие символы (кроме перевода строки)
    $decodedText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $decodedText);
    chat_log("After removing control chars: " . $decodedText);

    // Ограничиваем длину сообщения, например, 500 символов
    $decodedText = mb_substr($decodedText, 0, 500);
    chat_log("After length limit (500 chars max): " . $decodedText);

    // Убираем [img] теги для слабых классов
    if (get_user_class() < UC_SPOWER) {
        $bb = ["#\\[img\\]([^?](?:[^\\[]+|\\[(?!url)).(gif|jpg|jpeg|png))\\[/img\\]#i"];
        $html = ["*[url=\\1]картинка[/url]*"];
        $decodedText = preg_replace($bb, $html, $decodedText);
        chat_log("After removing [img] tags for weak class: " . $decodedText);
    }

    // Форматируем текст (например, bbcode, html)
    $formattedMessage = format_comment($decodedText);
    chat_log("After format_comment(): " . strip_tags($formattedMessage));

    // Собираем данные пользователя и сообщение
    $userData = [
        'user' => $CURUSER['id'],
        'username' => $CURUSER['username'],
        'class' => $CURUSER['class'],
        'warned' => $CURUSER['warned'],
        'donor' => $CURUSER['donor'],
        'gender' => $CURUSER['gender'],
        'parked' => $CURUSER['parked'],
        'enabled' => $CURUSER['enabled'],
        'time' => time(),
        'message' => $formattedMessage
    ];

    // Логируем окончательное сообщение, которое уйдёт в чат и базу
    chat_log("FINAL message for chat and DB: " . strip_tags($userData['message']));

    // Добавление в memcached
    $chat->add(
        $userData['user'], $userData['username'], $userData['class'], $userData['warned'],
        $userData['donor'], $userData['gender'], $userData['parked'], $userData['message'], $userData['time']
    );

    // Добавление в базу
    $values = array_map('sqlesc', $userData);
    $sql = "INSERT INTO shoutbox (userid, username, class, warned, donor, gender, parked, enabled, text, date)
            VALUES ({$values['user']}, {$values['username']}, {$values['class']}, {$values['warned']},
                    {$values['donor']}, {$values['gender']}, {$values['parked']}, {$values['enabled']},
                    {$values['message']}, {$values['time']})";
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    chat_log("Message inserted into DB.");
}

if ($action === 'delete') {
    if (get_user_class() >= UC_MODERATOR) {
        $id = (int)($_POST['id'] ?? 0);
        $tid = sqlesc($_POST['tid'] ?? '');

        $chat->purne($id);
        sql_query("DELETE FROM shoutbox WHERE date = $tid") or sqlerr(__FILE__, __LINE__);
        chat_log("Deleted message with date $tid and id $id.");
    } else {
        die("Нет прав на удаление сообщений.");
    }
}

// Проверка блокировки чата
if ($CURUSER['chat_ban'] === 'yes') {
    if ($CURUSER['chat_ban_until'] === '0000-00-00 00:00:00') {
        die("<div style='text-align:center'><b>Вы навсегда забанены в чате.</b></div>");
    } else {
        $timeLeft = mkprettytime(strtotime($CURUSER['chat_ban_until']) - gmtime());
        die("<div style='text-align:center'><b>Вы забанены в чате. До разблокировки: $timeLeft</b></div>");
    }
}

// Попробуем достать HTML из кеша
$cached_html = $mc->get("chat_html_cache");
if ($cached_html) {
    echo $cached_html;
    exit;
}
// Если кеш отсутствует, получаем сообщения
$msg = $chat->messages();

// Генерация HTML-вывода
$html = "<table border='0' width='100%' id='chatbox' style='word-wrap: break-word;'>\n";
$rowNum = 0;

foreach ($msg as $arr) {
    $del = '';
    if (get_user_class() >= UC_MODERATOR) {
        $del = " <span class='delmess' id='{$arr['id']}' tid='{$arr['time']}' style='cursor:pointer;'><img src='pic/delc.png' title='Удалить сообщение' /></span>\n";
    }

    $profile = '';
    if ($arr['gender'] == 1) {
        $profile = "<a href='user/id{$arr['user']}'><img src='pic/chatm.png' border='0' title='Профиль' /></a> ";
    } elseif ($arr['gender'] == 2) {
        $profile = "<a href='user/id{$arr['user']}'><img src='pic/chatf.png' border='0' title='Профиль' /></a> ";
    }

    $priv = "<span onclick=\"javascript:parent.jqcc.cometchat.chatWith('{$arr['user']}');\" style='cursor:pointer;'><img src='pic/privc.png' border='0' title='Приват' /></span>";

    $name = "<a style='cursor:pointer;' onclick=\"parent.document.shbox.text.focus();parent.document.shbox.text.value='[b]{$arr['username']}[/b]: '+parent.document.shbox.text.value;return false;\">" . get_user_class_color($arr['class'], $arr['username']) . get_user_icons_chat($arr) . "</a>";
    $datum = gmdate("H:i:s", $arr['time'] + ($CURUSER['timezone'] + $CURUSER['dst']) * 60);
    $message = $arr['message'];

    if ($arr['user'] === $CURUSER['id']) {
        $bg = ($rowNum % 2 === 0) ? '#FDFDFD' : '#F4F0E8';
        $html .= "<tr style=\"background-color:{$bg}\"><td><span class='date'>[$datum]</span> $del $profile $priv <u><b>$name</b></u>: <font color='#3D3A3A'>$message</font></td></tr>\n";
        $rowNum++;
    } else {
        if (strpos($message, "<strong>{$CURUSER['username']}</strong>") !== false) {
            $message = str_replace($CURUSER['username'], "<u><strong>{$CURUSER['username']}</strong></u>", $message);
        }
        $bg = ($rowNum % 2 === 0) ? '#FDFDFD' : '#F4F0E8';
        $html .= "<tr style=\"background-color:{$bg}\"><td><span class='date'>[$datum]</span> $del $profile $priv $name: $message</td></tr>\n";
        $rowNum++;
    }
}

$html .= "</table>";
$mc->set("chat_html_cache", $html, 1); // кешируем на 3 секунды

echo $html;