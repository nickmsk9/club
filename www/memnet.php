<?php
// Подключаем необходимые файлы и проверяем авторизацию
require "include/bittorrent.php";
dbconn();
loggedinorreturn();

global $mysqli;

// Только для модераторов и выше
if (get_user_class() < UC_MODERATOR) {
    stderr("Внимание!", "Это служебный раздел, доступ запрещён!");
}

$userid = $_GET["id"];

if (!is_valid_id($userid)) {
    stderr("Внимание!", "Некорректный ID пользователя!");
}

/**
 * Функция вычисления коэффициента раздачи
 * @param int $up - отправлено байт
 * @param int $down - получено байт
 * @param bool $color - выводить ли цвет
 * @return string
 */
function ratios($up, $down, $color = true)
{
    if ($down > 0) {
        $r = number_format($up / $down, 2);
        if ($color) {
            $r = "<font color=" . get_ratio_color($r) . ">$r</font>";
        }
    } elseif ($up > 0) {
        $r = "∞"; // бесконечность
    } else {
        $r = "---";
    }
    return $r;
}

$mask = "255.255.255.0";

// Получаем IP и имя пользователя по ID
$res = @mysqli_query($mysqli, "SELECT ip, username FROM users WHERE id=$userid") or sqlerr();
$user = mysqli_fetch_array($res);

// Получаем подсеть пользователя
$tmpip = explode(".", $user["ip"]);
$ip = $tmpip[0] . "." . $tmpip[1] . "." . $tmpip[2] . ".0";

// Регулярное выражение для проверки IP
$regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";

// Проверяем маску подсети
if (substr($mask, 0, 1) == "/") {
    $n = substr($mask, 1);
    if (!is_numeric($n) || $n < 0 || $n > 32) {
        stdmsg("Ошибка", "Некорректная маска подсети.");
        stdfoot();
        die();
    } else {
        $mask = long2ip(pow(2, 32) - pow(2, 32 - $n));
    }
} elseif (!preg_match($regex, $mask)) {
    stdmsg("Ошибка", "Некорректная маска подсети.");
    stdfoot();
    die();
}

// Запрос соседей по подсети, исключая самого пользователя
$query = "SELECT id, ip, username, class, last_access, added, uploaded, downloaded
          FROM users
          WHERE enabled='yes' AND status='confirmed' AND id<>$userid 
            AND (INET_ATON(ip) & INET_ATON('$mask')) = (INET_ATON('$ip') & INET_ATON('$mask'))";

 $res = mysqli_query($mysqli, $query) or sqlerr();

if (mysqli_num_rows($res)) {
    stdhead("Пользователи из вашей подсети");
    begin_main_frame();

    print("<table width='100%' border='0' cellspacing='0' cellpadding='5'>\n");
    print("<tr><td width='100%' align='center' colspan='8'><b>($user[ip]) Соседи пользователя $user[username]</b></td></tr>");
    print("<tr>
        <td class='colhead' align='center'>Имя</td>
        <td class='colhead' align='center'>Раздал</td>
        <td class='colhead' align='center'>Скачал</td>
        <td class='colhead' align='center'>Рейтинг</td>
        <td class='colhead' align='center'>Дата регистрации</td>
        <td class='colhead' align='center'>Последний вход</td>
        <td class='colhead' align='center'>Звание</td>
        <td class='colhead' align='center'>IP адрес</td>
    </tr>\n");

    while ($arr = mysqli_fetch_assoc($res)) {
        print("<tr>
            <td align='center'><b><a href='user/id$arr[id]'>$arr[username]</a></b></td>
            <td align='center'>" . mksize($arr["uploaded"]) . "</td>
            <td align='center'>" . mksize($arr["downloaded"]) . "</td>
            <td align='center'>" . ratios($arr["uploaded"], $arr["downloaded"]) . "</td>
            <td align='center'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added'])) . " назад</td>
            <td align='center'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['last_access'])) . " назад</td>
            <td align='center'>" . get_user_class_name($arr["class"]) . "</td>
            <td align='center'>$arr[ip]</td>
        </tr>\n");
    }

    print("</table>");
    end_main_frame();
    stdfoot();
} else {
    stderr("Не найдено", "Пользователей из вашей подсети не найдено.");
}
?>