<?php

declare(strict_types=1);

$VERSION = '$Id: memcache.php, PHP8+ Memcached refactor $';
define('DATE_FORMAT', 'Y/m/d H:i:s');
define('GRAPH_SIZE', 200);
define('MAX_ITEM_DUMP', 50);

$MEMCACHE_SERVERS = [
    '127.0.0.1:11211',
    // add more as needed
];

// Don't cache this page
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Helper functions
function duration(int $ts): string {
    global $time;
    $years = (int)((($time - $ts) / (7 * 86400)) / 52.177457);
    $rem = (int)(($time - $ts) - ($years * 52.177457 * 7 * 86400));
    $weeks = (int)(($rem) / (7 * 86400));
    $days = (int)(($rem) / 86400) - $weeks * 7;
    $hours = (int)(($rem) / 3600) - $days * 24 - $weeks * 7 * 24;
    $mins = (int)(($rem) / 60) - $hours * 60 - $days * 24 * 60 - $weeks * 7 * 24 * 60;
    $str = '';
    if ($years == 1) $str .= "$years year, ";
    if ($years > 1) $str .= "$years years, ";
    if ($weeks == 1) $str .= "$weeks week, ";
    if ($weeks > 1) $str .= "$weeks weeks, ";
    if ($days == 1) $str .= "$days day,";
    if ($days > 1) $str .= "$days days,";
    if ($hours == 1) $str .= " $hours hour and";
    if ($hours > 1) $str .= " $hours hours and";
    if ($mins == 1) $str .= " 1 minute";
    else $str .= " $mins minutes";
    return $str;
}

function bsize($s): string {
    foreach (['', 'K', 'M', 'G'] as $i => $k) {
        if ($s < 1024) break;
        $s /= 1024;
    }
    return sprintf("%5.1f %sBytes", $s, $k);
}

function menu_entry($ob, $title) {
    global $PHP_SELF;
    if ($ob == $_GET['op']) {
        return "<li><a class=\"child_active\" href=\"$PHP_SELF&op=$ob\">$title</a></li>";
    }
    return "<li><a class=\"active\" href=\"$PHP_SELF&op=$ob\">$title</a></li>";
}

function getHeader() {
    $host = $_SERVER['HTTP_HOST'];
    $header = <<<EOB
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head><title>Информация о Memcached</title>
<style type="text/css"><!--
body { background:white; font-size:100.01%; margin:0; padding:0; }
body,p,td,th,input,submit { font-size:0.8em;font-family:arial,helvetica,sans-serif; }
* html body   {font-size:0.8em}
* html p      {font-size:0.8em}
* html td     {font-size:0.8em}
* html th     {font-size:0.8em}
* html input  {font-size:0.8em}
* html submit {font-size:0.8em}
td { vertical-align:top }
a { color:black; font-weight:none; text-decoration:none; }
a:hover { text-decoration:underline; }
div.content { padding:1em 1em 1em 1em; position:absolute; width:97%; z-index:100; }

h1.memcache { background:rgb(153,153,204); margin:0; padding:0.5em 1em 0.5em 1em; }
* html h1.memcache { margin-bottom:-7px; }
h1.memcache a:hover { text-decoration:none; color:rgb(90,90,90); }
h1.memcache span.logo {
	background:rgb(119,123,180);
	color:black;
	border-right: solid black 1px;
	border-bottom: solid black 1px;
	font-style:italic;
	font-size:1em;
	padding-left:1.2em;
	padding-right:1.2em;
	text-align:right;
	display:block;
	width:130px;
	}
h1.memcache span.logo span.name { color:white; font-size:0.7em; padding:0 0.8em 0 2em; }
h1.memcache span.nameinfo { color:white; display:inline; font-size:0.4em; margin-left: 3em; }
h1.memcache div.copy { color:black; font-size:0.4em; position:absolute; right:1em; }
hr.memcache {
	background:white;
	border-bottom:solid rgb(102,102,153) 1px;
	border-style:none;
	border-top:solid rgb(102,102,153) 10px;
	height:12px;
	margin:0;
	margin-top:1px;
	padding:0;
}

ol,menu { margin:1em 0 0 0; padding:0.2em; margin-left:1em;}
ol.menu li { display:inline; margin-right:0.7em; list-style:none; font-size:85%}
ol.menu a {
	background:rgb(153,153,204);
	border:solid rgb(102,102,153) 2px;
	color:white;
	font-weight:bold;
	margin-right:0em;
	padding:0.1em 0.5em 0.1em 0.5em;
	text-decoration:none;
	margin-left: 5px;
	}
ol.menu a.child_active {
	background:rgb(153,153,204);
	border:solid rgb(102,102,153) 2px;
	color:white;
	font-weight:bold;
	margin-right:0em;
	padding:0.1em 0.5em 0.1em 0.5em;
	text-decoration:none;
	border-left: solid black 5px;
	margin-left: 0px;
	}
ol.menu span.active {
	background:rgb(153,153,204);
	border:solid rgb(102,102,153) 2px;
	color:black;
	font-weight:bold;
	margin-right:0em;
	padding:0.1em 0.5em 0.1em 0.5em;
	text-decoration:none;
	border-left: solid black 5px;
	}
ol.menu span.inactive {
	background:rgb(193,193,244);
	border:solid rgb(182,182,233) 2px;
	color:white;
	font-weight:bold;
	margin-right:0em;
	padding:0.1em 0.5em 0.1em 0.5em;
	text-decoration:none;
	margin-left: 5px;
	}
ol.menu a:hover {
	background:rgb(193,193,244);
	text-decoration:none;
	}


div.info {
	background:rgb(204,204,204);
	border:solid rgb(204,204,204) 1px;
	margin-bottom:1em;
	}
div.info h2 {
	background:rgb(204,204,204);
	color:black;
	font-size:1em;
	margin:0;
	padding:0.1em 1em 0.1em 1em;
	}
div.info table {
	border:solid rgb(204,204,204) 1px;
	border-spacing:0;
	width:100%;
	}
div.info table th {
	background:rgb(204,204,204);
	color:white;
	margin:0;
	padding:0.1em 1em 0.1em 1em;
	}
div.info table th a.sortable { color:black; }
div.info table tr.tr-0 { background:rgb(238,238,238); }
div.info table tr.tr-1 { background:rgb(221,221,221); }
div.info table td { padding:0.3em 1em 0.3em 1em; }
div.info table td.td-0 { border-right:solid rgb(102,102,153) 1px; white-space:nowrap; }
div.info table td.td-n { border-right:solid rgb(102,102,153) 1px; }
div.info table td h3 {
	color:black;
	font-size:1.1em;
	margin-left:-0.3em;
	}
.td-0 a , .td-n a, .tr-0 a , tr-1 a {
    text-decoration:underline;
}
div.graph { margin-bottom:1em }
div.graph h2 { background:rgb(204,204,204);; color:black; font-size:1em; margin:0; padding:0.1em 1em 0.1em 1em; }
div.graph table { border:solid rgb(204,204,204) 1px; color:black; font-weight:normal; width:100%; }
div.graph table td.td-0 { background:rgb(238,238,238); }
div.graph table td.td-1 { background:rgb(221,221,221); }
div.graph table td { padding:0.2em 1em 0.4em 1em; }

div.div1,div.div2 { margin-bottom:1em; width:35em; }
div.div3 { position:absolute; left:40em; top:1em; width:580px; }
//div.div3 { position:absolute; left:37em; top:1em; right:1em; }

div.sorting { margin:1.5em 0em 1.5em 2em }
.center { text-align:center }
.aright { position:absolute;right:1em }
.right { text-align:right }
.ok { color:rgb(0,200,0); font-weight:bold}
.failed { color:rgb(200,0,0); font-weight:bold}

span.box {
	border: black solid 1px;
	border-right:solid black 2px;
	border-bottom:solid black 2px;
	padding:0 0.5em 0 0.5em;
	margin-right:1em;
}
span.green { background:#60F060; padding:0 0.5em 0 0.5em}
span.red { background:#D06030; padding:0 0.5em 0 0.5em }

div.authneeded {
	background:rgb(238,238,238);
	border:solid rgb(204,204,204) 1px;
	color:rgb(200,0,0);
	font-size:1.2em;
	font-weight:bold;
	padding:2em;
	text-align:center;
	}

input {
	background:rgb(153,153,204);
	border:solid rgb(102,102,153) 2px;
	color:white;
	font-weight:bold;
	margin-right:1em;
	padding:0.1em 0.5em 0.1em 0.5em;
	}
//-->
</style>
</head>
<body>
<div class="head">
	<h1 class="memcache">
		<span class="logo"><a href="http://{$host}">������</a></span>
	</h1>
	<hr class="memcache">
</div>
<div class=content>
EOB;

    return $header;
}
function getFooter(){
    global $VERSION;
    $footer = '</div><!-- Based on apc.php '.$VERSION.'--></body>
</html>
';

    return $footer;

}

function getMenu() {
    global $PHP_SELF;
    echo "<ol class=menu>";
    if ($_GET['op'] != 4) {
        echo "<li><a href=\"$PHP_SELF&op={$_GET['op']}\">Обновить данные</a></li>";
    } else {
        echo "<li><a href=\"$PHP_SELF&op=2\">Назад</a></li>";
    }
    echo menu_entry(1, 'Статистика хоста'), menu_entry(2, 'Переменные');
    echo "</ol><br/>";
}

// Sanitize and globals
$_GET['op'] = $_GET['op'] ?? '1';
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? htmlentities(strip_tags($_SERVER['PHP_SELF'])) : '';
$PHP_SELF = $PHP_SELF . '?';
$time = time();
foreach ($_GET as $key => $g) {
    $_GET[$key] = htmlentities($g);
}

// Singleout
if (isset($_GET['singleout']) && is_numeric($_GET['singleout']) && $_GET['singleout'] >= 0 && $_GET['singleout'] < count($MEMCACHE_SERVERS)) {
    $MEMCACHE_SERVERS = [ $MEMCACHE_SERVERS[$_GET['singleout']] ];
}

// Memcached connection helpers
function memc_connect(string $server): ?Memcached {
    static $connections = [];
    if (isset($connections[$server])) {
        return $connections[$server];
    }
    [$host, $port] = explode(':', $server);
    $memc = new Memcached();
    $memc->addServer($host, (int)$port);
    // Test connection
    $stats = $memc->getStats();
    if (!isset($stats["$host:$port"]) || $stats["$host:$port"] === false) {
        return null;
    }
    $connections[$server] = $memc;
    return $memc;
}

function getMemcacheStats(bool $total = true) {
    global $MEMCACHE_SERVERS;
    $allStats = [];
    foreach ($MEMCACHE_SERVERS as $server) {
        $memc = memc_connect($server);
        if ($memc === null) continue;
        $stats = $memc->getStats();
        $allStats[$server] = $stats[$server] ?? [];
    }
    if (!$total) {
        // Return per-server array
        $result = [];
        foreach ($MEMCACHE_SERVERS as $server) {
            $memc = memc_connect($server);
            if ($memc === null) continue;
            $stats = $memc->getStats();
            $result[$server]['STAT'] = $stats[$server] ?? [];
        }
        return $result;
    } else {
        // Aggregate stats
        $sum = [];
        foreach ($allStats as $server => $row) {
            foreach ($row as $key => $val) {
                if (is_numeric($val)) {
                    if (!isset($sum[$key])) $sum[$key] = 0;
                    $sum[$key] += $val;
                } else {
                    $sum[$key][$server] = $val;
                }
            }
        }
        return $sum;
    }
}

function getCacheItems() {
    global $MEMCACHE_SERVERS;
    $serverItems = [];
    $totalItems = [];
    foreach ($MEMCACHE_SERVERS as $server) {
        $memc = memc_connect($server);
        if ($memc === null) continue;
        $itemsStats = $memc->getStats('items');
        $serverItems[$server] = [];
        $totalItems[$server] = 0;
        if (!isset($itemsStats[$server])) continue;
        foreach ($itemsStats[$server] as $key => $value) {
            if (preg_match('/items\:(\d+)\:(.+)/', $key, $m)) {
                $slabId = $m[1];
                $itemKey = $m[2];
                $serverItems[$server][$slabId][$itemKey] = $value;
                if ($itemKey === 'number') {
                    $totalItems[$server] += $value;
                }
            }
        }
    }
    return ['items' => $serverItems, 'counts' => $totalItems];
}

function dumpCacheSlab($server, $slabId, $limit) {
    $memc = memc_connect($server);
    if ($memc === null) return [];
    // Use getStats('cachedump', $slabId, $limit)
    $dump = $memc->getStats('cachedump', (int)$slabId, (int)$limit);
    $result = [];
    if (isset($dump[$server]) && is_array($dump[$server])) {
        $result = $dump[$server];
    }
    return $result;
}

function flushServer($server) {
    $memc = memc_connect($server);
    if ($memc === null) return false;
    return $memc->flush();
}

// HTML output
echo getHeader();
getMenu();

switch ($_GET['op']) {
    case 1: // host stats
        $phpversion = phpversion();
        $memcacheStats = getMemcacheStats();
        $memcacheStatsSingle = getMemcacheStats(false);

        $mem_size = $memcacheStats['limit_maxbytes'] ?? 0;
        $mem_used = $memcacheStats['bytes'] ?? 0;
        $mem_avail = $mem_size - $mem_used;
        $startTime = $time - (is_array($memcacheStats['uptime'] ?? null) ? array_sum($memcacheStats['uptime']) : ($memcacheStats['uptime'] ?? 0));

        $curr_items = $memcacheStats['curr_items'] ?? 0;
        $total_items = $memcacheStats['total_items'] ?? 0;
        $hits = ($memcacheStats['get_hits'] ?? 0);
        $misses = ($memcacheStats['get_misses'] ?? 0);
        $sets = $memcacheStats['cmd_set'] ?? 0;
        $hits = $hits == 0 ? 1 : $hits;
        $misses = $misses == 0 ? 1 : $misses;

        $req_rate = ($time - $startTime) > 0 ? sprintf("%.2f", ($hits + $misses) / ($time - $startTime)) : '0';
        $hit_rate = ($time - $startTime) > 0 ? sprintf("%.2f", $hits / ($time - $startTime)) : '0';
        $miss_rate = ($time - $startTime) > 0 ? sprintf("%.2f", $misses / ($time - $startTime)) : '0';
        $set_rate = ($time - $startTime) > 0 ? sprintf("%.2f", $sets / ($time - $startTime)) : '0';
?>
<div class="info div1"><h2>Общая информация о кэше</h2>
<table cellspacing=0><tbody>
<tr class=tr-1><td class=td-0>Версия PHP</td><td><?= $phpversion ?></td></tr>
<tr class=tr-0><td class=td-0>Сервер<?= (count($MEMCACHE_SERVERS) > 1 ? 'ы Memcached' : ' Memcached') ?></td><td>
<?php
    $i = 0;
    if (!isset($_GET['singleout']) && count($MEMCACHE_SERVERS) > 1) {
        foreach ($MEMCACHE_SERVERS as $server) {
            echo ($i + 1) . '. <a href="' . $PHP_SELF . '&singleout=' . $i++ . '">' . $server . '</a><br/>';
        }
    } else {
        echo '1.' . $MEMCACHE_SERVERS[0];
    }
    if (isset($_GET['singleout'])) {
        echo '<a href="' . $PHP_SELF . '">(все серверы)</a><br/>';
    }
?>
</td></tr>
<tr class=tr-1><td class=td-0>Всего доступно</td><td><?= bsize($mem_size) ?></td></tr>
</tbody></table>
</div>

<div class="info div1"><h2>Информация о сервере Memcached</h2>
<?php
foreach ($MEMCACHE_SERVERS as $server) {
    $stats = $memcacheStatsSingle[$server]['STAT'] ?? [];
    echo '<table cellspacing=0><tbody>';
    echo '<tr class=tr-1><td class=td-1>' . $server . '</td><td><a href="' . $PHP_SELF . '&server=' . array_search($server, $MEMCACHE_SERVERS) . '&op=6">[<b>Очистить сервер</b>]</a></td></tr>';
    $startTime = isset($stats['time']) && isset($stats['uptime']) ? $stats['time'] - $stats['uptime'] : 0;
    echo '<tr class=tr-0><td class=td-0>Время запуска</td><td>', date(DATE_FORMAT, $startTime), '</td></tr>';
    echo '<tr class=tr-1><td class=td-0>Время работы</td><td>', duration($startTime), '</td></tr>';
    echo '<tr class=tr-0><td class=td-0>Версия Memcached</td><td>' . ($stats['version'] ?? '') . '</td></tr>';
    echo '<tr class=tr-1><td class=td-0>Использовано</td><td>', bsize($stats['bytes'] ?? 0), '</td></tr>';
    echo '<tr class=tr-0><td class=td-0>Всего доступно</td><td>', bsize($stats['limit_maxbytes'] ?? 0), '</td></tr>';
    echo '</tbody></table>';
}
?>
</div>
<div class="graph div3"><h2>Диаграммы состояния сервера</h2>
<table cellspacing=0><tbody>
    <tr>
        <td class=td-0>Использование кэша</td>
        <td class=td-1>Попадания и промахи</td>
    </tr>
    <tr>
        <td class=td-0>
            <svg width="<?= GRAPH_SIZE + 50 ?>" height="<?= GRAPH_SIZE + 10 ?>">
                <?php
                // Pie chart: usage
                $size = GRAPH_SIZE;
                $cx = ($size + 50) / 2;
                $cy = ($size + 10) / 2;
                $r = min($size / 2, $size / 2);
                $usedAngle = ($mem_used > 0 && $mem_size > 0) ? ($mem_used / $mem_size) * 360 : 0;
                $freeAngle = 360 - $usedAngle;
                // Draw used (red)
                $x1 = $cx + $r * cos(deg2rad(-90));
                $y1 = $cy + $r * sin(deg2rad(-90));
                $x2 = $cx + $r * cos(deg2rad($usedAngle - 90));
                $y2 = $cy + $r * sin(deg2rad($usedAngle - 90));
                ?>
                <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="#60F060" stroke="#000" stroke-width="2"/>
                <?php if ($usedAngle > 0): ?>
                <path d="M<?= $cx ?>,<?= $cy ?> L<?= $x1 ?>,<?= $y1 ?> A<?= $r ?>,<?= $r ?> 0 <?= ($usedAngle > 180 ? 1 : 0) ?>,1 <?= $x2 ?>,<?= $y2 ?> Z"
                      fill="#D06030" stroke="#000" stroke-width="2"/>
                <?php endif; ?>
            </svg>
        </td>
        <td class=td-1>
            <svg width="<?= GRAPH_SIZE + 50 ?>" height="<?= GRAPH_SIZE + 10 ?>">
                <?php
                // Bar chart: hits/misses
                $total = $hits + $misses;
                $barW = 50;
                $barMaxH = $size - 21;
                $hitsH = $total ? ($hits * $barMaxH / $total) : 0;
                $missesH = $total ? ($misses * $barMaxH / $total) : 0;
                ?>
                <rect x="30" y="<?= $size - $hitsH ?>" width="<?= $barW ?>" height="<?= $hitsH ?>" fill="#60F060" stroke="#000"/>
                <rect x="130" y="<?= $size - $missesH ?>" width="<?= $barW ?>" height="<?= $missesH ?>" fill="#D06030" stroke="#000"/>
            </svg>
        </td>
    </tr>
    <tr>
        <td class=td-0><span class="green box">&nbsp;</span>Свободно: <?= bsize($mem_avail) . sprintf(" (%.1f%%)", $mem_size > 0 ? $mem_avail * 100 / $mem_size : 0) ?></td>
        <td class=td-1><span class="green box">&nbsp;</span>Попадания: <?= $hits . sprintf(" (%.1f%%)", $total > 0 ? $hits * 100 / $total : 0) ?></td>
    </tr>
    <tr>
        <td class=td-0><span class="red box">&nbsp;</span>Использовано: <?= bsize($mem_used) . sprintf(" (%.1f%%)", $mem_size > 0 ? $mem_used * 100 / $mem_size : 0) ?></td>
        <td class=td-1><span class="red box">&nbsp;</span>Промахи: <?= $misses . sprintf(" (%.1f%%)", $total > 0 ? $misses * 100 / $total : 0) ?></td>
    </tr>
</tbody></table>
<br/>
<div class="info"><h2>Информация о кэше</h2>
    <table cellspacing=0><tbody>
    <tr class=tr-0><td class=td-0>Объекты (всего)</td><td><?= $curr_items ?> (<?= $total_items ?>)</td></tr>
    <tr class=tr-1><td class=td-0>Попадания</td><td><?= $hits ?></td></tr>
    <tr class=tr-0><td class=td-0>Промахи</td><td><?= $misses ?></td></tr>
    <tr class=tr-1><td class=td-0>Частота запросов (попадания, промахи)</td><td><?= $req_rate ?> запросов/сек</td></tr>
    <tr class=tr-0><td class=td-0>Частота попаданий</td><td><?= $hit_rate ?> запросов/сек</td></tr>
    <tr class=tr-1><td class=td-0>Частота промахов</td><td><?= $miss_rate ?> запросов/сек</td></tr>
    <tr class=tr-0><td class=td-0>Частота сохранений</td><td><?= $set_rate ?> запросов/сек</td></tr>
    </tbody></table>
</div>
<?php
        break;
    case 2: // variables
        $m = 0;
        $cacheItems = getCacheItems();
        $items = $cacheItems['items'];
        $totals = $cacheItems['counts'];
        $maxDump = MAX_ITEM_DUMP;
        foreach ($items as $server => $entries) {
?>
        <div class="info"><table cellspacing=0><tbody>
        <tr><th colspan="2"><?= $server ?></th></tr>
        <tr><th>Номер блока</th><th>Информация</th></tr>
<?php
            foreach ($entries as $slabId => $slab) {
                $dumpUrl = $PHP_SELF . '&op=2&server=' . (array_search($server, $MEMCACHE_SERVERS)) . '&dumpslab=' . $slabId;
                echo "<tr class=tr-$m>";
                echo "<td class=td-0><center><a href=\"$dumpUrl\">$slabId</a></center></td>";
                echo "<td class=td-last><b>Количество объектов:</b> " . ($slab['number'] ?? 0) .
                    '<br/><b>Возраст:</b>' . duration($time - ($slab['age'] ?? 0)) .
                    '<br/> <b>Удалено:</b>' . ((isset($slab['evicted']) && $slab['evicted'] == 1) ? 'Да' : 'Нет');
                if ((isset($_GET['dumpslab']) && $_GET['dumpslab'] == $slabId) && (isset($_GET['server']) && $_GET['server'] == array_search($server, $MEMCACHE_SERVERS))) {
                    echo "<br/><b>Объекты:</b><br/>";
                    $dump = dumpCacheSlab($server, $slabId, $slab['number'] ?? $maxDump);
                    $i = 1;
                    foreach ($dump as $itemKey => $itemInfo) {
                        echo '<a href="' . $PHP_SELF . '&op=4&server=' . (array_search($server, $MEMCACHE_SERVERS)) . '&key=' . base64_encode($itemKey) . '">' . $itemKey . '</a>';
                        if ($i++ % 10 == 0) {
                            echo '<br/>';
                        } elseif ($i != (($slab['number'] ?? $maxDump) + 1)) {
                            echo ',';
                        }
                    }
                }
                echo "</td></tr>";
                $m = 1 - $m;
            }
?>
        </tbody></table>
        </div><hr/>
<?php
        }
        break;
    case 4: // item dump
        if (!isset($_GET['key']) || !isset($_GET['server'])) {
            echo "No key set!";
            break;
        }
        $theKey = base64_decode($_GET['key']);
        $theserver = $MEMCACHE_SERVERS[(int)$_GET['server']];
        $memc = memc_connect($theserver);
        $value = $memc ? $memc->get($theKey) : null;
        $stats = $memc ? $memc->getStats() : [];
        $size = is_string($value) ? strlen($value) : 0;
?>
        <div class="info"><table cellspacing=0><tbody>
            <tr><th>Сервер</th><th>Ключ</th><th>Значение</th><th>Удалить</th></tr>
            <tr>
                <td class=td-0><?= $theserver ?></td>
                <td class=td-0><?= htmlentities($theKey) ?><br/>Размер: <?= bsize($size) ?></td>
                <td><?= is_string($value) ? nl2br(htmlentities(chunk_split($value, 40))) : var_export($value, true) ?></td>
                <td><a href="<?= $PHP_SELF ?>&op=5&server=<?= (int)$_GET['server'] ?>&key=<?= base64_encode($theKey) ?>">Удалить</a></td>
            </tr>
        </tbody></table>
        </div><hr/>
<?php
        break;
    case 5: // item delete
        if (!isset($_GET['key']) || !isset($_GET['server'])) {
            echo "No key set!";
            break;
        }
        $theKey = base64_decode($_GET['key']);
        $theserver = $MEMCACHE_SERVERS[(int)$_GET['server']];
        $memc = memc_connect($theserver);
        $r = $memc ? $memc->delete($theKey) : false;
        echo 'Deleting ' . htmlentities($theKey) . ': ' . ($r ? 'OK' : 'NOT_FOUND');
        break;
    case 6: // flush server
        $theserver = $MEMCACHE_SERVERS[(int)$_GET['server']];
        $r = flushServer($theserver);
        echo 'Flush ' . $theserver . ": " . ($r ? 'OK' : 'FAILED');
        break;
}
echo getFooter();