<?php
declare(strict_types=1);
require_once("include/bittorrent.php");

dbconn();
parked();
gzip();
loggedinorreturn();
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false)
{	// if output_handler = ob_gzhandler, turn it off and remove the header sent by PHP
	@ob_end_clean();
	header('Content-Encoding:');
}

/*if (!preg_match(':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches))
	httperr();*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    stderr($lang['error'], $lang['invalid_id']);
}
$site = $_SERVER['HTTP_HOST'];
$nameParam = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
if ($nameParam === null || $nameParam === false) {
    stderr($lang['error'], $lang['invalid_id']);
}
$name = str_replace('.torrent', "[$site].torrent", $nameParam);

/*$id = 0 + $matches[1];
if (!$id)
	httperr();*/

$res = sql_query("SELECT name,owner,category,modded FROM torrents WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
if (!$row)
	stderr($lang['error'], $lang['invalid_id']);
// Determine ownership and moderation rights
$modcateg = $CURUSER['modcateg'] ?? null;
$owned = $moderator = 0;
if ((isset($modcateg) && $modcateg === $row['category']) || get_user_class() >= UC_MODERATOR) {
    $owned = $moderator = 1;
} elseif ($CURUSER['id'] === $row['owner']) {
    $owned = 1;
} else {
    $owned = 0;
}

if ($row["modded"] == "no" && $owned == "0")
stderr("Ошибка", "Раздача ожидает проверки");


/*
elseif ($downrow["downloaded"] >= '2' AND get_user_class() <= UC_USER)
stderr("Ошибка", "Вам разрешено качать не больше 2 (двух) торрентов в сутки .");
elseif ($downrow["downloaded"] >= '6' AND get_user_class() <= UC_POWER_USER)
stderr("Ошибка", "Вам разрешено качать не больше 6 торрентов в сутки .");
elseif ($downrow["downloaded"] >= '10' AND get_user_class() <= UC_SPOWER)
stderr("Ошибка", "Вам разрешено качать не больше 10 торрентов в сутки .");
elseif ($downrow["downloaded"] >= '15' AND get_user_class() <= UC_VIP_P)
stderr("Ошибка", "Вам разрешено качать не больше 15 торрентов в сутки .");

if(get_user_class() <= UC_VIP_P AND $CURUSER["id"] != $row["owner"]){
if ($downrow['userid'] == 0) {
sql_query("INSERT INTO users_data (userid,downloaded,down) VALUES (".sqlesc($CURUSER['id']).",'1','yes')") or sqlerr(__FILE__, __LINE__);
} else 
sql_query("UPDATE users_data SET downloaded = downloaded +1 WHERE userid =".sqlesc($CURUSER['id']))or sqlerr(__FILE__, __LINE__);
}
# система ограничения 
*/
$fn = "$torrent_dir/$id.torrent";

if (!$row || !is_file($fn) || !is_readable($fn))
	stderr($lang['error'], $lang['unable_to_read_torrent']);

sql_query("UPDATE torrents SET hits = hits + 1 WHERE id = ".sqlesc($id));

require_once "include/benc.php";



if (strlen($CURUSER['passkey']) != 32) {
	$CURUSER['passkey'] = md5($CURUSER['username'].get_date_time().$CURUSER['passhash']);
	sql_query("UPDATE users SET passkey=".sqlesc($CURUSER['passkey'])." WHERE id=".sqlesc($CURUSER['id']));
}

$dict = bdec_file($fn, (1024*1024));
/*
$dict['value']['announce']['value'] = $announce_urls[0]."?passkey=$CURUSER[passkey]";//"$DEFAULTBASEURL/announce.php?passkey=$CURUSER[passkey]";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
*/
function put_announce_urls(array &$dict, array $announceList): void
{
	unset($dict['value']['announce']);
	unset($dict['value']['announce-list']);
	$dict['value']['announce'] = bdec(benc_str($announceList[0]));
	$announces = [];
	$liststring = '';


	if (is_array($announceList))
	foreach ($announceList as $announce) {
		$announces[] = array('type' => 'list', 'value' => array(bdec(benc_str($announce))), 'strlen' => strlen("l".$announce."e"), 'string' => "l".$announce."e");
		$liststring .= "l".$announce."e";
	}
	$dict['value']['announce-list']['type'] = 'list';
	$dict['value']['announce-list']['value'] = $announces;


	$dict['value']['announce-list']['string'] = "l".$liststring."e";
	$dict['value']['announce-list']['strlen'] = strlen($dict['value']['announce-list']['string']);

}

$trackerUrls = [
    'http://' . $_SERVER['HTTP_HOST'] . '/%s/announce',
    'http://' . $_SERVER['HTTP_HOST'] . '/announce',
    // add more tracker URLs as needed
];

$announce_urls_list = [];
foreach ($trackerUrls as $trackerUrl) {
    // Insert passkey if placeholder is present
    $url = strpos($trackerUrl, '%s') !== false
        ? sprintf($trackerUrl, $CURUSER['passkey'])
        : $trackerUrl;

    // Validate URL before adding
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $announce_urls_list[] = $url;
    }
}


put_announce_urls($dict,$announce_urls_list);

header ("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
header ("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header ("Cache-Control: no-store, no-cache, must-revalidate");
header ("Cache-Control: post-check=0, pre-check=0", false);
header ("Pragma: no-cache");
header ("Accept-Ranges: bytes");
header ("Connection: close");
header ("Content-Transfer-Encoding: binary");
header ("Content-Type: application/x-bittorrent");
header ("Content-Disposition: attachment; filename=\"".$name."\"");

ob_implicit_flush(true);

print(benc($dict));
