<?php
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();

stdhead("Архив Чата");
begin_main_frame();

$perpage = 35;

// Получаем общее количество записей
$res = sql_query("SELECT COUNT(*) FROM shoutbox") or sqlerr();
[$count] = mysqli_fetch_row($res);

if ($count > 0) {
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER['PHP_SELF'] . '?');

    $query = "SELECT * FROM shoutbox ORDER BY date DESC $limit";
    $res = sql_query($query) or sqlerr();

    print("<table width='100%'>\n");

    if ($pagertop) {
        print("<tr><td align='left'>$pagertop</td></tr>");
    }

    print("<table width='100%' id='chatbox'>\n");

    echo <<<JS
    <script type="text/javascript">
    jQuery(function() {
        jQuery('#chatbox tr:odd').css('background-color', '#FDFDFD');
        jQuery('#chatbox tr:even').css('background-color', '#F4F0E8');
    });
    </script>
    JS;

    while ($arr = mysqli_fetch_assoc($res)) {
        $usercolor = "<a target='_blank' style='text-decoration:none' href='user/id{$arr["userid"]}'>" .
            get_user_class_color($arr['class'], $arr["username"]) . get_user_icons($arr) . "</a>";

        $datum = gmdate("d.m H:i", $arr["date"] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);
        $text = format_comment($arr["text"]);
        // Разрешаем отображение HTML-смайликов и тегов, декодируем сущности
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        // Удаляем все теги <a>, чтобы изображения смайликов выводились напрямую
        $text = preg_replace('#</?a[^>]*>#i', '', $text);


        // Подсветка своего ника
        $text = str_replace("[$CURUSER[username]]", "<b style='color: green;'>$CURUSER[username]</b>", $text);

        // Подсветка [тегов]
        $text = preg_replace("/\[((\s|.)+?)\]/", "<b style='color: black;'>[\\1]</b>", $text);

        // Приватное сообщение к тебе
        if (strpos($text, "privat($CURUSER[username])") !== false) {
            $text = str_replace("privat($CURUSER[username])", "<b style='background: red;color: white;'>$CURUSER[username]</b>:", $text);
            $text = preg_replace("/privat\(([^()<>\s]+?)\)/i", "<b style='color: black; background: white;'>\\1</b>", $text);
            echo "<tr><td>[$datum] $usercolor: $text</td></tr>\n";

        } elseif ((($CURUSER["id"] == $arr["userid"] || $CURUSER["id"] == 22) && get_user_class() >= $arr["class"]) && strpos($text, "privat(") !== false) {
            $text = preg_replace("/privat\(([^()<>\s]+?)\)/i", "<b style='color: red; background:white'>\\1</b>", $text);
            echo "<tr><td>[$datum] $usercolor: <span style='background-color:#a4fdfe'>$text</span></td></tr>\n";

        } elseif (strpos($text, "privat(") !== false) {
            // приватное, но не для тебя — не показываем
        } else {
            echo "<tr><td>[$datum] $usercolor: $text</td></tr>\n";
        }
    }

    print("</table>");

    if ($pagerbottom) {
        print("<tr><td align='left'>$pagerbottom</td></tr>");
    }
} else {
    echo "<div align='center'>Архив чата пуст.</div>";
}

end_main_frame();
stdfoot();
?>