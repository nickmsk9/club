<?php

require "include/bittorrent.php";
dbconn(false);
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR) die('Access denied, u\'re not sysop'); 


stdhead("Общее сообщение", false);
begin_main_frame();
?>
<table class=main width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td class=embedded>
<div align=center>
<form method=post id=messages name=messages action=takestaffmess.php>
<?
$body = '';
if (!empty($_GET["returnto"]) || !empty($_SERVER["HTTP_REFERER"]))
{
?>
<input type="hidden" name="returnto" value="<?= htmlspecialchars(!empty($_GET["returnto"]) ? $_GET["returnto"] : $_SERVER["HTTP_REFERER"]) ?>">
<?
}
?>
<table cellspacing=0 cellpadding=5>
<tr><td class="colhead" colspan="2">Общее сообщение всем членам администрации и пользователям</td></tr>
<tr>
<td>Кому отправлять:<br />
  <table style="border: 0" width="100%" cellpadding="0" cellspacing="0">
<?
	$per_line = 4;
	for ($class = UC_USER; $class <= UC_SYSOP; $class++) {
	if ($class % $per_line == 0 && $class > 0)
		echo '</tr><tr>';
?><td style="border: 0" width="20"><input type="checkbox" name="clases[]" value="<?=$class;?>">
</td>
<td style="border: 0"><?=get_user_class_name($class);?></td><?
	}
	while ($class % $per_line != 0) {
		$class++;
		?><td style="border: 0">&nbsp;</td><?
	}
     ?>
       <td style="border: 0">&nbsp;</td>
       <td style="border: 0">&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>
<TD colspan="2">Тема:
   <INPUT name="subject" type="text" size="70"></TD>
</TR>
<tr><td align="center">
<?textbbcode("messages","msg",$body);?>
<!--<textarea name=msg cols=80 rows=15><?=$body?></textarea>-->
</td></tr>
<tr>
<td colspan=2><div align="center"><b>Отправитель:&nbsp;&nbsp;</b>
<?=$CURUSER['username']?>
<input name="sender" type="radio" value="self" checked>
&nbsp; Система
<input name="sender" type="radio" value="system">
</div></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Отправить" class=btn></td></tr>
</table>
<?php $receiver = $receiver ?? 0; ?>
<input type=hidden name=receiver value=<?=$receiver?>>
</form>

 </div></td></tr></table>
<?
end_main_frame();
stdfoot();
?>