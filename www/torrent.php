<?php
require_once("include/bittorrent.php");

// Подключаемся к базе данных и инициализируем Memcached
dbconn();
global $mysqli, $memcached;
// Проверяем и инициализируем Memcached, если требуется
if (!isset($memcached) || !$memcached instanceof Memcached) {
    $memcached = new Memcached();
    $memcached->addServer('localhost', 11211);
}

// Установка заголовка контента
header ("Content-Type: text/html; charset=" . (isset($lang['language_charset']) ? $lang['language_charset'] : 'utf-8'));
global $lang, $CURUSER, $DEFAULTBASEURL;
getlang();
// Проверка на AJAX запрос и POST метод
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] == 'POST') {

    // Получаем и приводим к нужному типу входные параметры
    $id = isset($_POST["torrent"]) ? (int)$_POST["torrent"] : 0;
    $act = isset($_POST["act"]) ? (string)$_POST["act"] : '';

    // Проверка валидности ID и действия
    if (!is_valid_id($id) || empty($act))
        die("Ошибка");

    // Подключаем CSS
    print("<link rel=\"stylesheet\" href=\"".$DEFAULTBASEURL."/css/torrent.css\" type=\"text/css\">\n");
function getagent($httpagent, $peer_id = "") {
        if (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_B([0-9][0-9|*])(.+)$)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
        elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_CVS)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
        elseif (preg_match("/^Java\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/<2.0.7.0";
        elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
        elseif (preg_match("/BitTorrent\/S-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "Shadow's/$matches[1]";
        elseif (preg_match("/BitTorrent\/U-([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "UPnP/$matches[1]";
        elseif (preg_match("/^BitTor(rent|nado)\\/T-(.+)$/", $httpagent, $matches))
        return "BitTornado/$matches[2]";
        elseif (preg_match("/^BitTornado\\/T-(.+)$/", $httpagent, $matches))
        return "BitTornado/$matches[1]";
        elseif (preg_match("/^BitTorrent\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
        elseif (preg_match("/^ABC ([0-9]+\.[0-9]+(\.[0-9]+)*)\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
        elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTorrent/$matches[1]";
        elseif (preg_match("/^BitTorrent\/brst(.+)/", $httpagent, $matches))
        return "Burst";
        elseif (preg_match("/^RAZA (.+)$/", $httpagent, $matches))
        return "Shareaza/$matches[1]";
        elseif (preg_match("/Rufus\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Rufus/$matches[1]";
        elseif (preg_match("/^Python-urllib\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "G3 Torrent";
        elseif (preg_match("/MLDonkey\/([0-9]+).([0-9]+).([0-9]+)*/", $httpagent, $matches))
        return "MLDonkey/$matches[1].$matches[2].$matches[3]";
        elseif (preg_match("/ed2k_plugin v([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "eDonkey/$matches[1]";
        elseif (preg_match("/uTorrent\/([0-9]+)([0-9]+)([0-9]+)([0-9A-Z]+)/", $httpagent, $matches))
        return "µTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
        elseif (preg_match("/CT([0-9]+)([0-9]+)([0-9]+)([0-9]+)/", $peer_id, $matches))
        return "cTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
        elseif (preg_match("/Transmission\/([0-9]+).([0-9]+)/", $httpagent, $matches))
        return "Transmission/$matches[1].$matches[2]";
        elseif (preg_match("/KT([0-9]+)([0-9]+)([0-9]+)([0-9]+)/", $peer_id, $matches))
        return "KTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
        elseif (preg_match("/rtorrent\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "rTorrent/$matches[1]";
        elseif (preg_match("/^ABC\/Tribler_ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "Tribler/$matches[1]";
        elseif (preg_match("/^BitsOnWheels( |\/)([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "BitsOnWheels/$matches[2]";
        elseif (preg_match("/BitTorrentPlus\/(.+)$/", $httpagent, $matches))
        return "BitTorrent Plus!/$matches[1]";
        elseif (preg_match("/^eXeem( |\/)([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "eXeem$matches[1]$matches[2]";
        elseif (preg_match("/^libtorrent\/(.+)$/", $httpagent, $matches))
        return "libtorrent/$matches[1]";
        elseif (substr($peer_id, 0, 12) == "d0c")
        return "Mainline";
        elseif (substr($peer_id, 0, 1) == "M")
        return "Mainline/Decoded";
        elseif (substr($peer_id, 0, 3) == "-BB")
        return "BitBuddy";
        elseif (substr($peer_id, 0, 8) == "-AR1001-")
        return "Arctic Torrent/1.2.3";
        elseif (substr($peer_id, 0, 6) == "exbc\08")
        return "BitComet/0.56";
        elseif (substr($peer_id, 0, 6) == "exbc\09")
        return "BitComet/0.57";
        elseif (substr($peer_id, 0, 6) == "exbc\0:")
        return "BitComet/0.58";
        elseif (substr($peer_id, 0, 4) == "-BC0")
        return "BitComet/0.".substr($peer_id, 5, 2);
        elseif (substr($peer_id, 0, 7) == "exbc\0L")
        return "BitLord/1.0";
        elseif (substr($peer_id, 0, 7) == "exbcL")
        return "BitLord/1.1";
        elseif (substr($peer_id, 0, 3) == "346")
        return "TorrenTopia";
        elseif (substr($peer_id, 0, 8) == "-MP130n-")
        return "MooPolice";
        elseif (substr($peer_id, 0, 8) == "-SZ2210-")
        return "Shareaza/2.2.1.0";
        elseif (substr($peer_id, 0, 6) == "A310--")
        return "ABC/3.1";
        else
        return "Unknown";
}

	function dltable($name, $arr, $torrent)
{

        global $CURUSER, $lang,$DEFAULTBASEURL;
        $s = "<b>" . count($arr) . " $name</b>\n";
        if (!count($arr))
                return $s;
        $s .= "\n";
        $s .= "<table width=100% class=tt border=0 cellspacing=0 cellpadding=5>\n";
        $s .= "<tr><td class=tt>".$lang['user']."</td>" .
          "<td class=tt align=center>".$lang['port_open']."</td>".
          "<td class=tt align=right>".$lang['uploaded']."</td>".
          "<td class=tt align=right>".$lang['ul_speed']."</td>".
          "<td class=tt align=right>".$lang['downloaded']."</td>" .
          "<td class=tt align=right>".$lang['dl_speed']."</td>" .
          "<td class=tt align=right>".$lang['ratio']."</td>" .
          "<td class=tt align=right>".$lang['completed']."</td>" .
          "<td class=tt align=right>".$lang['connected']."</td>" .
          "<td class=tt align=right>".$lang['idle']."</td>" .
		  "<td class=tt align=right>".$lang['client']."</td></tr>\n";

        $now = time();
        $moderator = (isset($CURUSER) && get_user_class() >= UC_MODERATOR);
		$mod = get_user_class() >= UC_MODERATOR;
        foreach ($arr as $e) {
                // user/ip/port
                // check if anyone has this ip
                $s .= "<tr>\n";
                if ($e["username"])
                  $s .= "<td><a href=\"".$DEFAULTBASEURL."/user/id$e[userid]\"><b>".get_user_class_color($e["class"], $e["username"])."</b></a>".($mod ? "&nbsp;[<span title=\"{$e["ip"]}:{$e["port"]}\" style=\"cursor: pointer\">IP</span>]" : "")."</td>\n";
                else
                  $s .= "<td>" . ($mod ? $e["ip"] : preg_replace('/\.\d+$/', ".xxx", $e["ip"])) . "</td>\n";
                $secs = max(10, ($e["la"]) - $e["pa"]);
                $revived = $e["revived"] == "yes";
        		$s .= "<td align=\"center\">" . ($e[connectable] == "yes" ? "<span style=\"color: green; cursor: help;\" title=\"Порт открыт. Этот пир может подключатся к любому пиру.\">".$lang['yes']."</span>" : "<span style=\"color: red; cursor: help;\" title=\"Порт закрыт. Рекомендовано проверить настройки Firwewall'а.\">".$lang['no']."</span>") . "</td>\n";
                $s .= "<td align=\"right\"><nobr>" . mksize($e["uploaded"]) . "</nobr></td>\n";
                $s .= "<td align=\"right\"><nobr>" . mksize($e["uploadoffset"] / $secs) . "/s</nobr></td>\n";
                $s .= "<td align=\"right\"><nobr>" . mksize($e["downloaded"]) . "</nobr></td>\n";
                //if ($e["seeder"] == "no")
                        $s .= "<td align=\"right\"><nobr>" . mksize($e["downloadoffset"] / $secs) . "/s</nobr></td>\n";
                /*else
                        $s .= "<td align=\"right\"><nobr>" . mksize($e["downloadoffset"] / max(1, $e["finishedat"] - $e["st"])) . "/s</nobr></td>\n";*/
                if ($e["downloaded"]) {
                  $ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
                    $s .= "<td align=\"right\"><font color=" . get_ratio_color($ratio) . ">" . number_format($ratio, 3) . "</font></td>\n";
                } else
					if ($e["uploaded"])
	                  	$s .= "<td align=\"right\">Inf.</td>\n";
					else
	                  	$s .= "<td align=\"right\">---</td>\n";
                $s .= "<td align=\"right\">" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "</td>\n";
                $s .= "<td align=\"right\">" . mkprettytime($now - $e["st"]) . "</td>\n";
                $s .= "<td align=\"right\">" . mkprettytime($now - $e["la"]) . "</td>\n";
                $s .= "<td align=\"right\">" . htmlspecialchars(getagent($e["agent"], $e["peer_id"])) . "</td>\n";
                $s .= "</tr>\n";
        }
        $s .= "</table>\n";
        return $s;
}
    // Кеширование информации о торренте через Memcached
    $torrent_cache_key = "torrent_info_$id";
    $torrent = $memcached->get($torrent_cache_key);
    if ($torrent === false) {
        // Получаем данные о торренте из БД
        $res = sql_query("SELECT torrents.* , " . time() . " - torrents.last_action AS lastseed, categories.name AS cat_name
        FROM torrents LEFT JOIN categories ON torrents.category = categories.id WHERE torrents.id = $id")
            or sqlerr(__FILE__, __LINE__);
        // Используем mysqli_fetch_array вместо устаревшей mysql_fetch_array
        $torrent = mysqli_fetch_array($res, MYSQLI_ASSOC);
        if (!$torrent) die("Неверный идентификатор");
        // Сохраняем результат в Memcached на 5 минут
        $memcached->set($torrent_cache_key, $torrent, 300);
    }

    print("<style>\n");
    print("table.main td {border:1px solid #cecece;margin:0;}\n");
    print("table.main a {color:#266C8A;font-family:tahoma;}\n");
    print("</style>\n");

    if ($act == "stats") {
        // Вывод статистики по торренту
        $towner = (isset($torrent["owner_name"]) ? ("<a href=".$DEFAULTBASEURL."/user/id" . $torrent["owner"] . ">" . get_user_class_color($torrent["owner_class"], htmlspecialchars_uni($torrent["owner_name"])) . "</a>") : "<i>Аноним</i>");

        print("<table width=\"100%\" class=\"tt\" cellpadding=\"5\">\n");
        print("<tr>\n");
        print("<td class=tt><b>Раздал</b></td>\n");
        print("<td class=tt><b>Просмотров</b></td>\n");
        print("<td class=tt><b>Взят</b></td>\n");
        print("<td class=tt><b>Скачен</b></td>\n");
        print("<td class=tt><b>Активность</b></td>\n");
        print("</tr>\n");
        print("<tr>\n");
        print("<td> $towner</td>\n");
        print("<td>" . (isset($torrent['views']) ? $torrent['views'] : 0) . "</td>\n");
        print("<td>" . (isset($torrent['hits']) ? $torrent['hits'] : 0) . "</td>\n");
        print("<td>" . (isset($torrent['times_completed']) ? $torrent['times_completed'] : 0) . "</td>\n");
        print("<td>" . (isset($torrent["lastseed"]) ? mkprettytime($torrent["lastseed"]) : '-') . " назад</td>");
        print("</tr>\n");
        print("</table>\n");
        print("<br />");
        // Вывод тегов
        $tags = '';
        if (isset($torrent["tags"])) {
            $tmp_tags = explode(",", $torrent["tags"]);
            foreach ($tmp_tags as $tag) {
                $tag = trim($tag);
                if ($tag !== '') {
                    $tags .= "<a style=\"font-weight:normal;\" href=\"".$DEFAULTBASEURL."/browse.php?tag=".htmlspecialchars($tag)."\">".htmlspecialchars($tag)."</a>, ";
                }
            }
            if ($tags)
                $tags = substr($tags, 0, -2);
        }
        print("<table width=\"100%\" class=\"tt\" cellpadding=\"5\">\n");
        print("<tr>\n");
        print("<td class=tt><b>Тэги</b></td>\n");
        print("</tr>\n");
        print("<tr>\n");
        print("<td>".$tags."</td>");
        print("</tr>\n");
        print("</table>\n");
        die();
    }
    elseif ($act == "screens") {
        // Вывод скриншотов
        ?>
        <script type="text/javascript" src="<?=$DEFAULTBASEURL?>/fancybox/fancybox.js"></script>
        <script>
        jQuery(document).ready(function() {
         jQuery("a.screen").fancybox({
                'overlayShow' : false,
                });
        });
        </script>
        <link rel="stylesheet" type="text/css" href="<?=$DEFAULTBASEURL?>/fancybox/fancybox.css"/>
        <?php
        $scr1 = $scr2 = $scr3 = '';
        if (!empty($torrent["screen1"]))
            $scr1 = "<a href=\"" . htmlspecialchars($torrent["screen1"]) . "\" class=\"screen\"><img border='0' alt='" . htmlspecialchars($torrent["name"]) . "' width='180px' src='" . htmlspecialchars($torrent["screen1"]) . "' /></a>";
        if (!empty($torrent["screen2"]))
            $scr2 = "<a href=\"" . htmlspecialchars($torrent["screen2"]) . "\" class=\"screen\"><img border='0' alt='" . htmlspecialchars($torrent["name"]) . "' width='180px' src='" . htmlspecialchars($torrent["screen2"]) . "' /></a>";
        if (!empty($torrent["screen3"]))
            $scr3 = "<a href=\"" . htmlspecialchars($torrent["screen3"]) . "\" class=\"screen\"><img border='0' alt='" . htmlspecialchars($torrent["name"]) . "' width='180px' src='" . htmlspecialchars($torrent["screen3"]) . "' /></a>";
        print("$scr1 $scr2 $scr3");
        die();
    }
    elseif ($act == "peers") {
        // Вывод информации о сидерах и личерах
        $downloaders = array();
        $seeders = array();
        $subres = sql_query("SELECT seeder, finishedat, downloadoffset, uploadoffset, peers.ip, port, peers.uploaded, peers.downloaded, to_go, started AS st, connectable, agent, peer_id, last_action AS la, prev_action AS pa, userid, users.username, users.class FROM peers INNER JOIN users ON peers.userid = users.id WHERE torrent = $id") or sqlerr(__FILE__, __LINE__);
        while ($subrow = mysqli_fetch_array($subres, MYSQLI_ASSOC)) {
            if (isset($subrow["seeder"]) && $subrow["seeder"] == "yes")
                $seeders[] = $subrow;
            else
                $downloaders[] = $subrow;
        }

        // Сортировка личеров и сидеров
        function leech_sort($a, $b) {
            if (isset($_GET["usort"])) return seed_sort($a, $b);
            $x = isset($a["to_go"]) ? $a["to_go"] : 0;
            $y = isset($b["to_go"]) ? $b["to_go"] : 0;
            if ($x == $y) return 0;
            if ($x < $y) return -1;
            return 1;
        }
        function seed_sort($a, $b) {
            $x = isset($a["uploaded"]) ? $a["uploaded"] : 0;
            $y = isset($b["uploaded"]) ? $b["uploaded"] : 0;
            if ($x == $y) return 0;
            if ($x < $y) return 1;
            return -1;
        }

        usort($seeders, "seed_sort");
        usort($downloaders, "leech_sort");

        $s = "";
        $s .= dltable(isset($lang['details_seeding']) ? $lang['details_seeding'] : 'Сидеры', $seeders, $torrent) . "<br />" .
              dltable(isset($lang['details_leeching']) ? $lang['details_leeching'] : 'Личеры', $downloaders, $torrent);
        print "$s";
        die();
    }
    elseif ($act == "files") {
        // Вывод списка файлов торрента
        $s = "<table class=tt border=\"1\" cellspacing=0 cellpadding=\"5\">\n";
        $subres = sql_query("SELECT * FROM files WHERE torrent = $id ORDER BY id");
        $s .= "<tr><td class=tt>" . (isset($lang['path']) ? $lang['path'] : 'Путь') . "</td><td class=tt align=right>" . (isset($lang['size']) ? $lang['size'] : 'Размер') . "</td></tr>\n";
        while ($subrow = mysqli_fetch_array($subres, MYSQLI_ASSOC)) {
            $s .= "<tbody id=\"highlighted\"><tr><td>" . htmlspecialchars($subrow["filename"]) .
                "</td><td align=\"right\">" . mksize($subrow["size"]) . "</td></tr></tbody>\n";
        }
        $s .= "</table>\n";
        print " $s";
        die();
    }
    elseif ($act == "downloaded") {
        // Вывод информации о скачавших торрент
        if (isset($torrent["times_completed"]) && $torrent["times_completed"] > 0) {
            $res = mysqli_query($mysqli, "SELECT users.id, users.username, users.uploaded, users.downloaded, users.donor, users.enabled, users.warned, users.last_access, users.class,snatched.seeder, snatched.userid, snatched.uploaded AS sn_up, snatched.downloaded AS sn_dn FROM snatched INNER JOIN users ON snatched.userid = users.id WHERE snatched.finished='yes' AND snatched.torrent =" . sqlesc($id) . " ORDER BY users.class DESC ") or sqlerr(__FILE__,__LINE__);
            $snatched_full = "<table width=100% class=tt border=1 cellspacing=0 cellpadding=5>\n";
            $snatched_full .= "<tr><td class=tt>Юзер</td><td class=tt>Раздал</td><td class=tt>Скачал</td><td class=tt>Рейтинг</td><td class=tt align=center>ЛС</td></tr>";
            while ($arr = mysqli_fetch_assoc($res)) {
                // Глобальный рейтинг
                if ($arr["downloaded"] > 0) {
                    $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
                } else if ($arr["uploaded"] > 0)
                    $ratio = "Inf.";
                else
                    $ratio = "---";
                $uploaded = mksize($arr["uploaded"]);
                $downloaded = mksize($arr["downloaded"]);
                // Рейтинг по торренту
                if ($arr["sn_dn"] > 0) {
                    $ratio2 = number_format($arr["sn_up"] / $arr["sn_dn"], 2);
                    $ratio2 = "<font color=" . get_ratio_color($ratio2) . ">$ratio2</font>";
                } else if ($arr["sn_up"] > 0)
                    $ratio2 = "Inf.";
                else
                    $ratio2 = "---";
                $uploaded2 = mksize($arr["sn_up"]);
                $downloaded2 = mksize($arr["sn_dn"]);
                $snatched_full .= "<tbody id=\"highlighted\"><tr><td><a href=user/id" . $arr["userid"] . ">" . get_user_class_color($arr["class"], $arr["username"]) . "</a>"
                    . get_user_icons($arr) . "</td><td><nobr>$uploaded&nbsp;Общего<br>$uploaded2&nbsp;Торрент</nobr></td><td><nobr>$downloaded&nbsp;Общего<br>$downloaded2&nbsp;Торрент</nobr></td>"
                    . "<td><nobr>$ratio&nbsp;Общего<br>$ratio2&nbsp;Торрент</nobr></td>"
                    . "<td align=center><a href=message.php?action=sendmessage&amp;receiver=" . $arr["userid"] . "><img src=$pic_base_url/button_pm.gif border=\"0\"></a></td></tr></tbody>\n";
            }
            $snatched_full .= "</table>\n";
            $reseed_button = "";
            if ((isset($torrent["seeders"]) && $torrent["seeders"] == 0) || (isset($torrent["leechers"], $torrent["seeders"]) && $torrent["seeders"] > 0 && ($torrent["leechers"] / $torrent["seeders"] >= 2)))
                $reseed_button = "<form action=\"takereseed.php\"><input type=\"hidden\" name=\"torrent\" value=\"$id\" /><input type=\"submit\" value=\"Позвать скачавших\" /></form>";
            print($snatched_full);
            print($reseed_button);
            die();
        } else {
            print("<div class=\"tab_error\">В даный момент информация не доступна !</div>");
        }
        die();
    }
    else
        die("Косяк !!!");
} else {
    // Прямой доступ запрещён
    die("Прямой доступ запрещен");
}
?>