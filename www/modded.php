<?php

require "include/bittorrent.php";
dbconn(false);
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
  stderr($lang['error'], "Нет доступа.");

stdhead("Обзор проверки торрентов");
begin_main_frame();
print "<div align=\"center\" style=\"padding: 10px;\"><a href=\"modded.php\">Непроверенные</a> | <a href=\"modded.php?modded\">Проверенные</a> | <a href=\"modded.php?top\">Топ модераторов</a></div>";

if (isset($_GET["top"]))
{
  begin_frame("Топ модераторов (включая удаленные раздачи)");
  echo '<table width="100%" cellpadding="5"><tr><td class="colhead">№</td><td class="colhead">Модератор</td><td class="colhead">Проверил</td></tr>';
  $res = sql_query("SELECT id, username, class, moderated FROM users WHERE class >= ".UC_MODERATOR." AND moderated > 0 ORDER BY moderated DESC")  or sqlerr(__FILE__,__LINE__);
  if (!mysqli_num_rows($res))
      echo ("<tr><td colspan=\"3\">Нет статистики</td></tr>");
  else
  {
    $i=1;
    while ($row = mysqli_fetch_array($res))
    {
      echo '<tr><td>'.$i.'</td><td><a href="user/id'.$row["id"].'">'.get_user_class_color($row["class"], $row["username"]).'</a></td><td><a href="modded.php?moderator='.$row["id"].'">'.$row["moderated"].'</a></td></tr>';
      $i++;
    }
  }

  echo '</tr></table>';
  end_frame();
}

elseif (isset($_GET["modded"]))
{
  $count = number_format(get_row_count("torrents", "WHERE modded='yes'"));
  list($pagertop, $pagerbottom, $limit) = pager(15, $count, "modded.php?modded&");
  begin_frame("Проверенные торренты [$count]");
  echo '<table width="100%" cellpadding="5"><tr><td class="colhead">Торрент</td><td class="colhead">Загрузил</td><td class="colhead">Проверил</td><td class="colhead">Когда?</td></tr>';
  $res = sql_query("SELECT torrents.*, users.username, users.class FROM torrents LEFT JOIN users ON torrents.owner = users.id  WHERE modded = 'yes' ORDER BY torrents.modtime DESC $limit")  or sqlerr(__FILE__,__LINE__);
  if (!mysqli_num_rows($res))
      echo ("<tr><td colspan=\"4\">Нет проверенных торрентов</td></tr>");
  else
  {
    while ($row = mysqli_fetch_array($res))
      echo '<tr><td><a href="details/id'.$row["id"].'">'.htmlspecialchars($row["name"]).'</a></td><td><a href="user/id'.$row["owner"].'">'.get_user_class_color($row["class"], $row["username"]).'</a></td><td><a href="user/id'.$row["modby"].'">'.$row["modname"].'</a></td><td>'.$row["modtime"].'</td></tr>';
  }
  if ($count)
  {
    echo '<tr><td colspan="4">';
    echo $pagerbottom;
    echo '</td></tr>';
  }
  echo '</tr></table>';
  end_frame();
}

elseif (isset($_GET["moderator"]))
{
  $moderaror = unesc($_GET["moderator"]);
  $count = number_format(get_row_count("torrents", "WHERE modby = ".sqlesc($moderaror)));
  list($pagertop, $pagerbottom, $limit) = pager(25, $count, "modded.php?moderator=".unesc($_GET["moderator"])."&");
  $res = sql_query("SELECT users.id, users.username, users.class, torrents.modby FROM users LEFT JOIN torrents ON users.id = torrents.modby  WHERE torrents.modby = ".sqlesc($moderaror))  or sqlerr(__FILE__,__LINE__);
  $row = mysqli_fetch_array($res);
  begin_frame("Торренты, проверенные <a href=\"user/id".$row["id"]."\">".get_user_class_color($row["class"], $row["username"])."</a> [$count]");
 echo '<table width="100%" cellpadding="5"><tr><td class="colhead">Торрент</td><td class="colhead">Загрузил</td><td class="colhead">Проверен</td></tr>';
  $res = sql_query("SELECT torrents.*, users.username, users.class FROM torrents LEFT JOIN users ON torrents.owner = users.id  WHERE modby = ".sqlesc($moderaror)." ORDER BY torrents.modtime DESC")  or sqlerr(__FILE__,__LINE__);
  if (!mysqli_num_rows($res) || empty($moderaror))
      echo ("<tr><td colspan=\"4\">Не проверено ни одного торрента этим модератором</td></tr>");
  else
  {
    while ($row = mysqli_fetch_array($res))
      echo '<tr><td><a href="details/id'.$row["id"].'">'.htmlspecialchars($row["name"]).'</a></td><td><a href="user/id'.$row["owner"].'">'.get_user_class_color($row["class"], $row["username"]).'</a></td><td>'.$row["modtime"].'</td></tr>';
  }

  if ($count)
  {
    echo '<tr><td colspan="4">';
    echo $pagerbottom;
    echo '</td></tr>';
  }
  echo '</tr></table>';
  end_frame();
}

else
{
  $count = number_format(get_row_count("torrents", "WHERE modded='no'"));
  list($pagertop, $pagerbottom, $limit) = pager(15, $count, "modded.php?");
  begin_frame("Непроверенные торренты [$count]");
  echo '<table width="100%" cellpadding="5"><tr ><td class="colhead">Категория</td><td class="colhead">Торрент</td><td class="colhead">Загрузил</td><td class="colhead">Когда?</td></tr>';
  $res = sql_query("SELECT torrents.*,categories.name AS cat_name FROM torrents LEFT JOIN categories ON torrents.category = categories.id WHERE modded = 'no' ORDER BY torrents.id $limit")  or sqlerr(__FILE__,__LINE__);
  if (!mysqli_num_rows($res))
      echo ("<tr><td colspan=\"4\">Все торренты проверены</td></tr>");
  else
  {
    while ($row = mysqli_fetch_array($res))
					
      echo '<tr>
	  <td>'.$row['cat_name'].'</td>
	  <td><a href="details/id'.$row["id"].'">'.htmlspecialchars($row["name"]).'</a></td>
	  <td><a href="user/id'.$row["owner"].'">'.get_user_class_color($row["owner_class"], $row["owner_name"]).'</a></td>
	  <td>'.gmdate('Y-m-d H:i',$row['added'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60).'</td></tr>';
  }

  if ($count)
  {
    echo '<tr><td colspan="4">';
    echo $pagerbottom;
    echo '</td></tr>';
  }
  echo '</tr></table>';
  end_frame();
}
end_main_frame();
stdfoot();

?>
