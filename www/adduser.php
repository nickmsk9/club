<?php
declare(strict_types=1);

require "include/bittorrent.php";
dbconn();
// Disable strict SQL mode to allow inserts without default values
global $mysqli;
mysqli_query($mysqli, "SET SESSION sql_mode = ''");
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
	stderr($lang['error'], $lang['access_denied']);
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
		stderr($lang['error'], $lang['missing_form_data']);
	if ($_POST["password"] != $_POST["password2"])
		stderr($lang['error'], $lang['password_mismatch']);
	$username = sqlesc(htmlspecialchars($_POST["username"]));
	$password = $_POST["password"];
	$email = sqlesc(htmlspecialchars($_POST["email"]));
	$secret = mksecret();
	$passhash = sqlesc(md5($secret . $password . $secret));
	$secret = sqlesc($secret);
	// Capture user IP for database
	$ip = sqlesc(getip());

	sql_query("INSERT INTO users (added, last_access, secret, username, passhash, old_password, editsecret, status, email, ip, avatar) VALUES(".sqlesc(get_date_time()).", ".sqlesc(get_date_time()).", $secret, $username, $passhash, ".sqlesc('').", ".sqlesc('').", 'confirmed', $email, $ip, ".sqlesc('').")") or sqlerr(__FILE__, (string)__LINE__);
	$res = sql_query("SELECT id FROM users WHERE username=$username");
	$arr = mysqli_fetch_row($res);
	if (!$arr)
		stderr($lang['error'], $lang['unable_to_create_account']);
	define ('REGISTER', true);
	define ('ACTIVATION', 'no');
	$id = $arr[0];
	unset($email);
	$email = trim($_POST["email"]);
	header("Location: $DEFAULTBASEURL/user/id$arr[0]");
	die;
}
stdhead($lang['add_user']);
?>
<h1><?=$lang['add_user'];?></h1>
<form method=post action=adduser.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead><?=$lang['username'];?></td><td><input type=text name=username size=40></td></tr>
<tr><td class=rowhead><?=$lang['password'];?></td><td><input type=password name=password size=40></td></tr>
<tr><td class=rowhead><?=$lang['repeat_password'];?></td><td><input type=password name=password2 size=40></td></tr>
<tr><td class=rowhead>E-mail</td><td><input type=text name=email size=40></td></tr>
<tr><td colspan=2 align=center><input type=submit value="OK" class=btn></td></tr>
</table>
</form>
<? stdfoot(); ?>