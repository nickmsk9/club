<?
require_once ("include/bittorrent.php");
dbconn ();
if (get_user_class() < UC_SYSOP) die('Access denied, u\'re not sysop'); 

global $SITENAME, $SITEEMAIL;
$res = mysql_query ( "SELECT email FROM users" ) or sqlerr ( __FILE__, __LINE__ );
$counter = mysql_affected_rows ();
while ( $a = mysql_fetch_assoc ( $res ) ) {
	
	$subject = $_POST ['subject'];
	if (! $subject)
		stderr ( $lang ['error'], "Пожалуста, введите тему!" );
	
	$msg = $_POST ['msg'];
	if (! $msg)
		stderr ( $lang ['error'], "Введите текст сообщения!" );
	
	$message = <<<EOD
$msg
EOD;
	sent_mail ( $a ["email"], $SITENAME, $SITEEMAIL, $subject, $message, false );
}
stdhead ( "Спамилка" );
stdmsg ( "Успешно..", "Рассылка завершена. Отправлено <b>$counter</b> сообщений" );
stdfoot ();
?>