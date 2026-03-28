<?php
if (!defined('BLOCK_FILE')) {
 Header("Location: ../index.php");
 exit;
}
global $CURUSER;
?>
<script src="js/j.send.js" type="text/javascript"></script>

<script src="js/chat_mem.js" type="text/javascript"></script>

<script language=javascript>
function winop()
{
windop = window.open("moresmiles.php?form=shbox&text=text","mywin","height=500,width=500,resizable=yes,scrollbars=yes");
}
function winop2()
{
windop2 = window.open("moresmiles2.php?form=shbox&text=text","mywin2","height=450,width=550,resizable=yes,scrollbars=yes");
}

</script>
<style>
.button {
cursor: pointer;
}
.toolbar {
float: left;
}
#room {
float: right;
font-size:15px;
}

</style>
<?
$blocktitle = "Чат <span id=\"loading-chat\"></span>";


$content .="
<form name=\"shbox\" id=\"shbox\" onsubmit=\"return false;\">
<table cellspacing=\"1\" cellpadding=\"5\" id=\"chatmain\" width=\"100%\">
<td style=\"white-space: nowrap\">
    <div class=\"toolbar\">
	<img class=\"button\" src=\"pic/bold.gif\" name=\"btnBold\" onClick=\"doAddTags('[b]','[/b]','text')\" alt=\"Жирным\">
    <img class=\"button\" src=\"pic/italic.gif\" name=\"btnItalic\" onClick=\"doAddTags('[i]','[/i]','text')\" alt=\"Наклон\">
	<img class=\"button\" src=\"pic/underline.gif\" name=\"btnUnderline\" onClick=\"doAddTags('[u]','[/u]','text')\" alt=\"Подчеркнутый\">
	<img class=\"button\" src=\"pic/link.gif\" name=\"btnLink\" onClick=\"doURL('text')\" alt=\"Вставить ссылку\">
	<img class=\"button\" src=\"pic/picture.gif\" name=\"btnPicture\" onClick=\"doImage('text')\"alt=\"Вставить картинку\"></div>";

	$content .="<br /><br />
<input type=\"text\" id=\"text\" name=\"shbox_text\" class=\"message\"  style=\"width: 60%\"  MAXLENGTH=\"500\" required=\"required\" x-webkit-speech=\"\" speech=\"\" onwebkitspeechchange=\"\" data-jq-watermark=\"processed\"/>
<input type=\"submit\" value=\"Отправить\" name=\"newshout\" class=\"btn say\" />
<input type=\"button\" value=\"Смайлы\" onClick=\"javascript:winop()\"  class=\"btn\"/>
<input type=\"button\" value=\"Смайлы2\" onClick=\"javascript:winop2()\" class=\"btn\"/>
<input type=\"button\" value=\"Архив Чата\" onclick=\"window.location.href ='shoutbox_history.php'\" class=\"btn\"/>
</td>
</form>
<tr><td>
<div id=\"shout\" style=\"overflow: auto; width:854px; height:550px;\">Загрузка ...</div>
</td></tr>
</table>
";

?>

