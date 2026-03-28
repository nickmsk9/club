<?php

require_once("include/benc.php");
require_once("include/bittorrent.php");
global $max_torrent_size, $lang, $CURUSER;

ini_set("upload_max_filesize", $max_torrent_size);

function bark($msg) {
    global $lang;
    genbark($msg, $lang['error'] ?? 'Ошибка');
}

dbconn();
loggedinorreturn();
parked();

if (get_user_class() < UC_USER)
    die('Access denied');

foreach (explode(":", "descr:type:name") as $v) {
    if (!isset($_POST[$v]))
        bark("Не заполнены обязательные поля формы ($v)");
}

if (!isset($_FILES["tfile"]))
    bark("Файл не выбран для загрузки");

$name = $_POST['name'] ?? '';
$anonim = $_POST['anonim'] ?? '';
$image1 = $_POST['image1'] ?? '';
$image2 = $_POST['image2'] ?? '';
$image3 = $_POST['image3'] ?? '';
$tags = $_POST['tags'] ?? '';
$owner_name = $CURUSER['username'];
$owner_class = $CURUSER['class'];
$f = $_FILES["tfile"];
$fname = $_FILES['tfile']['name'];

// Транслитерация имени файла
$ru = array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я","А","Б","В","Г","Д","Е","Ё","Ж","З","И","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я"," ");
$en = array("a","b","v","g","d","e","e","g","z","i","i","k","l","m","n","o","p","r","s","t","u","f","h","c","ch","sh","sh","","","","e","yu","ya","A","B","V","G","D","E","E","G","Z","I","K","L","M","N","O","P","R","S","T","U","F","H","C","CH","SH","SH","","","","E","YU","YA","_");

$fname = htmlspecialchars(str_replace($ru, $en, $fname));

if (empty($fname))
    bark("Файл не загружен. Пустое имя файла!");

$descr = unesc($_POST["descr"]);
if (!$descr)
    bark("Вы должны ввести описание!");

$catid = (int) $_POST["type"];
if (!is_valid_id($catid))
    bark("Выберите корректную категорию!");

if (!validfilename($fname))
    bark("Неверное имя файла!");
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
    bark("Имя файла должно заканчиваться на .torrent");

$shortfname = $torrent = $matches[1];
if (!empty($name))
    $torrent = unesc($name);

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname))
    bark("Ошибка при загрузке файла");
if (!filesize($tmpname))
    bark("Файл пустой");

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
    bark("Файл не является .torrent");

function dict_check($d, $s) {
    if ($d["type"] != "dictionary")
        bark("not a dictionary");
    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        }
        if (!isset($dd[$k]))
            bark("dictionary is missing key(s)");
        if (isset($t)) {
            if ($dd[$k]["type"] != $t)
                bark("invalid entry in dictionary");
            $ret[] = $dd[$k]["value"];
        } else {
            $ret[] = $dd[$k];
        }
    }
    return $ret;
}

function dict_get($d, $k, $t) {
    if ($d["type"] != "dictionary")
        bark("not a dictionary");
    $dd = $d["value"];
    if (!isset($dd[$k]))
        return null;
    $v = $dd[$k];
    if ($v["type"] != $t)
        bark("invalid dictionary entry type");
    return $v["value"];
}

// Анонимная загрузка
if ($anonim === 'yes' && get_user_class() >= UC_PREUPLOADER) {
    $owner = 0;
    $oname = "Anonim";
} else {
    $owner = $CURUSER['id'];
    $oname = $CURUSER['username'];
}

list($info) = dict_check($dict, "info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if (strlen($pieces) % 20 != 0)
    bark("Неверный список хэшей");

$filelist = [];
$totallen = dict_get($info, "length", "integer");

if (isset($totallen)) {
    $filelist[] = [$dname, $totallen];
    $type = "single";
} else {
    $flist = dict_get($info, "files", "list");
    if (!is_array($flist) || !count($flist))
        bark("Файлы не найдены в торренте");

    $totallen = 0;
    foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;

        $pathParts = [];
        foreach ($ff as $ffe) {
            if ($ffe["type"] != "string")
                bark("Ошибка в имени файла");
            $pathParts[] = $ffe["value"];
        }

        $ffe = implode("/", $pathParts);
        if ($ffe === 'Thumbs.db')
            bark("В архиве не должно быть Thumbs.db");

        $filelist[] = [$ffe, $ll];
    }
    $type = "multi";
}

// Подготовка данных торрента
$dict['value']['announce'] = bdec(benc_str($announce_urls[0]));
$dict['value']['info']['value']['source'] = bdec(benc_str("[$DEFAULTBASEURL] $SITENAME"));
unset($dict['value']['announce-list'], $dict['value']['nodes']);
unset($dict['value']['info']['value']['crc32'], $dict['value']['info']['value']['ed2k']);
unset($dict['value']['info']['value']['md5sum'], $dict['value']['info']['value']['sha1']);
unset($dict['value']['info']['value']['tiger'], $dict['value']['azureus_properties']);
$dict = bdec(benc($dict));

$dict['value']['comment'] = bdec(benc_str("$DEFAULTBASEURL - $SITENAME"));
$dict['value']['created by'] = bdec(benc_str($oname));
$dict['value']['publisher'] = bdec(benc_str($oname));
$dict['value']['publisher.utf-8'] = bdec(benc_str($oname));
$dict['value']['publisher-url'] = bdec(benc_str("$DEFAULTBASEURL/user/id$owner"));
$dict['value']['publisher-url.utf-8'] = bdec(benc_str("$DEFAULTBASEURL/user/id$owner"));

list($info) = dict_check($dict, "info");
$infohash = sha1($info["string"]);

$torrent = htmlspecialchars(str_replace("_", " ", $torrent));
$added = $last_action = time();

// Подставим значения по умолчанию для всех потенциально пустых полей
$free = 0;
$modname = '';
$tags = '';

$ret = sql_query("INSERT INTO torrents 
(search_text, filename, owner, owner_name, owner_class, visible, info_hash, name, size, numfiles, type, descr, ori_descr, image1, category, save_as, added, last_action, screen1, screen2, screen3, free, modname, tags) 
VALUES (" . implode(",", array_map("sqlesc", array(
    searchfield("$shortfname $dname $torrent"),
    $fname,
    $owner,
    $owner_name,
    $owner_class,
    "no",
    $infohash,
    $torrent,
    $totallen,
    count($filelist),
    $type,
    $descr,
    $descr,
    $image1,
    $catid,
    $dname,
    $added,
    $last_action,
    $image1,
    $image2,
    $image3,
    $free,
    $modname,
    $tags
))) . ")");

if (!$ret) {
    $error = mysqli_error($GLOBALS['mysqli']);
    if (strpos($error, 'Duplicate') !== false)
        bark("Этот торрент уже был загружен!");
    bark("Ошибка базы данных: " . $error);
}

$id = mysqli_insert_id($GLOBALS['mysqli']);

sql_query("DELETE FROM files WHERE torrent = $id");
foreach ($filelist as $file) {
    sql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, " . sqlesc($file[0]) . ", " . $file[1] . ")");
}

move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");
@file_put_contents("$torrent_dir/$id.torrent", benc($dict));

if ($owner == 0) {
    write_log("Торрент #$id ($torrent) был загружен анонимно", "5DDB6E", "torrent");
} else {
    $iduser = $CURUSER["id"];
    $addusername = $CURUSER['username'];
    $link_touser = "[url={$DEFAULTBASEURL}/user/id{$iduser}]{$addusername}[/url]";
    write_log("Торрент #$id ($torrent) был загружен пользователем $link_touser", "5DDB6E", "torrent");
}

header("Location: {$DEFAULTBASEURL}/details/id{$id}");
exit;
?>