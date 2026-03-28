<?php
require_once("include/bittorrent.php");

function bark($msg) {
    stderr("Произошла ошибка", $msg);
}

dbconn();
loggedinorreturn();

if (!mkglobal("email:oldpassword:chpassword:passagain"))
    bark("missing form data");

// Modern cache clearing approach
if (isset($cache)) {
    $cache->delete('users_'.$CURUSER['id']);
}

$updateset = array();
$changedemail = 0;

if ($chpassword != "") {
    if (strlen($chpassword) > 40)
        bark("Извините, ваш пароль слишком длинный (максимум 40 символов)");
    if ($chpassword != $passagain)
        bark("Пароли не совпадают. Попробуйте еще раз.");
    if ($CURUSER["passhash"] != md5($CURUSER["secret"] . $oldpassword . $CURUSER["secret"]))
        bark("Вы ввели неправильный старый пароль.");

    $sec = mksecret();
    $passhash = md5($sec . $chpassword . $sec);
    $updateset[] = "secret = " . sqlesc($sec);
    $updateset[] = "passhash = " . sqlesc($passhash);
    
    logincookie($CURUSER["id"], $passhash, $language);
    $passupdated = 1;
}

if ($email != $CURUSER["email"]) {
    if (!validemail($email))
        bark("Это не похоже на настоящий E-Mail.");
    $r = sql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($r) > 0)
        bark("Этот e-mail адрес уже используется одним из пользователей трекера. (<b>".htmlspecialchars($email)."</b>)");
    $changedemail = 1;
}

$acceptpms = $_POST["acceptpms"] ?? 'yes';
$deletepms = (!empty($_POST["deletepms"]) ? "yes" : "no");
$savepms = (!empty($_POST["savepms"]) ? "yes" : "no");
$avatar = $_POST["avatar"] ?? '';
if (strlen($avatar) > 100) {
    bark("URL аватара слишком длинный (максимум 100 символов)");
}

// Check remote avatar size
if ($avatar) {
    if (!preg_match('#^((http)|(ftp):\/\/[a-zA-Z0-9\-]+?\.([a-zA-Z0-9\-]+\.)+[a-zA-Z]+(:[0-9]+)*\/.*?\.(gif|jpg|jpeg|png)$)#is', $avatar)) {
        stderr($lang['error'], $lang['avatar_adress_invalid']);
    }
    // Convert URL to local path if pointing to localhost
    $parsed = parse_url($avatar);
    if (!empty($parsed['host']) && ($parsed['host'] === 'localhost' || $parsed['host'] === $_SERVER['SERVER_NAME']) && !empty($parsed['path'])) {
        $imagePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $parsed['path'];
    } else {
        $imagePath = $avatar;
    }
    if (!file_exists($imagePath) || !is_readable($imagePath)) {
        stderr($lang['error'], $lang['avatar_adress_invalid']);
    }
    if (!(list($width, $height) = getimagesize($imagePath))) {
        stderr($lang['error'], $lang['avatar_adress_invalid']);
    }
    if ($width > $avatar_max_width || $height > $avatar_max_height) {
        stderr($lang['error'], sprintf($lang['avatar_is_too_big'], $avatar_max_width, $avatar_max_height));
    }
}

$avatars = (!empty($_POST["avatars"]) ? "yes" : "no");
$parked = $_POST["parked"] ?? 'no';
$updateset[] = "parked = " . sqlesc($parked);

if (get_user_class() == UC_SYSOP) {
    $hiden = $_POST["hiden"];
    $updateset[] = "hiden = " . sqlesc($hiden);
}

$gender = $_POST["gender"] ?? '';
$updateset[] = "gender = " . sqlesc($gender);

///////////////// BIRTHDAY MOD /////////////////////
$year = $_POST["year"] ?? '0000';
$month = $_POST["month"] ?? '00';
$day = $_POST["day"] ?? '00';
$birthday1 = date("$year.$month.$day");
$birthday = (($_POST["bres"] ?? '') == "yes" ? "0000-00-00" : $birthday1);
$updateset[] = "birthday = " . sqlesc($birthday);
///////////////// BIRTHDAY MOD /////////////////////

if (!empty($_POST['resetpasskey'])) {
    // Clear cache for passkey
    if (isset($cache)) {
        $cache->delete('users_p_'.$CURUSER['passkey']);
        $cache->delete('users_'.$CURUSER['passkey']);
    }
    $updateset[] = "passkey=''";
}

$updateset[] = "passkey_ip = " . (!empty($_POST["passkey_ip"]) ? sqlesc(getip()) : "''");

$info = $_POST["info"] ?? '';
$stylesheet = $_POST["stylesheet"] ?? '';
$country = $_POST["country"] ?? '';

$icq = (int) unesc($_POST["icq"] ?? '');
if (strlen($icq) > 32)
    bark("Жаль, Номер icq слишком длинный  (Макс - 32)");
$updateset[] = "icq = " . sqlesc($icq);

$skype = unesc($_POST["skype"] ?? '');
if (strlen($skype) > 32)
    bark("Жаль, Ваш skype слишком длинный  (Макс - 32)");
$updateset[] = "skype = " . sqlesc(htmlspecialchars($skype));

if (is_valid_id($stylesheet))
    $updateset[] = "stylesheet = '$stylesheet'";
if (is_valid_id($country))
    $updateset[] = "country = $country";

$updateset[] = "info = " . sqlesc($info);
$updateset[] = "acceptpms = " . sqlesc($acceptpms);
$updateset[] = "deletepms = '$deletepms'";
$updateset[] = "savepms = '$savepms'";
$updateset[] = "avatar = " . sqlesc($avatar);
$updateset[] = "avatars = '$avatars'";
$updateset[] = "timezone = " . (int) ($_POST["timezone"] ?? 0);
$updateset[] = "dst = " . (!empty($_POST["dst"]) ? 60 : 0);

$urladd = "";

if ($changedemail) {
    $sec = mksecret();
    $hash = md5($sec . $email . $sec);
    $obemail = urlencode($email);
    $updateset[] = "editsecret = " . sqlesc($sec);
    $thishost = $_SERVER["HTTP_HOST"];
    $thisdomain = preg_replace('/^www\./is', "", $thishost);
    
    $body = <<<EOD
You have requested that your user profile (username {$CURUSER["username"]})
on $thisdomain should be updated with this email address ($email) as
user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address {$_SERVER["REMOTE_ADDR"]}. Please do not reply.

To complete the update of your profile, please follow this link:

http://$thishost/confirmemail.php?id={$CURUSER["id"]}&hash=$hash&email=$obemail

Your new email address will appear in your profile after you do this. Otherwise
your profile will remain unchanged.
EOD;

    mail($email, "$thisdomain profile change confirmation", $body, "From: $SITEEMAIL");
    $urladd .= "&mailsent=1";
}

sql_query("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);

// Clear user cache after update
if (isset($cache)) {
    $cache->delete('users_'.$CURUSER['id']);
}

header("Location: $DEFAULTBASEURL/my.php?edited=1" . $urladd);
?>