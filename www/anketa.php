<?php
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();
global $CURUSER;

function bark($msg) {
    global $tracker_lang;
    stdhead($tracker_lang['error']);
    stdmsg($tracker_lang['error'], $msg);
    stdfoot();
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['act']) ? trim($_GET['act']) : '';

if ($action === "") {
    $res = sql_query("SELECT konkurs.*, users.id AS uid, users.username FROM konkurs LEFT JOIN users ON konkurs.uid = users.id WHERE konkurs.id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);

    if (!$row)
        stderr("Ошибка", "Нет доступа ! <a href=\"javascript:history.go(-1);\">Назад</a>");

    $ok = ($CURUSER["id"] === $row["uid"] || get_user_class() >= UC_CURATOR) ? 1 : 0;
    $del = get_user_class() >= UC_CURATOR ? "  <a href=\"/anketa.php?act=del&id=$id\" title=\"Удалить\"><font color='red'>(Удалить)</font></a>" : "";

    stdhead("Анкета участницы - " . $row["name"] . "  (" . $row["username"] . ")");
    begin_main_frame();
    begin_frame("<a href='/anketi.php' title='Анкеты Участниц Конкурса'>Анкеты</a> - " . $row["name"] . "  (" . $row["username"] . ")  " . $del, true);

    print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"75%\"> \n");
    print("<tr><td width=\"10%\"><b>Имя (ник на сайте)</b></td><td width=\"45%\">" . $row["name"] . "  (" . $row["username"] . ")</td></tr>");
    print("<tr><td><b>Возраст</b></td><td>" . $row["age"] . "</td></tr>");
    print("<tr><td><b>Откуда</b></td><td>" . $row["from"] . "</td></tr>");
    print("<tr><td><b>Цвет глаз</b></td><td>" . $row["eyes"] . "</td></tr>");
    print("<tr><td><b>Цвет волос</b></td><td>" . $row["hair"] . "</td></tr>");
    print("<tr><td><b>Грудь, талия, бедра</b></td><td>" . $row["body"] . "</td></tr>");
    print("<tr><td><b>О себе</b></td><td>" . format_comment($row["life"]) . "</td></tr>");
    print("<tr><td><b>Заветная мечта</b></td><td>" . $row["dream"] . "</td></tr>");
    print("<tr><td><b>Девиз</b></td><td>" . $row["deviz"] . "</td></tr>");
    print("<tr><td><b>Любимое Аниме</b></td><td>" . $row["anime"] . "</td></tr>");
    print("<tr><td><b>Профиль на сайте</b></td><td><a href=\"/user/id{$row['uid']}\" title=\"Профиль {$row['username']}\">{$row['username']}</a></td></tr>");

    // Скриншоты
    $scr1 = $row["photo1"] ? "<a href=\"{$row['photo1']}\" class=\"screen\"><img border='0' alt='{$row['name']}' width=150 src='{$row['photo1']}' /></a>" : "";
    $scr2 = $row["photo2"] ? "<a href=\"{$row['photo2']}\" class=\"screen\"><img border='0' alt='{$row['name']}' width=150 src='{$row['photo2']}' /></a>" : "";
    $scr3 = $row["photo3"] ? "<a href=\"{$row['photo3']}\" class=\"screen\"><img border='0' alt='{$row['name']}' width=150 src='{$row['photo3']}' /></a>" : "";

    print("<tr><td colspan=\"2\" align=\"center\">$scr1 $scr2 $scr3</td></tr>");
    print("<tr><td colspan=\"2\" align=\"center\"><input type=\"button\" onclick=\"history.back();\" value=\"Назад\"/></td></tr>");

    end_frame();
    end_main_frame();
    stdfoot();

} elseif ($action === "del") {
    if (get_user_class() >= UC_CURATOR) {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        sql_query("DELETE FROM konkurs WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        header("Refresh: 1; url=" . $DEFAULTBASEURL . "/anketi.php");
        stderr("Успешно", "Анкета удалена !");
    } else {
        header("Refresh: 1; url=" . $DEFAULTBASEURL . "/anketi.php");
        stderr("Ошибка", "Шо за ?!");
    }
}
