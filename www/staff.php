<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
stdhead("Персонал");
begin_main_frame();

global $lang, $DEFAULTBASEURL, $memcached;

// Инициализация массивов
$staff_table = [];
$col = [];

// Кеш staff — 600 сек
if ($memcached instanceof Memcached) {
    $res = $memcached->get('staff');
    if ($res === false) {
        $res = [];
        $sql = "SELECT id, username, last_access, avatar, class, donor, warned, enabled, gender, birthday
                FROM users 
                WHERE class IN (".UC_MODERATOR.", ".UC_ADMINISTRATOR.", ".UC_SYSOP.") 
                ORDER BY username";
        $q = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($q)) {
            $res[] = $row;
        }
        $memcached->set('staff', $res, 600);
    }
} else {
    // Fallback если memcached не работает
    $res = [];
    $sql = "SELECT id, username, last_access, avatar, class, donor, warned, enabled, gender, birthday
            FROM users 
            WHERE class IN (".UC_MODERATOR.", ".UC_ADMINISTRATOR.", ".UC_SYSOP.") 
            ORDER BY username";
    $q = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($q)) {
        $res[] = $row;
    }
}

// Формируем таблицу
foreach ($res as $arr) {
    $age = '';
    if (!empty($arr['birthday']) && $arr['birthday'] != "0000-00-00") {
        $current = date("Y-m-d", time() + ($CURUSER['tzoffset'] ?? 0) * 60);
        [$year2, $month2, $day2] = explode('-', $current);
        [$year1, $month1, $day1] = explode('-', $arr['birthday']);
        $age = $year2 - $year1 - ((($month2.$day2) < ($month1.$day1)) ? 1 : 0);
    }

    $avatar = (!empty($arr['avatar'])) 
        ? "<img src=\"" . htmlspecialchars($arr['avatar']) . "\" style=\"max-width:90px;border:1px double #ccc;\" alt=\"\" />"
        : "<img src=\"pic/default_avatar.gif\" style=\"max-width:90px;border:1px double #ccc;\" alt=\"\" />";

    $online = (strtotime($arr['last_access']) > (time() - 600)) 
        ? "<span style=\"color:green;\">Онлайн</span>"
        : "<span style=\"color:red;\">Оффлайн</span>";

    $staff_table[$arr['class']] = ($staff_table[$arr['class']] ?? '') .
    "<td class=\"embedded\" width=\"100%\">
        <div id=\"rounded-box-3\">
            <b class=\"r3\"></b>
            <b class=\"r1\"></b>
            <b class=\"r1\"></b>
            <div class=\"inner-box\">
                <table border=\"0\" cellpadding=\"10\" cellspacing=\"0\" width=\"100%\">
                    <tr>
                        <td class=\"embedded\">
                            <div id=\"left\">
                                <table class=\"inlay\" width=\"100%\">
                                    <tr valign=\"top\">
                                        <td width=\"90px\">$avatar</td>
                                        <td width=\"140px\">
                                            <p><b>Ник :</b> <a href=\"{$DEFAULTBASEURL}/user/id{$arr['id']}\">"
                                                . get_user_class_color($arr['class'], $arr['username']) . get_user_icons($arr) . "</a></p>
                                            <p><b>Возраст:</b> $age</p>
                                            <p><b>Статус:</b> $online</p>
                                            <p><b>Связь:</b> <a href=\"message.php?action=sendmessage&amp;receiver={$arr['id']}\">ЛС</a></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <b class=\"r1\"></b>
            <b class=\"r1\"></b>
            <b class=\"r3\"></b>
        </div>
    </td>";

    // Show 3 staff per row, separated by an empty column
    $col[$arr['class']] = ($col[$arr['class']] ?? 0) + 1;
    if ($col[$arr['class']] <= 2) {
        $staff_table[$arr['class']] .= "<td class=\"embedded\">&nbsp;</td>";
    } else {
        $staff_table[$arr['class']] .= "</tr><tr>";
        $col[$arr['class']] = 0;
    }
}

begin_frame("Персонал");
?>
<style>
table.inlay td {
    border: none;
    padding: 3px;
}
table.inlay p {
    margin:0;
}
#left {
    width: 240px;
    height: 105px;
    padding: 0;
    margin: 3px;
}
</style>
<table width="100%" cellspacing="0">
<tr>
<tr><td class="embedded" colspan="11">Вопросы, на которые есть ответы в правилах или FAQ, будут оставлены без внимания.</td></tr>
<tr><td class="embedded" colspan="11"><font color="red"><b>Навязчивые просьбы на должность Администратора и Модератора , будут караться Баном !</b></font></td></tr>
<td class="embedded" width="125">&nbsp;</td>
<td class="embedded" width="25">&nbsp;</td>
<td class="embedded" width="35">&nbsp;</td>
<td class="embedded" width="85">&nbsp;</td>
<td class="embedded" width="125">&nbsp;</td>
<td class="embedded" width="25">&nbsp;</td>
<td class="embedded" width="35">&nbsp;</td>
<td class="embedded" width="85">&nbsp;</td>
</tr>
<tr><td class="embedded" colspan="11">&nbsp;</td></tr>
<tr><td class="embedded" colspan="11"><div class="c_title"><?=$lang['class_sysop']?></div></td></tr>
<tr><td class="embedded" colspan="11"><hr color="#4040c0" size="1"></td></tr>
<tr>
<?= $staff_table[UC_SYSOP] ?? "" ?>
</tr>
<tr><td class="embedded" colspan="11">&nbsp;</td></tr>
<tr><td class="embedded" colspan="11"><div class="c_title"><?=$lang['class_moderator']?></div></td></tr>
<tr><td class="embedded" colspan="11"><hr color="#4040c0" size="1"></td></tr>
<tr>
<?= $staff_table[UC_MODERATOR] ?? "" ?>
</tr>
</table>
<?
end_frame();
end_main_frame();
stdfoot();
?>