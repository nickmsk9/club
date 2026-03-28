<?php

require "include/bittorrent.php";

dbconn();
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR)
	stderr($lang['error'], "Permission denied.");

$action = isset($_GET['action']) ? $_GET['action'] : '';
$warning = '';

//   Delete News Item    //////////////////////////////////////////////////////

if ($action == 'delete')
{
	$newsid = (int)$_GET["newsid"];
  if (!is_valid_id($newsid))
  	stderr($lang['error'],"Invalid news item ID - Code 1.");

  $returnto = htmlentities($_GET["returnto"]);

  $sure = $_GET["sure"];
  if (!$sure)
    stderr("Удалить новость","Вы действителньо хотите удалить эту новость? Нажмите\n" .
    	"<a href=news.php?action=delete&newsid=$newsid&returnto=$returnto&sure=1>сюда</a> если вы уверены.");

  sql_query("DELETE FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);
  global $mysqli;
	if (mysqli_affected_rows($mysqli) === 1)
	{
		if ($returnto != "")
			header("Location: $returnto");
		else
			$warning = "Новость <b>успешно</b> удалена";
	}
}

//   Add News Item    /////////////////////////////////////////////////////////

if ($action == 'add')
{

	$subject = $_POST["subject"];
	if (!$subject)
		stderr($lang['error'],"Тема новости не может быть пустой!");

	$body = $_POST["body"];
	if (!$body)
		stderr($lang['error'],"Тело новости не может быть пустым!");

	if (isset($_POST['added']) && trim($_POST['added']) !== '') {
	    $added = sqlesc($_POST['added']);
	} else {
	    $added = sqlesc(get_date_time());
	}

  sql_query("INSERT INTO news (userid, added, body, subject) VALUES (".
  	$CURUSER['id'] . ", $added, " . sqlesc($body) . ", " . sqlesc($subject) . ")") or sqlerr(__FILE__, __LINE__);
  global $mysqli;
	if (mysqli_affected_rows($mysqli) === 1)
		$warning = "Новость <b>успешно добавлена</b>";
	else
		stderr($lang['error'],"Только-что произошло что-то непонятное.");
}

//   Edit News Item    ////////////////////////////////////////////////////////

if ($action == 'edit')
{

	$newsid = (int)$_GET["newsid"];

  if (!is_valid_id($newsid))
  	stderr($lang['error'],"Invalid news item ID - Code 2.");

  $res = sql_query("SELECT * FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

	if (mysqli_num_rows($res) !== 1)
	  stderr($lang['error'], "No news item with ID.");

	$arr = mysqli_fetch_assoc($res);

  if ($_SERVER['REQUEST_METHOD'] == 'POST')
  {
  	$body = $_POST['body'];
  	$subject = $_POST['subject'];


	$subject = $_POST["subject"];
	if ($subject == "")
		stderr($lang['error'],"Тема новости не может быть пустой!");

    if ($body == "")
    	stderr($lang['error'], "Тело новости не может быть пустым!");

    $body = sqlesc($body);

    $subject = sqlesc($subject);

    $editedat = sqlesc(get_date_time());

    sql_query("UPDATE news SET body=$body, subject=$subject WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

    $returnto = htmlentities($_POST['returnto']);

		if ($returnto != "")
			header("Location: $returnto");
		else
			$warning = "Новость <b>успешно</b> отредактирована";
  }
  else
  {
 	 	$returnto = htmlentities($_GET['returnto']);
	  stdhead("Редактирование новости");
	  print("<form method=post id=news name=news action=news.php?action=edit&newsid=$newsid>\n");
	  print("<table border=1 cellspacing=0 cellpadding=5>\n");
	  print("<tr><td class=colhead>Редактирование новости<input type=hidden name=returnto value=$returnto></td></tr>\n");
	  print("<tr><td>Тема: <input type=text name=subject size=70 value=\"" . htmlspecialchars($arr["subject"]) . "\"/></td></tr>");
	  print("<tr><td style='padding: 0px'>");
	  textbbcode("news","body",htmlspecialchars($arr["body"]));
	  //<textarea name=body cols=145 rows=5 style='border: 0px'>" . htmlspecialchars($arr["body"]) . 
	  print("</textarea></td></tr>\n");
	  print("<tr><td align=center><input type=submit value='Отредактировать'></td></tr>\n");
	  print("</table>\n");
	  print("</form>\n");
	  stdfoot();
	  die;
  }
}

//   Other Actions and followup    ////////////////////////////////////////////

stdhead("Новости");
if ($warning)
	print("<p><font size=-3>($warning)</font></p>");
// Значения по умолчанию для формы добавления
$defaultSubject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '';
$defaultBody = isset($_POST['body']) ? htmlspecialchars($_POST['body']) : '';
begin_main_frame();
print("<form method=post id=news name=news action=news.php?action=add>\n");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead>Добавить новость</td></tr>\n");
print("<tr><td>Тема: <input type=\"text\" name=\"subject\" size=\"70\" value=\"{$defaultSubject}\"/></td></tr>");
print("<tr><td style='padding: 0px'>");
textbbcode("news","body",$defaultBody);
//<textarea name=body cols=145 rows=5 style='border: 0px'>
print("</textarea></td></tr>\n");
print("<tr><td align=center><input type=submit value='Добавить' class=btn></td></tr>\n");
print("</table></form><br /><br />\n");
end_main_frame();

$res = sql_query("SELECT * FROM news ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0)
{


 	begin_main_frame();
	begin_frame();

	while ($arr = mysqli_fetch_assoc($res))
	{
		$newsid = $arr["id"];
		$body = $arr["body"];
		$subject = $arr["subject"];
	  $userid = $arr["userid"];
	  $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " назад)";

    $res2 = sql_query("SELECT username, donor FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr2 = mysqli_fetch_assoc($res2);

    $postername = $arr2["username"];

    if ($postername == "")
    	$by = "Неизвестно [$userid]";
    else
    	$by = "<a href=user/id$userid><b>$postername</b></a>" .
    		($arr2["donor"] == "yes" ? "<img src=pic/star.gif alt='Donor'>" : "");

	  print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");
    print("Добавлена $added&nbsp;-&nbsp;$by");
    print(" - [<a href=news.php?action=edit&newsid=$newsid><b>Редактировать</b></a>]");
    print(" - [<a href=news.php?action=delete&newsid=$newsid><b>Удалить</b></a>]");
    print("</td></tr></table></p>\n");

	  begin_table(true);
      print("<tr valign=top><td><b>".$subject."</b></td></tr>\n");
	  print("<tr valign=top><td class=comment>".format_comment($body)."</td></tr>\n");
	  end_table();
	}
	end_frame();
	end_main_frame();
}
else
  stdmsg("Извините", "Новостей нет!");
stdfoot();
die;
?>