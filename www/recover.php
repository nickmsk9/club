<?php
require_once 'include/bittorrent.php';
global $memcache_obj, $mysqli;
dbconn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        stderr($tracker_lang['error'], 'Вы должны ввести email адрес');
    }

    $res = sql_query(
        "SELECT id, username, passhash 
         FROM users 
         WHERE email = " . sqlesc($email) . " 
         LIMIT 1"
    ) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr($tracker_lang['error'], 'Email адрес не найден в базе данных.');
    }

    $sec = mksecret();
    sql_query(
        "UPDATE users 
         SET editsecret = " . sqlesc($sec) . " 
         WHERE id = " . sqlesc($arr['id'])
    ) or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($mysqli) === 0) {
        stderr($tracker_lang['error'], 'Ошибка базы данных. Свяжитесь с администратором относительно этой ошибки.');
    }

    $hash = md5($sec . $email . $arr['passhash'] . $sec);

    $body = <<<EOD
<table border="0" cellpadding="0" cellspacing="0" width="550" align="center">
<tr>
    <td height="40">
        <font color="#bc5349" size="5"><b>анимеклуб.лв</b></font>
    </td>
</tr>
<tr>
    <td>
    <table width="100%" cellspacing="0" cellpadding="2" bgcolor="e9fefe" border="1">
    <tr>
        <td>
        <table border="0" width="100%" cellspacing="10" cellpadding="10">
        <tr>
            <td>
            По вашей просьбе мы выслали вам информацию о восстановлении пароля.<br />
            Если вы подтверждаете этот запрос, перейдите по следующей ссылке:<br />
            http://{$_SERVER['HTTP_HOST']}/recover.php?id={$arr['id']}&secret={$hash}<br />
            <p>Если Вы не запрашивали новый пароль, сообщите нам или проигнорируйте это письмо.</p>
            <p align="right">С уважением, Администрация анимеклуб.лв.</p>
            </td>
        </tr>
        </table>
        </td>
    </tr>
    </table>
    </td>
</tr>
<tr>
    <td align="right" height="20">
        <small>
        <a href="http://{$_SERVER['HTTP_HOST']}" target="_blank">анимеклуб.лв</a> |
        <a href="http://{$_SERVER['HTTP_HOST']}/forum" target="_blank">форум</a> | © 2011
        </small>
    </td>
</tr>
</table>
EOD;

    send_mime_mail(
        'AnimeClub Mail Robot',
        $SITEEMAIL,
        $arr['username'],
        $email,
        'UTF-8',
        'KOI8-R',
        'Подтверждение восстановления пароля',
        $body,
        true
    ) or stderr($tracker_lang['error'], 'Невозможно отправить E-mail. Пожалуйста сообщите администрации об ошибке.');

    stderr($tracker_lang['success'], "Подтверждающее письмо было отправлено. Через несколько минут вам придёт письмо с дальнейшими указаниями.");
    exit;
}

if (isset($_GET['id'], $_GET['secret'])) {
    $id    = (int)($_GET['id'] ?? 0);
    $md5   = $_GET['secret'] ?? '';
    if ($id <= 0) {
        stderr($tracker_lang['error'], 'Ошибка данных id. Свяжитесь с администратором относительно этой ошибки.');
    }
    $memcache_obj->delete("users_{$id}");

    $res2 = sql_query(
        "SELECT username, email, passhash, editsecret 
         FROM users 
         WHERE id = " . sqlesc($id)
    ) or sqlerr(__FILE__, __LINE__);
    $arr2 = mysqli_fetch_assoc($res2);
    if (!$arr2) {
        stderr($tracker_lang['error'], 'Пользователь не найден.');
    }

    $email = $arr2['email'];
    $sec   = hash_pad($arr2['editsecret']);
    if (trim($sec) === '') {
        stderr($tracker_lang['error'], 'Ошибка данных secret. Свяжитесь с администратором относительно этой ошибки.');
    }
    if ($md5 !== md5($sec . $email . $arr2['passhash'] . $sec)) {
        stderr($tracker_lang['error'], 'Ошибка данных md5. Свяжитесь с администратором относительно этой ошибки.');
    }

    // Generate new random password
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newpassword = '';
    for ($i = 0; $i < 10; $i++) {
        $newpassword .= $chars[random_int(0, strlen($chars) - 1)];
    }

    $newsec = mksecret();
    $newpasshash = md5($newsec . $newpassword . $newsec);

    sql_query(
        "UPDATE users 
         SET secret = " . sqlesc($newsec) . ",
             editsecret = '',
             passhash = " . sqlesc($newpasshash) . "
         WHERE id = " . sqlesc($id) . " 
           AND editsecret = " . sqlesc($arr2['editsecret'])
    ) or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($mysqli) === 0) {
        stderr($tracker_lang['error'], 'Невозможно обновить данные пользователя. Пожалуйста свяжитесь с администратором относительно этой ошибки.');
    }

    $body2 = <<<EOD
<table border="0" cellpadding="0" cellspacing="0" width="550" align="center">
<tr>
    <td height="40"><font color="#bc5349" size="5"><b>анимеклуб.лв</b></font></td>
</tr>
<tr>
    <td>
    <table width="100%" cellspacing="0" cellpadding="2" bgcolor="e9fefe" border="1">
    <tr>
        <td>
        <table border="0" width="100%" cellspacing="10" cellpadding="10">
        <tr>
            <td>
            По вашей просьбе мы заменили пароль к вашему аккаунту.<br />
            Здесь представлена информация для входа:<br />
            <b>Пользователь:</b> {$arr2['username']}<br />
            <b>Пароль:</b> {$newpassword}<br />
            <p>Войти можете здесь: <a href="http://{$_SERVER['HTTP_HOST']}/login.php" target="_blank">анимеклуб.лв</a></p>
            <p align="right">С уважением, Администрация анимеклуб.лв.</p>
            </td>
        </tr>
        </table>
        </td>
    </tr>
    </table>
    </td>
</tr>
<tr>
    <td align="right" height="20">
        <small>
        <a href="http://{$_SERVER['HTTP_HOST']}" target="_blank">анимеклуб.лв</a> |
        <a href="http://{$_SERVER['HTTP_HOST']}/forum" target="_blank">форум</a> | © 2011
        </small>
    </td>
</tr>
</table>
EOD;

    send_mime_mail(
        'AnimeClub Mail Robot',
        $SITEEMAIL,
        $arr2['username'],
        $email,
        'UTF-8',
        'KOI8-R',
        'Новые данные для входа',
        $body2,
        true
    ) or stderr($tracker_lang['error'], 'Невозможно отправить E-mail. Пожалуйста сообщите администрации об ошибке.');

    stderr($tracker_lang['success'], "Новые данные по аккаунту отправлены на E-Mail <b>{$email}</b>.");
    exit;
}

// Display recovery form
stdhead('Восстановление пароля');
begin_main_frame();
begin_frame('Восстановление пароля', true);
echo <<<HTML
<form method="post" action="recover.php">
    <table>
        <tr><td class="colhead" colspan="2">Восстановление имени пользователя или пароля</td></tr>
        <tr><td colspan="2">Используйте форму ниже для восстановления пароля и ваши данные будут отправлены на почту. Вы должны будете подтвердить запрос.</td></tr>
        <tr><td class="rowhead">Зарегистрированный email</td>
            <td><input type="email" name="email" size="40" required></td></tr>
        <tr><td colspan="2" align="center"><input type="submit" value="Восстановить"></td></tr>
    </table>
</form>
HTML;
end_frame();
end_main_frame();
stdfoot();
?>