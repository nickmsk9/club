<?
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR) {
die("Access denied!");}

if(isset($_POST["delmp"])) {
sql_query("DELETE FROM comments WHERE id IN (".implode(", ", $_POST['delmp']).")") or sqlerr(__FILE__, __LINE__);
}

//goback();
header("Refresh: 0; url=/commentslast.php");
?> 