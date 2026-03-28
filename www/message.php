<?php
require_once("include/bittorrent.php");
require_once("include/secrets.php");
$DEFAULTBASEURL = "http://" . $_SERVER['HTTP_HOST'];
global $lang;
getlang('main');
gzip();
dbconn();
// Disable strict SQL mode to allow inserts without default values for messages
global $mysqli;
mysqli_query($mysqli, "SET SESSION sql_mode = ''");
loggedinorreturn();
parked();

// Константы
define('PM_DELETED', 0);    // Сообщение удалено
define('PM_INBOX', 1);      // Входящие
define('PM_SENTBOX', -1);   // Исходящие

// Очистка кеша сообщений
global $memcached;
if ($memcached instanceof Memcached) {
    $memcached->delete('messages_' . $CURUSER['id']);
}

// Определение действия
$action = $_GET['action'] ?? $_POST['action'] ?? 'viewmailbox';

// === Просмотр почтового ящика ===
if ($action === "viewmailbox") {
    $mailbox = (int)($_GET['box'] ?? PM_INBOX);
    $mailbox_name = ($mailbox === PM_INBOX) ? $lang['inbox'] : $lang['outbox'];

    stdhead($mailbox_name);
    begin_main_frame();
    ?>

    <script type="text/javascript">
        var checkflag = "false";
        function check(field) {
            for (let i = 0; i < field.length; i++) {
                field[i].checked = (checkflag === "false");
            }
            checkflag = (checkflag === "false") ? "true" : "false";
        }
    </script>

    <h1><?= $mailbox_name ?></h1>

    <div align="right">
        <form action="message.php" method="get">
            <input type="hidden" name="action" value="viewmailbox">
            <?= $lang['go_to']; ?>:
            <select name="box">
                <option value="1"<?= ($mailbox === PM_INBOX ? " selected" : "") ?>><?= $lang['inbox']; ?></option>
                <option value="-1"<?= ($mailbox === PM_SENTBOX ? " selected" : "") ?>><?= $lang['outbox']; ?></option>
            </select>
            <input type="submit" value="<?= $lang['go_go_go']; ?>">
        </form>
    </div>

    <table border="0" cellpadding="4" cellspacing="0" width="100%">
        <form action="message.php" method="post" name="form1">
            <input type="hidden" name="action" value="moveordel">
            <tr>
                <td width="2%" class="colhead">&nbsp;</td>
                <td width="51%" class="colhead"><?= $lang['subject']; ?></td>
                <td width="35%" class="colhead"><?= $mailbox === PM_INBOX ? $lang['sender'] : $lang['receiver']; ?></td>
                <td width="10%" class="colhead"><?= $lang['date']; ?></td>
                <td width="2%" class="colhead">
                    <input type="checkbox" title="<?= $lang['mark_all']; ?>" onClick="this.value=check(document.form1.elements);">
                </td>
            </tr>

            <?php
            if ($mailbox !== PM_SENTBOX) {
                // Show all received messages, including those with any location
                $res = sql_query("SELECT m.*, u.username AS sender_username 
                                  FROM messages m 
                                  LEFT JOIN users u ON m.sender = u.id  
                                  WHERE m.receiver = " . sqlesc($CURUSER['id']) . " 
                                  ORDER BY m.id DESC") or sqlerr(__FILE__, __LINE__);
            } else {
                $res = sql_query("SELECT m.*, u.username AS receiver_username 
                                  FROM messages m 
                                  LEFT JOIN users u ON m.receiver = u.id 
                                  WHERE m.sender = " . sqlesc($CURUSER['id']) . " 
                                  AND m.saved = 'yes' 
                                  ORDER BY m.id DESC") or sqlerr(__FILE__, __LINE__);
            }

            if (mysqli_num_rows($res) === 0) {
                echo "<tr><td colspan='6' align='center'>{$lang['no_messages']}.</td></tr>";
            } else {
                while ($row = mysqli_fetch_assoc($res)) {
                    $subject = htmlspecialchars($row['subject'] ?? '');
                    if (strlen($subject) === 0) {
                        $subject = $lang['no_subject'];
                    }

                    $bgcolor = ($row['sender'] == 0) ? "bgcolor=\"#DDE9F4\"" : "";
                    $icon = ($row['unread'] === 'yes' && $mailbox !== PM_SENTBOX) ? "pn_inboxnew.gif" : "pn_inbox.gif";
                    $alt = ($row['unread'] === 'yes' && $mailbox !== PM_SENTBOX) ? $lang['mail_unread'] : $lang['mail_read'];

                    $username = $lang['from_system'];
                    $receiver = $lang['from_system'];

                    if (!empty($row['sender']) && isset($row['sender_username'])) {
                        $username = "<a href=\"user/id{$row['sender']}\">" . htmlspecialchars($row["sender_username"]) . "</a>";
                    } else {
                        $username = $lang['from_system'];
                    }

                    if (!empty($row['receiver']) && isset($row['receiver_username'])) {
                        $receiver = "<a href=\"user/id{$row['receiver']}\">" . htmlspecialchars($row["receiver_username"]) . "</a>";
                    } else {
                        $receiver = $lang['from_system'];
                    }

                    echo "<tr {$bgcolor}>";
                    echo "<td><img src=\"pic/{$icon}\" alt=\"{$alt}\"></td>";
                    echo "<td><a href=\"message.php?action=viewmessage&amp;id={$row['id']}\">{$subject}</a></td>";
                    echo "<td>" . ($mailbox === PM_SENTBOX ? $receiver : $username) . "</td>";
                    echo "<td nowrap>" . display_date_time($row['added']) . "</td>";
                    echo "<td><input type=\"checkbox\" name=\"messages[]\" title=\"{$lang['mark']}\" value=\"{$row['id']}\" id=\"checkbox_tbl_{$row['id']}\"></td>";
                    echo "</tr>";
                }
            }
            ?>

            <tr class="colhead">
                <td colspan="6" align="right" class="colhead">
                    <input type="hidden" name="box" value="<?= $mailbox ?>">
                    <input type="submit" name="delete" title="<?= $lang['delete_marked_messages']; ?>" value="<?= $lang['delete']; ?>" onClick="return confirm('<?= $lang['sure_mark_delete']; ?>')">
                    <input type="submit" name="markread" title="<?= $lang['mark_as_read']; ?>" value="<?= $lang['mark_read']; ?>" onClick="return confirm('<?= $lang['sure_mark_read']; ?>')">
                </td>
            </tr>
        </form>
    </table>

    <div align="left">
        <img src="pic/pn_inboxnew.gif" alt="Непрочитанные" /> <?= $lang['mail_unread_desc']; ?><br />
        <img src="pic/pn_inbox.gif" alt="Прочитанные" /> <?= $lang['mail_read_desc']; ?>
    </div>

    <?php
    end_main_frame();
    stdfoot();
}


// начало просмотр тела сообщения
if ($action == "viewmessage") {
    global $mysqli;
        $pm_id = (int) $_GET['id'];
        if (!$pm_id)
        {
                stderr($lang['error'], "У вас нет прав для просмотра этого сообщения.");
        }
        // Get the message
        $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id) . ' AND (receiver=' . sqlesc($CURUSER['id']) . ' OR (sender=' . sqlesc($CURUSER['id']). ' AND saved=\'yes\')) LIMIT 1') or sqlerr(__FILE__,__LINE__);
        if (mysqli_num_rows($res) == 0)
        {
                stderr($lang['error'],"Такого сообщения не существует.");
        }
        // Prepare for displaying message
        $message = mysqli_fetch_assoc($res);
        if ($message['sender'] == $CURUSER['id'])
        {
                // Display to
                $res2 = sql_query("SELECT username FROM users WHERE id=" . sqlesc($message['receiver'])) or sqlerr(__FILE__,__LINE__);
                $sender = mysqli_fetch_array($res2);
                $sender = "<A href=\"user/id" . $message['receiver'] . "\">" . $sender[0] . "</A>";
                $reply = "";
                $from = "Кому";
        }
        else
        {
                $from = "От кого";
                if ($message['sender'] == 0)
                {
                        $sender = "Системное";
                        $reply = "";
                }
                else
                {
                        $res2 = sql_query("SELECT username FROM users WHERE id=" . sqlesc($message['sender'])) or sqlerr(__FILE__,__LINE__);
                        $sender = mysqli_fetch_array($res2);
                        $sender = "<A href=\"user/id" . $message['sender'] . "\">" . $sender[0] . "</A>";
                        $reply = " [ <A href=\"message.php?action=sendmessage&amp;receiver=" . $message['sender'] . "&amp;replyto=" . $pm_id . "\">Ответить</A> ]";
                }
        }
        $body = format_comment($message['msg']);
        $added = display_date_time($message['added']);
        if (get_user_class() >= UC_MODERATOR && $message['sender'] == $CURUSER['id'])
        {
                $unread = ($message['unread'] == 'yes' ? "<SPAN style=\"color: #FF0000;\"><b>(Новое)</b></A>" : "");
        }
        else
        {
                $unread = "";
        }
        $subject = htmlspecialchars($message['subject']);
        if (strlen($subject) <= 0)
        {
                $subject = "Без темы";
        }
        // Mark message unread
        sql_query("UPDATE messages SET unread='no' WHERE id=" . sqlesc($pm_id) . " AND receiver=" . sqlesc($CURUSER['id']) . " LIMIT 1");
        // Display message
        stdhead("Личное Сообщение (Тема: $subject)");
		begin_main_frame(); ?>
        <TABLE width="100%" border="0" cellpadding="4" cellspacing="0">
        <TR><h2>Тема: <?=$subject?></h2></TR>
        <TR>
        <TD width="50%" class="colhead"><?=$from?></TD>
        <TD width="50%" class="colhead">Дата отправки</TD>
        </TR>
        <TR>
        <TD><?=$sender?></TD>
        <TD><?=$added?>&nbsp;&nbsp;<?=$unread?></TD>
        </TR>
        <TR>
        <TD colspan="2"><?=$body?></TD>
        </TR>
        <TR>
        <TD align="right" colspan=2>[ <A href="message.php?action=deletemessage&id=<?=$pm_id?>">Удалить</A> ]<?=$reply?> [ <A href="message.php?action=forward&id=<?=$pm_id?>">Переслать</A> ]</TD>
        </TR>
		</table>
        <?
		end_main_frame();
        stdfoot();
}
// конец просмотр тела сообщения


// начало просмотр посылка сообщения
if ($action == "sendmessage") {
    global $mysqli;

        $receiver = $_GET["receiver"];
        if (!is_valid_id($receiver))
                stderr($lang['error'], "Неверное ID получателя");

        $replyto = $_GET["replyto"] ?? null;
        $body = "";
        if ($replyto && !is_valid_id($replyto))
                stderr($lang['error'], "Неверное ID сообщения");

        $auto = $_GET["auto"] ?? null;
        $std = $_GET["std"] ?? null;

        if (($auto || $std ) && get_user_class() < UC_MODERATOR)
                stderr($lang['error'], "Досступ запрещен.");

        $res = sql_query("SELECT * FROM users WHERE id=$receiver") or die(mysqli_error($mysqli));
        $user = mysqli_fetch_assoc($res);
        if (!$user)
                stderr($lang['error'], "Пользователя с таким ID не существует.");
        if ($auto)
                $body = $pm_std_reply[$auto];
        if ($std)
                $body = $pm_template[$std][1];

        if ($replyto) {
                $res = sql_query("SELECT * FROM messages WHERE id=$replyto") or sqlerr(__FILE__, __LINE__);
                $msga = mysqli_fetch_assoc($res);
                if ($msga["receiver"] != $CURUSER["id"])
                        stderr($lang['error'], "Вы пытаетесь ответить не на свое сообщение!");

                $res = sql_query("SELECT username FROM users WHERE id=" . $msga["sender"]) or sqlerr(__FILE__, __LINE__);
                $usra = mysqli_fetch_assoc($res);
                $body .= "[quote=$usra[username]] ".htmlspecialchars($msga['msg'])."[/quote]\n";
                // Change
                $subject = "Re: " . htmlspecialchars($msga['subject']);
                // End of Change
        }

        stdhead("Отсылка сообщений", false);
		begin_main_frame();
        ?>

        <table class="main" border="0" align="center" width="100%" cellspacing="0" cellpadding="0"><tr><td class="embedded">
        <form id=message name=message method=post action=message.php>
        <input type=hidden name=action value=takemessage>
        <table class=message width="100%"  cellspacing=0 cellpadding=5>
        <tr><td colspan=2 class=colhead>Сообщение для <a class=altlink_white href=user/id<?=$receiver?>><?=$user["username"]?></a></td></tr>
        <TR>
        <?php $subject = $subject ?? ""; ?>
        <TD colspan="2"><B>Тема:&nbsp;&nbsp;</B>
        <INPUT name="subject" type="text" size="60" value="<?=$subject?>" maxlength="255"></TD>
        </TR>
        <tr><td <?=$replyto?" colspan=2":""?>>
        <?
        textbbcode("message","msg","$body");
        ?>

        </td></tr>
        <tr>
        <? if ($replyto) { ?>
        <td align=center><input type=checkbox name='delete' value='yes' <?=$CURUSER['deletepms'] == 'yes'?"checked":""?>>Удалить сообщение после ответа
        <input type=hidden name=origmsg value=<?=$replyto?>></td>
        <? } ?>
        <td align=center><input type=checkbox name='save' value='yes' <?=$CURUSER['savepms'] == 'yes'?"checked":""?>>Сохранить сообщение в отправленных</td></tr>
        <tr><td <?=$replyto?" colspan=2":""?> style=text-align:center;>
		<input type=submit value="Отправить!" class="btn">
		<input type="button" value="Смайлы" class="btn" onClick="javascript:winop()" />
		 <input type="button" value="Смайлы2" class="btn" onClick="javascript:winop2()" />
		</td></tr>
       </table>
	   <input type=hidden name=receiver value=<?=$receiver?>>
        
		</form>
		

        </div></td></tr></table>
        <?
		end_main_frame();
        stdfoot();
}
// конец посылка сообщения


// начало прием посланного сообщения
if ($action == 'takemessage') {
    global $mysqli;

    $receiver = $_POST["receiver"];
    $origmsg = $_POST["origmsg"] ?? null;
    $save = $_POST["save"] ?? 'no';
    $returnto = $_POST["returnto"] ?? null;
    if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
        stderr($lang['error'],"Неверный ID");
    $msg = trim($_POST["msg"]);
    if (!$msg)
        stderr($lang['error'],"Пожалуйста введите сообщение!");
    $subject = trim($_POST['subject']);
    if (!$subject)
        stderr($lang['error'],"Пожалуйста введите тему сообщения!");
    // Change
    $save = ($save == 'yes') ? "yes" : "no";
    // End of Change
    $res = sql_query("SELECT email, username, acceptpms, notifs, parked, UNIX_TIMESTAMP(last_access) as la FROM users WHERE id=$receiver") or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($res);
    if (!$user)
        stderr($lang['error'], "Нет пользователя с таким ID $receiver.");
    //Make sure recipient wants this message
    if ($user["parked"] == "yes")
        stderr($lang['error'], "Этот аккаунт припаркован.");
    if (get_user_class() < UC_MODERATOR)
    {
        if ($user["acceptpms"] == "yes")
        {
            $res2 = sql_query("SELECT * FROM blocks WHERE userid=$receiver AND blockid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res2) == 1)
                sttderr("Отклонено", "Этот пользователь добавил вас в черный список.");
        }
        elseif ($user["acceptpms"] == "friends")
        {
            $res2 = sql_query("SELECT * FROM friends WHERE userid=$receiver AND friendid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res2) != 1)
                stderr("Отклонено", "Этот пользователь принимает сообщение только из списка своих друзей");
        }
        elseif ($user["acceptpms"] == "no")
            stderr("Отклонено", "Этот пользователь не принимает сообщения.");
    }

    sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location, spam) VALUES(" . $CURUSER["id"] . ", " . $CURUSER["id"] . ",
    $receiver, '" . get_date_time() . "', " . sqlesc($msg) . ", " . sqlesc($subject) . ", " . sqlesc($save) . ", 1, " . sqlesc(time()) .")") or sqlerr(__FILE__, __LINE__);

    $sended_id = mysqli_insert_id($mysqli);

    // Отправка уведомления получателю
    if ($user["notifs"] && strpos($user["notifs"], "[pm]") !== false) {
        $subject_notify = "Новое личное сообщение от " . $CURUSER['username'];
        $body_notify = "У вас новое личное сообщение от пользователя [url={$DEFAULTBASEURL}/user/id{$CURUSER['id']}]{$CURUSER['username']}[/url].\n\nТема: [b]{$subject}[/b]\n\nПросмотреть сообщение: {$DEFAULTBASEURL}/message.php?action=viewmessage&id={$sended_id}";
        sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location, spam) VALUES(0, 0, $receiver, '" . get_date_time() . "', " . sqlesc($body_notify) . ", " . sqlesc($subject_notify) . ", 'no', 1, " . sqlesc(time()) . ")") or sqlerr(__FILE__, __LINE__);
    }

    global $memcached;
    if ($memcached instanceof Memcached) {
        $memcached->delete('messages_' . $receiver);
    }

    $delete = $_POST["delete"] ?? null;
    if ($origmsg)
    {
        if ($delete == "yes")
        {
            // Make sure receiver of $origmsg is current user
            $res = sql_query("SELECT * FROM messages WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res) == 1)
            {
                $arr = mysqli_fetch_assoc($res);
                if ($arr["receiver"] != $CURUSER["id"])
                    stderr($lang['error'],"Вы пытаетесь удалить не свое сообщение!");
                if ($arr["saved"] == "no")
                    sql_query("DELETE FROM messages WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
                elseif ($arr["saved"] == "yes")
                    sql_query("UPDATE messages SET location = '0' WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
            }
        }
        if (!$returnto)
            $returnto = "$DEFAULTBASEURL/message.php";
    }
    if ($returnto) {
        header("Location: $returnto");
        die;
    }
    else {
        header ("Refresh: 2; url=message.php");
        stderr($lang['success'] , "Сообщение было успешно отправлено!");
    }

}
// конец прием посланного сообщения


//начало массовая рассылка
if ($action == 'mass_pm') {
    global $mysqli;
        if (get_user_class() < UC_MODERATOR)
                stderr($lang['error'], $lang['access_denied']);
        $n_pms = 0 + $_POST['n_pms'];
        $pmees = $_POST['pmees'];
        $auto = $_POST['auto'];

        if ($auto)
                $body=$mm_template[$auto][1];

        stdhead("Отсылка сообщений", false);
		begin_main_frame();
        ?>
        <table class=main border=0 cellspacing=0 cellpadding=0>
        <tr><td class=embedded><div align=center>
        <form id=message method=post action=<?=$_SERVER['PHP_SELF']?> name=message>
        <input type=hidden name=action value=takemass_pm>
        <? if ($_SERVER["HTTP_REFERER"]) { ?>
        <input type=hidden name=returnto value="<?=htmlspecialchars($_SERVER["HTTP_REFERER"]);?>">
        <? } ?>
        <table border=1 cellspacing=0 cellpadding=5>
        <tr><td class=colhead colspan=2>Массовая рассылка для <?=$n_pms?> пользовате<?=($n_pms>1?"лей":"ля")?></td></tr>
        <TR>
        <TD colspan="2"><B>Тема:&nbsp;&nbsp;</B>
        <INPUT name="subject" type="text" size="60" maxlength="255"></TD>
        </TR>
        <tr><td colspan="2"><div align="center">
        <?=textbbcode("message","msg","$body");?>
        </div></td></tr>
        <tr><td colspan="2"><div align="center"><b>Комментарий:&nbsp;&nbsp;</b>
        <input name="comment" type="text" size="70">
        </div></td></tr>
        <tr><td><div align="center"><b>От:&nbsp;&nbsp;</b>
        <?=$CURUSER['username']?>
        <input name="sender" type="radio" value="self" checked>
        &nbsp; Системное
        <input name="sender" type="radio" value="system">
        </div></td>
        <td><div align="center"><b>Take snapshot:</b>&nbsp;<input name="snap" type="checkbox" value="1">
         </div></td></tr>
        <tr><td colspan="2" align=center><input type=submit value="Отправить!" class=btn>
        </td></tr>
        <input type=hidden name=pmees value="<?=$pmees?>">
        <input type=hidden name=n_pms value=<?=$n_pms?>>
        </form><br /><br />
        </div>
        </td>
        </tr>
</table>
        <?
		end_main_frame();
        stdfoot();

}
//конец массовая рассылка


//начало прием сообщений из массовой рассылки
if ($action == 'takemass_pm') {
    global $mysqli;
        if (get_user_class() < UC_MODERATOR)
                stderr($lang['error'], $lang['access_denied']);
        $msg = trim($_POST["msg"]);
        if (!$msg)
                stderr($lang['error'],"Пожалуйста введите сообщение.");
        $sender_id = ($_POST['sender'] == 'system' ? 0 : $CURUSER['id']);
$from_is = sqlesc ( unesc ( $_POST ['pmees'] ) );  
        // Change
        $subject = trim($_POST['subject']);
        $query = "INSERT INTO messages (sender, receiver, added, msg, subject, location, poster) ". "SELECT $sender_id, u.id, '" . get_date_time(time()) . "', " .
        sqlesc($msg) . ", " . sqlesc($subject) . ", 1, $sender_id " . $from_is;
        // End of Change
        sql_query($query) or sqlerr(__FILE__, __LINE__);
        $n = mysqli_affected_rows($mysqli);
        $n_pms = $_POST['n_pms'];
        $comment = $_POST['comment'];
        $snapshot = $_POST['snap'];
        // add a custom text or stats snapshot to comments in profile
        if ($comment || $snapshot)
        {
                $res = sql_query("SELECT u.id, u.uploaded, u.downloaded, u.modcomment ".$from_is) or sqlerr(__FILE__, __LINE__);
                if (mysqli_num_rows($res) > 0)
                {
                        $l = 0;
                        while ($user = mysqli_fetch_array($res))
                        {
                                unset($new);
                                $old = $user['modcomment'];
                                if ($comment)
                                        $new = $comment;
                                        if ($snapshot)
                                        {
                                                $new .= ($new?"\n":"") . "MMed, " . date("Y-m-d") . ", " .
                                                "UL: " . mksize($user['uploaded']) . ", " .
                                                "DL: " . mksize($user['downloaded']) . ", " .
                                                "r: " . (($user['downloaded'] > 0)?($user['uploaded']/$user['downloaded']) : 0) . " - " .
                                                ($_POST['sender'] == "system"?"System":$CURUSER['username']);
                                        }
                                        $new .= $old?("\n".$old):$old;
                                        sql_query("UPDATE users SET modcomment = " . sqlesc($new) . " WHERE id = " . $user['id']) or sqlerr(__FILE__, __LINE__);
                                        if (mysqli_affected_rows($mysqli))
                                                $l++;
                        }
                }
        }
        header ("Refresh: 3; url=message.php");
        stderr($lang['success'], (($n_pms > 1) ? "$n сообщений из $n_pms было" : "Сообщение было")." успешно отправлено!" . ($l ? " $l комментарий(ев) в профиле " . (($l>1) ? "были" : " был") . " обновлен!" : ""));
}
//конец прием сообщений из массовой рассылки


//начало перемещение, помечание как прочитанного
if ($action == "moveordel") {
    global $mysqli;
        $pm_id = (int) $_POST['id'];
        $pm_box = (int) $_POST['box'];
        $pm_messages = $_POST['messages'];
        if ($_POST['move']) {
                if ($pm_id) {
                        // Move a single message
                        @sql_query("UPDATE messages SET location=" . sqlesc($pm_box) . ", saved = 'yes' WHERE id=" . sqlesc($pm_id) . " AND receiver=" . $CURUSER['id'] . " LIMIT 1");
                }
                else {
                        // Move multiple messages
                        @sql_query("UPDATE messages SET location=" . sqlesc($pm_box) . ", saved = 'yes' WHERE id IN (" . implode(", ", array_map("sqlesc", array_map("intval", $pm_messages))) . ') AND receiver=' . $CURUSER['id']);
                }
                // Check if messages were moved
                if (@mysqli_affected_rows($mysqli) == 0) {
                        stderr($lang['error'], "Не возможно переместить сообщения!");
                }
                header("Location: message.php?action=viewmailbox&box=" . $pm_box);
                exit();
        }
        elseif ($_POST['delete']) {
                if ($pm_id) {
                        // Delete a single message
                        $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                        $message = mysqli_fetch_assoc($res);
                        if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no') {
                                sql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                        }
                        elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED) {
                                sql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                        }
                        elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes') {
                                sql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                        }
                        elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED) {
                                sql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                        }
                } else {
                        // Delete multiple messages
                        if (is_array($pm_messages))
                        foreach ($pm_messages as $id) {
                                $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc((int) $id));
                                $message = mysqli_fetch_assoc($res);
                                if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no') {
                                        sql_query("DELETE FROM messages WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
                                }
                                elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED) {
                                        sql_query("DELETE FROM messages WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
                                }
                                elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes') {
                                        sql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
                                }
                                elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED) {
                                        sql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
                                }
                        }
                }
                // Check if messages were moved
                if (@mysqli_affected_rows($mysqli) == 0) {
                        stderr($lang['error'],"Сообщение не может быть удалено!");
                }
                else {
                        header("Location: message.php?action=viewmailbox&box=" . $pm_box);
                        exit();
                }
        }
        elseif ($_POST["markread"]) {
                //помечаем одно сообщение
                if ($pm_id) {
                        sql_query("UPDATE messages SET unread='no' WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
                }
                //помечаем множество сообщений
                else {
                		if (is_array($pm_messages))
                        foreach ($pm_messages as $id) {
                                $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc((int) $id));
                                $message = mysqli_fetch_assoc($res);
                                sql_query("UPDATE messages SET unread='no' WHERE id = " . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
                        }
                }
                // Проверяем, были ли помечены сообщения
                if (@mysqli_affected_rows($mysqli) == 0) {
                        stderr($lang['error'], "Сообщение не может быть помечено как прочитанное! ");
                }
                else {
                        header("Location: message.php?action=viewmailbox&box=" . $pm_box);
                        exit();
                }
        }

stderr($lang['error'],"Нет действия.");
}
//конец перемещение, помечание как прочитанного


//начало пересылка
if ($action == "forward") {
    global $mysqli;
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                // Display form
                $pm_id = (int) $_GET['id'];

                // Get the message
                $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id) . ' AND (receiver=' . sqlesc($CURUSER['id']) . ' OR sender=' . sqlesc($CURUSER['id']) . ') LIMIT 1') or sqlerr(__FILE__,__LINE__);

                if (!$res) {
                        stderr($lang['error'], "У вас нет разрешения пересылать это сообщение.");
                }
                if (mysqli_num_rows($res) == 0) {
                        stderr($lang['error'], "У вас нет разрешения пересылать это сообщение.");
                }
                $message = mysqli_fetch_assoc($res);

                // Prepare variables
                $subject = "Fwd: " . htmlspecialchars($message['subject']);
                $from = $message['sender'];
                $orig = $message['receiver'];

                $res = sql_query("SELECT username FROM users WHERE id=" . sqlesc($orig) . " OR id=" . sqlesc($from)) or sqlerr(__FILE__,__LINE__);

                $orig2 = mysqli_fetch_assoc($res);
                $orig_name = (isset($orig2['username']))
                    ? "<A href=\"user/id" . $from . "\">" . $orig2['username'] . "</A>"
                    : $lang['from_system'];
                if ($from == 0) {
                        $from_name = "Системное";
                        $from2['username'] = "Системное";
                }
                else {
                        $from2 = mysqli_fetch_array($res);
                        if (!$from2 || !isset($from2['username'])) {
                            $from2['username'] = $lang['from_system'];
                        }
                        $from_name = "<A href=\"user/id" . $from . "\">" . htmlspecialchars($from2['username']) . "</A>";
                }

                $body = "-------- Оригинальное сообщение от " . htmlspecialchars($from2['username']) . ": --------<BR>" . format_comment($message['msg']);

                stdhead($subject);
				begin_main_frame();?>

                <FORM action="message.php" method="post">
                <INPUT type="hidden" name="action" value="forward">
                <INPUT type="hidden" name="id" value="<?=$pm_id?>">
                <TABLE border="0" cellpadding="4" cellspacing="0">
                <TR><TD class="colhead" colspan="2"><?=$subject?></TD></TR>
                <TR>
                <TD>Кому:</TD>
                <TD><INPUT type="text" name="to" value="Введите имя" size="83"></TD>
                </TR>
                <TR>
                <TD>Оригинальный<BR>отправитель:</TD>
                <TD><?=$orig_name?></TD>
                </TR>
                <TR>
                <TD>От:</TD>
                <TD><?=$from_name?></TD>
                </TR>
                <TR>
                <TD>Тема:</TD>
                <TD><INPUT type="text" name="subject" value="<?=$subject?>" size="83"></TD>
                </TR>
                <TR>
                <TD>Сообщение:</TD>
                <TD><TEXTAREA name="msg" id=msg cols="80" rows="8"></TEXTAREA><BR><?=$body?></TD>
                </TR>
                <TR>
                <TD colspan="2" align="center">Сохранить сообщение <INPUT type="checkbox" name="save" value="1"<?=$CURUSER['savepms'] == 'yes'?" checked":""?>>&nbsp;<INPUT type="submit" value="Переслать"></TD>
                </TR>
		</TABLE>
                </FORM><?
				end_main_frame();
                stdfoot();
        }

        else {

                // Forward the message
                $pm_id = (int) $_POST['id'];

                // Get the message
                $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id) . ' AND (receiver=' . sqlesc($CURUSER['id']) . ' OR sender=' . sqlesc($CURUSER['id']) . ') LIMIT 1') or sqlerr(__FILE__,__LINE__);
                if (!$res) {
                        stderr($lang['error'], "У вас нет разрешения пересылать это сообщение.");
                }

                if (mysql_num_rows($res) == 0) {
                        stderr($lang['error'], "У вас нет разрешения пересылать это сообщение.");
                }

                $message = mysql_fetch_assoc($res);
                $subject = (string) $_POST['subject'];
                $username = strip_tags($_POST['to']);

                // Try finding a user with specified name

                $res = sql_query("SELECT id FROM users WHERE LOWER(username)=LOWER(" . sqlesc($username) . ") LIMIT 1");
                if (!$res) {
                        stderr($lang['error'], "Пользователя, с таким именем не существует.");
                }
                if (mysqli_num_rows($res) == 0) {
                        stderr($lang['error'], "Пользователя, с таким именем не существует.");
                }

                $to = mysqli_fetch_array($res);
                $to = $to[0];

                // Get Orignal sender's username
                if ($message['sender'] == 0) {
                        $from = "Системное";
                }
                else {
                        $res = sql_query("SELECT * FROM users WHERE id=" . sqlesc($message['sender'])) or sqlerr(__FILE__,__LINE__);
                        $from = mysqli_fetch_assoc($res);
                        $from = $from['username'];
                }
                $body = (string) $_POST['msg'];
                $body .= "\n-------- Оригинальное сообщение от " . $from . ": --------\n" . $message['msg'];
                $save = (int) $_POST['save'];
                if ($save) {
                        $save = 'yes';
                }
                else {
                        $save = 'no';
                }

                //Make sure recipient wants this message
                if (get_user_class() < UC_MODERATOR) {
                        if ($from["acceptpms"] == "yes") {
                                $res2 = sql_query("SELECT * FROM blocks WHERE userid=$to AND blockid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
                                if (mysqli_num_rows($res2) == 1)
                                        stderr("Отклонено", "Этот пользователь добавил вас в черный список.");
                        }
                        elseif ($from["acceptpms"] == "friends") {
                                $res2 = sql_query("SELECT * FROM friends WHERE userid=$to AND friendid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
                                if (mysqli_num_rows($res2) != 1)
                                        stderr("Отклонено", "Этот пользователь принимает сообщение только из списка своих друзей.");
                        }

                        elseif ($from["acceptpms"] == "no")
                                stderr("Отклонено", "Этот пользователь не принимает сообщения.");
                }
                sql_query(
                    "INSERT INTO messages 
                        (poster, sender, receiver, added, msg, subject, saved, location, spam, unread)
                     VALUES ("
                        . sqlesc($CURUSER['id']) . ", " 
                        . sqlesc($CURUSER['id']) . ", "
                        . sqlesc($to) . ", "
                        . sqlesc(get_date_time()) . ", "
                        . sqlesc($body) . ", "
                        . sqlesc($subject) . ", "
                        . sqlesc($save) . ", "
                        . PM_INBOX . ", "
                        . "0, "  // spam flag
                        . sqlesc('yes')  // mark as unread
                     . ")"
                ) or sqlerr(__FILE__, (string)__LINE__);
                        stderr("Удачно", "ЛС переслано.");
        }
}
//конец пересылка


//начало удаление сообщения
if ($action == "deletemessage") {
    global $mysqli;
        $pm_id = (int) $_GET['id'];

        // Delete message
        $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
        if (!$res) {
                stderr($lang['error'],"Сообщения с таким ID не существует.");
        }
        if (mysqli_num_rows($res) == 0) {
                stderr($lang['error'],"Сообщения с таким ID не существует.");
        }
        $message = mysqli_fetch_assoc($res);
        if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no') {
                $res2 = sql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
        }
        elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED) {
                $res2 = sql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
        }
        elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes') {
                $res2 = sql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
        }
        elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED) {
                $res2 = sql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
        }
        if (!$res2) {
                stderr($lang['error'],"Невозможно удалить сообщение.");
        }
        if (mysqli_affected_rows($mysqli) == 0) {
                stderr($lang['error'],"Невозможно удалить сообщение.");
        }
        else {
                header("Location: message.php?action=viewmailbox&id=" . $message['location']);
                exit();
        }
}
//конец удаление сообщения
?>