<?php
declare(strict_types=1);
define ('IN_ANNOUNCE', true);
require_once('./include/functions_announce.php');
global $mysqli;
require_once('./include/secrets.php'); // если $mysqli не создаётся в другом месте

global $gzip , $auto_enter_cheater;
gzip();

foreach (array('passkey','info_hash','peer_id','event','ip','localip','ipv6') as $x) {
    if(isset($_GET[$x]))
        $GLOBALS[$x] = '' . $_GET[$x];
}

// Проверка и инициализация числовых параметров
foreach (array('port','downloaded','uploaded','left') as $x) {
    if (isset($_GET[$x])) {
        $GLOBALS[$x] = (float) $_GET[$x];
    } else {
        err('Missing key: '.$x);
    }
}

// Удалить get_magic_quotes_gpc() - устаревшее

// Проверка наличия ключей
foreach (array('passkey','info_hash','peer_id','port','downloaded','uploaded','left') as $x)
    if (!isset($GLOBALS[$x])) err('Missing key: '.$x);
// Validate fixed-length parameters
foreach (['info_hash','peer_id'] as $x) {
    if (strlen($$x) !== 20) {
        err('Invalid ' . $x . ' length: ' . strlen($$x));
    }
}
if (strlen($passkey) !== 32) {
    err('Invalid passkey! Re-download the .torrent from http://' . $_SERVER['HTTP_HOST']);
}
// IP
$ip = getip();

// rsize ограничение
$rsize = 50;
foreach(array('num want', 'numwant', 'num_want') as $k) {
    if (isset($_GET[$k])) {
        $rsize = max(0, min(200, (int)($_GET[$k])));
        break;
    }
}

// Кэшируем User-Agent из заголовков, если возможно
if (function_exists('getallheaders'))
    $headers = getallheaders();
else
    $headers = emu_getallheaders();
$agent = isset($headers['User-Agent']) ? $headers['User-Agent'] : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

if (!$port || $port > 0xffff)
    err("Invalid port");
if (!isset($event))
    $event = '';
$seeder = ($left == 0) ? 'yes' : 'no';

if (isset($headers['Cookie']) || isset($headers['Accept-Language']) || isset($headers['Accept-Charset']))
    err('Anti-Cheater: You cannot use this agent ('.$agent.')');
//if($agent == "uTorrent/2040(21515)")err("uTorrent 2.04(21515) забанен . Пожалуйсто обновите Ваш торрент клиент .");  
//if(substr($peer_id, 1, 2) == 'UT' && substr($peer_id, 3, 3) >= 204) err("uTorrent 2.04 is banned. Downgrade to 2.02.");
if(substr($peer_id, 0, 4) == "FUTB") err("FUTB? Fuck You Too."); //patched version of BitComet 0.57 (FUTB- Fuck U TorrentBits)
if(substr($peer_id, 0, 3) == "-AZ") err("Azureus is banned.");
if(substr($peer_id, 0, 3) == "-TS") err("TorrentStorm is Banned.");
if(substr($peer_id, 0, 5) == "Mbrst") err("Burst! is Banned.");
if(substr($peer_id, 0, 3) == "-BB") err("BitBuddy is Banned.");
if(substr($peer_id, 0, 3) == "-SZ") err("Shareaza is Banned.");
if(substr($peer_id, 0, 5) == "turbo") err("TurboBT is banned.");
if(substr($peer_id, 0, 4) == "T03A") err("Please Update your BitTornado.");
if(substr($peer_id, 0, 4) == "T03B") err("Please Update your BitTornado.");
if(substr($peer_id, 0, 3 ) == "FRS") err("Rufus is Banned.");
if(substr($peer_id, 0, 2 ) == "eX") err("eXeem is Banned.");
if(substr($peer_id, 0, 8 ) == "-TR0005-") err("Transmission/0.5 is Banned.");
if(substr($peer_id, 0, 8 ) == "-TR0006-") err("Transmission/0.6 is Banned.");
if(substr($peer_id, 0, 8 ) == "-XX0025-") err("Transmission/0.6 is Banned.");
if(substr($peer_id, 0, 1 ) == ",") err ("RAZA is banned.");
if(substr($peer_id, 0, 3 ) == "-AG") err("This is a banned client. We recommend uTorrent or Azureus.");
if(substr($peer_id, 0, 3 ) == "R34") err("BTuga/Revolution-3.4 is not an acceptalbe client. Please read the FAQ on recommended clients.");
if(substr($peer_id, 0, 4) == "exbc") err("This version of BitComet is banned! You can thank DHT for this ban!");
if (substr($peer_id, 0, 3) == '-FG') err("FlashGet is banned!");

dbconn();

$memcache_obj = new Memcache;
$memcache_obj->connect('127.0.0.1', 11211);

// Получаем пользователя по passkey
// $annuserz = $memcache_obj->get('users_p_'.$passkey);
// if (false === $annuserz) { ... }
$escaped_passkey = $mysqli->real_escape_string($passkey);
$annres = $mysqli->query('SELECT SQL_NO_CACHE id, uploaded, downloaded, enabled, passkey, class, parked, passkey_ip FROM users WHERE passkey="'.$escaped_passkey.'" LIMIT 1') or err('Tracker error 2');
$annuserz = $annres->fetch_assoc();
// $memcache_obj->set('users_p_'.$passkey, $annuserz, 0, rand(1000, 1800));
$GLOBALS["ANNUSER"] = $annuserz;
global $ANNUSER;

if ($ANNUSER['passkey'] == '')
    err('Invalid passkey! Re-download the .torrent from '.$DEFAULTBASEURL);

$hash = $mysqli->real_escape_string(bin2hex($info_hash));
if(false === ($torrent = $memcache_obj->get('torrent_'.$hash))) {
    $res = $mysqli->query('SELECT id, visible, banned FROM torrents WHERE info_hash = "'.$hash.'"') or err($mysqli->error);
    $torrent = $res->fetch_assoc();
    $memcache_obj->set('torrent_'.$hash, $torrent, 0, rand(600, 1200));
}

if (empty($torrent['id']) || $torrent['id'] == 0)
    err('Torrent not registered with this tracker.');

$torrentid = $torrent["id"];
$fields = 'seeder, peer_id, ip, port, uploaded, downloaded, userid, last_action,'.time().' AS nowts, prev_action AS prevts';
$whereap = array();
if ($seeder == 'yes')
    $whereap[] = 'seeder = \'no\'';
$wherep = count($whereap) ? ' AND '.implode(' AND ', $whereap) : '';
$res = $mysqli->query('SELECT '.$fields.' FROM peers WHERE torrent = '.$torrentid.$wherep) or err($mysqli->error);
$compact = (isset($_GET['compact']) && $_GET['compact'] == 1);
$no_peer_id = (isset($_GET['no_peer_id']) && $_GET['no_peer_id'] == 1);
$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("min interval") . "i" . 300 ."e5:"."peers" ;

unset($self);
$plist = '';
while ($row = $res->fetch_assoc()) {
    if ($row['peer_id'] == $peer_id) {
        $userid = $row['userid'];
        $self = $row;
        continue;
    }
    if ($compact) {
        $peer_ip = ip2long($row['ip']);
        $plist .= pack('Nn', $peer_ip, $row['port']);
    } else {
        $resp .= 'd' .
            benc_str('ip') . benc_str($row['ip']) .
            (!$no_peer_id ? benc_str("peer id") . benc_str($row["peer_id"]) : '') .
            benc_str('port') . 'i' . $row['port'] . 'ee';
    }
}
$res->free();
$resp .= ($compact ? benc_str($plist) : '') . ("ee");
$selfwhere = 'torrent = '.$torrentid.' AND peer_id = '.sqlesc($peer_id);

if (!isset($self)) {
    $res = $mysqli->query('SELECT '.$fields.' FROM peers WHERE '.$selfwhere) or err($mysqli->error);
    $row = $res->fetch_assoc();
    if ($row) {
        $userid = $row["userid"];
        $self = $row;
    }
    $res->free();
}

// Минимальный интервал между анонсами
$announce_wait = 10;
if (isset($self) && ($self['prevts'] > ($self['nowts'] - $announce_wait )))
    err('There is a minimum announce time of ' . $announce_wait . ' seconds');

if (!isset($self)) {
    if ($ANNUSER['enabled'] == 'no')
        err('This account is disabled.');
    $userid = (int)$ANNUSER["id"];
    $passkey_ip = $ANNUSER["passkey_ip"];
    if ($passkey_ip != '' && getip() != $passkey_ip)
        err('Unauthorized IP for this passkey!');
    if ($ANNUSER["class"] < UC_VIP_P){
        $gigs = $ANNUSER['uploaded'] / (1024*1024*1024);
        $ratio = (($ANNUSER['downloaded'] > 0) ? ($ANNUSER['uploaded'] / $ANNUSER['downloaded']) : 1);
        if ($ratio < 0.25) $max = 0;
        elseif ($ratio < 0.5 || $gigs < 5) $max = 2;
        elseif ($ratio < 0.65 || $gigs < 6.5) $max = 3;
        elseif ($ratio < 0.8 || $gigs < 8) $max = 4;
        elseif ($ratio < 0.95 || $gigs < 9.5) $max = 5;
        elseif ($ratio <= 1.00 || $gigs < 12) $max = 6;
        elseif ($ratio > 1.1 || $gigs > 25) $max = 25;
        elseif ($ratio > 1.3 || $gigs > 100) $max = 100;
        else $max = 0;
        if ($max > 0) {
            $res = $mysqli->query("SELECT COUNT(*) AS num FROM peers WHERE userid='$userid' AND seeder='no'") or err("Tracker error 5");
            $row = $res->fetch_assoc();
            if ($row['num'] >= $max) err("Download Request Denied  - Torrent Limit ( $max Downloads ) Exceeded");
            $res->free();
        }
    }
} else {
    $userclass = $ANNUSER["class"];
    $upthis = max(0, $uploaded - $self["uploaded"]);
    $downthis = max(0, $downloaded - $self["downloaded"]);
    $free = ($userclass == UC_VIP_P) ? 100 : 0;
    if ($free > 0)
        $downthis = $downthis * ((100-$free)/100);
    if ($upthis > 0 || $downthis > 0)
        $mysqli->query('UPDATE users SET uploaded = uploaded + '.$upthis.', downloaded = downloaded + '.$downthis.' WHERE id='.$userid) or err('Tracker error 3');
    //=== abnormal upload detection
    if ($upthis > 2097152) {
        $diff = (time() - $self['last_action']);
        $rate = ($upthis / ($diff + 1));
        $last_up = $ANNUSER['uploaded'];
        if ($rate > 2097152) {
            auto_enter_cheater($ANNUSER['id'], $rate, $upthis, $diff, $torrentid, $agent, $ip, $last_up );
        }
    }
}

$dt = sqlesc(date('Y-m-d H:i:s', time()));
$updateset = array();
$snatch_updateset = array();

if ($event == 'stopped') {
    if (isset($self)) {
        $mysqli->query('UPDATE snatched SET seeder = "no", connectable = "no" WHERE torrent = '.$torrentid.' AND userid = '.$userid) or err($mysqli->error);
        $mysqli->query('DELETE FROM peers WHERE '.$selfwhere);
        if ($mysqli->affected_rows && $self['seeder'] == 'yes')
            $updateset[] = 'seeders = seeders - 1';
        elseif ($mysqli->affected_rows && $self['seeder'] != 'yes')
            $updateset[] = 'leechers = leechers - 1';
    }
} else {
    if ($event == 'completed') {
        $snatch_updateset[] = "finished = 'yes'";
        //$snatch_updateset[] = "completedat = $dt";
        $snatch_updateset[] = "seeder = 'yes'";
        $updateset[] = 'times_completed = times_completed + 1';
    }
    if (isset($self)) {
        $downloaded2 = max(0, $downloaded - $self['downloaded']);
        $uploaded2 = max(0, $uploaded - $self['uploaded']);
        if ($downloaded2 > 0 || $uploaded2 > 0) {
            $snatch_updateset[] = "uploaded = uploaded + $uploaded2";
            $snatch_updateset[] = "downloaded = downloaded + $downloaded2";
        }
        $snatch_updateset[] = "port = $port";
        $snatch_updateset[] = "seeder = '$seeder'";
        $prev_action = $self['last_action'];
        $mysqli->query("UPDATE peers SET uploaded = $uploaded, downloaded = $downloaded, uploadoffset = $uploaded2, downloadoffset = $downloaded2, to_go = $left, last_action = ".time().", prev_action = ".sqlesc($prev_action).", seeder = '$seeder'"
            . ($seeder == "yes" && $self["seeder"] != $seeder ? ", finishedat = " . time() : "") . ", agent = ".sqlesc($agent)." , ipv6 = ".sqlesc($GLOBALS['ipv6'])." WHERE $selfwhere") or err('Tracker error 666');
        if ($mysqli->affected_rows && $self['seeder'] != $seeder) {
            if ($seeder == 'yes') {
                $updateset[] = 'seeders = seeders + 1';
                $updateset[] = 'leechers = leechers - 1';
            } else {
                $updateset[] = 'seeders = seeders - 1';
                $updateset[] = 'leechers = leechers + 1';
            }
        }
    } else {
        if ($ANNUSER['parked'] == 'yes')
            err('Error, your account is parked!');
        if (portblacklisted($port))
            err('Port '.$port.' is blacklisted.');
        else {
            $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$sockres) {
                $connectable = 'no';
                if (isset($nc) && $nc == 'yes')
                    err('Your client is not connectable! Check your Port-configuration or search on forums.');
            }else {
                $connectable = 'yes';
                @fclose($sockres);
            }
        }
        $res = $mysqli->query('SELECT torrent, userid FROM snatched WHERE torrent = '.$torrentid.' AND userid = '.$userid) or err($mysqli->error);
        $check = $res->fetch_assoc();
        $res->free();
        if (!$check)
            $mysqli->query("INSERT INTO snatched (torrent, userid, port) VALUES ($torrentid, $userid, $port)");
        $ret = $mysqli->query("INSERT INTO peers (connectable, torrent, peer_id, ip, ipv6, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, agent, uploadoffset, downloadoffset, passkey) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ",".sqlesc($GLOBALS['ipv6']).", $port, $uploaded, $downloaded, $left, ".time().", ".time().", '$seeder', $userid, " . sqlesc($agent) . ", $uploaded, $downloaded, " . sqlesc($passkey) . ")");
        if ($ret) {
            if ($seeder == 'yes')
                $updateset[] = 'seeders = seeders + 1';
            else
                $updateset[] = 'leechers = leechers + 1';
        }
    }
}
if ($seeder == 'yes') {
    $updateset[] = 'last_action = '.time();
}
if (count($updateset) > 0)
    $mysqli->query('UPDATE torrents SET ' . join(", ", $updateset) . ' WHERE id = '.$torrentid);
if (count($snatch_updateset) > 0)
    $mysqli->query('UPDATE snatched SET ' . join(", ", $snatch_updateset) . ' WHERE torrent = '.$torrentid.' AND userid = '.$userid) or err($mysqli->error."Line: ".__LINE__);

benc_resp_raw($resp);
?>