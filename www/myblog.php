<?php
require "include/bittorrent.php"; 
dbconn(false); 
loggedinorreturn(); 

// Инициализация Memcached (если потребуется)
global $memcache_obj;
if (!isset($memcache_obj) || !$memcache_obj instanceof Memcached) {
    $memcache_obj = new Memcached();
    if (empty($memcache_obj->getServerList())) {
        $memcache_obj->addServer('127.0.0.1', 11211);
    }
}

// Безопасная обработка tag
$tag = isset($_GET["tag"]) ? htmlspecialchars(strtolower($_GET["tag"])) : "";
if (empty($tag)) unset($tag);

// Безопасная обработка bid
if (isset($_GET['bid'])) {
    $bid = (int)$_GET['bid'];
} else {
    $bid = $CURUSER['id'];
}

// Формируем параметры для SQL
$wherein = '';
$addparam = '';
if (isset($tag)) {
    $wherein = 'AND LOWER(blogs.tags) LIKE ("%' . $tag . '%")';
    $addparam = "tag=" . $tag . "&";
}
if (isset($bid)) {
    $wherein = '';
    $addparam = "bid=" . $bid . "&";
}

// Получаем количество блогов пользователя
$res1 = sql_query("SELECT COUNT(*) FROM blogs WHERE uid = " . sqlesc($bid) . " $wherein"); 
$row1 = mysqli_fetch_array($res1); 
$count = $row1[0]; 
unset($row1);

$perpage = 7; 
if (!$count || !is_valid_id($bid)) 
    stderr($lang['error'], "Нет блога с таким ID"); 

// Безопасная инициализация $pagerlink
$pagerlink = "";

// Фикс добавления параметров к pager
if ($addparam != "") {
    if ($pagerlink != "") {
        if ($addparam[strlen($addparam) - 1] != ";") { // & = &amp;
            $addparam = $addparam . "&" . $pagerlink;
        } else {
            $addparam = $addparam . $pagerlink;
        }
    }
} else {
    $addparam = $pagerlink;
}

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "myblog.php?" . $addparam);

stdhead("Блоги"); 
begin_main_frame();
?>
<style>
#posts {margin: 10px 15px 10px 15px}
#posts .avatar {border: 0px; width: 100px; height: 100px; padding: 1px 1px 1px 1px;}
#post-list {padding: 0px 10px 10px 10px;}
#post-list .date {color: #92918D; margin-bottom: 2px;}
#post-list .date a{color: #92918D;}
#post-list .date a:hover{text-decoration: none;}
#post-list .title {font-size: 20px; padding-bottom: 7px;}
#post-list .text {line-height: 17px;padding: 10px 0;}
#post-list .post-bottom {color: #202020; font-style: italic;}
#post-list .greyb {color: #92918D;}
#post-list .greyb:hover {color: #d82a0e;}
#post-list .cat-btm {color: #92918D; margin-right: 20px;}
#post-list .lnk-btm {margin-left: 20px; color: #6aa100;}
#posts-content .space {margin-top: 40px;}
#img {margin:2px;}
#img:hover{border-bottom:1px dashed #202020;}
</style>
<?php

blog_menu();
$sql = sql_query(
    "SELECT blogs.*,u.id, u.username,u.class,u.avatar 
     FROM blogs 
     LEFT JOIN users u ON blogs.uid = u.id  
     WHERE uid = " . sqlesc($bid) . " $wherein 
     ORDER BY bid DESC $limit"
) or die(mysqli_error($GLOBALS["___mysqli_ston"]));

while ($row = mysqli_fetch_assoc($sql)) { 
    $bid = (int)$row['bid'];
    $avatar = ($row["avatar"] == "" ? "/themes/Anime/images/default_avatar.gif" : "" . $row['avatar'] . "");
    $userid = (int)$row['uid'];
    $moderate = "";
    if ($CURUSER["id"] == $userid || get_user_class() >= UC_MODERATOR) {
        $moderate = "  <a id='img' href=\"/blog.php?bid=" . $bid . "&amp;action=edit\"><img src='/pic/blog/edit.png' border='0px' alt='Редактировать' /></a><br />
        <a id='img' href=\"/blog.php?bid=" . $bid . "&amp;action=delete\"><img src='/pic/blog/delete.png' border='0px' alt='Удалить' /></a>";
    }
    $date = get_blog_time($row['p_added']);
    $username = $row['username'];
    $postname = trim($row['subject']);
    $full_url = "/blog.php?bid=$bid";
    $text = explode("[more]", $row['txt']);
    $text = format_comment($text[0]);
    $tags = htmlspecialchars($row['tags']);
    $tags = blogtags($tags);
    $com_count = (int)$row['comments'];
    $views_count = (int)$row['views'];

    $h = "<table id=\"posts\" width=\"98%\"><tbody><tr>
    <td class=\"avatar\" style=\"border: 0px; width: 100px; height: 100px; padding: 1px 1px 1px 1px;\" valign=\"top\" align=\"center\"><img src=\"" . $avatar . "\" border=\"0px\" style=\"max-width: 90px; max-height: 90px;\"/></td>
    <td valign=\"top\" border=\"0px\">
    <div id=\"post-list\" style='float:left;width:678px;'><div class=\"title\" >" . $postname . "</div>
    <div class=\"date\">Написал <a href=\"/user/id" . $userid . "\"><u>" . $username . "</u></a>, <i>" . $date . "</i></div>
    <div class=\"text\">" . $text . "</div>
    <div class=\"post-bottom\">
    <span class=\"cat-btm\">Метки: " . $tags . "</span><br />
    <span class=\"cat-btm\">Комментарии: <b>" . $com_count . "</b></span>
    <span class=\"cat-btm\">Просмотров: <b>" . $views_count . "</b></span> 
    <span class=\"cat-btm\"><a href=" . $full_url . ">Читать далее...</a></span> 
    </div>
    </div>
    <div style='float:right;width:31px;height:77px;padding-top:2px;'>" . $moderate . "</div><div style='clear:both;'></div>
    </td>
    </tr>
    </tbody></table>";

    echo $h;
}

print("<table><tr><td id=no_border>" . $pagerbottom . "</td></tr></table>");
end_main_frame();
stdfoot();
?>