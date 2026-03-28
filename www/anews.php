<?php

require "include/bittorrent.php";

dbconn();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
	stderr($lang['error'], "Permission denied.");

$action = $_GET["action"];

//   Delete News Item    //////////////////////////////////////////////////////

if ($action == 'delete')
{
	$anewsid = (int)$_GET["anewsid"];
  if (!is_valid_id($anewsid))
  	stderr($lang['error'],"Invalid anews item ID - Code 1.");

  $returnto = htmlentities($_GET["returnto"]);

  $sure = $_GET["sure"];
  if (!$sure)
    stderr("Удалить Афишу","Вы действителньо хотите удалить эту Афишу? Нажмите\n" .
    	"<a href=?action=delete&anewsid=$anewsid&returnto=$returnto&sure=1>сюда</a> если вы уверены.");

  sql_query("DELETE FROM anews WHERE id=$anewsid") or sqlerr(__FILE__, __LINE__);

	if ($returnto != "")
		header("Location: $returnto");
	else
		$warning = "Афиша <b>успешно</b> удалена";
}

//   Add News Item    /////////////////////////////////////////////////////////

if ($action == 'add')
{

	$subject = $_POST["subject"];
	if (!$subject)
		stderr($lang['error'],"Тема Афиши не может быть пустой!");

	$body = $_POST["body"];
	if (!$body)
		stderr($lang['error'],"Тело Афиши не может быть пустым!");

	$poster = $_POST["poster"];
	if (!$poster)
		stderr($lang['error'],"Укажите постер Афиши!");


	$screen = $_POST["screen"];
	$screen2 = $_POST["screen2"];
	$screen3 = $_POST["screen3"];

		
		
	$added = $_POST["added"];
	if (!$added)
		$added = sqlesc(get_date_time());

  sql_query("INSERT INTO anews (userid, added, body, poster, screen, screen2, screen3, subject) VALUES (".
  	$CURUSER['id'] . ", $added, " . sqlesc($body) . "," . sqlesc($poster) . "," . sqlesc($screen) . "," . sqlesc($screen2) . "," . sqlesc($screen3) . ", " . sqlesc($subject) . ")") or sqlerr(__FILE__, __LINE__);
	if (mysql_affected_rows() == 1)
		$warning = "Афиша <b>успешно добавлена</b>";
	else
		stderr($lang['error'],"Только-что произошло что-то непонятное.");
}

//   Edit News Item    ////////////////////////////////////////////////////////

if ($action == 'edit')
{

	$anewsid = (int)$_GET["anewsid"];

  if (!is_valid_id($anewsid))
  	stderr($lang['error'],"Invalid anews item ID - Code 2.");

  $res = sql_query("SELECT * FROM anews WHERE id=$anewsid") or sqlerr(__FILE__, __LINE__);

	if (mysql_num_rows($res) != 1)
	  stderr($lang['error'], "No news item with ID.");

	$arr = mysql_fetch_array($res);

  if ($_SERVER['REQUEST_METHOD'] == 'POST')
  {
  	$body = $_POST['body'];
  	$subject = $_POST['subject'];
	$poster = $_POST['poster'];
	$screen = $_POST['screen'];
	$screen2 = $_POST['screen2'];
	$screen3 = $_POST['screen3'];
	$subject = $_POST["subject"];
	
	if ($subject == "")
		stderr($lang['error'],"Тема Афиши не может быть пустой!");

    if ($body == "")
    	stderr($lang['error'], "Тело Афиши не может быть пустым!");
    if ($poster == "")
    	stderr($lang['error'], "Укажите постер Афиши!");


    $body = sqlesc($body);

    $subject = sqlesc($subject);
	$poster = sqlesc($poster);
	$screen = sqlesc($screen);
	$screen2 = sqlesc($screen2);
	$screen3 = sqlesc($screen3);
    $editedat = sqlesc(get_date_time());

    sql_query("UPDATE anews SET body=$body, poster=$poster, screen=$screen, screen2=$screen2, screen3=$screen3, subject=$subject WHERE id=$anewsid") or sqlerr(__FILE__, __LINE__);

    $returnto = htmlentities($_POST['returnto']);

		if ($returnto != "")
			header("Location: $returnto");
		else
			$warning = "Афиша <b>успешно</b> отредактирована";
  }
  else
  {
 	 	$returnto = htmlentities($_GET['returnto']);
	  stdhead("Редактирование Афиши");
	   	begin_main_frame();
	begin_frame(true);

	  print("<form method=post id=anews name=anews action=?action=edit&anewsid=$anewsid>\n");
	  print("<table border=1 cellspacing=0 cellpadding=5 width=95%>\n");
	  print("<tr><td class=colhead>Редактирование Афиши<input type=hidden name=returnto value=$returnto></td></tr>\n");
	  print("<tr><td>Тема: <input type=text name=subject  size=50 value=\"" . htmlspecialchars($arr["subject"]) . "\"/></td></tr>");
	  print("<tr><td>Постер: <input type=text name=poster  size=50 value=\"" . htmlspecialchars($arr["poster"]) . "\"/></td></tr>");
	  print("<tr><td style='padding: 0px'>");
	  textbbcode("anews","body",htmlspecialchars($arr["body"]));
	  //<textarea name=body cols=145 rows=5 style='border: 0px'>" . htmlspecialchars($arr["body"]) . 
	  print("</textarea></td></tr>\n");
	  print("<tr><td>Скрин: <input type=text name=screen  size=50 value=\"" . htmlspecialchars($arr["screen"]) . "\"/></td></tr>");
	  print("<tr><td>Скрин2: <input type=text name=screen2  size=50 value=\"" . htmlspecialchars($arr["screen2"]) . "\"/></td></tr>");
	  print("<tr><td>Скрин3: <input type=text name=screen3  size=50 value=\"" . htmlspecialchars($arr["screen3"]) . "\"/></td></tr>");

	  print("<tr><td align=center><input type=submit value='Отредактировать'></td></tr>\n");
	  print("</table>\n");
	  print("</form>\n");
	  	end_frame();
	end_main_frame();
	  stdfoot();
	  die;
  }
}

//   Other Actions and followup    ////////////////////////////////////////////

stdhead("Афиши"); 
	begin_main_frame();
	begin_frame(true);
if ($warning)
print("<p><font size=-3>($warning)</font></p>");
print("<form method=post id=anews name=anews action=?action=add>\n");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead>Добавить Афишу</td></tr>\n");
print("<tr><td>Тема: <input type=text name=subject size=50 value=\"" . htmlspecialchars($arr["subject"]) . "\"/></td></tr>");
print("<tr><td>Постер: <input type=text name=poster  size=50 value=\"" . htmlspecialchars($arr["poster"]) . "\"/></td></tr>");
print("<tr><td style='padding: 0px'>");
textbbcode("anews","body","");
//<textarea name=body cols=145 rows=5 style='border: 0px'>
print("</textarea></td></tr>\n");
print("<tr><td>Скрин: <input type=text name=screen size=50 value=\"" . htmlspecialchars($arr["screen"]) . "\"/></td></tr>");
print("<tr><td>Скрин2: <input type=text name=screen2  size=50 value=\"" . htmlspecialchars($arr["screen2"]) . "\"/></td></tr>");
print("<tr><td>Скрин3: <input type=text name=screen3  size=50 value=\"" . htmlspecialchars($arr["screen3"]) . "\"/></td></tr>");

print("<tr><td align=center><input type=submit value='Добавить' class=btn></td></tr>\n");
print("</table></form><br /><br />\n");
	end_frame();
	end_main_frame();
$res = sql_query("SELECT * FROM anews ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) > 0)
{


 	begin_main_frame();
	begin_frame(true);

	while ($arr = mysql_fetch_array($res))
	{
		$anewsid = $arr["id"];
		$body = $arr["body"];
		$subject = $arr["subject"];
		$poster = $arr["poster"];
		$screen = $arr["screen"];
		$screen2 = $arr["screen2"];
		$screen3 = $arr["screen3"];
	  $userid = $arr["userid"];
	  $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " назад)";

    $res2 = sql_query("SELECT username, donor FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr2 = mysql_fetch_array($res2);

    $postername = $arr2["username"];

    if ($postername == "")
    	$by = "Неизвестно [$userid]";
    else
    	$by = "<a href=user/id$userid><b>$postername</b></a>" .
    		($arr2["donor"] == "yes" ? "<img src=pic/star.gif alt='Donor'>" : "");

	  print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");
    print("Добавлена $added&nbsp;-&nbsp;$by");
    print(" - [<a href=?action=edit&anewsid=$anewsid><b>Редактировать</b></a>]");
    print(" - [<a href=?action=delete&anewsid=$anewsid><b>Удалить</b></a>]");
    print("</td></tr></table></p>\n");

	 
	}
	end_frame();
	end_main_frame();
}
else
  stdmsg("Извините", "Афиш нет!");
stdfoot();
die;
?>