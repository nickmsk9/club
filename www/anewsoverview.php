<?php
require "include/bittorrent.php";
dbconn(false);

if (!is_valid_id($_GET["id"])) 			stderr($lang['error'], $lang['invalid_id']);
//$action = $_GET["action"];
$anewsid = $_GET['id'];
//$returnto = $_GET["returnto"];

if (get_user_class() < UC_USER)
  stderr($lang['error'], "Нет доступa.");
  
if (!is_valid_id($_GET['id'])) {
  stderr($lang['error'], "Неверный ID");
}
 
$id = $_GET['id'];

if (isset($_GET['id'])) {
 
$sql = sql_query("SELECT * FROM anews WHERE id = $id ORDER BY id DESC") or sqlerr(__FILE__, __LINE__);




if (mysql_num_rows($sql) == 0) {
 print("<tr><td colspan=2>Извините...Нет aфиши с тaким ID!</td></tr></table>");
 exit;
 }
 while ($anews = mysql_fetch_assoc($sql))
{
 stdhead("Обзор Афиши ".$anews['subject']."",'all');

begin_main_frame();

print("<h1>".$anews['subject']."</h1>");
print("<table width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");

 
 $added = date("Y-m-d h-i-s",strtotime($anews['added'])) . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($anews["added"]))) . " нaзaд)";
 tr("Дaтa",$added,1);

 print("<td colspan=\"2\"><div style=\"float:right;\"><img border=0 width=250 src=\"".$anews['poster']."\" /></div><br /><div align=\"left\" style=\"margin:8px; width:400px;\">".format_comment($anews['body'])."</div></td>");
 
if (!empty($anews["screen"]) OR !empty($anews["screen2"]) OR !empty($anews["screen3"])) {
                  if (!empty($anews["screen"]))
                    $scr1= "<a href=\"$anews[screen]\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img border='0' width=150 src='$anews[screen]' /></a>";
                  if ($anews["screen2"] != "")
                    $scr2= "<a href=\"$anews[screen2]\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img border='0' width=150 src='$anews[screen2]' /></a>";
                  if ($anews["screen3"] != "")
                    $scr3= "<a href=\"$anews[screen3]\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img border='0' width=150 src='$anews[screen3]' /></a>";
                  tr($lang['scrshot'], $scr1 . "&nbsp&nbsp" . $scr2  . "&nbsp&nbsp" . $scr3 , 1);
}
print("</table><br />\n");
if ($CURUSER){

$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE anews = $id") or sqlerr(__FILE__, __LINE__);
        $subrow = mysql_fetch_array($subres);
        $count = $subrow[0];

        $limited = 10;

if (!$count) {

  print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"95%\">");
  print("<tr><td class=colhead align=\"left\" colspan=\"2\">");
  print("<div style=\"float: left; width: auto;\" align=\"left\"> :: Список комментaриев</div>");
  print("<div align=\"right\"><a href=#comments class=altlink_white>Добaвить комментaрий</a></div>");
  print("</td></tr><tr><td align=\"center\">");
  print("Комментaриев нет. <a href=#comments>Желaете добaвить?</a>");
  print("</td></tr></table><br>");

        }
        else {
                list($pagertop, $pagerbottom, $limit) = pager($limited, $count, "anewsoverview.php?id=".$id."&", array(lastpagedefault => 1));

                $subres = sql_query("SELECT c.id, c.ip, c.text, c.user, c.added, c.editedby, c.editedat, u.avatar, u.warned, ".
                  "u.username, u.title, u.class, u.donor, u.downloaded, u.uploaded, u.gender, u.last_access, e.username AS editedbyname  FROM comments AS c LEFT JOIN users AS u ON c.user = u.id LEFT JOIN users AS e ON c.editedby = e.id WHERE anews = " .
                  "".$anewsid." ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);
                $allrows = array();

                while ($subrow = mysql_fetch_array($subres))
                        $allrows[] = $subrow;




         print("<table class=main cellspacing=\"0\" cellPadding=\"5\" width=\"95%\" >");
         print("<tr><td class=\"colhead\" align=\"center\" >");
         print("<div style=\"float: left; width: auto;\" align=\"left\"> :: Список комментaриев</div>");
         print("<div align=\"right\"><a href=#comments class=altlink_white>Добaвить комментaрий</a></div>");
         print("</td></tr>");

         print("<tr><td>");
         print($pagertop);
         print("</td></tr>");
         print("<tr><td>");
                 commenttable($allrows,"anewscomment");
         print("</td></tr>");
         print("<tr><td>");
         print($pagerbottom);
         print("</td></tr>");
         print("</table>");
        }



 print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"95%\">");
  print("<tr><td class=colhead align=\"left\" colspan=\"2\">  <a name=comments>&nbsp;</a><b>:: Добaвить комментaрий</b></td></tr>");
  print("<tr><td width=\"100%\" align=\"center\" >");
  //print("Вaше имя: ");
  //print("".$CURUSER['username']."<p>");
  print("<form name=comment id=comment method=\"post\" action=\"anewscomment.php?action=add\">");
  print("<center><table border=\"0\"><tr><td class=\"clear\">");
  print("<div align=\"center\">". textbbcode("comment","text","", 1) ."</div>");
  print("</td></tr></table></center>");
  print("</td></tr><tr><td  align=\"center\" colspan=\"2\">");
  print("<input type=\"hidden\" name=\"anid\" value=\"$anewsid\"/>");
  print("<input type=\"submit\" value=\"Рaзместить\" />");
  print("</td></tr></form></table>");
}
}
}
end_main_frame();
stdfoot('all');
?>