<?php
require_once("include/bittorrent.php");
dbconn();
getlang();
header("Content-Type: text/html; charset=".$lang['language_charset']);
if (get_user_class() < UC_SYSOP) die('Access denied, u\'re not sysop');

global $SITENAME, $SITEEMAIL;

// Проверяем, был ли запрос отправлен через XMLHttpRequest
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    stdhead();
    begin_main_frame();
    ?>

<script>
var usersEachRequest = 150;
var timeWait = 10;

var lU = 0;
var uCount = 0;

function userCount() {
    jQuery.post('massEmail.php', {'act': 'count'},
        function(cn) {
            uCount = parseInt(cn);
        }, 'html'
    );
}

function sendEmail() {
    tr = timeWait;
    if (lU == 0) userCount();

    var msg = jQuery('#msg').val();
    var subject = jQuery('#subject').val();

    if (msg.length > 0) {
        jQuery.post('massEmail.php', {
                'msg': msg,
                'subject': subject,
                'lU': lU,
                'uER': usersEachRequest
            },
            function(response) {
                jQuery('#response').html(response);
            }, 'html'
        );
        timeOut();
        lU += usersEachRequest;
        return true;
    } else {
        alert('Тема и текст не могут быть пустыми!');
        return false;
    }
}

function timeOut() {
    if (tr == 0 && lU <= uCount) {
        sendEmail();
        return true;
    }

    if (tr > 0 && lU <= uCount) {
        jQuery('#timeRemains').html("<font color=\"red\">" + tr + " сек.</font>");
        tr--;
        setTimeout('timeOut();', 1000);
    } else jQuery('#timeRemains').html("");
}
</script>

<table>
<tr>
<td colspan="2" style="border: 0">
&nbsp;Тема: <input name="subject" type="text" id="subject" size="70" /></td>
</tr>
<tr>
<td align="center" style="border: 0">
<?php
$body = '';
textbbcode("message", "msg", $body, 0);
?>
</td>
</tr>
<tr>
<td colspan="2" align="center" style="border: 0">
<input type="button" onclick="sendEmail();" value="Отправить" class="btn" />
<div id="response"></div>
<div id="timeRemains"></div>
</td>
</tr>
</table>

<?php
    end_main_frame();
    stdfoot();
    die();
}

$lU = intval($_POST['lU']);
$usersEachRequest = intval($_POST['uER']);
$msg = iconv("UTF-8", "CP1251", $_POST['msg']);
$subject = iconv("UTF-8", "CP1251", $_POST['subject']);
$name = iconv("UTF-8", "CP1251", "AnimeClub.Lv");
$mail = iconv("UTF-8", "CP1251", $SITEEMAIL);
$c = mysql_fetch_row(sql_query("SELECT COUNT(id) FROM users"));
$count = $c[0];

if ($_POST["act"] == "count") {
    print($count);
    die();
}

$res = mysql_query("SELECT email FROM users ORDER BY id LIMIT ".$lU.", ".$usersEachRequest."") or sqlerr(__FILE__, __LINE__);
$i = 0;
while ($a = mysql_fetch_assoc($res)) {
    $i++;
    $message = <<<EOD
$msg 
EOD;
    @sent_mail($a["email"], $name, $mail, $subject, $msg);
}

if (($lU + $i) >= $count) {
    echo "<font color=\"green\">Рассылка завершена. Отправлено <b>$count</b> сообщений</font>";
    die;
}

echo "<b><font color=\"red\">" . ($lU + $i) . "</font></b> из <b>" . $count . "</b> послано...";
?>