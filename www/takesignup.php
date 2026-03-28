<?php
require_once("include/bittorrent.php");

dbconn();
global $mysqli;
global $CURUSER;
global $users, $lang, $default_language, $use_email_act;
if ($CURUSER)
    stderr($lang['error'], sprintf($lang['signup_already_registered'], $SITENAME));

if (!mkglobal("wantusername:wantpassword:passagain:email"))
    stderr($lang['error'], "Прямой доступ к этому файлу не разрешен.");

define('REGISTER', true);
if ($users > 0 && $use_email_act == 1)
    define('ACTIVATION', 'yes');
else
    define('ACTIVATION', 'no');

function bark($msg) {
    global $lang;
    stdhead();
    stdmsg($lang['error'], $msg, 'error');
    stdfoot();
    exit;
}

function validusername($username)
{
    if ($username == "")
        return false;

    $allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_".
        "абвгдеёжзиклмнопрстуфхшщэюяьъАБВГДЕЁЖЗИКЛМНОПРСТУФХШЩЭЮЯЬЪ";

    for ($i = 0; $i < strlen($username); ++$i)
        if (strpos($allowedchars, $username[$i]) === false)
            return false;

    return true;
}
if (!isset($_POST["timezone"], $_POST["dst"], $_POST["gender"], $_POST["country"], $_POST["year"], $_POST["month"], $_POST["day"])) {
    bark("Пожалуйста, заполните все обязательные поля.");
}
$timezone = 0 + $_POST["timezone"];
$dst = ($_POST["dst"] ? 60 : 0);
$gender = $_POST["gender"];
$country = $_POST["country"];
$year = $_POST["year"];
$month = $_POST["month"];
$day = $_POST["day"];

if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($gender) || empty($country))
    bark("Все поля обязательны для заполнения.");

if (strlen($wantusername) > 12)
    bark("Извините, имя пользователя слишком длинное (максимум 12 символов)");

if (strlen($wantusername) < 4)
    bark("Извините, имя пользователя слишком короткое (минимум 4 символов)");

if ($wantpassword != $passagain)
    bark("Пароли не совпадают! Похоже вы ошиблись. Попробуйте еще.");

if (strlen($wantpassword) < 6)
    bark("Извините, пароль слишком коротки (минимум 6 символов)");

if (strlen($wantpassword) > 40)
    bark("Извините, пароль слишком длинный (максимум 40 символов)");

if ($wantpassword == $wantusername)
    bark("Извините, пароль не может быть такой-же как имя пользователя.");

if (!validemail($email))
    bark("Это не похоже на реальный email адрес.");

if (!validusername($wantusername))
    bark("Неверное имя пользователя.");

if ($year=='0000' || $month=='00' || $day=='00')
    stderr($lang['error'],"Похоже вы указали неверную дату рождения");
$birthday = date("$year.$month.$day");

// check if email addy is already in use
$res = sql_query("SELECT COUNT(*) FROM users WHERE email=".sqlesc($email)) or sqlerr();
$a = mysqli_fetch_row($res);
if ($a[0] != 0)
    bark("E-mail адрес ".htmlspecialchars($email)." уже зарегистрирован в системе.");

$ip = getip();

if (isset($_COOKIE["uid"]) && is_numeric($_COOKIE["uid"]) && $users) {
    $cid = intval($_COOKIE["uid"]);
    $c = sql_query("SELECT enabled FROM users WHERE id = $cid ORDER BY id DESC LIMIT 1");
    $co = mysqli_fetch_row($c);
    if ($co[0] == 'no') {
        sql_query("UPDATE users SET ip = ".sqlesc($ip).", last_access = NOW() WHERE id = $cid");
        bark("Ваш IP забанен на этом трекере. Регистрация невозможна.");
    } else
        bark("Регистрация невозможна!");
} else {
    $res = sql_query("SELECT enabled, id FROM users WHERE ip LIKE ".sqlesc($ip)." ORDER BY last_access DESC LIMIT 1");
    $b = mysqli_fetch_row($res);
    if ($b[0] == 'no') {
        $banned_id = $b[1];
        setcookie("uid", $banned_id, time()+31536000, "/");
        bark("Ваш IP забанен на этом трекере. Регистрация невозможна.");
    }
}

$secret = mksecret();
$wantpasshash = md5($secret . $wantpassword . $secret);
$editsecret = (!$users?"":mksecret());

if ((!$users) || (!$use_email_act == true))
    $status = 'confirmed';
else
    $status = 'pending';

// Добавьте переменную $avatar
// Добавьте переменные по инструкции
$avatar = '';
$icq = '';
$skype = '';
$title = '';
$notifs = '';
$passkey = md5($wantpasshash . rand());
$passkey_ip = $ip;
$added = get_date_time();
$lastwarned = $added;
$query = "INSERT INTO users (username, passhash, secret, editsecret, gender, timezone, dst, country, email, status, added, birthday, ip, old_password, avatar, icq, skype, title, notifs, lastwarned, passkey, passkey_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($mysqli, $query);
if (!$stmt) {
    bark("Ошибка подготовки SQL-запроса: " . htmlspecialchars(mysqli_error($mysqli)));
}
mysqli_stmt_bind_param($stmt, "sssssiiissssssssssssss",
    $wantusername,
    $wantpasshash,
    $secret,
    $editsecret,
    $gender,
    $timezone,
    $dst,
    $country,
    $email,
    $status,
    $added,
    $birthday,
    $ip,
    $wantpasshash,
    $avatar,
    $icq,
    $skype,
    $title,
    $notifs,
    $lastwarned,
    $passkey,
    $passkey_ip
);

if (!mysqli_stmt_execute($stmt)) {
    if (mysqli_errno($mysqli) == 1062)
        bark("Пользователь $wantusername уже зарегистрирован!");
    bark("Неизвестная ошибка. Ответ от сервера mySQL: ".htmlspecialchars(mysqli_error($mysqli)));
}
$id = mysqli_insert_id($mysqli);
write_log("Зарегистрирован новый пользователь $wantusername","FFFFFF","tracker");

$psecret = md5($editsecret);

$body = <<<BLOCKHTML
<table border="0" cellpadding="0" cellspacing="0" width="550" align="center">
<tbody><tr><td height="40"><font color= "#bc5349" size="5"><b><div>анимеклуб.лв</div></b></font></td>
</tr><tr><td>
<table width="100%" cellspacing="0" cellpadding="2" bgcolor="e9fefe" border="1">
<tbody><tr><td><table border="0" width="100%" cellspacing="10" cellpadding="10"><tbody><tr><td>Вы зарегистрировались на анимеклуб.лв и указали 
этот e-mail (<font color="blue"><b>$email</b></font>).<br>
Здесь представлена информация, для входа на анимеклуб.лв:<br><br> 
<b>Имя пользователя:</b> $wantusername <br>
<b>Пароль:</b> $wantpassword <br>
<b>Адрес E-mail:</b> $email<br><br>

Если вы этого не делали, просто проигнорируйте данное письмо.<br> 
Для вашего сведения, попытка регистрации была произведена с <br>IP адреса <b>{$_SERVER["REMOTE_ADDR"]}</b><br><br>

На это письмо отвечать не нужно. <br>
Для завершения регистрации, проследуйте по ссылке:<br><br>

<a href="$DEFAULTBASEURL/confirm.php?id=$id&secret=$psecret" target="_blank"><font color="blue"><b>$DEFAULTBASEURL/confirm.php?id=$id&secret=$psecret</b></font></a><br><br>

После этого вы можете начать пользоваться своим эккаунтом. <br>
Если вы не подтвердите регистрацию в течении 2 дней, эккаунт будет удален с сервера анимеклуб.лв. <br />
<p align="right">С уважением, Администрация анимеклуб.лв.</p></td></tr></tbody></table></td></tr></tbody></table>
</td></tr><tr><td align="right" height="20"><small><a href="http://animeclub.lv" target="_blank">анимеклуб.лв</a> | <a href="http://animeclub.lv/forum" target="_blank">форум</a> | @ 2011</small></td></tr></tbody></table>
BLOCKHTML;

if($use_email_act && $users) {
    send_mime_mail('AnimeClub Mail Robot',
                   $SITEEMAIL,
                   $wantusername,
                   $email,
                   'UTF8',
                   'KOI8-R',
                   'Подтверждение регистрации ',
                   $body, true);
} else {
    logincookie($id, $wantpasshash, $default_language);
}

header("Refresh: 0; url=ok.php?type=signup&email=" . urlencode($email));
?>