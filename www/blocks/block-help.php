<?php
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $lang, $friendly_title;

if (!cache_check('help_main', 600)) {
    $content = "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\"><tr><td class=\"text\">";

    if (cache_check("need_help", 600)) {
        $res = cache_read("need_help");
    } else {
        $res = sql_query("SELECT torrents.id, torrents.name, torrents.seeders, torrents.leechers, torrents.owner, torrents.owner_name FROM torrents WHERE (leechers > 0 AND seeders = 0) OR (leechers / seeders >= 4) ORDER BY leechers DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
        $need_help_cache = [];
        while ($cache_data = mysqli_fetch_assoc($res)) {
            $need_help_cache[] = $cache_data;
        }

        cache_write("need_help", $need_help_cache);
        $res = $need_help_cache;
    }

    foreach ($res as $arr) {
        $torrname = $arr['name'];
        if (strlen($torrname) > 155) {
            $torrname = substr($torrname, 0, 155) . "...";
        }
        $content .= "<b><a href=\"details/id" . $arr['id'] . "-" . friendly_title($arr['name']) . "\" alt=\"" . htmlspecialchars($arr['name'], ENT_QUOTES) . "\" title=\"" . htmlspecialchars($arr['name'], ENT_QUOTES) . "\">" . htmlspecialchars($torrname, ENT_QUOTES) . "</a></b>&nbsp;<font color=\"#0099FF\"><b> (Раздают: " . number_format($arr['seeders']) . " Качают: " . number_format($arr['leechers']) . ") by " . htmlspecialchars($arr['owner_name'], ENT_QUOTES) . "</b></font><hr>\n";
    }

    $content .= "</td></tr></table>";
    cache_write('help_main', $content);
} else {
    $content = cache_read('help_main');
}
?>