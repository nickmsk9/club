<?php
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();

global $mcache, $mysqli, $lang, $CURUSER;
// Инициализация Memcached
if (!isset($mcache) || !($mcache instanceof Memcached)) {
    $mcache = new Memcached();
    $mcache->addServer('localhost', 11211);
}



$id = (int)$_GET["id"];
if (!$id)
	die();
	
$mcache->delete_value('torrent_'.$id);
$mcache->delete_value('torrent_desc'.$id);
$res = $mysqli->query("SELECT * FROM torrents WHERE id = $id");
if (!$res) {
    stderr("Ошибка", "Ошибка базы данных: " . $mysqli->error);
}
$row = $res->fetch_assoc();
if (!$row)
	die();

stdhead("Редактирование торрента \"" . htmlspecialchars($row["name"]) . "\"");
begin_main_frame();

print"<script language=\"javascript\" type=\"text/javascript\" src=\"js/show_hide.js\"></script>";
?>
	<script type="text/javascript" src="markitup/jquery.markitup.pack.js"></script>
<script type="text/javascript" src="markitup/sets/bbcode/set.js"></script>
<link rel="stylesheet" type="text/css" href="markitup/skins/simple/style.css" />
<link rel="stylesheet" type="text/css" href="markitup/sets/bbcode/style.css" />
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('#descr').markItUp(mySettings);			
});
</script>
<span style="cursor: pointer;" onclick="javascript: show_hide('s')"><img border="0" src="pic/plus.gif" id="pics" alt="<?= isset($lang['n_show']) ? htmlspecialchars($lang['n_show'], ENT_QUOTES) : 'Показать'; ?>"></span>&nbsp;
			<span style="cursor: pointer;" onclick="javascript: show_hide('s')">
			<b><font color="blue"><u>[Подсказки]</u></font></b></span>
			<span id="ss" style="display: none;">
			<?
			begin_frame('Подсказка по описанию');
begin_table();
?> 
<p>Любые отклонения от шаблона описания могут быть сочтены за грубую ошибку, и раздача будет удалена, а Вы получите предупреждение.</p>

<p>После второго предупреждения, Вы будете сняты с должности.</p>

<p>Для успешного оформления, внимательно изучите шаблон.</p>

<p>В полях "Постер" и "Скриншоты" указывайте ссылки на картинки со стороннего фото-хостинга. Ссылка должна выглядеть примерно так - http://<?php echo $_SERVER['HTTP_HOST']; ?>/poster.jpg</p>


<?
end_table();
end_frame();

begin_frame('Тэги иконок для описания');
begin_table();
?>
<tr><td>
<br>
<img src="/pic/thq/vo_rus.png" alt="Изображение"> - *vo_rus*<br>
<img src="/pic/thq/vo_eng.png" alt="Изображение"> - *vo_eng*<br>
<img src="/pic/thq/vo_lat.png" alt="Изображение"> - *vo_lat*<br>
<img src="/pic/thq/vo_other.png" alt="Изображение"> - *vo_other*<br></td><td><br>
<img src="/pic/thq/mvo_rus.png" alt="Изображение"> - *mvo_rus*<br>
<img src="/pic/thq/mvo_eng.png" alt="Изображение"> - *mvo_eng*<br>
<img src="/pic/thq/mvo_lat.png" alt="Изображение"> - *mvo_lat*<br>
<img src="/pic/thq/mvo_other.png" alt="Изображение"> - *mvo_other*<br></td><td><br>
<img src="/pic/thq/dub_rus.png" alt="Изображение"> - *dub_rus*<br>
<img src="/pic/thq/dub_eng.png" alt="Изображение"> - *dub_eng*<br>
<img src="/pic/thq/dub_lat.png" alt="Изображение"> - *dub_lat*<br>
<img src="/pic/thq/dub_other.png" alt="Изображение"> - *dub_other*<br></td></tr>
<tr><td>
<br>Японский:<br>
<img src="/pic/thq/jap_snd.png" alt="Изображение"> - *jap_snd*<br><br>
</td><td>
<br>Хардсаб: <br>
<img src="/pic/thq/hsub_ru.png" alt="Изображение"> - *hsub_ru*<br>
<img src="/pic/thq/hsub_eng.png" alt="Изображение"> - *hsub_eng*<br>
<img src="/pic/thq/hsub_lv.png" alt="Изображение"> - *hsub_lv*<br>
<img src="/pic/thq/hsub_other.png" alt="Изображение"> - *hsub_other*<br>
</td><td><br>Софтсаб: <br>
<img src="/pic/thq/ssub_ru.png" alt="Изображение"> - *ssub_ru*<br>
<img src="/pic/thq/ssub_eng.png" alt="Изображение"> - *ssub_eng*<br>
<img src="/pic/thq/ssub_lv.png" alt="Изображение"> - *ssub_lv*<br>
<img src="/pic/thq/ssub_other.png" alt="Изображение"> - *ssub_other*
</td></tr><?
end_table();
end_frame();
?>
			</span>
<?
if (!isset($CURUSER) || ($CURUSER['id'] != $row['owner'] && get_user_class() < UC_MODERATOR)) {

    stdmsg(isset($lang['error']) ? $lang['error'] : 'Ошибка', 'Вы не можете редактировать этот торрент.');
} else {
	print("<form id=edit name=\"edit\" method=post action=takeedit.php enctype=multipart/form-data>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" colspan=\"2\">Редактировать торрент</td></tr>");
	tr($lang['torrent_file'], "<input type=file name=tfile size=80>\n", 1);
	tr($lang['torrent_name'], "<input type=\"text\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"80\" />", 1);
	tr($lang['images'], "<input type=\"text\" value=\"" . htmlspecialchars($row["image1"]) . "\" name=image1 size=80> <br />", 1);
if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false))
  $c = "";
else
  $c = " checked";
	//tr("Описание", "<textarea name=\"descr\" rows=\"10\" cols=\"80\">" . htmlspecialchars($row["ori_descr"]) . "</textarea><br />(HTML <b>не</b> разрешен. Нажмите <a href=tags.php>сюда</a> для получения информации о тегах.)", 1);
	print("<tr><td class=rowhead style='padding: 3px'>".$lang['description']."</td><td>");
	                    print("<textarea name=descr id=descr  cols=86 rows=18>".htmlspecialchars($row['ori_descr'])."</textarea>");

	//textbbcode("edit","descr",htmlspecialchars($row["ori_descr"]));
	?>
<?
	print("</td></tr>\n");
	tr($lang['scrshot'], "<input type=\"text\" value=\"" . htmlspecialchars($row["screen1"]) . "\" name=screen1 size=80> <br />", 1);
	tr($lang['scrshot'], "<input type=\"text\" value=\"" . htmlspecialchars($row["screen2"]) . "\" name=screen2 size=80> <br />", 1);
	tr($lang['scrshot'], "<input type=\"text\" value=\"" . htmlspecialchars($row["screen3"]) . "\" name=screen3 size=80> <br />", 1);

	$s = "<select name=\"type\">\n";

	$cats = genrelist();
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	tr("Тип", $s, 1);
      ////// МОД ТЭГОВ [by merdox] //////
  ?>
      <style type="text/css" media="screen">
          code {font:99.9%/1.2 consolas,'courier new',monospace;}
          #from a {margin:2px 2px;font-weight:normal;}
          #tags {width:36em;}
          a.selected {background:#c00; color:#fff;}
          .addition {margint-top:2em; text-align:right;}
      </style>
      <script type="text/javascript" src="js/tagto.js"></script>

      <script type="text/javascript">
          (function($){
              $(document).ready(function(){
                  $("#from").tagTo("#tags");
              });
          })(jQuery);
      </script>

  <?
  $s = '<input type="hidden" name="oldtags" value="' . htmlspecialchars($row["tags"]) . '"><input type="text" id="tags" name="tags" value="'. htmlspecialchars($row["tags"]).'"'.($add_tag ? "" : " readonly=\"readonly\"").'>';
  $s .= '<div id="from">';
  $tags = taggenrelist($row["category"]);
  foreach ($tags as $tag)
  	$s .= "<a href='#'>" . htmlspecialchars($tag["name"]) . "</a>\n";
  $s .= "</div>\n";
  tr("Тэги", $s, 1);
  ////// МОД ТЭГОВ [by merdox] //////

	tr("Видимый", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Видимый на главной<br /><table border=0 cellspacing=0 cellpadding=0 width=420><tr><td class=embedded>Обратите внимание, что торрент автоматически станет видмым когда появиться раздающий и автоматически перестанет быть видимым (станет мертвяком) когда не будет раздающего некоторое время. Используйте этот переключатель для ускорения процеса. Также учтите что невидимые торренты (мертвяки) все-равно могут быть просмотрены и найдены, это просто не по-умолчанию.</td></tr></table>", 1);
	if(get_user_class() >= UC_ADMINISTRATOR)
		tr("Забанен", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" />", 1);

	/*	
	$prc .= "<select name=\"free\">";
	for ($i = 00; $i <= 3; ++$i)
	{
	$selected = ($row['free'] == $i*10) ? " selected=\"selected\"" : "";
	$prc .= "<option value=".$i."0".$selected.">".$i."0</option>";
	}
	$prc .= "</select> процентов"; 
	tr("Скидка", "".$prc."<br /> Скидка на скачивание . Не учитывается процент довнлоад !!!", 1); 
		*/
		
		//if(get_user_class() >= UC_MODERATOR)
       // tr("Важный", "<input type=\"checkbox\" name=\"sticky\"" . (($row["sticky"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"yes\" /> Прикрепить этот торрент (всегда наверху)", 1);
	   		if(get_user_class() >= UC_ADMINISTRATOR)
		tr("Поднять", "<input type=\"checkbox\" name=\"update\" value=\"1\" /> Раздача будет стоять первая в списке торрентов", 1);
	   
	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Ок\" style=\"height: 25px; width: 100px\"> <input type=reset value=\"Обратить изменения\" style=\"height: 25px; width: 135px\"></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	print("<p>\n");
	/*
  print("<form method=\"post\" action=\"delete.php\">\n");
  print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
  print("<tr><td class=embedded style='background-color: #F5F4EA;padding-bottom: 5px' colspan=\"2\"><b>Удалить торрент</b> Причина:</td></tr>");
  print("<td><input name=\"reasontype\" type=\"radio\" value=\"1\">&nbsp;Мертвяк </td><td> 0 раздающих, 0 качающих = 0 соединений</td></tr>\n");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"2\">&nbsp;Дупликат</td><td><input type=\"text\" size=\"40\" name=\"reason[]\"></td></tr>\n");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"3\">&nbsp;Nuked</td><td><input type=\"text\" size=\"40\" name=\"reason[]\"></td></tr>\n");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"4\">&nbsp;Правила</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(Обязательно)</td></tr>");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"5\" checked>&nbsp;Другое:</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(Обязательно)</td></tr>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
  print("<td colspan=\"2\" align=\"center\"><input type=submit value='Удалить' style='height: 25px'></td></tr>\n");
  print("</table>");
	print("</form>\n");
	*/
	print("</p>\n");
}
end_main_frame();
stdfoot();

?>