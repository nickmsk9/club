<?php
include("include/bittorrent.php");
dbconn();
global $mysqli;

stdhead('Отлов мультитрекерных аккаунтов');
begin_main_frame();

$count = 0;
$array = [];
$torrents = [];

$res = mysqli_query($mysqli, "SELECT GROUP_CONCAT(DISTINCT p.torrent) AS torrents, GROUP_CONCAT(DISTINCT p.ip) AS ips, p.userid, u.username FROM peers p LEFT JOIN users u ON p.userid = u.id WHERE u.enabled = 'yes' GROUP BY p.userid ORDER BY p.userid;");
if (!$res) {
    die("Ошибка запроса: " . mysqli_error($mysqli));
}
while ($row = mysqli_fetch_assoc($res)) 
{
    $ips = explode(",", $row["ips"]);
    $torrents_ids = explode(",", $row["torrents"]);

    if (count($ips) > 1 && count($torrents_ids) < 5) 
    {
        $array[] = [
            "id" => $row["userid"],
            "name" => $row["username"],
            "ips" => $ips, 
            "torrents" => $torrents_ids,
        ];
        $count++;
    }
}

$res = mysqli_query($mysqli, "SELECT id, name FROM torrents");
if (!$res) {
    die("Ошибка запроса: " . mysqli_error($mysqli));
}
while ($row = mysqli_fetch_assoc($res)) {
    $torrents[$row["id"]] = $row["name"];
}
 
print "<h1>Отлов мультитрекерных аккаунтов</h1>";
print "<h2>Подозрительных личностей: " . htmlspecialchars($count) . "</h2>";
 
print "<table width='100%' cellpadding='5'>";
foreach ($array as $v) 
{
    print "<tr><td><a href='/user/id" . intval($v["id"]) . "'>" . htmlspecialchars($v["name"]) . "</a></td><td>";
 
    foreach ($v["ips"] as $ip) {
        print "<a href='/usersearch.php?ip=" . htmlspecialchars($ip) . "'>" . htmlspecialchars($ip) . "</a><br />";
    }
 
    print "</td><td>";
 
    foreach ($v["torrents"] as $t) {
        $t_name = $torrents[$t] ?? 'Неизвестно';
        print "<a target='_blank' href='/details/id" . intval($t) . "'>" . htmlspecialchars($t_name) . "</a><br />";
    }
 
    print "</td></tr>";
}
print "</table>";
end_main_frame();
stdfoot();
?>