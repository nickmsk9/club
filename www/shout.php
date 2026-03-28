<?php
require_once("include/bittorrent.php");
include("include/class/jsend.class.php");
global $gzip,$memcache_obj,$CURUSER,$dbconn;;
dbconn();
gzip();
$jSEND = new jSEND(); 

header ("Content-Type: text/html; charset=" . $lang['language_charset']);
	
if($CURUSER)
{
if($CURUSER['enabled']== 'no')
die("Not Found");

$chat_ban_until = $CURUSER['chat_ban_until'];
$chat_ban = $CURUSER["chat_ban"] == "yes";
if ($chat_ban == 'yes'){

if ($chat_ban_until == '0000-00-00 00:00:00')
$S =("<td align=\"center\">На неограниченый срок</td></tr>\n");
else 
$S = ("<table border=\"0\" width=\"100%\" id=\"chatbox\">\n<td align=\"center\"><b>Вы забанены в Чате ! <br />" . mkprettytime(strtotime($chat_ban_until) - gmtime()) . " осталось до снятия блокировки . </b></td></tr></table>\n");
die($S);

} 
if ($_POST["action"] == "add")
{

$text = $_POST["text"];
$text = $jSEND->getData($text);

if(get_user_class() < UC_SPOWER) {
	$bb[] = "#\[img\]([^?](?:[^\[]+|\[(?!url)).(gif|jpg|jpeg|png))\[/img\]#i";
	$html[] = "*[url=\\1]картинка[/url]*";
		$text = preg_replace($bb, $html, $text);
}
if ($text == "/clear" && get_user_class() == UC_SYSOP) 
{mysql_query("TRUNCATE TABLE shoutbox");}
   
$sender = $CURUSER["id"];
$username = $CURUSER["username"];
$class = $CURUSER['class'];
$warned = $CURUSER["warned"];
$donor = $CURUSER["donor"];
$gender = $CURUSER["gender"];
$enabled = $CURUSER["enabled"];
$parked = $CURUSER["parked"];
$datee = time();
 sql_query("INSERT INTO shoutbox (userid, class, username, date, text, warned, donor, gender, enabled,parked) VALUES (".implode(", ", array_map("sqlesc",  array($sender, $class, $username, $datee, $text, $warned, $donor, $gender, $enabled, $parked))).")") or sqlerr(__FILE__,__LINE__);    }
 elseif 
 ($_POST["action"] == "delete"){ 
 $id = (int) $_POST["id"];
 if(get_user_class() >= UC_MODERATOR)
 sql_query("DELETE FROM shoutbox WHERE date = $id") or sqlerr(__FILE__, __LINE__);
else die("Нет доступа.");} 
?><script language="JavaScript" type="text/javascript">
    jQuery(function() {
      jQuery('.delmess').click ( function(){
        var messid = jQuery(this).attr('id');
		var delite = confirm("Вы точно хотите удалть сообщение ?"); 
		if(delite==true)
		jQuery.post('shout.php',{'id':messid,'action':'delete'},function (response) {
        jQuery('#shout').empty();
        jQuery('#shout').append(response);
		});
	});
	jQuery('#chatbox tr:odd').css('background-color', '#FDFDFD');
	jQuery('#chatbox tr:even').css('background-color', '#F4F0E8');


	});
	</script>
<?
echo("<table border=\"0\" width=\"100%\" id=\"chatbox\" style=\"word-wrap: break-word;\" >\n");
?>
<script>
</script>
<?

$res = sql_query("SELECT * FROM shoutbox ORDER BY date DESC LIMIT 25") or sqlerr(__FILE__, __LINE__);
				while ($arr = mysql_fetch_array($res)) 
 {
 
 
if (get_user_class() >= UC_MODERATOR)
$del = " <span class=\"delmess\" id=\"" . $arr['date'] . "\" style=\"cursor:pointer;\"><img src=\"pic/delc.png\" title=\"Удалить сообщение\" /></span>\n";
if($arr['gender'] == 1)
$profile = "<span><a href=\"user/id".$arr['userid']."\"><img src=\"pic/chatm.png\" border=\"0px\" title=\"Посмотреть профиль\" /></a></span>\n";
elseif ($arr['gender'] == 2)
$profile = "<span><a href=\"user/id".$arr['userid']."\"><img src=\"pic/chatf.png\" border=\"0px\" title=\"Посмотреть профиль\" /></a></span>\n";

$priv = "<span onclick=\"parent.document.shbox.text.focus();parent.document.shbox.text.value='privat(".$arr["username"].") '+parent.document.shbox.text.value;return false;\" style=\"cursor: pointer; color: red; font-weight: bold;\"><img src=\"pic/privc.png\" border=\"0px\" title=\"Приват\" /></span>\n";
$name = "<a style=\"cursor: pointer;\" onClick=\"parent.document.shbox.text.focus();parent.document.shbox.text.value='[b]".$arr["username"]."[/b]: '+parent.document.shbox.text.value;return false;\">".get_user_class_color($arr["class"], $arr["username"]) .get_user_icons_chat($arr)."</a>";
$datum = gmdate("H:i:s",$arr["date"] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);
		
		$id = $arr["date"];
		$uid = $arr["userid"];
		
		if(false === ($atext = $memcache_obj->get('shout'.$id.$uid)));
		{
		$atext = format_comment($arr["text"]);
		$memcache_obj->set('shout'.$id.$uid, $atext, 0, 600); 
		}
		$arr['text'] = $atext;
		
if (strpos($arr["text"], "privat($CURUSER[username])") !== false) {
$variabila = "privat($CURUSER[username])";
$nume = substr($variabila, 7);
$nume = substr($nume, 0, strlen($nume)-1);
if (($CURUSER["username"] == $nume) || ($CURUSER["id"] == "".$arr["userid"]."")) {
$arr["text"] = str_replace("privat($CURUSER[username])","<b style='background: red;color: white;'>$CURUSER[username]</b>:",$arr["text"]); 
$arr["text"] = preg_replace("/privat\(([^()<>\s]+?)\)/i","<b style='color: #000000; background: #FFFFFF;'>\\1</b>", $arr["text"]);
echo("<tr><td>\n<span class=\"date\">[" . $datum . "]</span>$del$profile$priv$name : ".$arr["text"]."\n</td></tr>\n");
}
} else
if ((($CURUSER["id"] == "".$arr["userid"]."") OR (get_user_class() >= UC_ADMINISTRATOR)) AND (get_user_class() >= $arr["class"]) AND (strpos($arr["text"], "privat(") !== false)) {
$arr["text"] = preg_replace("/privat\(([^()<>\s]+?)\)/i","<b style='color: red; background:white'>\\1</b>", $arr["text"]);
echo("<tr><td><span class=\"date\">[" . $datum . "]</span>$del$profile$priv$name : <span style=\"background-color:#a4fdfe\">".$arr["text"]."</span></td></tr>\n");
 } elseif (strpos($arr["text"], "privat(") !== false) 
 { }
 else {
	if ($arr["userid"] === $CURUSER["id"]) {
	echo("<tr><td><span class=\"date\">[" . $datum . "]</span>$del $profile$priv<u><b>$name</b></u> : <font color=\"#3D3A3A\">".$arr["text"]."</font></td></tr>\n");
	} else {
	if (strpos($arr['text'],"<strong>".$CURUSER["username"]."</strong>") !== false) {

	$arr["text"] = str_replace("$CURUSER[username]","<u><strong>$CURUSER[username]</strong></u>",$arr["text"]); 
	echo("<tr><td><span class=\"date\">[" . $datum . "]</span>$del $profile$priv$name: <font color=\"#3D3A3A\">".$arr["text"]."</font></td></tr>\n");
	}else
	echo("<tr><td><span class=\"date\">[" . $datum . "]</span>$del $profile$priv$name: ".$arr["text"]."</td></tr>\n");
}
}
}echo("</table>");
} else die('Прямой доступ закрыт'); 
?>