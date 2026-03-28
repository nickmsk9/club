<?php
require_once("include/bittorrent.php");
dbconn();
global $mysqli;
global $TBDEV;
$baseurl = (is_array($TBDEV) && isset($TBDEV['baseurl'])) ? $TBDEV['baseurl'] : '';

$lang = array(
    'cheaters_uname' => "Имя",
    'cheaters_error' => "Ошибка",
    'cheaters_rc' => "Ratio Cheaters - Nosey Cunt !",
    'cheaters_upped' => "Раздал",
    'cheaters_d' => "Отключить",
    'cheaters_downspeed' => "Скорость",
    'cheaters_r' => "Удалить",
    'cheaters_within' => "Время ",
    'cheaters_speed' => "Скорость",
    'cheaters_sec' => "сек",
    'cheaters_uc' => "Клиент :",
    'cheaters_ipa' => "Ip Аддрес:",
    'cheaters_client' => "Клиент",
    'cheaters_hbcc' => " помечен как читер , из за высокой скорости отдачи  !",
    'cheaters_cad' => "Пометить всех",
    'cheaters_car' => "Пометить всех",
    'cheaters_ac' => "Готово",
    'torrent' => "Торрент"
);

stdhead('Накрутка Аплоуда');

if (!isset($CURUSER["class"]) || $CURUSER["class"] < UC_ADMINISTRATOR)
    stderr($lang['cheaters_error'], "{$lang['cheaters_rc']}");

if (isset($_POST["nowarned"]) && $_POST["nowarned"] == "nowarned") {
    if ((empty($_POST["desact"]) || !is_array($_POST["desact"])) && (empty($_POST["remove"]) || !is_array($_POST["remove"]))) {
        stderr("Ошибка", "Вы должны выбрать хотя бы одного пользователя.");
    }

    if (!empty($_POST["remove"]) && is_array($_POST["remove"])) {
        $ids = array_map('intval', $_POST["remove"]);
        if (!empty($ids)) {
            $sql = "DELETE FROM cheaters WHERE id IN (" . implode(", ", $ids) . ")";
            $res = $mysqli->query($sql);
            if (!$res) sqlerr(__FILE__, __LINE__);
        }
    }

    if (!empty($_POST["desact"]) && is_array($_POST["desact"])) {
        $ids = array_map('intval', $_POST["desact"]);
        if (!empty($ids)) {
            $sql = "UPDATE users SET enabled = 'no' WHERE id IN (" . implode(", ", $ids) . ")";
            $res = $mysqli->query($sql);
            if (!$res) sqlerr(__FILE__, __LINE__);
        }
    }
}

begin_main_frame();
begin_frame("Читеры:", true);

$res = $mysqli->query("SELECT COUNT(*) FROM cheaters") or sqlerr(__FILE__, __LINE__);
$row = $res->fetch_array();
$count = isset($row[0]) ? $row[0] : 0;
$perpage = 15;
$pager = pager($perpage, $count, "cheaters.php?action=cheaters&amp;");
if (!is_array($pager)) $pager = [];

echo "<form action='cheaters.php?action=cheaters' method='post'>
<script type='text/javascript'>
function klappe(id) {
    var el = document.getElementById('k' + id);
    el.style.display = (el.style.display == 'none') ? 'block' : 'none';
}
function check(field) {
    for (let i = 0; i < field.length; i++) {
        field[i].checked = !field[i].checked;
    }
}
</script>";

echo isset($pager['pagertop']) ? $pager['pagertop'] : '';

echo "<table width=\"80%\">
<tr>
<td class=\"tableb\" width=\"10\" align=\"center\">#</td>
<td class=\"tableb\">{$lang['cheaters_uname']}</td>
<td class=\"tableb\" width=\"10\" align=\"center\">{$lang['cheaters_d']}</td>
<td class=\"tableb\" width=\"10\" align=\"center\">{$lang['cheaters_r']}</td>
</tr>\n";

$res = $mysqli->query("SELECT * FROM cheaters ORDER BY added DESC " . ($pager['limit'] ?? '')) or sqlerr(__FILE__, __LINE__);
if ($res) {
    while ($arr = $res->fetch_assoc()) {
        $cheater_id = (int)($arr['id'] ?? 0);
        $userid = (int)($arr['userid'] ?? 0);
        $torrentid = (int)($arr['torrentid'] ?? 0);
        $upthis = $arr['upthis'] ?? 0;
        $rate = $arr['rate'] ?? 0;
        $timediff = $arr['timediff'] ?? '';
        $client = htmlspecialchars($arr['client'] ?? '');
        $userip = htmlspecialchars($arr['userip'] ?? '');
        $added = $arr['added'] ?? '';

        $rrr = $mysqli->query("SELECT id, username, class, downloaded, uploaded FROM users WHERE id = $userid");
        $aaa = $rrr && $rrr->num_rows > 0 ? $rrr->fetch_assoc() : [];

        $user_id = (int)($aaa['id'] ?? 0);
        $username = htmlspecialchars($aaa['username'] ?? 'Unknown');
        $user_downloaded = $aaa['downloaded'] ?? 0;
        $user_uploaded = $aaa['uploaded'] ?? 0;

        $rrr2 = $mysqli->query("SELECT name FROM torrents WHERE id = $torrentid");
        $aaa2 = $rrr2 && $rrr2->num_rows > 0 ? $rrr2->fetch_assoc() : [];

        $ratio = ($user_downloaded > 0) ? number_format($user_uploaded / $user_downloaded, 3) : "---";
        $uppd = mksize($upthis);

$cheater_info = "<b><a href='{$baseurl}/userdetails.php?id=$user_id'>$username</a></b>{$lang['cheaters_hbcc']}<br /><br />
        {$lang['cheaters_upped']} <b>$uppd</b><br />
        {$lang['cheaters_speed']} <b>" . mksize($rate) . "/s</b><br />
        {$lang['cheaters_within']} <b>" . htmlspecialchars($timediff) . " {$lang['cheaters_sec']}</b><br />
        {$lang['cheaters_uc']} <b>$client</b><br />
        {$lang['cheaters_ipa']} <b>$userip</b><br />
        {$lang['torrent']} <b><a href='/details/id$torrentid'>Ссылка</a></b>";

        echo "<tr>
        <td class=\"tableb\" align=\"center\">$cheater_id</td>
        <td class=\"tableb\" align=\"left\">
            <a href=\"javascript:klappe('a1$cheater_id')\">$username</a> - Added: " . get_date_time($added, 'DATE') . "
            <div id=\"ka1$cheater_id\" style=\"display: none;\"><font color=\"red\">$cheater_info</font></div>
        </td>
        <td class=\"tableb\" align=\"center\"><input type=\"checkbox\" name=\"desact[]\" value=\"$user_id\" /></td>
        <td class=\"tableb\" align=\"center\"><input type=\"checkbox\" name=\"remove[]\" value=\"$cheater_id\" /></td>
        </tr>";
    }
}

if (isset($CURUSER["class"]) && $CURUSER["class"] >= UC_ADMINISTRATOR) {
    echo "<tr>
    <td class=\"tableb\" colspan=\"4\" align=\"right\">
        <input type=\"button\" value=\"{$lang['cheaters_cad']}\" onclick=\"this.value=check(this.form.elements['desact[]'])\" />
        <input type=\"button\" value=\"{$lang['cheaters_car']}\" onclick=\"this.value=check(this.form.elements['remove[]'])\" />
        <input type=\"hidden\" name=\"nowarned\" value=\"nowarned\" />
        <input type=\"submit\" name=\"submit\" value=\"{$lang['cheaters_ac']}\" />
    </td>
    </tr>";
}

echo "</table></form>";

echo isset($pager['pagerbottom']) ? $pager['pagerbottom'] : '';

end_frame();
end_main_frame();
stdfoot();
die;
?>