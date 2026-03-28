<?php

if (!defined('IN_TRACKER'))
    die('Hacking attempt!');

// Редактор BBCode для формы
function commbbcode($form, $name, $text = '') {
?>
<script type="text/javascript" src="js/bbcodes.js"></script>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td colspan="2" class="brd">
<script type="text/javascript">

var text_enter_url       = "Введите полный URL ссылки";
var text_enter_page      = "Введите номер страницы";
var text_enter_url_name  = "Введите название сайта";
var text_enter_page_name = "Введите описание ссылки";
var text_enter_image     = "Введите полный URL изображения";
var text_enter_email     = "Введите e-mail адрес";
var text_code            = "Использование: [code] Здесь Ваш код.. [/code]";
var text_quote           = "Использование: [quote] Здесь Ваша Цитата.. [/quote]";
var error_no_url         = "Вы должны ввести URL";
var error_no_title       = "Вы должны ввести название";
var error_no_email       = "Вы должны ввести e-mail адрес";
var prompt_start         = "Введите текст для форматирования";
var img_title            = "Введите по какому краю выравнивать картинку (left, center, right)";
var email_title          = "Введите описание ссылки (необязательно)";
var text_pages           = "Страница";
var image_align          = "left";

var selField  = "<?= $name ?>";
var fombj    = document.getElementById('<?= $form ?>');

function SmileIT(privatesmile,form,text){
    window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+" "+privatesmile+" ";
    window.opener.document.forms[form].elements[text].focus();
}

function winop() {
    windop = window.open("moresmiles.php?form=<?= $form ?>&text=<?= $name ?>","mywin","height=500,width=500,resizable=yes,scrollbars=yes");
}
function winop2() {
    windop = window.open("moresmiles2.php?form=<?= $form ?>&text=<?= $name ?>","mywin2","height=450,width=550,resizable=yes,scrollbars=yes");
}

</script>
<style type="text/css">
.f_textarea{margin-top:-1px;color:#757575;font-size:11px;font-family:tahoma;background-repeat:repeat-x;width:400px;height:150px;border:1px solid #e0e0e0;}
.editor_button {
border: 1px solid #ccc;
margin: 1px;
padding: 2px;
}
.editor_buttoncl {
border: 2px solid #ccc;
margin:1px;
padding:2px;
}
.editor_button:hover {
cursor: pointer;
filter:progid:DXImageTransform.Microsoft.Alpha(opacity=50);
-moz-opacity: 0.5;
}
.editor_buttoncl:hover {
cursor: pointer;
filter:progid:DXImageTransform.Microsoft.Alpha(opacity=50);
-moz-opacity: 0.5;
}
</style>

<img id="b_b" class="editor_button" onclick="simpletag('b')" title="Полужирный" src="pic/bbeditor/images/bold.gif" border="0">
<img id="b_i" class="editor_button" onclick="simpletag('i')" title="Наклонный текст" src="pic/bbeditor/images/italic.gif" border="0">
<img id="b_u" class="editor_button" onclick="simpletag('u')" title="Подчеркнутый текст" src="pic/bbeditor/images/underline.gif" border="0">
<img class="editor_button" onclick="tag_url()" title="Вставка ссылки" src="pic/bbeditor/images/link.gif" border="0">
<img class="editor_button" onclick="tag_image()" title="Вставка картинки" src="pic/bbeditor/images/picture.gif" border="0">
<img id="b_quote" class="editor_button" onclick="simpletag('quote')" title="Вставка цитаты" src="pic/bbeditor/images/quote.gif" border="0">
<img class="editor_button" onclick="closeall()" title="Закрыть все открытые теги" src="pic/bbeditor/images/unlink.gif" border="0">
</td>
</tr>
<tr>
<td colspan="2" class="brd">
<textarea name="<?= $name ?>" id="<?= $name ?>" rows="10" class="f_textarea expand200-800" onclick="setNewField(this.name, document.getElementById('<?= $form ?>'))"><?= $text ?></textarea>
</td>
</tr>
</table>
<?php
}

// Категории блогов — исправлено на mysqli
function blog_cats() {
    $ret = [];
    global $mysqli;
    $res = $mysqli->query("SELECT cid, name FROM blogs_cat ORDER BY name ASC");
    while ($row = $res->fetch_assoc())
        $ret[] = $row;
    return $ret;
}

// Время для блога
function get_blog_time($timestamp = 0) {
    global $CURUSER;
    if ($timestamp)
        return gmdate("Y-m-d H:i:s", $timestamp + (($CURUSER["timezone"] + $CURUSER["dst"]) * 60));
}

// AJAX таблица комментариев к блогам
function blogtable_ajax($rows, $redaktor = "blog") {
    global $CURUSER, $avatar_max_width;

    foreach ($rows as $row) {
        $online = (strtotime($row["last_access"]) > gmtime() - 600) ? "online" : "offline";
        $online_text = ($online == "online") ? "В сети" : "Не в сети";

        print(" <div id=\"rounded-box-3\">
    <b class=\"r3\"></b><b class=\"r1\"></b><b class=\"r1\"></b><div class=\"inner-box\">
<table class=\"maibaugrand\" width=\"97%\" border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"3\" >");
        print("<tr><td class=\"colhead\" align=\"left\" border=\"0\" colspan=\"2\" height=\"24\">");

        if (isset($row["username"])) {
            $title = $row["title"];
            if ($title == "") {
                $title = get_user_class_name($row["class"]);
            } else {
                $title = htmlspecialchars_uni($title);
            }
            print(":: <img src=\"pic/buttons/button_" . $online . ".gif\" alt=\"" . $online_text . "\" title=\"" . $online_text . "\" style=\"position: relative; top: 2px;\" border=\"0\" height=\"14\">"
                . " <a name=comm" . $row["id"] . " href=user/id" . $row["user"] . " class=altlink_white><b>" . get_user_class_color($row["class"], htmlspecialchars_uni($row["username"])) . "</b></a> ::"
                . ($row["donor"] == "yes" ? "<img src=pic/star.gif alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src=\"/pic/warned.gif\" alt=\"Warned\">" : "") . " $title ::\n");

        } else {
            print("<a name=\"comm" . $row["id"] . "\"><i>[Anonymous]</i></a>\n");
        }

        $avatar = htmlspecialchars_uni($row["avatar"]);
        if (!$avatar) {
            $avatar = "pic/default_avatar.gif";
        }
        $text = "<div id=\"comment_text" . $row['id'] . "\" width=\"80%\">" . format_comment($row["text"]) . "</div>\n";

        if ($row["editedby"]) {
            $text .= "<p style=float:right><i><font size=1 class=small_com>Последний раз редактировалось <a href=user/id" . $row["editedby"] . ">" . $row["editedbyname"] . "</a> в " . get_blog_time($row['editedat']) . "</font></i></p>\n";
        }

        print("</td></tr>");
        print("<tr valign=top>\n");
        print("<td style=\"padding: 0px; width: 5%;\" align=\"center\" ><img src=\"$avatar\" width=\"$avatar_max_width\"> </td>\n");
        print("<td width=\"100%\" border=\"0\" class=\"text\">");

        print("$text</td>\n");
        print("</tr>\n");
        print("<tr><td class=colhead align=\"center\" colspan=\"2\">");
        print "<div style=\"float: left; width: auto;\">"
            . ($CURUSER ? " [<a href=\"javascript:;\" onClick=\"BL_CommentQuote('" . $row['id'] . "','" . $row['blogid'] . "')\" class=\"altlink_white\">Цитата</a>]" : "")
            . ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? " [<a href=\"javascript:;\" onClick=\"BL_EditComment('" . $row['id'] . "','" . $row['blogid'] . "')\" class=\"altlink_white\">Изменить</a>]" : "")
            . (get_user_class() >= UC_MODERATOR ? " [<a href=\"javascript:;\" onClick=\"BL_DeleteComment('" . $row['id'] . "','" . $row['blogid'] . "')\">Удалить</a>]" : "")
            . ($row["editedby"] && get_user_class() >= UC_MODERATOR ? " [<a href=\"javascript:;\"  onClick=\"BL_ViewOriginal('" . $row['id'] . "','" . $row['blogid'] . "')\" class=\"altlink_white\">Оригинал</a>]" : "")
            . (get_user_class() >= UC_MODERATOR ? " IP: " . ($row["ip"] ? "<a href=\"usersearch.php?ip=" . $row["ip"] . "\" class=\"altlink_white\">" . $row["ip"] . "</a>" : "Неизвестен") : "")
            . "</div>";

        print("<div align=\"right\">Комментарий добавлен: " . get_blog_time($row["added"]) . " </div></td></tr>");
        print("</table></div><b class=\"r1\"></b><b class=\"r1\"></b><b class=\"r3\"></b></div><br />");
    }
}

// Форматирование тегов (устранены баги инициализации переменной)
function blogtags($addtags) {
    $tags = '';
    foreach (explode(",", $addtags) as $tag) {
        $tag = trim($tag);
        if ($tag !== '') {
            $tags .= "&nbsp;<font color=#bc5349><a style=\"font-weight:normal\" href=\"blogs.php?tag=" . htmlspecialchars($tag) . "\">" . htmlspecialchars($tag) . "</a></font>, ";
        }
    }
    if ($tags)
        $tags = substr($tags, 0, -2);
    if (empty($addtags) || $tags === '')
        $tags = "Нет тэгов";
    return $tags;
}

// Меню блогов
function blog_menu() {
    echo "<table><tr><td class=\"embedded\" >
    <a style='font-size:16px;font-weight:bold;' href=\"/blog.php?action=add\">Добавить запись в блог</a> |
    <a style='font-size:16px;font-weight:bold;' href='/myblog.php'>Мои записи</a> |
    <a style='font-size:16px;font-weight:bold;' href='/blogs.php'>Записи из блогов</a> |
    <a style='font-size:16px;font-weight:bold;' href='/blogs.php?stats'>Статистика</a>
    </td></tr></table>";
}
?>