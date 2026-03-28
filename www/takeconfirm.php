<?

require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();

$id = 0 +  (int)$_GET["id"];
if (!is_valid_id($id))
	stderr("Ошибка", "А вот этого лучше не делать...");
if (isset($_POST["conusr"]))
	sql_query("UPDATE users SET status = 'confirmed' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST["conusr"])) . ") AND status = 'pending'") or sqlerr(__FILE__,__LINE__);
?>