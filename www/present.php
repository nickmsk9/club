<?php

require_once("include/bittorrent.php");
dbconn();

global $CURUSER, $lang, $mysqli;

header("Content-Type: text/html; charset=" . $lang['language_charset']);

if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] === 'POST') {

    $from = (int)$CURUSER["id"];
    $to = isset($_POST["to"]) ? (int)$_POST["to"] : 0;
    $amount = isset($_POST["amount"]) ? (int)$_POST["amount"] : 0;

    if (empty($from) || empty($to) || empty($amount)) {
        die("Прямой доступ закрыт");
    }

    if ($from === $to) {
        die("Вы не можете дарить бонусы себе.");
    }

    if ($amount < 10 || $amount > 100) {
        die("Оо Бонуси в - ??Страноо..)");
    }

    $stmt = $mysqli->prepare("SELECT bonus FROM users WHERE id = ?");
    $stmt->bind_param("i", $from);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res || $res->num_rows === 0) {
        die("Ошибка доступа к данным.");
    }

    $row = $res->fetch_assoc();
    if ($row['bonus'] < $amount) {
        die("У вас недостаточно бонусов.");
    }

    // Передача бонусов
    $stmt = $mysqli->prepare("UPDATE users SET bonus = bonus + ? WHERE id = ?");
    $stmt->bind_param("ii", $amount, $to);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("UPDATE users SET bonus = bonus - ? WHERE id = ?");
    $stmt->bind_param("ii", $amount, $from);
    $stmt->execute();
    $stmt->close();

    // Личное сообщение
    $msg_pm = "Пользователь [url=user/id{$CURUSER['id']}]{$CURUSER['username']}[/url] подарил вам $amount бонусов.\n";
    $subj_pm = "Подарок от пользователя {$CURUSER['username']}";
    $sender = 0;
    $poster = 0;
    $stmt = $mysqli->prepare(
        "INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES (?, ?, NOW(), ?, ?, ?)"
    );
    $stmt->bind_param("iisis", $sender, $to, $msg_pm, $poster, $subj_pm);
    $stmt->execute();
    $stmt->close();

    die("Вы подарили пользователю $amount бонусов");
} else {
    die("Прямой доступ закрыт");
}

?>