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
  	if($_POST["gid"] > 0){
  $albumid = 0 + $_POST["gid"];
$res = sql_query("SELECT * FROM album WHERE id = ".sqlesc($albumid)."") or sqlerr(__FILE__,__LINE__);
$arr = mysql_fetch_array($res);
if (!$arr)
stderr($lang['error'], "Нет такой фотографии!!!");
$name = $arr[0];
$text = trim($_POST["text"]);
$torrentid = 0;
	  if (!$text)
			stderr($lang['error'], $lang['comment_cant_be_empty']);
  	}else{
     $torrentid = 0 + $_POST["tid"];
	  if (!is_valid_id($torrentid))
			stderr($lang['error'], $lang['invalid_id']);
		$res = sql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
		$arr = mysql_fetch_array($res);
		if (!$arr)
		  stderr($lang['error'], $lang['no_torrent_with_such_id']);
		$name = $arr[0];
	  $text = trim($_POST["text"]);
	  $albumid =0;
	  if (!$text)
			stderr($lang['error'], $lang['comment_cant_be_empty']);
  	}
	  sql_query("INSERT INTO comments (user, torrent, added, text, ori_text, ip,galary) VALUES (" .
	      $CURUSER["id"] . ",$torrentid, '" . get_date_time() . "', " . sqlesc($text) .
	       "," . sqlesc($text) . "," . sqlesc(getip()) . ",$albumid)")or sqlerr(__FILE__,__LINE__);

	  $newid = mysql_insert_id();
  	if(!$_POST["gid"]){
	  sql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $torrentid");
  	}

  	if($_POST["gid"]){
  		
	  header("Refresh: 0; url=album.php?id=$albumid&viewcomm=$newid#comm$newid");
  	}
	  die;
	}

  $torrentid = 0 + $_GET["tid"];
  if (!is_valid_id($torrentid))
		stderr($lang['error'], $lang['invalid_id']);

	$res = sql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_array($res);
	if (!$arr)
	  stderr($lang['error'], $lang['no_torrent_with_such_id']);

	stdhead("Добление комментария к \"" . $arr["name"] . "\"");

	print("<p><form name=\"comment\" method=\"post\" action=\"comment.php?action=add\">\n");
	print("<input type=\"hidden\" name=\"tid\" value=\"$torrentid\"/>\n");
?>
	<table class=main border=0 cellspacing=0 cellpadding=3>
	<tr>
	<td class="colhead">
<?
	print("".$lang['add_comment']." к \"" . htmlspecialchars($arr["name"]) . "\"");
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

	$res = sql_query("SELECT comments.id, text, comments.ip, comments.added, username, title, class, users.id as user, users.avatar, users.donor, users.enabled, users.warned, users.parked FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $torrentid ORDER BY comments.id DESC LIMIT 5");

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
  $commentid = 0 + $_GET["cid"];
  if (!is_valid_id($commentid))
		stderr($lang['error'], $lang['invalid_id']);

  $res = sql_query("SELECT c.*, t.name, t.id AS tid, u.username FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id JOIN users AS u ON c.user = u.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
  	stderr($lang['error'], $lang['invalid_id']);

	if($arr["galary"] >0){
		stdhead("Редактирование комментария к фотографие № \"" . htmlspecialchars($arr["galary"]) . "\"");
}else{
	stdhead("Редактирование комментария ($arr[galary]) к \"" . htmlspecialchars($arr["name"]) . "\"");
}

	$text = "[quote=$arr[username]]" . $arr["text"] . "[/quote]\n";

	print("<form method=\"post\" name=\"comment\" action=\"comment.php?action=add\">\n");
		if($arr["galary"] >0){
	print("<input type=\"hidden\" name=\"gid\" value=\"$arr[galary]\" />\n");

		}else{
	print("<input type=\"hidden\" name=\"tid\" value=\"$arr[tid]\" />\n");
		}
?>

	<table class=main border=0 cellspacing=0 cellpadding=3>
	<tr>
	<td class="colhead">
<?
	if($arr["galary"] >0){
		print("Редактирование комментария к фотографие № \"" . htmlspecialchars($arr["galary"]) . "\"");
}else{
	print("Редактирование комментария к \"" . htmlspecialchars($arr["name"]) . "\"");
}
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
  $commentid = 0 + $_GET["cid"];
  if (!is_valid_id($commentid))
		stderr($lang['error'], $lang['invalid_id']);

  $res = sql_query("SELECT c.*, t.name, t.id AS tid FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
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
if($arr["galary"] >0){
		  header("Refresh: 0; url=album.php?id=$arr[galary]&viewcomm=$commentid#comm$commentid");
}else{
		if ($returnto)
	  	header("Location: $returnto");
		else
		  header("Location: $DEFAULTBASEURL/");      // change later ----------------------
}
		  die;
	}

	if($arr["galary"] >0){
		stdhead("Редактирование комментария к фотографие № \"" . htmlspecialchars($arr["galary"]) . "\"");
}else{
	stdhead("Редактирование комментария ($arr[galary]) к \"" . htmlspecialchars($arr["name"]) . "\"");
}
	print("<form method=\"post\" name=\"comment\" action=\"comment.php?action=edit&amp;cid=$commentid\">\n");
	print("<input type=\"hidden\" name=\"returnto\" value=\"details/id{$arr["tid"]}&amp;viewcomm=$commentid#comm$commentid\" />\n");
	print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
?>

	<table class=main border=0 cellspacing=0 cellpadding=3>
	<tr>
	<td class="colhead">
<?
if($arr["galary"] >0){
		print("Редактирование комментария к фотографие № \"" . htmlspecialchars($arr["galary"]) . "\"");
}else{
	print("Редактирование комментария к \"" . htmlspecialchars($arr["name"]) . "\"");
}
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

  $commentid = 0 + $_GET["cid"];

  if (!is_valid_id($commentid))
		stderr($lang['error'], $lang['invalid_id']);

  $sure = $_GET["sure"];

  if (!$sure)
  {
		stderr($lang['delete']." ".$lang['comment'], sprintf($lang['you_want_to_delete_x_click_here'],$lang['comment'],"?action=delete&cid=$commentid&sure=1"));
  }


	$res = sql_query("SELECT torrent, galary FROM comments WHERE id=$commentid")  or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_array($res);
	if ($arr)
	$torrentid = $arr["torrent"];

	sql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__,__LINE__);
	if($arr["galary"] > 0){
		list($commentid) = mysql_fetch_row(sql_query("SELECT id FROM comments WHERE galary = ".$arr["galary"]." ORDER BY added DESC LIMIT 1"));
		$returnto = "album.php?id=$arr[galary]&viewcomm=$commentid#comm$commentid";
	}else{
	if ($torrentid && mysql_affected_rows() > 0)
	sql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $torrentid");
	
	list($commentid) = mysql_fetch_row(sql_query("SELECT id FROM comments WHERE torrent = $torrentid ORDER BY added DESC LIMIT 1"));

	$returnto = "details/id$torrentid&amp;viewcomm=$commentid#comm$commentid";
	}
	if ($returnto)
	  header("Location: $returnto");
	else
	  header("Location: $DEFAULTBASEURL/");      // change later ----------------------
	die;
}
elseif ($action == "vieworiginal")
{
	if (get_user_class() < UC_MODERATOR)
		stderr($lang['error'], $lang['access_denied']);

  $commentid = 0 + $_GET["cid"];

  if (!is_valid_id($commentid))
		stderr($lang['error'], $lang['invalid_id']);

  $res = sql_query("SELECT c.*, t.name, t.id AS tid FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
  $arr = mysql_fetch_array($res);
  if (!$arr)
  	stderr($lang['error'], "Неверный идентификатор $commentid.");

  stdhead("Просмотр оригинала");
  print("<h1>Оригинальное содержание комментария №$commentid</h1><p>\n");
	print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
  print("<tr><td class=comment>\n");
	echo htmlspecialchars($arr["ori_text"]);
  print("</td></tr></table>\n");
if($arr["galary"] > 0){
  $returnto = "album.php?id=$arr[galary]&viewcomm=$commentid#comm$commentid";
}else{
  $returnto = "details/id{$arr["tid"]}&amp;viewcomm=$commentid#comm$commentid";
}
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