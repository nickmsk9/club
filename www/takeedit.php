<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die("Direct access to this file not allowed."); 

require_once("include/benc.php");
require_once("include/bittorrent.php");
global $mcache, $mysqli, $max_torrent_size, $DEFAULTBASEURL, $SITENAME, $CURUSER;
// Инициализация Memcached
if (!isset($mcache) || !($mcache instanceof Memcached)) {
    $mcache = new Memcached();
    $mcache->addServer('localhost', 11211);
}

function bark($msg) {
    stderr("Ошибка", $msg);
}

function dict_check($d, $s) {
    if ($d["type"] != "dictionary") bark("not a dictionary");
    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        }
        if (!isset($dd[$k])) bark("dictionary is missing key(s)");
        if (isset($t)) {
            if ($dd[$k]["type"] != $t) bark("invalid entry in dictionary");
            $ret[] = $dd[$k]["value"];
        } else {
            $ret[] = $dd[$k];
        }
    }
    return $ret;
}

function dict_get($d, $k, $t) {
    if ($d["type"] != "dictionary") bark("not a dictionary");
    $dd = $d["value"];
    if (!isset($dd[$k])) return;
    $v = $dd[$k];
    if ($v["type"] != $t) bark("invalid dictionary entry type");
    return $v["value"];
}

if (!mkglobal("id:name:descr:type"))
    bark("missing form data");

$id = (int)$id;
$type = (int)$type; // <-- ВАЖНО: добавлено!
if (!$id) die();

dbconn();
loggedinorreturn();

// Используем mysqli
$res = $mysqli->query("SELECT owner, filename, save_as, image1, screen1, screen2, screen3, category FROM torrents WHERE id = " . (int)$id);
if (!$res) bark("Ошибка БД: " . $mysqli->error);
$row = $res->fetch_assoc();
if (!$row) die();

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)
    bark("Вы не владелец!");

$updateset = array();
$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1] ?? '';
$dname = $row["save_as"];

if (isset($_FILES["tfile"]) && !empty($_FILES["tfile"]["name"])) {
    $f = $_FILES["tfile"];
    $fname = $_FILES['tfile']['name'];
$ru = array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я","А","Б","В","Г","Д","Е","Ё","Ж","З","И","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я"," ");
$en = array("a","b","v","g","d","e","e","g","z","i","i","k","l","m","n","o","p","r","s","t","u","f","h","c","ch","sh","sh","","","","e","yu","ya","A","B","V","G","D","E","E","G","Z","I","K","L","M","N","O","P","R","S","T","U","F","H","C","CH","SH","SH","","","","E","YU","YA","_");

    $fname = htmlspecialchars(str_replace($ru, $en, $fname));

    if (empty($fname)) bark("Файл не загружен. Пустое имя!");
    if (!validfilename($fname)) bark("Неверное имя!");
    if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) bark("Файл не .torrent");

    $tmpname = $f["tmp_name"];
    if (!is_uploaded_file($tmpname)) bark("Не загружен!");
    if (!filesize($tmpname)) bark("Пустой файл!");

    $dict = bdec_file($tmpname, $max_torrent_size);
    if (!isset($dict)) bark("Файл не бинарный!");

    list($info) = dict_check($dict, "info");
    list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");
    if (strlen($pieces) % 20 != 0) bark("Неверные pieces");

    $filelist = array();
    $totallen = dict_get($info, "length", "integer");
    if (isset($totallen)) {
        $filelist[] = array($dname, $totallen);
    } else {
        $flist = dict_get($info, "files", "list");
        if (!isset($flist)) bark("missing files");
        if (!count($flist)) bark("no files");

        $totallen = 0;
        foreach ($flist as $fn) {
            list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
            $totallen += $ll;
            $ffa = array();
            foreach ($ff as $ffe) {
                if ($ffe["type"] != "string") bark("ошибка имени файла");
                $ffa[] = $ffe["value"];
            }
            $ffe = implode("/", $ffa);
            if ($ffe === 'Thumbs.db') bark("Файл Thumbs.db запрещён!");
            $filelist[] = array($ffe, $ll);
        }
    }

    $dict['value']['announce'] = bdec(benc_str($announce_urls[0]));
    $dict['value']['info']['value']['source'] = bdec(benc_str("[$DEFAULTBASEURL] $SITENAME"));
    unset($dict['value']['announce-list'], $dict['value']['nodes']);
    unset($dict['value']['info']['value']['crc32'], $dict['value']['info']['value']['ed2k']);
    unset($dict['value']['info']['value']['md5sum'], $dict['value']['info']['value']['sha1']);
    unset($dict['value']['info']['value']['tiger'], $dict['value']['azureus_properties']);
    unset($dict['value']['info']['value']['private']);

    $dict = bdec(benc($dict));
    $dict['value']['comment'] = bdec(benc_str("Торрент создан для '$SITENAME'"));
    $dict['value']['created by'] = bdec(benc_str($CURUSER['username']));
    $dict['value']['publisher'] = bdec(benc_str($CURUSER['username']));
    $dict['value']['publisher.utf-8'] = bdec(benc_str($CURUSER['username']));
    $dict['value']['publisher-url'] = bdec(benc_str("$DEFAULTBASEURL/user/id$CURUSER[id]"));
    $dict['value']['publisher-url.utf-8'] = bdec(benc_str("$DEFAULTBASEURL/user/id$CURUSER[id]"));

    list($info) = dict_check($dict, "info");
    $infohash = sha1($info["string"]);

    move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

    if ($fp = fopen("$torrent_dir/$id.torrent", "w")) {
        fwrite($fp, benc($dict));
        fclose($fp);
    }

    $updateset[] = "info_hash = " . sqlesc($infohash);
    $updateset[] = "filename = " . sqlesc($fname);
    $updateset[] = "save_as = " . sqlesc($dname);
    $updateset[] = "size = " . sqlesc($totallen);

    $mysqli->query("DELETE FROM files WHERE torrent = " . (int)$id) or bark("Ошибка БД: " . $mysqli->error);
    $mysqli->query("UPDATE torrents SET added = " . time() . " WHERE id = " . (int)$id) or bark("Ошибка БД: " . $mysqli->error);

    foreach ($filelist as $file) {
        $file0_esc = $mysqli->real_escape_string($file[0]);
        $mysqli->query("INSERT INTO files (torrent, filename, size) VALUES (" . (int)$id . ", '" . $file0_esc . "', " . (int)$file[1] . ")") or bark("Ошибка БД: " . $mysqli->error);
    }
}

// Теги
$name = htmlspecialchars($name);
$replace = array(", ", " , ", " ,");
$tags = trim(str_replace($replace, ",", mb_convert_case(unesc($_POST["tags"]), MB_CASE_LOWER, $mysql_charset)));
$oldtags = unesc($_POST["oldtags"]);

$un = array_diff(explode(",", $tags), explode(",", $oldtags));
$un2 = array_diff(explode(",", $oldtags), explode(",", $tags));

$ret = array();
$type_esc = $mysqli->real_escape_string($type);
$res = $mysqli->query("SELECT name FROM tags WHERE category = '$type_esc'") or bark("Ошибка БД: " . $mysqli->error);
while ($row = $res->fetch_assoc())
    $ret[] = $row["name"];

$union = array_intersect($ret, $un);
$ununion = array_diff($un, $ret);

foreach ($union as $tag) {
    $tag_esc = $mysqli->real_escape_string($tag);
    $mysqli->query("UPDATE tags SET howmuch=howmuch+1 WHERE name LIKE CONCAT('%', '" . $tag_esc . "', '%')") or bark("Ошибка БД: " . $mysqli->error);
}
foreach ($un2 as $tag) {
    $tag_esc = $mysqli->real_escape_string($tag);
    $mysqli->query("UPDATE tags SET howmuch=howmuch-1 WHERE name LIKE CONCAT('%', '" . $tag_esc . "', '%')") or bark("Ошибка БД: " . $mysqli->error);
}
foreach ($ununion as $tag) {
    $type_esc = $mysqli->real_escape_string($type);
    $tag_esc = $mysqli->real_escape_string($tag);
    $mysqli->query("INSERT INTO tags (category, name, howmuch) VALUES ('$type_esc', '$tag_esc', 1)") or bark("Ошибка БД: " . $mysqli->error);
}

$descr = unesc($_POST["descr"]);
if (!$descr) bark("Вы должны ввести описание!");

$updateset[] = "name = " . sqlesc($name);
$updateset[] = "tags = " . sqlesc($tags);
$updateset[] = "screen3 = " . sqlesc($_POST['screen3']);
$updateset[] = "screen2 = " . sqlesc($_POST['screen2']);
$updateset[] = "screen1 = " . sqlesc($_POST['screen1']);
$updateset[] = "image1 = " . sqlesc($_POST['image1']);
$updateset[] = "search_text = " . sqlesc(htmlspecialchars("$shortfname $dname"));
$updateset[] = "descr = " . sqlesc($descr);
$updateset[] = "ori_descr = " . sqlesc($descr);
$updateset[] = "category = $type";

if (get_user_class() >= UC_ADMINISTRATOR && $_POST["update"]) {
    $updateset[] = "added = " . sqlesc(time());
}
$updateset[] = "banned = '" . (isset($_POST['banned']) && $_POST['banned'] ? 'yes' : 'no') . "'";
$updateset[] = "visible = '" . ($_POST["visible"] ? "yes" : "no") . "'";

// Финальный запрос
$mysqli->query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = " . (int)$id) or bark("Ошибка БД: " . $mysqli->error);

$mcache->delete('torrent_' . $id);
$mcache->delete('torrent_desc' . $id);

write_log("Торрент '" . htmlspecialchars($name) . "' был отредактирован пользователем " . htmlspecialchars($CURUSER['username']), "f99c57", "torrent");

$returl = "details/id" . htmlspecialchars($id);
if (isset($_POST["returnto"]))
    $returl .= "&returnto=" . urlencode($_POST["returnto"]);

header("Refresh: 0; url=$returl");
exit;
?>