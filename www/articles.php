<?
require_once("include/bittorrent.php");
dbconn(false);

stdhead("Статьи");
begin_main_frame();

if ($_GET["category"] > 0){
 $wherein = "WHERE categories LIKE '%".(0+abs($_GET["category"]))."%'";
 $addparam = "category=".$_GET['category']."&";

 }
$row = sql_query("SELECT id, enname, name, (SELECT COUNT(*) FROM articles $wherein) as count FROM article_categories  ORDER BY id") or die(mysql_error());
while($cats = mysql_fetch_assoc($row))
{
	$cat[$cats["id"]] = array (
							"id" => $cats["id"],
							"enname" => $cats["enname"],
							"name" => $cats["name"]
						);
	if(!$count) $count = (int)$cats["count"];
}

$articlesPerPage = 15;

unset($row);
$tab = "";

    if ($addparam != "") {
 if ($pagerlink != "") {
  if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
    $addparam = $addparam . "&" . $pagerlink;
  } else {
    $addparam = $addparam . $pagerlink;
  }
 }
    } else {
 $addparam = $pagerlink;
    }
list($pagertop, $pagerbottom, $limit) = pager($articlesPerPage, $count, "articles.php?". $addparam);
$row = sql_query("SELECT a.id, a.name, a.userid,  a.categories, a.added, a.views, u.class, u.username, (SELECT COUNT(id) FROM article_comments WHERE articleid = a.id) as comments FROM articles a LEFT JOIN users u ON u.id = a.userid $wherein ORDER BY added DESC " . $limit) or die(mysql_error());
$count = mysql_num_rows($row);
while($ar = mysql_fetch_assoc($row))
{
	$category = array();
	$cats = explode(",", $ar["categories"]);
	foreach($cats as $c)
        if(!empty($c) && $c != "")
		  $category[] = "<a href=\"articles.php?category=".$cat[$c]["id"]."\">" . $cat[$c]["name"] . "</a>";

	$user = get_user_class_color($ar["class"], ($ar["username"] ? $ar["username"] : "[аноним]"));
	$added = "(" . get_elapsed_time(sql_timestamp_to_unix_timestamp($ar["added"])) . " назад)";
	$details_v = $ar["views"] ;
	$details_c = $ar["comments"];
	

	$tab .=<<<BLOCKHTML
<tr style="background: rgb(245, 248, 250)">
<td colspan="10" align="left">
				<a href="articleDetails.php?article=[ID]" title="[NAME]"><span style="margin-left:5px; font-size:1.3em;">[NAME]</span>
</td></tr>
<tr>
<td align="center"><span style=" font-size:0.9em;">Просмотры: [DETAILS_V]</span></td>
<td align="center"><span style=" font-size:0.9em;">Комментарии:[DETAILS_C]</span></td>
<td align="center"><span style=" font-size:0.9em;">Категори[CAT_END]: [CAT]</span></td>
<td align="center"><span style=" font-size:0.9em;">Добавил(а): <a href="user/id[UID]">[USER]</a></span></td>
<td align="center"><span style=" font-size:0.9em;">[ADDED]</span></td>
</tr>
BLOCKHTML;

	if(!$str) $str = array("[ID]", "[NAME]",  "[CAT_END]", "[CAT]","[DETAILS_V]", "[DETAILS_C]", "[UID]", "[USER]", "[ADDED]");
	$vars = array($ar["id"], $ar["name"],  (count($category) > 1 ? "и" : "я"), (count($category) == 0 ? "---" : implode(", ", $category)),$details_v,$details_c, $ar["userid"], $user,  $added);
	$table .= str_replace($str, $vars, $tab);
	unset($tab, $vars, $cats);
}

if (cache_check("articles_cat", 600))
$ress = cache_read("articles_cat");
else {
$ress = sql_query("SELECT * FROM article_categories")or die(mysql_error());
$articles_cat_cache = array();
    while ($cache_data = mysql_fetch_array($ress))
        $articles_cat_cache[] = $cache_data;

    cache_write("articles_cat", $articles_cat_cache);
    $ress = $articles_cat_cache;
    }

$showbegins="<table id=no_border width=99% cellpadding=5 align=center><tr>";

foreach ($ress as $row) 
{  

	$shows .="<td id=no_border width=25% align=center><a href=articles.php?category=$row[id]>$row[name]</a></td>";
	$countcat++;
	if($countcat == 3){
	$shows .="<tr></tr>";
	$countcat = 0;
	}
}

if($countcat != 4){
	for($countcat; $countcat ==4; $countcat++){
		$shows .="<td></td>";
	}
}
$showends="</tr></table>";

if(get_user_class() >= UC_CURATOR )
$s = "<a href=/articleActions.php?action=add>Добавить</a> | <a href=/articleCategory.php>Категории</a>";
print("<div class=\"c_title\">Афиша</div>");

print("<div align=\"center\">".$s."<br> ".$showbegins."".$shows."".$showends."</div><br>");
		print ("<tr><td id=no_border><table class=\"embedded\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\" valign=\"top\" align=\"left\">");
		
		if($count == 0)
{
	print("<tr style=\"background: rgb(245, 248, 250)\"><td colspan=\"10\" align=\"center\">Извените, ничего не найдено.</td></tr>");
} else {
		print ($table);
		}
		print("</table></td></tr><tr><td id=no_border>".$pagerbottom."</td></tr>");

end_main_frame();
stdfoot();
?>