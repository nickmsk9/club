<?
require_once("include/bittorrent.php");

dbconn();

if (get_user_class() < UC_SYSOP) {
	die;
}

require_once($rootpath . 'include/cleanup.php');

$s_s = $queries;
docleanup();
$s_e = $queries;

stdhead("Очистка трекера");
stdmsg("Готово", "Очистка завершена успешно. На очистку использовано ".($s_e - $s_s)." запрос(ов).");
stdfoot();
?>