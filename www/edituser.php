<?php
require_once("include/bittorrent.php");

if (!mkglobal("id"))
	die();

$id = 0 + $id;
if (!$id)
	die();

dbconn();

loggedinorreturn();

$res = sql_query("SELECT * FROM users WHERE id = $id");
$user = mysqli_fetch_array($res);
if (!$user)
	die();
$enabled = $user["enabled"] == 'yes';

stdhead("Редактирование пользователя \"" . $user["username"] . "\"");




if (get_user_class() >= UC_MODERATOR && $user["class"] <= get_user_class() )
{
begin_main_frame();
  begin_frame("Редактирование пользователя", true);
  print("<form method=\"post\" action=\"modtask.php\">\n");
  print("<input type=\"hidden\" name=\"action\" value=\"edituser\">\n");
  print("<input type=\"hidden\" name=\"userid\" value=\"$id\">\n");
  print("<input type=\"hidden\" name=\"returnto\" value=\"user/id$id\">\n");
  print("<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
  print("<tr><td class=\"rowhead\">Заголовок</td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"title\" value=\"" . htmlspecialchars($user['title']) . "\"></tr>\n");
	$avatar = htmlspecialchars($user["avatar"]);
  print("<tr><td class=\"rowhead\">Аватар</td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"avatar\" value=\"$avatar\"></tr>\n");
  print("<tr><td class=\"rowhead\">VIP до </td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"donate\" value=\"" . $user['vipuntil'] . "\"></tr>\n");
  print("<tr><td class=\"rowhead\">Бонусов</td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"bonus\" value=\"" . htmlspecialchars($user['bonus']) . "\"></tr>\n");
  if($CURUSER['id'] == 1)
  print("<tr><td class=\"rowhead\">Карма</td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"karma\" value=\"" . htmlspecialchars($user['karma']) . "\"></tr>\n");
  print("<tr><td class=\"rowhead\">Дата последнего торрента</td><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"last_upload\" value=\"" . htmlspecialchars($user['last_upload']) . "\"></tr>\n");

	// we do not want mods to be able to change user classes or amount donated...
	if ($CURUSER["class"] < UC_ADMINISTRATOR)
	  print("<input type=\"hidden\" name=\"donor\" value=\"{$user['donor']}\">\n");
	else {
	  print("<tr><td class=\"rowhead\">Донор</td><td colspan=\"2\" align=\"left\"><input type=\"radio\" name=\"donor\" value=\"yes\"" .($user["donor"] == "yes" ? " checked" : "").">Да <input type=\"radio\" name=\"donor\" value=\"no\"" .($user["donor"] == "no" ? " checked" : "").">Нет</td></tr>\n");
	}


	if (get_user_class() == UC_ADMINISTRATOR && $user["class"] > UC_MODERATOR)
	  print("<input type=\"hidden\" name=\"class\" value=\"{$user['class']}\">\n");
	else
	{
	  print("<tr><td class=\"rowhead\">Класс</td><td colspan=\"2\" align=\"left\"><select name=\"class\">\n");
	  if (get_user_class() == UC_SYSOP)
	  	$maxclass = UC_ADMINISTRATOR;
	  elseif (get_user_class() == UC_ADMINISTRATOR)
	    $maxclass = UC_UPLOADER;
	  else
	    $maxclass = get_user_class() - 0;
	  $prefix = "";
	  for ($i = 0; $i <= $maxclass; ++$i)
	    print("<option value=\"$i\"" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "</option>");
	  print("</select></td></tr>\n");
	}
	print("<tr><td class=\"rowhead\">Сбросить день рождения</td><td colspan=\"2\" align=\"left\"><input type=\"radio\" name=\"resetb\" value=\"yes\">Да<input type=\"radio\" name=\"resetb\" value=\"no\" checked>Нет</td></tr>\n");
	$modcomment = htmlspecialchars($user["modcomment"]);
	print("<tr><td class=rowhead>История пользователя</td><td colspan=2 align=left><textarea cols=60 rows=8".(get_user_class() < UC_SYSOP ? " readonly" : " name=modcomment").">$modcomment</textarea></td></tr>\n");
	print("<tr><td class=rowhead>Добавить заметку</td><td colspan=2 align=left>");
	textbbcode("modtask", "modcomm", "");
	print("</td></tr>\n");
	print("<tr><td class=rowhead>Инфо</td><td colspan=2 align=left>");
	textbbcode("modtask", "info", htmlspecialchars($user["info"] ?? ""));
	print("</td></tr>\n");
	$warned = $user["warned"] == "yes";


 	print("<tr><td class=\"rowhead\"" . (!$warned ? " rowspan=\"2\"": "") . ">Предупреждение</td>
 	<td align=\"left\" width=\"20%\">" .
  ( $warned
  ? "<input name=\"warned\" value=\"yes\" type=\"radio\" checked>Да<input name=\"warned\" value=\"no\" type=\"radio\">Нет"
 	: "Нет" ) ."</td>");

	if ($warned) {
		$warneduntil = $user['warneduntil'];
		if ($warneduntil == '0000-00-00 00:00:00')
    		print("<td align=\"center\">На неограниченый срок</td></tr>\n");
		else {
    		print("<td align=\"center\">До $warneduntil");
	    	print(" (" . mkprettytime(strtotime($warneduntil) - gmtime()) . " осталось)</td></tr>\n");
 	    }
  } else {
    print("<td>Предупредить на <select name=\"warnlength\">\n");
    print("<option value=\"0\">------</option>\n");
    print("<option value=\"1\">1 неделю</option>\n");
    print("<option value=\"2\">2 недели</option>\n");
    print("<option value=\"4\">4 недели</option>\n");
    print("<option value=\"8\">8 недель</option>\n");
    print("<option value=\"255\">Неограничено</option>\n");
    print("</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Комментарий в ЛС:</td></tr>\n");
    print("<tr><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"warnpm\"></td></tr>");
  }
  ////////////////////////////////////////////////////////
  
  
  	$chat_ban = $user["chat_ban"] == "yes";

 	print("<tr><td class=\"rowhead\"" . (!$chat_ban ? " rowspan=\"2\"": "") . ">Бан в чате</td>
 	<td align=\"left\" width=\"20%\">" .
  ( $chat_ban
  ? "<input name=\"chat_ban\" value=\"yes\" type=\"radio\" checked>Да<input name=\"chat_ban\" value=\"no\" type=\"radio\">Нет"
 	: "Нет" ) ."</td>");

	if ($chat_ban) {
		$chat_ban_until = $user['chat_ban_until'];
		if ($chat_ban_until == '0000-00-00 00:00:00')
    		print("<td align=\"center\">На неограниченый срок</td></tr>\n");
		else {
    		print("<td align=\"center\">До $chat_ban_until");
	    	print(" (" . mkprettytime(strtotime($chat_ban_until) - gmtime()) . " осталось)</td></tr>\n");
 	    }
  } else {
    print("<td>Бан в Чате на <select name=\"chat_ban_length\">\n");
    print("<option value=\"0\">------</option>\n");
    print("<option value=\"3\">3 часов</option>\n");
    print("<option value=\"6\">6 часов</option>\n");
    print("<option value=\"12\">12 часов</option>\n");
    print("<option value=\"24\">24 часа</option>\n");
    print("<option value=\"72\">3 дня</option>\n");
    print("<option value=\"144\">6 дней</option>\n");
    print("<option value=\"504\">2 недели</option>\n");
    print("<option value=\"555\">Неограничено</option>\n");
    print("</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Комментарий в ЛС:</td></tr>\n");
    print("<tr><td colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"chat_ban_pm\"></td></tr>");
  }
  
  ///////////////////////////////////////////////////////////
    print("<tr><td class=\"rowhead\" rowspan=\"2\">Включен</td><td colspan=\"2\" align=\"left\">
	<input name=\"enabled\" value=\"yes\" type=\"radio\"" . ($enabled ? " checked" : "") . ">Да <input name=\"enabled\" value=\"no\" type=\"radio\"" . (!$enabled ? " checked" : "") . ">Нет</td></tr>\n");
    if ($enabled)
    	print("<tr><td colspan=\"2\" align=\"left\">Причина отключения:&nbsp;<input type=\"text\" name=\"disreason\" size=\"60\" /></td></tr>");
	else
		print("<tr><td colspan=\"2\" align=\"left\">Причина включения:&nbsp;<input type=\"text\" name=\"enareason\" size=\"60\" /></td></tr>");
		
		
	print("<tr><td class=\"rowhead\" rowspan=\"2\">Загрузка торрентов</td><td colspan=\"2\" align=\"left\">
	<input type=\"radio\" name=\"upload\" value=\"yes\"" .($user["upload"] == "yes" ? " checked" : "").">Да <input type=\"radio\" name=\"upload\" value=\"no\"" .($user["upload"] == "no" ? " checked" : "").">Нет</td></tr>\n");
	print("<tr><td colspan=\"2\" align=\"left\">Причина отключения:&nbsp;<input type=\"text\" name=\"uplreason\" size=\"60\" /></td></tr>");

?>
<script type="text/javascript">
function togglepic(bu, picid, formid)
{
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);
    
    if(pic.src == bu + "/pic/plus.gif")
    {
        pic.src = bu + "/pic/minus.gif";
        form.value = "minus";
    }else{
        pic.src = bu + "/pic/plus.gif";
        form.value = "plus";
    }
}
</script>
<?php
  print("<tr><td class=\"rowhead\">Изменить раздачу</td><td align=\"left\"><img src=\"pic/plus.gif\" id=\"uppic\" onClick=\"togglepic('$DEFAULTBASEURL','uppic','upchange')\" style=\"cursor: pointer;\">&nbsp;<input type=\"text\" name=\"amountup\" size=\"10\" /><td>\n<select name=\"formatup\">\n<option value=\"mb\">MB</option>\n<option value=\"gb\">GB</option></select></td></tr>");
  print("<tr><td class=\"rowhead\">Изменить скачку</td><td align=\"left\"><img src=\"pic/plus.gif\" id=\"downpic\" onClick=\"togglepic('$DEFAULTBASEURL','downpic','downchange')\" style=\"cursor: pointer;\">&nbsp;<input type=\"text\" name=\"amountdown\" size=\"10\" /><td>\n<select name=\"formatdown\">\n<option value=\"mb\">MB</option>\n<option value=\"gb\">GB</option></select></td></tr>");
  print("<tr><td class=\"rowhead\">Сбросить passkey</td><td colspan=\"2\" align=\"left\"><input name=\"resetkey\" value=\"1\" type=\"checkbox\"></td></tr>\n");
  if ($CURUSER["class"] < UC_ADMINISTRATOR)
  	print("<input type=\"hidden\" name=\"deluser\">");
  else
  	print("<tr><td class=\"rowhead\">Удалить</td><td colspan=\"2\" align=\"left\"><input type=\"checkbox\" name=\"deluser\"></td></tr>");
  print("</td></tr>");
  print("<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\"ОК\"></td></tr>\n");
  print("</table>\n");
  print("<input type=\"hidden\" id=\"upchange\" name=\"upchange\" value=\"plus\"><input type=\"hidden\" id=\"downchange\" name=\"downchange\" value=\"plus\">\n");
  print("</form>\n");
  end_frame();
  end_main_frame();
}
stdfoot();

?>