<?php
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();

function bark($msg) {
    global $tracker_lang, $memcached_obj;
    stdhead($tracker_lang['error']);
    stdmsg($tracker_lang['error'], $msg);
    stdfoot();
    exit;
}

global $CURUSER;
$CURUSER = loggedinuser();
if (!is_array($CURUSER) || !isset($CURUSER['id'])) {
    bark("Ошибка: пользователь не авторизован!");
}


$userid = (int)$CURUSER['id'];
$torid = isset($_GET['torid']) ? (int)$_GET['torid'] : 0;
$act = isset($_GET['act']) ? htmlspecialchars($_GET['act']) : '';

if (!empty($act) && $act == 'add') {

    $res = sql_query("SELECT name FROM torrents WHERE id = $torid") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_array($res);
    if (empty($arr["name"])) {
        bark("Торрент не существует!");
    }
    if (empty($torid)) {
        bark("Торрент не выбран!");
        header("Refresh: 3; url=browse");
        exit;
    }
    if (get_row_count("subscribe", "WHERE userid = $userid AND torid = $torid") > 0) {
        bark("Торрент \"" . $arr['name'] . "\" уже в подписках.");
    }
    sql_query("INSERT INTO subscribe (userid, torid) VALUES ($userid, $torid)") or sqlerr(__FILE__, __LINE__);
global $DEFAULTBASEURL;
    header("Refresh: 3; url=" . $DEFAULTBASEURL . "/details/id" . $torid);
    bark("Вы успешно подписались на торрент - \"" . $arr['name'] . "\"!", false);

} elseif (!empty($act) && $act == 'del') {

    if (!isset($_POST['delsub']) || !is_array($_POST['delsub'])) {
        bark("Ничего не выбрано");
    }
    // Безопасная обработка входящих id
    $ids = array_map("intval", $_POST['delsub']);
    $ids_str = implode(", ", $ids);

    $res2 = sql_query("SELECT id, userid FROM subscribe WHERE id IN ($ids_str)") or sqlerr(__FILE__, __LINE__);

    while ($arr = mysqli_fetch_assoc($res2)) {
        if ((int)$arr['userid'] === $CURUSER['id']) {
            sql_query("DELETE FROM subscribe WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);
        } else {
            bark("Вы пытаетесь удалить не свою подписку!", true);
        }
    }
    global $DEFAULTBASEURL;
    header("Refresh: 3; url=" . $DEFAULTBASEURL . "/sub.php");
    bark("Вы удалили подписку на торренты!", false);

} else {
    stdhead("Подписки");
    begin_main_frame();

    $res = sql_query("SELECT COUNT(*) FROM subscribe WHERE userid = " . sqlesc($CURUSER["id"]));
    $row = mysqli_fetch_array($res);
    $count = $row[0];

    if (!$count) {
        stdmsg($lang['error'], "У Вас нету подписок!", 'error');
    } else {
        ?>
        <table class="embedded" cellspacing="0" cellpadding="5" width="100%">
        <tr><td class="colhead" align="center" colspan="12">Список подписок</td></tr>
        <?php
        $perpage = 25;
        list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "sub.php?");
        $res = sql_query(
            "SELECT subscribe.id AS subscribeid, torrents.id, torrents.name, torrents.type, torrents.comments, torrents.leechers, torrents.seeders, torrents.owner, torrents.image1, torrents.owner_name, torrents.owner_class, torrents.tags, torrents.modded,
            torrents.save_as, torrents.numfiles, torrents.added, torrents.size, torrents.times_completed, torrents.category
            FROM subscribe
            INNER JOIN torrents ON subscribe.torid = torrents.id
            WHERE subscribe.userid = " . sqlesc($CURUSER["id"]) . "
            ORDER BY torrents.added DESC $limit"
        ) or sqlerr(__FILE__, __LINE__);
        print("<tr><td class=\"index\" colspan=\"12\">");
        print($pagertop);
        print("</td></tr>");
        subscribetable($res);
        print("<tr><td class=\"index\" colspan=\"12\">");
        print($pagerbottom);
        print("</td></tr>");
        print("</table>");
    }
    end_main_frame();
    stdfoot();
}
?>