<?php
require "include/bittorrent.php";
require "include/secrets.php";
dbconn();
getlang('support');

// Проверка капчи и обработка формы
if (isset($_POST['setCaptcha'])) {
    include_once('./captcha/cf.captcha.php');

    if (check_captcha($_POST['setCaptcha'])) {
        // ID получателя (служба поддержки)
        $receiver = 1;

        // Определяем пользователя
        if (!$CURUSER) {
            $uid = 0;
            $nick = trim($_POST["nick"] ?? '');
            if ($nick === '') {
                stderr($lang['error'], $lang['no_name']);
            }
            $mail = trim($_POST['mail'] ?? '');
        } else {
            $uid = $CURUSER["id"];
            $nick = "[url=user/id{$uid}]" . $CURUSER["username"] . "[/url]";
            $mail = $CURUSER["email"] ?? '';
        }

        $ip = getip();
        $msg_body = trim($_POST["msg"] ?? '');
        if ($msg_body === '') {
            stderr($lang['error'], $lang['no_body']);
        }

        $subject = trim($_POST['subject'] ?? '');
        if ($subject === '') {
            stderr($lang['error'], $lang['no_subject']);
        }

        // Формируем сообщение
        $msg = "[b]Сообщение для службы тех. поддержки[/b] от [b]{$nick}[/b]\n";
        $msg .= "(ip-адрес — {$ip})\n\n";
        $msg .= "Email: {$mail}\n\n";
        $msg .= "Текст сообщения:\n{$msg_body}";

        // Вставка в БД с полем spam
        sql_query("INSERT INTO messages 
            (poster, sender, receiver, added, msg, subject, location, spam) 
            VALUES (
                " . sqlesc($uid) . ", 
                " . sqlesc($uid) . ", 
                " . sqlesc($receiver) . ", 
                '" . get_date_time() . "', 
                " . sqlesc($msg) . ", 
                " . sqlesc($subject) . ", 
                1,
                0
            )") or sqlerr(__FILE__, __LINE__);

        $sended_id = mysqli_insert_id($mysqli);

        header("Refresh: 2; url=support.php");
        stderr($lang['success'], $lang['success_msg_sent']);
        exit;
    } else {
        stderr($lang['error'], $lang['error_code']);
    }
}

stdhead($lang['support']);
begin_main_frame();

// Запуск сессии (если не запущена)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo '<script type="text/javascript" src="' . $DEFAULTBASEURL . '/js/g.placeholder.js"></script>';
?>

<h2><?= $lang['support'] ?></h2>
<h3><?= $lang['your_ip'] . " " . getip() ?></h3>

<form name="message" method="post" id="message" action="support.php">
    <table class="main" align="center" width="100%">
        <tr><td>
        <?php if ($CURUSER): ?>
            <input name="nick" type="text" class="input" value="<?= htmlsafechars($CURUSER["username"]) ?>" size="30" maxlength="60" readonly />
        <?php else: ?>
            <input placeholder="<?= $lang['nick'] ?>" name="nick" type="text" class="input" id="nick" size="30" maxlength="60" />
            <script type="text/javascript">inputPlaceholder(document.getElementById('nick'))</script>
        <?php endif; ?>
        </td></tr>

        <tr><td>
            <input placeholder="<?= $lang['subject'] ?>" name="subject" id="subject" type="text" class="input" size="60" maxlength="60" />
            <script type="text/javascript">inputPlaceholder(document.getElementById('subject'))</script>
        </td></tr>

        <?php if (!$CURUSER): ?>
        <tr><td>
            <input placeholder="Ваш e-mail" name="mail" id="mail" type="text" class="input" size="60" maxlength="60" />
            <script type="text/javascript">inputPlaceholder(document.getElementById('mail'))</script>
        </td></tr>
        <?php endif; ?>

        <tr><td align="center">
            <?php textbbcode("message", "msg", ""); ?>
        </td></tr>

        <tr><td>
            <div style="width: 400px; float: left; height: 60px">
                <br />
                <img id="captcha_img" src="./captcha/cf.captcha.php?img=<?= time() ?>" /><br />
                <a href="#" onclick="document.getElementById('captcha_img').src = './captcha/cf.captcha.php?img=' + Math.random(); return false">Обновить картинку</a><br/>
            </div>
            <div style="clear: both"></div>
            <br />
            <input placeholder="<?= $lang['user_code'] ?>" name="setCaptcha" id="setCaptcha" type="text" size="30" maxlength="60" />
            <script type="text/javascript">inputPlaceholder(document.getElementById('setCaptcha'))</script>
        </td></tr>

        <tr><td>
            <button type="submit" class="button"><?= $lang['sent'] ?></button>
        </td></tr>
    </table>
</form>

<?php
end_main_frame();
stdfoot();
?>