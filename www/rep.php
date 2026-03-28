<?php
require_once("include/bittorrent.php");
global $memcache_obj, $mysqli;

dbconn();
header("Content-Type: text/html; charset=" . $lang['language_charset']);

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] == 'POST') {

    $userid = (int)$_POST['userid'];
    $fromid = (int)$_POST['from'];
    $type = $_POST["type"];
    $descr = htmlspecialchars($_POST['descr']);

    if (empty($userid) || empty($fromid) || empty($type)) {
        die("Ошибка! Чего-то не хватает!");
    }

    if ($userid == $fromid || $fromid != $CURUSER['id']) {
        die("<td class=\"text\" style=\"padding:0px;text-align:center;font-size:15px;font-weight:bold;\">Нельзя самому себе повышать репутацию!</td>");
    }

    $res = sql_query("SELECT username FROM users WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_array($res);

    if (!empty($arr['username'])) {
        $canres = sql_query("SELECT added FROM karma WHERE userid = " . sqlesc($userid) . " AND fromid = " . sqlesc($fromid) . " AND old = 'no'");
        $canarr = mysqli_fetch_array($canres);

        if (!empty($canarr)) {
            die("<td class=\"text\" style=\"padding:0px;text-align:center;font-size:15px;font-weight:bold;\">Вы не можете изменить репутацию пользователю <br /> до: " . get_date_time($canarr['added'] + 1209600) . "</td>");
        }

        $karma = ($type === "plus") ? "karma + 1" : "karma - 1";

        sql_query("UPDATE users SET karma = $karma WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        sql_query("INSERT INTO karma (userid, fromid, added, type, descr) VALUES (" . sqlesc($userid) . "," . sqlesc($fromid) . ", " . sqlesc(time()) . "," . sqlesc($type) . ", " . sqlesc($descr) . ")") or sqlerr(__FILE__, __LINE__);

        $msg_pm = "Пользователь [url=/user/id" . $CURUSER['id'] . "]" . $CURUSER['username'] . "[/url] поставил вам $type в репутацию со следующим сообщением:\n\n $descr\n";
        $subj_pm = "Уведомление об изменении репутации";

  sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject, spam) VALUES (0, " . sqlesc($userid) . ", NOW(), " . sqlesc($msg_pm) . ", 0, " . sqlesc($subj_pm) . ", 0)") or sqlerr(__FILE__, __LINE__);

        if ($memcache_obj instanceof Memcached) {
            $memcache_obj->delete('users_' . $userid);
        }
        ?>

        <div id="set_rep" style="padding:10px;font-size:15px;font-weight:bold;">
        Репутация успешно изменена для пользователя <?=htmlspecialchars($arr['username'])?>
        </div>

        <?php
    } else {
        die("Такого пользователя не существует!");
    }

} else {
    die("Какого тут ищем?");
}