<?php
require_once("include/bittorrent.php");
dbconn();
global $mysqli;
global $validemail;

function decode_unicode_url($str) {
    $res = '';

    $i = 0;
    $max = strlen($str) - 6;
    while ($i <= $max) {
        $character = $str[$i];
        if ($character == '%' && $str[$i + 1] == 'u') {
        $value = hexdec(substr($str, $i + 2, 4));
        $i += 6;

        if ($value < 0x0080)
            $character = chr($value);
        else if ($value < 0x0800)
            $character =
                chr((($value & 0x07c0) >> 6) | 0xc0)
                . chr(($value & 0x3f) | 0x80);
        else
            $character =
                chr((($value & 0xf000) >> 12) | 0xe0)
                . chr((($value & 0x0fc0) >> 6) | 0x80)
                . chr(($value & 0x3f) | 0x80);
        } else
            $i++;

        $res .= $character;
    }

    return $res . substr($str, $i);
}


header ("Content-Type: text/html; charset=" . $lang['language_charset']);

if ($_POST["action"] == "username") {

    function validusername($username) {
        if ($username == "")
          return false;
        $allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_ "."абвгдеёжзиклмнопрстуфхшщэюяьъАБВГДЕЁЖЗИКЛМНОПРСТУФХШЩЭЮЯЬЪ";
        for ($i = 0; $i < strlen($username); ++$i)
          if (strpos($allowedchars, $username[$i]) === false)
            return false;
        return true;
    }

    $wantname = $_POST["username"];
    $wantusername = urldecode(decode_unicode_url($wantname));
    $res_q = sql_query("SELECT COUNT(*) FROM users WHERE USERNAME = ".sqlesc($wantusername));
    if (!$res_q) die("Ошибка БД: " . $mysqli->error);
    $res = mysqli_fetch_row($res_q);
    if ($res[0] != 0)
        ajaxerr("Пользователь $wantusername уже зарегистрирован", "294");
    elseif (empty($wantusername))
        ajaxerr("Не указано имя пользователя", "294");
    elseif (strlen($wantusername) > 12)
        ajaxerr("Имя пользователя должно быть не более 12 символов", "294");
    elseif (!validusername($wantusername))
        ajaxerr("Неверное имя пользователя", "294");
    else
        ajaxsucc("Вы можете использовать это имя", "294");
}

if ($_POST["action"] == "password"){
    $wantpass = $_POST["password"];

    $wantpassword = urldecode(decode_unicode_url($wantpass));
    $pagain = $_POST["passagain"];
    $passagain = urldecode(decode_unicode_url($pagain));

    if (empty($wantpassword))
        ajaxerr("Введите пароль", "294");
    elseif (empty($passagain))
        ajaxerr("Продублируйте пароль", "294");
    elseif ($wantpassword != $passagain)
        ajaxerr("Пароли не совпадают.", "294");
    elseif (strlen($wantpassword) < 6)
        ajaxerr("Минимальная длина пароля 6 символов", "294");
    elseif (strlen($wantpassword) > 40)
        ajaxerr("Максимальная длина пароля 40 символов", "294");
    else
        ajaxsucc("Вы можете использовать этот пароль", "294");
}



if ($_POST["action"] == "email"){
    $email = $_POST["email"];

    $res_q = sql_query("SELECT COUNT(*) FROM users WHERE email = ".sqlesc($email));
    if (!$res_q) die("Ошибка БД: " . $mysqli->error);
    $res = mysqli_fetch_row($res_q);
    if (empty($email))
        ajaxerr("Не указан e-mail адрес", "294");
    elseif ($res[0] != 0)
        ajaxerr("Этот e-mail адрес уже зарегистрирован", "294");
    elseif (!validemail($email))
        ajaxerr("Этот e-mail не правильного формата, проверьте написание", "294");
    elseif (validemail($email))
        ajaxsucc("Вы можете использовать этот e-mail адрес", "294");
}
?> 