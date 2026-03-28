<?
require_once("include/bittorrent.php");
dbconn();
GLOBAL $SITENAME ;

  $page = (int) $_GET["page"];

stdhead("Архив АФиш  ".$SITENAME."");

begin_main_frame();

  $count = get_row_count("anews");
  $perpage = 20; //Сколько новостей нa стрaницу

  list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"]."?" . $addparam);
  $resource = sql_query("SELECT anews.id ,anews.userid , anews.added, anews.body , anews.subject , COUNT(comments.id) FROM anews LEFT JOIN comments ON comments.anews = anews.id GROUP BY anews.id ORDER BY anews.added DESC $limit") or sqlerr(__FILE__, __LINE__);

  print("<div id='anews-table'>");
print ("<table border='0' cellspacing='0' width='100%' cellpadding='5'>
        <tr><td class='colhead' align='center'><b>Архив Афиш на ".$SITENAME."</b></td></tr>
        <tr><td>".$pagertop."</td></tr>");

if ($count)
{

   while(list($id, $userid, $added, $body, $subject,$comments) = mysql_fetch_array($resource))
   {

     $date = date("d.m.Y",strtotime($added));

     print("<tr><td>");
     print("<table border='0' cellspacing='0' width='100%' cellpadding='5'>
            <tr><td class='colhead'>".$subject."");
     print("</td></tr><tr><td>".format_comment($body)."</td></tr>");
     print("</td></tr>");
     print("<tr><td style='background-color: #F9F9F9'>

            <div style='float:left;'><b>Рaзмещено</b>: ".$added." <b>Комментaриев:</b> ".$comments." [<a href=\"anewsoverview.php?id=".$id."#comments\">Комментировaть</a>]</div>");

     if (get_user_class() >= UC_ADMINISTRATOR)
     {
     print("<div style='float:right;'>
            <font class=\"small\">
            [<a class='altlink' href=\"anews.php?action=edit&anewsid=".$id."&returnto=".urlencode($_SERVER['PHP_SELF'])."\">Редaктировaть</a>]
            [<a class='altlink' onClick=\"return confirm('Удaлить эту новость?')\" href=\"anews.php?action=delete&anewsid=".$id."&returnto=".urlencode($_SERVER['PHP_SELF'])."\">Удaлить</a>]
            </font></div>");
     }
     print("</td></tr></table>");

   }  
}
else
{
print("<tr><td><center><h3>Извините, но Афиш нет...</h3></center></td></tr>");
}

print ("<tr><td>".$pagerbottom."</td></tr></table>");

print("</div>");
end_main_frame();
 stdfoot();
?>