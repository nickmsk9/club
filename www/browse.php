<?php
require_once("include/bittorrent.php");
global $getlang, $addtags, $mcache;
gzip();
dbconn(false);
parked();
getlang('browse');

// Без Notice: проверяем параметры
$mode = $_GET['mode'] ?? '';
$searchstr = $_GET['search'] ?? '';
$yearstr = $_GET['year'] ?? '';
$tagstr = $_GET['tag'] ?? '';
$letter = $_GET['letter'] ?? '';
$sort = $_GET['sort'] ?? '';
$type = $_GET['type'] ?? '';
$incldead = $_GET['incldead'] ?? '';
$category = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$all = $_GET['all'] ?? false;

// Переключение режимов
if ($mode === 'thumbs' || $mode === 'simple' || $mode === 'full') {
    setcookie('browsemode', $mode, time() + 31536000, '/');
    header("Location: $DEFAULTBASEURL/browse");
    exit;
}

$cats = genrelist();

$searchstr = searchfield((string)$searchstr);
$cleansearchstr = htmlspecialchars($searchstr);
$searchstr = strtolower($searchstr);
if (empty($cleansearchstr)) unset($cleansearchstr);

$yearstr = (int)unesc($yearstr);
$cleanyearstr = htmlspecialchars_uni($yearstr);
$yearstr = strtolower($yearstr);
if (empty($cleanyearstr)) unset($cleanyearstr);

$tagstr = (string)unesc($tagstr);
$cleantagstr = htmlspecialchars($tagstr);
if (empty($cleantagstr)) unset($cleantagstr);

if (empty($letter)) unset($letter);

// Сортировка
$orderby = "ORDER BY torrents.added DESC";
$pagerlink = "";
if ($sort && $type) {
    $columns = [
        1 => "name", 2 => "numfiles", 3 => "comments", 4 => "added",
        5 => "size", 6 => "times_completed", 7 => "seeders",
        8 => "leechers", 9 => "owner", 10 => "modby"
    ];
    $column = $columns[(int)$sort] ?? "added";
    if ((int)$sort === 10 && get_user_class() < UC_MODERATOR) $column = "added";

    $ascdesc = ($type === 'asc') ? "ASC" : "DESC";
    $linkascdesc = ($type === 'asc') ? "asc" : "desc";

    $orderby = "ORDER BY torrents.$column $ascdesc";
    $pagerlink = "sort=" . intval($sort) . "&type=$linkascdesc&";
}

$addparam = "";
$wherea = [];
$wherecatina = [];

if ($incldead == 1) {
    $addparam .= "incldead=1&amp;";
    if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
        $wherea[] = "banned != 'yes'";
} elseif ($incldead == 2) {
    $addparam .= "incldead=2&amp;";
    $wherea[] = "visible = 'no'";
} elseif ($incldead == 3) {
    $addparam .= "incldead=3&amp;";
    $wherea[] = "seeders = 0";
} else {
    $wherea[] = "modded = 'yes'";
}

if (!$all && !$category && isset($CURUSER["notifs"])) {
    $all = true;
    foreach ($cats as $cat) {
        $cid = $cat['id'];
        $all &= $_GET["c$cid"] ?? false;
        if (strpos($CURUSER["notifs"], "[cat$cid]") !== false) {
            $wherecatina[] = $cid;
            $addparam .= "c$cid=1&amp;";
        }
    }
} elseif ($category) {
    if (!is_valid_id($category)) stderr($lang['error'], "Invalid category ID.");
    $wherecatina[] = $category;
    $addparam .= "cat=$category&amp;";
} else {
    $all = true;
    foreach ($cats as $cat) {
        $cid = $cat['id'];
        $all &= $_GET["c$cid"] ?? false;
        if ($_GET["c$cid"] ?? false) {
            $wherecatina[] = $cid;
            $addparam .= "c$cid=1&amp;";
        }
    }
}

if ($all) {
    $wherecatina = [];
    $addparam = "";
}

if (count($wherecatina) > 1)
    $wherecatin = implode(",", $wherecatina);
elseif (count($wherecatina) == 1)
    $wherea[] = "category = {$wherecatina[0]}";
else
    $wherecatin = "";

$wherebase = $wherea;

if (isset($cleansearchstr)) {
    $wherea[] = "LOWER(torrents.name) LIKE ('%".sqlwildcardesc($searchstr)."%')";
    $addparam .= "search=" . urlencode($cleansearchstr) . "&amp;";
}

if (isset($cleanyearstr)) {
    $wherea[] = "LOWER(torrents.name) LIKE ('%".sqlwildcardesc($yearstr)."%')";
    $addparam .= "year=" . urlencode($yearstr) . "&amp;";
}

if (isset($cleantagstr)) {
    $wherea[] = "torrents.tags LIKE ('%".sqlwildcardesc($tagstr)."%')";
    $addparam .= "tag=" . urlencode($tagstr) . "&amp;";
}

if (isset($letter)) {
    $letter = mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $letter);
    $wherea[] = "torrents.name LIKE BINARY '$letter%'";
    $addparam .= "letter=" . urlencode($letter) . "&amp;";
}

// Устранение предупреждения Undefined variable $wherecatin
$wherecatin = $wherecatin ?? "";
$where = implode(" AND ", $wherea);
if (!empty($wherecatin))
    $where .= ($where ? " AND " : "") . "category IN ($wherecatin)";
if ($where != "")
    $where = "WHERE $where";

if (isset($cleansearchstr))
{
		$wherea[] = 'LOWER(torrents.name) LIKE("%'.sqlwildcardesc(strtolower($cleansearchstr)).'%")';
        $addparam .= "search=" . urlencode($cleansearchstr) . "&amp;";
}


//////////////////*****************
if (isset($cleanyearstr))
{
		$wherea[] = "LOWER(torrents.name) LIKE('%".sqlwildcardesc(strtolower($yearstr))."%')";
        $addparam .= "year=" . urlencode($yearstr) . "&amp;";
}
////////////////*******************


if (isset($cleantagstr))
{
		$wherea[] = "torrents.tags LIKE '%" . sqlwildcardesc($tagstr) . "%'";
        $addparam .= "tag=" . urlencode($tagstr) . "&";
}
if (isset($letter))
{
$letter=mysql_escape_string($letter);
        $wherea[] = "torrents.name LIKE BINARY '$letter%'";
        $addparam .= "letter=" . urlencode($letter) . "&amp;";
} 

$where = implode(" AND ", $wherea);
if ($wherecatin)
        $where .= ($where ? " AND " : "") . "category IN (" . $wherecatin . ")";

if ($where != "")
        $where = "WHERE $where";

$mem_get_browse = $mcache->get_value('browse_'.md5($where));
if ($mem_get_browse === false) {
$res = sql_query("SELECT COUNT(*) FROM torrents $where ") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$date = $row;
$mcache->cache_value('browse_'.md5($where), $date, rand(100 , 1000 ));	  } else
$row = $mem_get_browse;
$count = $row[0];
$num_torrents = $count;

if (!$count && isset($cleansearchstr)) {
        $wherea = $wherebase;
        //$orderby = "ORDER BY id DESC";
        $searcha = explode(" ", $cleansearchstr);
        $sc = 0;
        foreach ($searcha as $searchss) {
                if (strlen($searchss) <= 1)
                        continue;
                $sc++;
                if ($sc > 5)
                        break;
                $ssa = array();
                $ssa[] = 'LOWER(torrents.name) LIKE("%'.sqlwildcardesc(strtolower($cleansearchstr)).'%")';
        }
        if ($sc) {
                $where = implode(" AND ", $wherea);
                if ($where != "")
                        $where = "WHERE $where";
                $res = sql_query("SELECT COUNT(*) FROM torrents $where");
                $row = mysqli_fetch_array($res);
                $count = $row[0];
        }
}
/////////////////////////////*******************************
if (!$count && isset($cleanyearstr)) {
        $wherea = $wherebase;
        //$orderby = "ORDER BY id DESC";
        $yearcha = explode(" ", $cleanyearstr);
        $sc = 0;
        foreach ($yearcha as $yearss) {
                if (strlen($yearss) <= 1)
                        continue;
                $sc++;
                if ($sc > 5)
                        break;
                $ssa = array();
                $ssa[] = "LOWER(torrents.name) LIKE('%".strtolower($yearstr)."%')";
        }
        if ($sc) {
                $where = implode(" AND ", $wherea);
                if ($where != "")
                        $where = "WHERE $where";
                $res = sql_query("SELECT COUNT(*) FROM torrents $where");
                $row = mysqli_fetch_array($res);
                $count = $row[0];
        }
}
///////////////////////////////*********************************

$torrentsperpage = 12;

$res = [];
$torrents_name = [];
$cats = $cats ?? [];
$wherecatina = $wherecatina ?? [];
$addparam = $addparam ?? '';
$pagerlink = $pagerlink ?? '';
$letter = $letter ?? null;
$num_torrents = $num_torrents ?? 0;

// формирование $addparam
if ($addparam !== "") {
    if ($pagerlink !== "") {
        if (substr($addparam, -1) !== ";") {
            $addparam .= "&" . $pagerlink;
        } else {
            $addparam .= $pagerlink;
        }
    }
} else {
    $addparam = $pagerlink;
}

list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse?" . $addparam);
$query = "SELECT SQL_NO_CACHE torrents.* FROM torrents $where $orderby $limit";

$result_res = sql_query($query) or sqlerr(__FILE__, __LINE__);
while ($result_row = mysqli_fetch_assoc($result_res)) {
    $res[] = $result_row;
    $torrents_name[] = $result_row["name"];
}

if (isset($cleansearchstr) || isset($tag_array)) {
    stdhead(
        (!empty($torrents_name) && !$CURUSER ? implode(", ", $torrents_name) . " < " : "") .
        (!empty($searchstr) ? $searchstr . " < " : "") .
        (isset($tag_array) ? " " . implode(", ", $tag_array) . " < " : "") .
        (!empty($title_cat) ? " " . $title_cat . " < " : "") .
        $lang['b_search_results_for'],
        true,
        (isset($searchstr) ? $searchstr . "," : "") .
        (isset($tag_array) ? implode(",", $tag_array) . "," : "") .
        (!empty($title_cat) ? $title_cat . "," : "") .
        (!empty($torrents_name) ? implode(",", $torrents_name) : "")
    );
} else {
    stdhead(
        (!empty($torrents_name) && !$CURUSER ? implode(", ", $torrents_name) . " < " : "") .
        (!empty($title_cat) ? $title_cat . " < " : "") .
        $lang['browse_head'],
        true,
        (!empty($title_cat) ? $title_cat . ", " : "") .
        (!empty($torrents_name) ? implode(",", $torrents_name) : "")
    );
}

begin_main_frame();

?>
<table class="embedded" cellspacing="0" cellpadding="5" width="100%">
<tr>
<td id="no_border" class="colhead" align="center" colspan="12">
<noindex>
<h2>
<?= $lang['b_list'] ?> - 
[<a class="altlink" href="browse?mode=thumbs"><?= $lang['b_list_img'] ?></a>] - 
[<a class="altlink" href="browse?mode=simple"><?= $lang['b_list_list'] ?></a>] - 
[<a class="altlink" href="browse?mode=full">Полное описание</a>]
</h2>
</noindex>
</td>
</tr>
<tr><td colspan="12">
<br />
<form method="get" action="/browse">
<table class="embedded" align="center" width="100%" style="margin:0 0 15px 0;">
<tr><td>
<div class="layout">
<div class="mini donwloadtor">
<fieldset style="border-color:#C2EFC2;"><legend><span style="font-size:17px;"><b><?= $lang['b_search'] ?></b></legend>
<div align="center" style="position:relative;">
<p class="download">
<script src="<?= $DEFAULTBASEURL ?>/js/suggest.js" type="text/javascript"></script>
<input id="suggestinput" name="search" type="text" size="60" x-webkit-speech="" speech="" onwebkitspeechchange="" data-jq-watermark="processed"/>
<?= $lang['b_in'] ?>
<select name="incldead">
<option value="0"><?= $lang['b_active'] ?></option>
<option value="1"<?= (isset($_GET["incldead"]) && $_GET["incldead"] == 1) ? " selected" : "" ?>><?= $lang['b_search_dead'] ?></option>
<option value="2"<?= (isset($_GET["incldead"]) && $_GET["incldead"] == 2) ? " selected" : "" ?>><?= $lang['b_search_only_dead'] ?></option>
<option value="3"<?= (isset($_GET["incldead"]) && $_GET["incldead"] == 3) ? " selected" : "" ?>><?= $lang['b_search_no_seeds'] ?></option>
</select>

<select name="cat">
<option value="0">(<?= $lang['b_all_types'] ?>)</option>
<?php
$catdropdown = "";
foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    if (isset($_GET["cat"]) && $cat["id"] == $_GET["cat"])
        $catdropdown .= " selected";
    $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
}
echo $catdropdown;
?>
</select>

<?php
$yeararray = range(1950, (int)date('Y'));
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : 0;

$years = '<select name="year"><option value="">--- Выберите ---</option>';
foreach ($yeararray as $val) {
    $years .= '<option value="' . $val . '"' . ($val === $selected_year ? ' selected' : '') . '>' . $val . ' год</option>' . "\n";
}
$years .= '</select>';

echo $years;
?>
<input type="submit" value="<?= $lang['b_search'] ?>!" />
<div id="suggest"></div>
</p>
</div></fieldset>
</div></div>
</td></tr>
</table>

<table class="embedded" colspan="12" align="center">

<?php
$i = 0;
$catsperrow = 7;
foreach ($cats as $cat) {
    if ($i && $i % $catsperrow == 0) echo "</tr><tr>";
    echo "<td class=\"bottom\" style=\"padding-bottom:2px;padding-left:7px\">
        <input name=\"c{$cat['id']}\" type=\"checkbox\" " .
        (in_array($cat['id'], $wherecatina) ? "checked " : "") .
        "value=\"1\">
        <a class=\"catlink\" href=\"browse/cat{$cat['id']}\" title=\"" . htmlspecialchars($cat['name']) . "\">" . htmlspecialchars($cat['name']) . "</a></td>\n";
    $i++;
}

// Не выводим пустые ячейки, если нет оставшихся категорий
?>
</table></form>

<?php

/*
// вывод букв и цифр
for ($i = 1; $i <= 10; ++$i) {
    $label = ($i == 10) ? 0 : $i;
    if ($label == $letter) echo "<b>$label</b>\n";
    else echo "<a href=\"browse?{$addparam}letter=$label\"><b>$label</b></a>\n";
}

for ($i = 65; $i <= 90; ++$i) {
    $l = chr($i);
    if ($l == $letter) echo "<b>$l</b>\n";
    else echo "<a href=\"browse?{$addparam}letter=$l\"><b>$l</b></a>\n";
}

echo "<br>";

for ($i = 192; $i <= 223; ++$i) { // кириллица (UTF-8)
    $l = iconv("CP1251", "UTF-8", chr($i));
    if ($l == $letter) echo "<b>$l</b>\n";
    else echo "<a class='button' href=\"browse?{$addparam}letter=$l\"><b>$l</b></a>\n";
}

echo "&nbsp;&nbsp;<a class='button' rel=\"nofollow\" href=\"browse?search=&cat=0\"><b>All</b></a>\n";
*/
// результаты поиска
if (!empty($cleansearchstr)) {
    echo "<tr><td class=\"index\" colspan=\"12\">" . $lang['b_search_results_for'] . " \"" . htmlspecialchars($cleansearchstr) . "\"</td></tr>\n";
}
if (!empty($cleanyearstr)) {
    echo "<tr><td class=\"index\" colspan=\"12\">" . $lang['b_search_results_for'] . ": \"" . htmlspecialchars($cleanyearstr) . "\"</td></tr>\n";
}
if (!empty($cleantagstr)) {
    echo "<tr><td class=\"index\" colspan=\"12\">" . $lang['b_search_results_for_tag'] . ": \"" . htmlspecialchars($cleantagstr) . "\"</td></tr>\n";
}

echo "</td></tr>";

if ($num_torrents) {
    echo "<tr><td class=\"index\" id=\"no_border\" colspan=\"12\">$pagertop</td></tr>";
    torrenttable($res, "index");
    echo "<tr><td class=\"index\" id=\"no_border\" colspan=\"12\">$pagerbottom</td></tr>";
} else {
    echo "<tr><td class=\"index\" colspan=\"12\">" . ($lang['b_nothing_found'] ?? 'Ничего не найдено') . "</td></tr>\n";
}
echo "</table>";

begin_frame();
require_once("include/cloud_func.php");

echo cloud(12, 22);

end_frame();

end_main_frame();
stdfoot();
?>