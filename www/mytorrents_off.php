<?
require_once("include/bittorrent.php");

dbconn(false);

loggedinorreturn();

stdhead("Мои торренты",'all');
begin_main_frame();
$where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes' AND visible = 'no' AND seeders = '0'";
$res = sql_query("SELECT COUNT(*) FROM torrents $where");
$row = mysql_fetch_array($res);
$count = $row[0];

if (!$count) {
	stdmsg("Все пучком !", "Все ваши торренты в порядке !");
end_main_frame();

	stdfoot();
	die();
}
else {
?>
<table class="embedded" cellspacing="0" cellpadding="3" width="100%">
<tr><td class="colhead" align="center" colspan="12">Мои торренты</td></tr>
<?

	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "mytorrents_off.php?");

$res = array();

        $query = "SELECT torrents.* FROM torrents $where ORDER BY id DESC $limit";

	$result_res = sql_query($query) or sqlerr(__FILE__, __LINE__);
	while ($result_row = mysql_fetch_assoc($result_res))
	{
		$res[]=$result_row;
		$torrents_name[]=$result_row["name"];
	}
	print("<tr><td class=\"index\" colspan=\"12\">");
	print($pagertop);
	print("</td></tr>");

	torrenttable($res, "mytorrents");

	print("<tr><td class=\"index\" colspan=\"12\">");
	print($pagerbottom);
	print("</td></tr>");

	print("</table>");

}
end_main_frame();
stdfoot('all');

?>