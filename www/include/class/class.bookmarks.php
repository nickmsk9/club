<?php

# ВАЖНО: Не редактируйте ниже, если не уверены в своих действиях!
if (!defined('IN_TRACKER')) {
    die('Попытка взлома!');
}

function bookmarktable($res)
{
    global $pic_base_url, $CURUSER, $DEFAULTBASEURL, $use_wait, $use_ttl, $ttl_days, $lang, $mysqli;

    $rows = 0;

    // Если $res не является объектом mysqli_result, не выводим таблицу
    if (!($res instanceof mysqli_result)) {
        return $rows;
    }
?>
<script type="text/javascript" src="js/wz_tooltip.js"></script>
<?php
    // Сортировка закладок
    $count_get = 0;
    $oldlink = '';

    foreach ($_GET as $get_name => $get_value) {
        $get_name = mysqli_real_escape_string($mysqli, strip_tags(str_replace(['"', "'"], '', $get_name)));
        $get_value = mysqli_real_escape_string($mysqli, strip_tags(str_replace(['"', "'"], '', $get_value)));

        if ($get_name !== 'sort' && $get_name !== 'type') {
            if ($count_get > 0) {
                $oldlink .= '&' . $get_name . '=' . $get_value;
            } else {
                $oldlink .= $get_name . '=' . $get_value;
            }
            $count_get++;
        }
    }

    if ($count_get > 0) {
        $oldlink .= '&';
    }

    $link3 = $link4 = $link5 = $link7 = $link8 = $link9 = $link10 = '';

    if (isset($_GET['sort'], $_GET['type'])) {
        $sort = $_GET['sort'];
        $type = $_GET['type'];
        $links = ['3', '4', '5', '7', '8', '9', '10'];
        foreach ($links as $link) {
            if ($sort === $link) {
                ${"link$link"} = ($type === 'desc') ? 'asc' : 'desc';
            }
        }
    }

    if ($link3 === '') $link3 = 'desc';
    if ($link4 === '') $link4 = 'desc';
    if ($link5 === '') $link5 = 'desc';
    if ($link7 === '') $link7 = 'desc';
    if ($link8 === '') $link8 = 'desc';
    if ($link9 === '') $link9 = 'desc';
    if ($link10 === '') $link10 = 'desc';
?>
    <td align="center"><img src="pic/browse/genre.gif" alt="<?= htmlspecialchars($lang['type']); ?>" border="0" /></td>
    <td align="left" width="50%"><img src="pic/browse/release.gif" alt="<?= htmlspecialchars($lang['name']); ?>" border="0" /></td>
    <td align="center"><a href="browse.php?<?= htmlspecialchars($oldlink); ?>sort=3&type=<?= htmlspecialchars($link3); ?>" class="altlink_white"><img src="pic/browse/comments.gif" alt="<?= htmlspecialchars($lang['comments']); ?>" border="0" /></a></td>
    <td align="center"><a href="browse.php?<?= htmlspecialchars($oldlink); ?>sort=5&type=<?= htmlspecialchars($link5); ?>" class="altlink_white"><img src="pic/browse/mb.gif" alt="<?= htmlspecialchars($lang['size']); ?>" border="0" /></a></td>
    <td align="center"><a href="browse.php?<?= htmlspecialchars($oldlink); ?>sort=7&type=<?= htmlspecialchars($link7); ?>" class="altlink_white"><img src="pic/browse/seeders.gif" alt="<?= htmlspecialchars($lang['seeds']); ?>" border="0" /></a>|<a href="browse.php?<?= htmlspecialchars($oldlink); ?>sort=8&type=<?= htmlspecialchars($link8); ?>" class="altlink_white"><img src="pic/browse/leechers.gif" alt="<?= htmlspecialchars($lang['leechers']); ?>" border="0" /></a></td>
<?php
    print('<td align="center"><a href="browse.php?' . htmlspecialchars($oldlink) . 'sort=9&type=' . htmlspecialchars($link9) . '" class="altlink_white"><img src="pic/browse/upped.gif" alt="' . htmlspecialchars($lang['uploadeder']) . '" border="0" /></a></td>' . "\n");
    print('<td align="center">Удалить</td>' . "\n");
    print('</tr>' . "\n");
    print('<tbody>');

    while ($row = mysqli_fetch_assoc($res)) {
        print('<form method="post" action="takedelbookmark.php">');

        if ($row['modded'] === 'no' && get_user_class() < UC_MODERATOR && $row['owner'] !== $CURUSER['id']) {
            print('');
        } else {
            $id = $row['id'];
            print('<tr>' . "\n");
            print('<td align="center" rowspan=2 width=1% style="padding: 0px">');
            $catid = (int)$row['category'];
$catname = htmlspecialchars(cat_name($catid));
$catimg = "./pic/cats/{$catid}.gif";

print('<a href="' . htmlspecialchars($DEFAULTBASEURL) . '/browse/cat' . $catid . '">');
print('<img src="' . $catimg . '" alt="' . $catname . '" border="0" />');
print('</a>');
            print('</td>' . "\n");

            $img_tor = '';
            if (!empty($row['image1'])) {
                $img_tor = $row['image1'];
            }

            $dispname = $row['name'];

            print('<td colspan="10" align="left"><a ' . ($img_tor ? 'onmouseover="Tip(\'<img src=' . htmlspecialchars($img_tor) . ' width=200>\', 300, 600, PADDING, 1, \'red\', \'red\');" onmouseout="UnTip();"' : '') . ' href="details/');
            if (isset($variant) && $variant === 'mytorrents') {
                print('returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;');
            }
            print('id' . htmlspecialchars($id));
            print('"><b>' . htmlspecialchars($dispname) . '</b></a> ' . "\n");
            print('</td></tr><tr>');

            print('<td class="small">');
            print('<noindex><font size="1" color="#bc5349"> Тэги: ' . addtags($row['tags'], 0) . '</font></noindex>');
            print('</td>' . "\n");

            print('<td align="center">' . htmlspecialchars($row['comments']) . '</td>' . "\n");
            print('<td align="center">' . str_replace(' ', '&nbsp;', mksize($row['size'])) . '</td>' . "\n");
            print('<td align="center">');
            print('<b><span class="' . linkcolor($row['seeders']) . '">' . htmlspecialchars($row['seeders']) . '</span></b>');
            print(' | ');
            print('<b><span class="' . linkcolor($row['leechers']) . '">' . htmlspecialchars($row['leechers']) . '</span></b>' . "\n");

            if (get_user_class() >= UC_MODERATOR) {
                print('&nbsp;(' . htmlspecialchars($row['times_completed']) . ')');
            }

            print('</td>');

            print('<td align="center">' . (isset($row['owner_name']) ? ('<a href="user/id' . htmlspecialchars($row['owner']) . '"><b>' . get_user_class_color($row['owner_class'], htmlspecialchars_uni($row['owner_name'])) . '</b></a>') : '<i>(unknown)</i>') . '</td>' . "\n");

            print('<td align="center"><input type="checkbox" name="delbookmark[]" value="' . htmlspecialchars($row['bookmarkid']) . '" /></td>');
            print('</tr>' . "\n");
        }
    }
    print('</tbody>');
    print('<tr><td colspan="12" align="right"><input type="submit" value="Удалить"></td></tr>' . "\n");
    print('</form>' . "\n");

    return $rows;
}

?>