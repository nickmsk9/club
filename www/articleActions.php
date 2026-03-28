<?

# +
# +---------------------------------+
# +   articles mod by qwertzuiop    |
# +---------------------------------+
# +

require_once("include/bittorrent.php");
dbconn(false);


$maxScreens = 3;

$action = "add";
$actions = array("delete", "edit", "add");
if(in_array($_GET["action"], $actions))
	$action = htmlspecialchars($_GET["action"]);


function getMainForm($catArray, $data = array(), $error = "")
{
	global $action, $title, $maxScreens;
	
	if($error != "")
		print("<div class=\"error\">".$error."</div>");
	
	print("<form name=\"act_".$action."\" id=\"act_".$action."\" method=\"post\" action=\"articleActions.php?action=".$action . ($action == "edit" ? "&article=".(0+$_GET["article"]) : "") . "\">\n");
	print("<input type=\"hidden\" name=\"action\" value=\"".$action."\">\n");
	print(($action == "edit" ? "<input type=\"hidden\" name=\"id\" value=\"".(0+$_GET["article"])."\">\n" : ""));
	print("<table border=\"0\" cellspacing=\"0\" align=\"center\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" colspan=\"2\">".$title."</td></tr>");
	tr("Название", "<input type=\"text\" name=\"name\" value=\"" . $data["name"] . "\" size=\"80\" />", 1);
	tr("Обложка", "<input type=\"text\" name=\"mainimage\" size=\"80\" value=\"".$data["mainimage"]."\">", 1);
	print("<tr><td class=\"rowhead\" style=\"padding: 3px;\">Текст</td><td>");
	textbbcode("act_".$action, "text", htmlspecialchars($data["text"]));
	print("</td></tr>\n");
	
	foreach($catArray as $k => $c)
	{
		$cat .= (($k-1)<count($catArray)) && ($k % 2 == 0) ? "</tr><tr>" : "";
		$cat .= "<td width=\"300\"><input id=\"cat_".$c["id"]."\" name=\"cat_".$c["id"]."\" type=\"checkbox\"" . ($c["checked"] == true ? " checked" : "") . " value=\"yes\"> <label for=\"cat_".$c["id"]."\">" . htmlspecialchars($c["name"]) . "</label></td>";
	}
	tr("Категории", "<table border=\"0\"><tr>" . $cat . "</tr></table>", 1);
	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"button\" value=\"Предпросмотр\" style=\"height: 25px; width: 100px\" onClick=\"javascript:ajaxpreview('text');\" ><input type=\"submit\" value=\"Готово!\" style=\"height: 25px; width: 100px\"></td></tr>\n");
	?>
	<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<div id="loading-layer" style="display:none;font-family: Verdana;font-size: 11px;width:200px;height:50px;background:#FFF;padding:10px;text-align:center;border:1px solid #000">
     <div style="font-weight:bold;" id="loading-layer-text">Загрузка. Пожалуйста, подождите...</div><br />
     <img src="pic/loading.gif" border="0" />
</div>
<br />
	<?

	tr("Предосмотр","<div id=\"preview\" style=\"width:100%;\"></div>",1);
	print("</table></form>\n");
    if($action == "edit") print("<br /><center><input type=\"button\" value=\"Удалить статью\" onclick=\"if(confirm('Точно удалить?')) location.href = 'articleActions.php?action=delete&article=".$_GET["article"]."';\"></center>");
}


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	function err($msg)
	{
		global $categories;
		
		if(!is_array($categories))
		{
			$row = sql_query("SELECT id, name FROM article_categories ORDER BY id") or die(mysql_error());
			while($cats = mysql_fetch_assoc($row))
				$categories[] = array("id" => $cats["id"], "name" => $cats["name"]);
		}

		foreach($_POST as $k => $v)
			$dataB[$k] = $v;
		foreach($categories as $v)
			$catB[] = array("id" => $v["id"], "name" => $v["name"], "checked" => ($_POST["cat_".$v["id"]] != "" && isset($_POST["cat_".$v["id"]]) ? true : false));
		
		stdhead("Редактирование статьи");
		print(getMainForm($catB, $dataB, $msg));
		stdfoot();
		die;
	}
	
	function getCheckedData()
	{
		$need = array("name", "text");
		foreach($need as $n)
		{
			if(!$_POST[$n] || $_POST[$n] == "")
				err("Как минимум одно нужное поле не заполнено!");
			else
				$data[$n] = sqlesc($_POST[$n]);
		}
		$catsPost = array();
		$imgPost = $catsPost = array();
		foreach($_POST as $k => $v)
		{
			if(strpos($k, "cat_") !== false && $v == "yes")
			{
				$catsPost[] = intval(preg_replace("/[^0-9]/", "", $k));
				continue;
			}
			elseif(strpos($k, "image_") !== false && $v != "")
				$imgPost[] = $v;
		}
		if(count($catsPost) == 0) err("Не выбрана категория!");
		$data["cat"] = implode(",", $catsPost);
		krsort($imgPost);
		if($_POST["mainimage"])
			$data["mainimage"] = sqlesc($_POST["mainimage"]);
		else
		{
			$data["mainimage"] = sqlesc($imgPost[0]);
			unset($imgPost[0]);
		}
		$data["images"] =  sqlesc(implode("<|>", $imgPost));
		
		return $data;
	}
	
	
	
	if($action == "add" || $action == "edit")
	{
		if($action == "edit")
		{
			$id = (int)$_POST["id"];
			if($id <= 0 ||!$id)
			{
				stderr("Ошибка", "Этой статьи не существует");
				die;
			}
			$aId = sql_query("SELECT id, userid FROM articles WHERE id = '".$id."' LIMIT 1");
			if(mysql_num_rows($aId) <> 1)
			{
				stderr("Ошибка", "Этой статьи не существует");
				die;
			}
			$aIdA = mysql_fetch_array($aId);
			if($aIdA["userid"] <> $CURUSER["id"] && get_user_class() < UC_MODERATOR)
			{
				stderr("Ошибка", "Вы не имеите права редактировать эту статью!");
				die;
			}
		}
		
		$data = getCheckedData();
		
		if($action == "edit")
		{
			sql_query("UPDATE articles SET text = ".$data["text"].", name = ".$data["name"].", categories = ',".$data["cat"].",', images = ".$data["images"].", mainimage = ".$data["mainimage"]." WHERE id = ".$id." LIMIT 1") or die(mysql_error());
			header("Location: $DEFAULTBASEURL/articleDetails.php?article=".$id);
		}
		else
		{
			sql_query("INSERT INTO articles (userid, name, text, categories, added, images, mainimage) VALUES ('".$CURUSER["id"]."', ".$data["name"].", ".$data["text"].", ',".$data["cat"].",', '".get_date_time()."', ".$data["images"].", ".$data["mainimage"].")") or die(mysql_error());
			header("Location: $DEFAULTBASEURL/articleDetails.php?article=".mysql_insert_id());
		}
	}
 
	die;
}

if($action == "add")
{
	$title = "Добавить статью";
	stdhead($title);
	begin_main_frame();
	$row = sql_query("SELECT id, name FROM article_categories ORDER BY id") or die(mysql_error());
	while($cats = mysql_fetch_assoc($row))
		$catArray[] = array("id" => $cats["id"], "name" => $cats["name"], "checked" => false);
	print(getMainForm($catArray));
	end_main_frame();
	stdfoot();
}

elseif($action == "edit")
{
	$id = (int)$_GET["article"];
	if($id <= 0 ||!$id)
	{
		stderr("Ошибка", "Этой статьи не существует");
		die;
	}
	$aId = sql_query("SELECT * FROM articles WHERE id = '".$id."' LIMIT 1");
	if(mysql_num_rows($aId) <> 1)
	{
		stderr("Ошибка", "Этой статьи не существует");
		die;
	}
	$aIdA = mysql_fetch_array($aId);
	if($aIdA["userid"] <> $CURUSER["id"] && get_user_class() < UC_MODERATOR)
	{
		stderr("Ошибка", "Вы не имеите права редактировать эту статью!");
		die;
	}
	
	$img = explode("<|>", $aIdA["images"]);
	foreach($img as $k => $v)
		$dataA["image_".$k] = $v;
		
	$dataA["name"] = $aIdA["name"];
	$dataA["text"] = $aIdA["text"];
	$dataA["brandnew"] = $aIdA["brandnew"];
	$dataA["mainimage"] = $aIdA["mainimage"];
	
	$curCats = explode(",", $aIdA["categories"]);
	unset($cat);
	$cat = sql_query("SELECT id, name FROM article_categories ORDER BY id") or die(mysql_error());
	while($v = mysql_fetch_assoc($cat))
		$catA[] = array("id" => $v["id"], "name" => $v["name"], "checked" => (in_array($v["id"], $curCats) ? true : false));
	
	stdhead("Редактирование статьи");
	begin_main_frame();
	print(getMainForm($catA, $dataA));
	end_main_frame();
	stdfoot();
	die;
}

elseif($action == "delete")
{
	$id = (int)$_GET["article"];
    if($id <= 0 ||!$id)
	{
		stderr("Ошибка", "Этой статьи не существует");
		die;
	}
	$aId = sql_query("SELECT id, userid FROM articles WHERE id = '".$id."' LIMIT 1");
	if(mysql_num_rows($aId) <> 1)
	{
    	stderr("Ошибка", "Этой статьи не существует");
		die;
	}
	$aIdA = mysql_fetch_array($aId);
	if($aIdA["userid"] <> $CURUSER["id"] && get_user_class() < UC_MODERATOR)
	{	
        stderr("Ошибка", "Вы не имеите права удалить эту статью!");
    	die;
	}
        
	sql_query("DELETE FROM articles WHERE id = ".$id." LIMIT 1") or die(mysql_error());
	header("Location: $DEFAULTBASEURL/articles.php");
}

?>