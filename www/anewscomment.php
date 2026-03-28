<?
require_once("include/bittorrent.php");

$action = $_GET["action"];

dbconn(false);

loggedinorreturn();
parked();

if ($action == "add")
{
  if ($_SERVER["REQUEST_METHOD"] == "POST")
  {
    if(!is_valid_id($_POST["anid"])) stderr($lang["error"],$lang["invalid_id"]);
    
    $anid = 0 + $_POST["anid"];
	  $text = trim($_POST["text"]);
	  if (!$text)
			stderr($lang['error'], $lang['comment_cant_be_empty']);

    
	  sql_query("INSERT INTO comments (user, anews, added, text, ori_text, ip) VALUES (" .
	      $CURUSER["id"] . ",$anid, '" . get_date_time() . "', " . sqlesc($text) .
	       "," . sqlesc($text) . "," . sqlesc(getip()) . ")") or die(mysql_error());
	       
	       $anewid = mysql_insert_id();
     
	  header("Refresh: 0; url=anewsoverview.php?id=$anid&viewcomm=$anewid#comm$anewid");
	  die;
	}

  if (!is_valid_id($_GET["anid"]))
		stderr($lang['error'], $lang['invalid_id']);
  $anid = 0 + $_GET["anid"];

	stdhead("Добление комментария к новости");

	print("<p><form name=\"comment\" method=\"post\" action=\"anewscomment.php?action=add\">\n");
	print("<input type=\"hidden\" name=\"anid\" value=\"$anid\"/>\n");
?>
	<table class="main" border="0" cellspacing="0" cellpadding="3">
	<tr>
	<td class="colhead">
<?
	print("".$lang['add_comment']." к новости");
?>
	</td>
	</tr>
	<tr>
	<td>
<?
	textbbcode("comment","text","");
?>
	</td></tr></table>
<?
	//print("<textarea name=\"text\" rows=\"10\" cols=\"60\"></textarea></p>\n");
	print("<p><input type=\"submit\" value=\"Добавить\" /></p></form>\n");

	$res = sql_query("SELECT comments.id, text, comments.ip, comments.added, username, title, class, users.id as user, users.avatar, users.donor, users.enabled, users.warned, users.parked FROM comments LEFT JOIN users ON comments.user = users.id WHERE anews = $anid ORDER BY comments.id DESC");

	$allrows = array();
	while ($row = mysql_fetch_array($res))
	  $allrows[] = $row;

	if (count($allrows)) {
	  print("<h2>Последние комментарии, в обратном порядке</h2>\n");
	  commenttable($allrows);
	}

  stdfoot();
	die;
}
elseif ($action == "quote")
{
  if (!is_valid_id($_GET["cid"]))
		stderr($lang['error'], $lang['invalid_id']);
  $commentid = 0 + $_GET["cid"];
  $res = sql_query("SELECT c.*, a.id AS aid, u.username FROM comments AS c LEFT JOIN anews AS a ON c.anews = a.id JOIN users AS u ON c.user = u.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
  	stderr($lang['error'], $lang['invalid_id']);

 	stdhead("Добавления комментария");

	$text = "[quote=$arr[username]]" . $arr["text"] . "[/quote]\n";

	print("<form method=\"post\" name=\"comment\" action=\"anewscomment.php?action=add\">\n");
	print("<input type=\"hidden\" name=\"anid\" value=\"$arr[anid]\" />\n");
?>

	<table class="main" border="0" cellspacing="0" cellpadding="3">
	<tr>
	<td class="colhead">
<?
	print("Добавления комментария");
?>
	</td>
	</tr>
	<tr>
	<td>
<?
	textbbcode("comment","text",htmlspecialchars($text));
?>
	</td></tr></table>

<?

	print("<p><input type=\"submit\" value=\"Добавить\" /></p></form>\n");

	stdfoot();

}
elseif ($action == "edit")
{
  if (!is_valid_id($_GET["cid"]))
		stderr($lang['error'], $lang['invalid_id']);
  $commentid = 0 + $_GET["cid"];
  $res = sql_query("SELECT c.*, a.id AS anid FROM comments AS c LEFT JOIN anews AS a ON c.anews = a.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
  	stderr($lang['error'], $lang['invalid_id']);

	if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
		stderr($lang['error'], $lang['access_denied']);

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
	  $text = $_POST["text"];
    $returnto = $_POST["returnto"];

	  if ($text == "")
	  	stderr($lang['error'], $lang['comment_cant_be_empty']);
	  	
	  $text = sqlesc($text);

	  $editedat = sqlesc(get_date_time());

	  sql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);


		if ($returnto)
	  	header("Location: $returnto");
		else
		  header("Location: url=anewsoverview.php?id=$anid");      // change later ----------------------
		die;
	}

 	stdhead("Редактирование комментария к новости");

	print("<form method=\"post\" name=\"comment\" action=\"anewscomment.php?action=edit&amp;cid=$commentid\">\n");
	print("<input type=\"hidden\" name=\"returnto\" value=\"anewsoverview.php?id=".$arr['anid']."&amp;viewcomm=$commentid#comm$commentid\" />\n");
	print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
?>

	<table class="main" border="0" cellspacing="0" cellpadding="3">
	<tr>
	<td class="colhead">
<?
	print("Редактирование комментария");
?>
	</td>
	</tr>
	<tr>
	<td>
<?
	textbbcode("comment","text",htmlspecialchars($arr["text"]));
?>
	</td></tr></table>

<?

	print("<p><input type=\"submit\" value=\"Отредактировать\" /></p></form>\n");

	stdfoot();
	die;
}

elseif ($action == "delete")
{
	if (get_user_class() < UC_MODERATOR)
		stderr($lang['error'], $lang['access_denied']);

  if (!is_valid_id($_GET["cid"]))
		stderr($lang['error'], $lang['invalid_id']);
		  $commentid = 0 + $_GET["cid"];


	$res = sql_query("SELECT anews FROM comments WHERE id=$commentid")  or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_array($res);
	if ($arr)
		$anid = $arr["anews"];

	sql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__,__LINE__);

 
	list($commentid) = mysql_fetch_row(sql_query("SELECT id FROM comments WHERE anews = $anid ORDER BY added DESC LIMIT 1"));

	$returnto = "anewsoverview.php?id=$anid&amp;viewcomm=$commentid#comm$commentid";

	if ($returnto)
	  header("Location: $returnto");
	else
	  header("Location: url=anewsoverview.php?id=$anid");      // change later ----------------------
	die;
}
elseif ($action == "vieworiginal")
{
	if (get_user_class() < UC_MODERATOR)
		stderr($lang['error'], $lang['access_denied']);


  if (!is_valid_id($_GET["cid"]))
		stderr($lang['error'], $lang['invalid_id']);
  $commentid = 0 + $_GET["cid"];
  $res = sql_query("SELECT ac.*, a.id AS aid FROM comments AS c LEFT JOIN anews AS a ON c.anews = a.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
  	stderr($lang['error'], "Неверный идентификатор $commentid.");

  stdhead("Просмотр оригинала");
  print("<h1>Оригинальное содержание комментария №$commentid</h1><p>\n");
	print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
  print("<tr><td class=comment>\n");
	echo htmlspecialchars($arr["ori_text"]);
  print("</td></tr></table>\n");

  $returnto = "anewsoverview.php?id=".$arr["anid"]."&amp;viewcomm=$commentid#comm$commentid";

//	$returnto = "details/id$torrentid&amp;viewcomm=$commentid#$commentid";

	if ($returnto)
 		print("<p><font size=small><a href=$returnto>Назад</a></font></p>\n");

	stdfoot();
	die;
}
else
	stderr($lang['error'], "Unknown action");

die;
?>