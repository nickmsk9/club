<?

# +
# +---------------------------------+
# +   articles mod by qwertzuiop    |
# +---------------------------------+
# +

require_once("include/bittorrent.php");
dbconn(false);

$commentsPerPage = 5;
$width = 200;

$action = $_GET["action"];
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$id = (int)$_POST["articleid"];

	if($action == "newcomment")
	{
		$ch = sql_query("SELECT id FROM articles WHERE id = '".$id."' LIMIT 1") or sqlerr(__FILE__, __LINE__);
		if(mysql_num_rows($ch) <> 1)
		{
			stderr("Ошибка", "Этой статьи не существует");
			die;
		}
		if(!$_POST["text"] || $_POST["text"] == "")
		{
			stderr("Ошибка", "Вы не ввели текст комментария!");
			die;
		}
		
		sql_query("INSERT INTO article_comments (userid, articleid, text, added) VALUES ('".$CURUSER["id"]."', '".$id."', ".sqlesc($_POST["text"]).", '".get_date_time()."')") or sqlerr(__FILE__, __LINE__);;
		
	}
	
}

if((int)$_GET["comment"]  > 0 && $action == "deletecomment")
{
	$ch = sql_query("SELECT userid FROM article_comments WHERE id = '".((int)(0+$_GET["comment"]))."' AND articleid = '".(int)$_GET["article"]."' LIMIT 1") or sqlerr(__FILE__, __LINE__);
	if(mysql_num_rows($ch) <> 1)
	{
		stderr("Ошибка", "Этот комментарий не существует");
		die;
	}
	if($CURUSER['id'] == $c['userid'] || get_user_class() >= UC_MODERATOR)
		sql_query("DELETE FROM article_comments WHERE id = '".((int)(0+$_GET["comment"]))."' LIMIT 1") or sqlerr(__FILE__, __LINE__);
}


$id = (!$id) || ($id <= 0) ? (int)$_GET["article"] : $id;
if($id <= 0 ||!$id)
{
	stderr("Ошибка", "Этой статьи не существует");
	die;
}

$aId = sql_query("SELECT a.*, u.class, u.username, (SELECT COUNT(id) FROM article_comments WHERE articleid = '".$id."') as comments FROM articles a LEFT JOIN users u ON u.id = a.userid WHERE a.id = '".$id."' LIMIT 1") or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($aId) <> 1)
{
	stderr("Ошибка", "Этой статьи не существует");
	die;
}
$aA = mysql_fetch_array($aId);

//$aaCats = explode(",", $aA["categories"]);
//foreach($aaCats as $c)
//    if(!empty($c))
//       $aC[] = $c;

$row = sql_query("SELECT id, enname, name FROM article_categories WHERE id IN(".substr($aA["categories"], 1, -1).") ORDER BY id") or sqlerr(__FILE__, __LINE__);
while($cats = mysql_fetch_array($row))
{
	$cat[$cats["id"]] = array (
							"id" => $cats["id"],
							"enname" => $cats["enname"],
							"name" => $cats["name"]
						);
}

	sql_query("UPDATE articles SET views = views + 1 WHERE id = ".$id." LIMIT 1") or sqlerr(__FILE__, __LINE__);


$title = $aA["name"];
stdhead($title);
?>
<link rel="stylesheet" type="text/css" href="fancybox/fancybox.css"/>
<script type="text/javascript" src="fancybox/fancybox.js"></script>
<script>
jQuery(document).ready(function() {
 jQuery("a.screen").fancybox({
 'overlayShow' : false,
 });
 });

</script>
<?
begin_main_frame();

$tab = "";

$category = array();
$cats = explode(",", $aA["categories"]);
foreach($cats as $c)
	$category[] = "<a href=\"articles.php?category=".$cat[$c]["id"]."\">" . $cat[$c]["name"] . "</a>";

$text = format_comment($aA["text"]);
$bb[] = "#<img class=\"linked-image\" src=\"(.*?)\" (.*?) />#is";
$html[] = "<a href=\"\\1\"><img class=\"linked-image\" width=\"".$width."\" src=\"\\1\" \\2 /></a>";
$text = preg_replace($bb, $html, $text);

$user = get_user_class_color($aA["class"], $aA["username"]);
$ableToEdit = $CURUSER["id"] == $aA["userid"] || get_user_class() >= UC_MODERATOR ? " <a href=\"articleActions.php?action=edit&article=".$aA["id"]."\"><img border=\"0\" src=\"pic/pen.gif\" alt=\"Редактировать\" title=\"Редактировать\" /></a>" : "";

if($aA["mainimage"] != "") $image = $aA["mainimage"];
else
{
	$im = explode("<|>", $aA["images"]);
	$image = $im[0];
}

if(isset($image) && strpos($image, "http://") !== false)
	$image = "<noindex><a class=\"screen\" href='".$image."' alt='".$ar["name"]."'><img border='0' src='".$image."' width='".$width."' alt='".$ar["name"]."' /></a></noindex>";
else $image = "[нет картинки]";
$views = $aA["views"];


$det .=<<<BLOCKHTML
<tr><td align="left" valign="top" align="center" colspan="10" style="border-style:none;" width="100%">
	<div><div style="float:right; margin:5px;">[IMAGE]</div>
		<div class="artText">[TEXT]</div>
		
	</div>
</td></tr>

<tr>
<td align="center"><span style="font-size:0.9em;">Просмотры: [VIEWS]</span></td>
<td align="center"><span style="font-size:0.9em;">Категори[CAT_END]: [CAT]</span></td>
<td align="center"><span style="font-size:0.9em;">Добавил:<a href="user/id[UID]">[USER]</a></span></td>
<td align="center"><span style="font-size:0.9em;">[ADDED]</span></td>
</tr>
BLOCKHTML;

if(!$str) $str = array("[ID]", "[NAME]", "[TEXT]", "[IMAGE]", "[CAT_END]", "[CAT]", "[VIEWS]", "[UID]", "[USER]", "[ADDED]");
	$vars = array($aA["id"], $aA["name"], $text, $image, (count($category) > 1 ? "и" : "я"), (count($category) == 0 ? "---" : substr(implode(", ", $category), 38, -43)), $views, $aA["userid"], $user, get_elapsed_time(sql_timestamp_to_unix_timestamp($aA["added"])) . " назад ");
	$details .= str_replace($str, $vars, $det);
	unset($tab, $vars, $cats);


print("<table class=\"embedded\" cellpadding=\"5\" width=\"100%\" valign=\"top\" align=\"center\">
		<tr><td class=\"colhead\" align=\"center\" valign=\"top\" style=\"border-style:none;\" width=\"99%\">
			<div style=\"margin-left:5px;\"><h2><a href=\"/articles.php\">Статьи</a> - " . $title . " " . $ableToEdit . "</h2></div>
		</td></tr>
		<tr><td>
			<table class=\"embedded\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\" valign=\"top\" align=\"left\">
				".$details."
			</table>
		</td></tr>
		
		</table>");

if ($aA["comments"] > 0)
{
	list($pagertop, $pagerbottom, $limit) = pager($commentsPerPage, $aA["comments"], "articleDetails.php?article=".$id."&");
	$com = sql_query("SELECT c.*, u.username, u.class, u.avatar FROM article_comments c LEFT JOIN users u ON u.id = c.userid WHERE c.articleid = '".$id."' ORDER BY c.added DESC " . $limit) or sqlerr(__FILE__, __LINE__);
	print("<br /><table class=\"inlay\" width=\"100%\" align=\"center\">\n");
	while ($c = mysql_fetch_assoc($com))
	{
		print("<tr valign=\"top\">
				<td width=\"63\">
					<img src=\"" . ($c['avatar'] ? $c['avatar'] : "pic/default_avatar.gif") . "\" style=\"border:1px solid #999; padding:5px; width:60px;\" title=\"\" alt=\"\" />
				</td>
				<td>
					<div style=\"padding-bottom:5px;\">
						<span style=\"float:right; color:#C0C0C0; margin-bottom:7px;\">
								Добавил: <a href=\"user/id" . $c['userid'] . "\">" . get_user_class_color($c['class'], ($c['username'] ? $c['username'] : "[аноним]")) . "</a>,&nbsp;" . get_elapsed_time(sql_timestamp_to_unix_timestamp($c['added'])) . " назад"
								 . ($CURUSER['id'] == $c['userid'] || get_user_class() >= UC_MODERATOR ? " <a href=\"articleDetails.php?article=" . $id . "&action=deletecomment&comment=" . $c['id'] . "\"><img src=\"pic/warned5.gif\" border=\"0\" /></a>" : "") . "
						</span>
						<div style=\"margin-bottom:5px; margin-left:5px; margin-top:5px;\">
							" . format_comment($c['text']) . "
						</div>
					</div>
				</td>
				</tr>\n");
	}
	print("</table>\n");
	print("<table border=\"0\">\n");
	print("<tr><td style=\"border:none;\">");
	print($pagertop);
	print("</td></tr>");
	print("</table>\n");
}
else
{
	print("<br /><table width=\"100%\" align=\"center\">\n");
	print("<tr>
			<td style=\"border:none; padding:5px;\" valign=\"top\" width=\"50\">
				<p1 style=\"line-height:16px;\"><b>Нет комментариев.</b></p1>
			</td>
			</tr>
		");
	print("</table>\n");
}
if ($CURUSER){
	print("<br /><table border=\"0\" cellspacing=\"0\" align=\"center\" cellpadding=\"5\">\n");
	print("<tr><td>");
	print("<form name=\"addComment\" id=\"addComment\" method=\"post\" action=\"articleDetails.php?action=newcomment\">\n");
	print("<input type=\"hidden\" name=\"articleid\" value=\"".$id."\">\n");
	textbbcode("addComment", "text");
	print("</td></tr>\n");
	print("<tr><td align=\"center\"><input type=\"submit\" value=\"Добавить комментарий!\"></td></tr>\n");
	print("</table>\n");
	print("</form>\n");

}
end_main_frame();
stdfoot();

?>