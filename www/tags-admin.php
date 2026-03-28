<?
require "include/bittorrent.php";
dbconn(false);
global $mysqli;
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
    stderr("Ошибка", "Доступ запрещен.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["edit"])) {
    $id = (int)$_POST["id"];
    if(empty($id))
      stderr("Ошибка", "Не пытайся меня взломать");
    $res = sql_query("SELECT * FROM tags WHERE id = $id");
    if (!mysqli_num_rows($res))
      stderr("Ошибка", "Нет такого ID");
    else {
      sql_query("UPDATE tags SET name = LOWER(".sqlesc($_POST["name"])."), category = ".sqlesc($_POST["category"])." WHERE id = $id;");
      header("Location: $DEFAULTBASEURL/tags-admin.php");
    }
  }

  elseif (isset($_POST["add"])) {
      if (empty($_POST["name"]) || empty($_POST["category"]))
        stderr("Ошибка", "Вы заполнили не все поля формы");
      sql_query("INSERT INTO tags (name, category) VALUES (LOWER(".sqlesc($_POST["name"])."), ".sqlesc($_POST["category"]).");") or sqlerr(__FILE__, __LINE__);
      header("Location: $DEFAULTBASEURL/tags-admin.php");
  }
  else {
    stderr("Ошибка", "Не выбрано действие");
  }
}

elseif (isset($_GET["edit"])) {
  $id = (int)$_GET["edit"];
  $res = sql_query("SELECT * FROM tags WHERE id = $id");
  if (!mysqli_num_rows($res))
    stderr("Ошибка", "Нет такого ID");
  else {
    $row = mysqli_fetch_array($res);
    stdhead("Редактирование тэга \"".$row["name"]."\"");
    begin_main_frame("Редактирование тэга \"".$row["name"]."\"");
    print "<form name=\"edit\" action=\"tags-admin.php\" method=\"POST\">\n";
    print "<input type=\"hidden\" name=\"edit\"/>\n";
    print "<input type=\"hidden\" name=\"id\" value=\"".$row["id"]."\"/>\n";
    print "<table>\n";
    print "<tr><td>Название:</td><td><input type=\"text\" style=\"width:200px;\" name=\"name\" value=\"".$row["name"]."\"></td></tr>\n";

    $s = "<select name=\"category\" style=\"width:200px;\">\n<option value=\"0\">(".$lang['choose'].")</option>\n";
    $cats = genrelist();
    foreach ($cats as $cat)
	    $s .= "<option value=\"".$cat["id"]."\" ".($cat['id'] == $row['category'] ? " selected=\"selected\"" : "").">" . htmlspecialchars($cat["name"]) . "</option>\n";
    $s .= "</select>\n";

    print "<tr><td>Категория:</td><td>$s</td></tr>\n";
    print "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Сохранить\">&nbsp;&nbsp;<input type=\"reset\" value=\"Сбросить\"></td></tr>\n";
    print "</table>\n";
    print "</form>";
    end_main_frame();
    stdfoot();
  }
}

elseif (isset($_GET["add"])) {
    stdhead("Добавление тэга");
    begin_main_frame("Добавление тэга");
    print "<form name=\"add\" action=\"tags-admin.php\" method=\"POST\">\n";
    print "<input type=\"hidden\" name=\"add\"/>\n";
    print "<table>\n";
    print "<tr><td>Название:</td><td><input type=\"text\" style=\"width:200px;\" name=\"name\"></td></tr>\n";

    $s = "<select name=\"category\" style=\"width:200px;\">\n<option value=\"0\">(".$lang['choose'].")</option>\n";
    $cats = genrelist();
    foreach ($cats as $cat)
	    $s .= "<option value=\"".$cat["id"]."\">" . htmlspecialchars($cat["name"]) . "</option>\n";
    $s .= "</select>\n";

    print "<tr><td>Категория:</td><td>$s</td></tr>\n";
    print "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Сохранить\">&nbsp;&nbsp;<input type=\"reset\" value=\"Сбросить\"></td></tr>\n";
    print "</table>\n";
    print "</form>";
    end_main_frame();
    stdfoot();
}

elseif (isset($_GET["delete"])) {
  $id = (int)$_GET["delete"];
  $res = sql_query("SELECT name FROM tags WHERE id = $id") or sqlerr(__FILE__, __LINE__);
  if (!mysqli_num_rows($res))
    stderr("Ошибка", "Нет такого ID");
  else {
    sql_query("DELETE FROM tags WHERE id = $id;");
    header("Location: $DEFAULTBASEURL/tags-admin.php");
  }
}

else {
  stdhead("Управление тэгами");
  begin_main_frame("Управление тэгами");
  begin_frame();
  echo '<div style="float:right; margin-bottom:10px;"><a href="tags-admin.php?add=1">Добавить новый</a></div>';
  print "<style>img{border:none;}</style>\n";
  print "<table id=\"browsetags\" width=\"100%\">\n";
  print "<tr>\n";
  print "<td class=\"colhead\">Тэг</td>";
  print "<td class=\"colhead\">Категория</td>";
  print "<td class=\"colhead\" align=\"center\">Обзор</td>";
  print "<td class=\"colhead\" align=\"center\">Торрентов с тэгом</td>";
  print "<td class=\"colhead\" align=\"center\">Редактировать</td>";
  print "<td class=\"colhead\" align=\"center\">Удалить</td>";
  print "</tr>\n";

  $count = number_format(get_row_count("tags"));
  $perpage = 35; //Количество тэгов на странице
  list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "tags-admin.php?");

  $res = sql_query("SELECT t.*, c.id AS cat_id, c.name AS cat_name FROM tags AS t LEFT JOIN categories AS c ON t.category = c.id ORDER BY c.id $limit;") or sqlerr(__FILE__, __LINE__);
  print $pagertop;
  while ($row = mysqli_fetch_array($res)){
      print "<tr>\n";
      print "<td>".$row["name"]."</td>\n";
      print "<td>".$row["cat_name"]."</td>\n";
      print "<td align=\"center\"><a href=\"browse.php?tag=".$row["name"]."&cat=".$row["cat_id"]."&incldead=1\"><img src=\"pic/viewnfo.gif\" /></a></td>\n";
      print "<td align=\"center\">".$row["howmuch"]."</td>\n";
      print "<td align=\"center\"><a href=\"tags-admin.php?edit=".$row["id"]."\"><img src=\"pic/edit_com.png\" /></a></td>\n";
      print "<td align=\"center\"><a href=\"tags-admin.php?delete=".$row["id"]."\"><img src=\"pic/delete.png\" /></a></td>\n";
      print "</tr>\n";
  }
  print "<tr><td colspan=\"6\"><br />";
  print $pagerbottom;
  print "</td></tr></table>";
  end_frame();
  end_main_frame();
  stdfoot();
}
?>