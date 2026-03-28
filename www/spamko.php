<?php
require "include/bittorrent.php";
gzip();
dbconn(false);

loggedinorreturn();

if (get_user_class() < UC_SYSOP) {
    stderr($lang['error'], $lang['access_denied']);
    die();
}

if (get_user_class() == UC_SYSOP && isset($_POST["delmp"])) {
    // Безопасно получаем параметр page из $_GET
    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 0;
    $link = $_SERVER["PHP_SELF"] . "?page=" . $page;
    sql_query("DELETE FROM messages WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST["delmp"])) . ")");
    @header("Location: $DEFAULTBASEURL$link") or die("Перенаправление на эту же страницу.<script>setTimeout('document.location.href=\"$DEFAULTBASEURL$link\"', 10);</script>");
}

// Безопасно получаем параметры send и receiv из $_GET
$send = isset($_GET["send"]) ? (int)$_GET["send"] : 0;
$receiv = isset($_GET["receiv"]) ? (int)$_GET["receiv"] : 0;

if (!empty($receiv) && empty($send)) {
    $on_main = "<form action=" . $_SERVER["PHP_SELF"] . "><input type=hidden><input type=submit value='Главная страничка Всех сообщений' tn></form>";
    $rclastd = " class=\"a\" ";
    $for_pagers = "?receiv=" . $receiv . "&";
    $sql_rs = "WHERE receiver=" . sqlesc($receiv);
} elseif (!empty($send) && empty($receiv)) {
    $on_main = "<form action=" . $_SERVER["PHP_SELF"] . "><input type=hidden name=action value=on_main><input type=submit value='Главная страничка Всех сообщений' tn></form>";
    $sclastd = " class=\"a\" ";
    $for_pagers = "?send=" . $send . "&";
    $sql_rs = "WHERE sender=" . sqlesc($send);
} else {
    $sclastd = " class=\"b\" ";
    $rclastd = " class=\"b\" ";
    $for_pagers = "?";
    unset($sql_rs);
    unset($send);
    unset($receiv);
}

// Гарантируем, что $sql_rs определён хотя бы как пустая строка
if (!isset($sql_rs)) $sql_rs = "";

$res2 = sql_query("SELECT COUNT(*) FROM messages $sql_rs");
$row = mysqli_fetch_array($res2);
$count = $row[0];
$perpage = 60;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . $for_pagers);

stdhead("Спам контроль - $perpage из $count");
begin_main_frame();
?>
<script language="Javascript" type="text/javascript">
var checkflag = "false";
function check(field) {
    if (checkflag == "false") {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        checkflag = "true";
    } else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        checkflag = "false";
    }
}
</script>
<?
echo $pagertop;

$res = sql_query("SELECT m.msg,m.subject,m.unread, m.receiver,m.sender, m.id, m.added,
s.last_access AS sender_l, s.id AS sender_id ,s.username AS sender_u,s.class AS sender_c,
r.last_access AS receiver_l, r.id AS receiver_id, r.username AS receiver_u, r.class AS receiver_c
FROM messages AS m LEFT JOIN users AS s ON m.sender = s.id  LEFT JOIN users AS r ON m.receiver = r.id $sql_rs ORDER BY m.id DESC $limit") or sqlerr(__FILE__, __LINE__);

print("<table border=0 cellspacing=0 cellpadding=5>\n");
?>

<form method="post" action="<?=$_SERVER["PHP_SELF"];?>?page=<?=isset($_GET["page"]) ? (int)$_GET["page"] : 0;?>" name="form1">
<tr>
<td align=center>Отправитель</td>
<td align=center>Получатель</td>
<td align=center>UR</td>
<td align=center>Тема</td>
<td align=center>Содержание</td>
<td align=center>Дата</td>
<TD width="2%" class="colhead"><INPUT type="checkbox" title="<?=$lang['mark_all'];?>" value="<?=$lang['mark_all'];?>" onClick="this.value=check(document.form1.elements);"></TD>
</tr>
<?
while ($arr = mysqli_fetch_assoc($res)) {
    $re = "<br><a href=" . $_SERVER["PHP_SELF"] . "?receiv=" . $arr["receiver_id"] . "><img src=\"/pic/mail-markread.gif\" alt=\"Прочитать все входящие сообщения для " . $arr["receiver_u"] . "\" border='0'></a>";
    $receiver = "<center><a href=userdetails.php?id=" . $arr["receiver_id"] . "><b>" . get_user_class_color($arr["receiver_c"], $arr["receiver_u"]) . "</b></a>$re</center>";
    $last_access_r = $arr["receiver_l"];
    if ($arr["receiver_l"] == "0000-00-00 00:00:00")
        $last_access_r = $lang['never'];

    $se = "<br><a href=" . $_SERVER["PHP_SELF"] . "?send=" . $arr["sender_id"] . "><img src=\"/pic/mail-send.gif\" alt=\"Прочитать все исходящие сообщения от " . $arr["sender_u"] . "\" border='0'></a>";

    // кто послал сообщение
    if (!$arr["sender_u"]) {
        $sender = "<center><font color=red>[<b>id " . $arr["sender_id"] . "</b>]</font><br><b>Удален</b>$se</center>";
    } else {
        $sender = "<center><a href=userdetails.php?id=" . $arr["sender_id"] . "><b>" . get_user_class_color($arr["sender_c"], $arr["sender_u"]) . "</b></a>$se</center>";
    }
    if ($arr["sender"] == 0)
        $sender = "<center><font color=gray>[<b>System</b>]</font></center>";

    $msg = format_comment($arr["msg"]);
    $last_access_s = $arr["sender_l"];
    if ($arr["sender_l"] == "0000-00-00 00:00:00")
        $last_access_s = $lang['never'];
    $added = $arr["added"];
    if ($arr["receiver_l"] > $arr["added"])
        $added = "<i>" . $added . "</i>";

    $subject = htmlspecialchars_uni($arr["subject"]);
    if ($CURUSER["id"] == $arr["receiver"] && $arr["sender"] <> 0)
        $subject = "<a href=\"message.php?action=sendmessage&receiver=" . $arr["sender"] . "&replyto=" . $arr["id"] . "\">" . $subject . "</a>";

    if (!$arr["subject"] || $arr["subject"] == "Re:")
        $subject = "<b>Тема пуста</b>";

    if ($arr["unread"] != "yes") {
        $newmessageview = "<img  style=\"border:none\" alt=\"Сообщение прочитанно\" title=\"Сообщение прочитанно\" src=\"pic/ok.gif\">";
    } else {
        $newmessageview = "<img  style=\"border:none\" alt=\"Сообщение не прочитанно\" title=\"Сообщение не прочитанно\" src=\"pic/err.gif\">";
    }

    if (
        ($arr["sender_c"] <= $CURUSER["class"] && $arr["receiver_c"] <= $CURUSER["class"])
        ||
        ($arr["sender"] == $CURUSER["id"] || $arr["receiver"] == $CURUSER["id"])
    ) {
        print("<tr>
        <td $sclastd align=center>$sender<br>$last_access_s</td>
        <td $rclastd align=center>$receiver<br>$last_access_r</td>
        <td>$newmessageview</td>
        <td>$subject</td>
        <td align=left>$msg</td>
        <td align=center>$added</td>");
        echo("<TD align=center><INPUT type=\"checkbox\" name=\"delmp[]\" value=\"" . $arr['id'] . "\" id=\"checkbox_tbl_" . $arr['id'] . "\"></TD>\n</TR>\n");
    }
}
print("</table>");
?>
<input type="submit" value="Удалить выделенные сообщения" /> <br><br>
</form>
<?
if (isset($on_main)) echo $on_main;
print($pagerbottom);
end_main_frame();
stdfoot();
?>