<?

require "include/bittorrent.php";
dbconn(false);

loggedinorreturn();

// delete items older than a week
  $secs = 7 * 86400;
  stdhead("Логи");
 begin_main_frame();
if (get_user_class() < UC_MODERATOR) {
     stdmsg("Ошибка","Доступ в этот раздел закрыт!", error);
     stdfoot();
     die();
     }
  
//
//мод очистки логов
//
if (get_user_class() >= UC_ADMINISTRATOR)
{
$d_tracker = " [<a style='color: red' onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='red'\" onClick=\"return confirm('Вы уверены, что хотите очистить лог трекера?')\" title='Очистить лог!' href='log.php?type_clear=tracker'>D</a>]";
$d_bans = " [<a style='color: red' onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='red'\" onClick=\"return confirm('Вы уверены, что хотите очистить лог банов?')\" title='Очистить лог!' href='log.php?type_clear=bans'>D</a>]";
$d_release = " [<a style='color: red' onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='red'\" onClick=\"return confirm('Вы уверены, что хотите очистить лог релизов?')\" title='Очистить лог!' href='log.php?type_clear=release'>D</a>]"; 
$d_torrent = " [<a style='color: red' onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='red'\" onClick=\"return confirm('Вы уверены, что хотите очистить лог торрентов?')\" title='Очистить лог!' href='log.php?type_clear=torrent'>D</a>]"; 
$d_system = " [<a style='color: red' onmouseover=\"this.style.color='blue'\" onmouseout=\"this.style.color='red'\" onClick=\"return confirm('Вы уверены, что хотите очистить лог системы?')\" title='Очистить лог!' href='log.php?type_clear=system'>D</a>]"; 
}
else
{
$d_tracker = $d_bans = $d_release = $d_torrent = $d_system = "";
}

//получаем команду и проверяем класс
//если не Администратор, ошибка!
// Безопасно получаем параметр type_clear из $_GET
  $type_clear = isset($_GET["type_clear"]) ? htmlspecialchars($_GET["type_clear"]) : "";
if ($type_clear !=="" && (get_user_class() >= UC_ADMINISTRATOR))
{
  if($type_clear == "tracker")
  {print ("<h1 style='color: red'>лог трекера очищен</h1>");$_GET["type"] = "tracker";$logtype = "трекера";}
  elseif($type_clear == "bans")
  {print ("<h1 style='color: red'>лог банов очищен</h1>");$_GET["type"] = "bans";$logtype = "банов";}
  elseif($type_clear == "release")
  {print ("<h1 style='color: red'>лог релизов очищен</h1>");$_GET["type"] = "release";$logtype = "релизов";}
  elseif($type_clear == "torrent")
  {print ("<h1 style='color: red'>лог торрентов очищен</h1>");$_GET["type"] = "torrent";$logtype = "торрентов";}
  elseif($type_clear == "system")
  {print ("<h1 style='color: red'>лог системы очищен</h1>");$_GET["type"] = "system";$logtype = "системы";}  

$logdelluser = $CURUSER["username"];
//чистим лог:
sql_query("DELETE FROM sitelog WHERE type = ".sqlesc($type_clear)."") or sqlerr(__FILE__, __LINE__);
//и пишем в него данные об очистке:
write_log("Лог $logtype был очищен пользователем  $logdelluser","FFFF00","tracker");
}
elseif ($type_clear !=="" && (get_user_class() < UC_ADMINISTRATOR)) 
{
     stdmsg("Ошибка","Вы не имеете достаточных прав!", error);
     stdfoot();
     die();
}
//


// Безопасно получаем параметр type из $_GET
$type = isset($_GET["type"]) ? $_GET["type"] : "";
if(!$type || $type == 'simp') $type = "tracker";
   
     print("<center><hr>"  .
        ($type == 'tracker' || !$type ? "<b><u>Трекер</u></b>$d_tracker" : "<a href=log.php?type=tracker>Трекер</a>$d_tracker") . " | " .
         ($type == 'bans' ? "<b><u>Баны</u></b>$d_bans" : "<a href=log.php?type=bans>Баны</a>$d_bans") . " | " .
        ($type == 'torrent' ? "<b><u>Торренты</u></b>$d_torrent" : "<a href=log.php?type=torrent>Торренты</a>$d_torrent") . " | " .
        ($type == 'error' ? "<b><u>Система</u></b>$d_system" : "<a href=log.php?type=system>Система</a>$d_system") . "</center><hr>\n");


if ($type == "tracker")$printtype = "трекера";
if ($type == "bans")$printtype = "банов";
if ($type == "torrent")$printtype = "торрентов";
if ($type == "system")$printtype = "система";

//конец мода
//некоторые изменения - есть и ниже

  sql_query("DELETE FROM sitelog WHERE " . gmtime() . " - UNIX_TIMESTAMP(added) > $secs") or sqlerr(__FILE__, __LINE__);
  $limit = ($type == 'announce' ? "LIMIT 1000" : "");
  $res = sql_query("SELECT txt, added, color FROM `sitelog` WHERE type = ".sqlesc($type)." ORDER BY `added` DESC $limit") or sqlerr(__FILE__, __LINE__);
  print("<h1>Логи $printtype</h1>\n");
  if (mysqli_num_rows($res) == 0)
    print("<b>Лог файл пустой</b>\n");
  else
  {
    print("<table border=0 align=center cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=colhead align=left>Дата</td><td class=colhead align=left>Время</td><td class=colhead align=left>Событие</td></tr>\n");
    while ($arr = mysqli_fetch_assoc($res))
    {
      $date = substr($arr['added'], 0, strpos($arr['added'], " "));
      $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
      print("<tr style=\"background-color: #$arr[color]\"><td>$date</td><td>$time</td><td align=left>".format_comment(htmlspecialchars($arr['txt']))."</td></tr>\n");
    }
    print("</table>");
  }
  end_main_frame();
  stdfoot();
?> 