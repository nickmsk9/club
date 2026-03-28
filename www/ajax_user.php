<?php

require "include/bittorrent.php";

dbconn();
global $mysqli;
header ("Content-Type: text/html; charset=" . $lang['language_charset']);

$nick = base64_decode(trim($_GET["text"]));

if(strlen($nick) < 1)
    $nick = "a";

$res = mysqli_query($mysqli, "SELECT * FROM users WHERE username LIKE '" . mysqli_real_escape_string($mysqli, $nick) . "%' ORDER BY username LIMIT 50");

$antal = mysqli_num_rows($res);

$num = mysqli_num_rows($res);

$ut = "<table border=0 align=center cellspacing=0 cellpadding=5>\n";
$ut .= "<tr><td class=colhead align=left>Аккаунт</td><td class=colhead>Зарегистрирован</td><td class=colhead>Последний вход</td><td class=colhead align=left>Статус</td></tr>\n";

for ($i = 0; $i < $num; ++$i) {
    $arr = mysqli_fetch_assoc($res);

    if ($arr['added'] == '0000-00-00 00:00:00')
        $arr['added'] = '-';
    if ($arr['last_access'] == '0000-00-00 00:00:00')
        $arr['last_access'] = '-';

    $ut .= "<tr><td align=left><a href=user/id{$arr['id']}><b>{$arr['username']}</b></a>" . 
           ((isset($arr["donated"]) && $arr["donated"] > 0) ? "<img src=/pic/star.gif border=0 alt='Donor'>" : "") . "</td>" .
           "<td>{$arr['added']}</td><td>{$arr['last_access']}</td>" .
           "<td align=left>" . get_user_class_name($arr["class"], $arr['doljuploader'] ?? 0) . "</td></tr>\n";
}
$ut .= "</table>\n";
$ut .= "<br>" . $antal ." hittade";

echo $ut;
?>