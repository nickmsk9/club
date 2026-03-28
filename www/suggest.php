<?php
require_once("include/bittorrent.php");
getlang();
gzip();
dbconn(false);
header ("Content-Type: text/html; charset=" . $lang['language_charset']);

if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER["REQUEST_METHOD"] == 'GET') {
    $q = trim(strip_tags(iconv('utf-8', $lang['language_charset'], base64_decode($_GET["q"]))));
    if (empty($q) || strlen($q) < 3) {
      die();
    }
    $res = sql_query("SELECT t.id, t.name, t.added, t.free, t.category, c.name AS cat_name, c.image AS cat_pic FROM torrents t LEFT JOIN categories c ON t.category = c.id WHERE t.name LIKE " . sqlesc("%$q%") . " ORDER BY id DESC LIMIT 0,10;") or sqlerr(__FILE__, __LINE__);
    print("<div class=\"kubas\" style=\"position:absolute;width: 857px; left: -50px\">\n");
    print("<table width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td style=\"padding : 7px 7px 5px 7px; background: transparent url(pic/transpx.png) repeat scroll 0% 0%;\"><table style=\"border:1px solid #FFFFFF;width:100%;margin:0 auto 10px;\">");
    print("<tr><td style=\"background-color : #cdcdcd; padding:7px 3px;\"><span style=\"color:#666666; font-size:12pt\">Результаты быстрого поиска</span></td><td width=\"20\" align=\"right\" style=\"background-color : #CDCDCD;  padding:7px 3px;\"><a onclick=\"javascript: jQuery('#suggest').hide();\"><img src=\"pic/cross.png\" border=0 alt=\"Скрыть\"></a></td></tr>\n");
    if(mysql_num_rows($res) < 1) {
       print("<tr style=\"background-color:#fffffc;\"><td style=\"border-bottom:1px solid #e5e5e5;padding:5px 3px;font-size:.9em;\" colspan=\"2\">Поиск не дал результатов</td></tr>\n");
       die();
    }
    else {
        $i = 1;
        while ($row = mysql_fetch_array($res)) {
          print("<tr style=\"background-color:#FAFAFA\"><td style=\"border-bottom:1px solid #e5e5e5;padding:5px 3px;font-size:.9em;\" colspan=\"2\" width=\"1%\">
          <table width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td width=40><a href=\"browse.php?cat=" . $row["category"] . "\">
          <img src=\"pic/cats/" . $row["cat_pic"] . "\" title=\"" . $row["cat_name"] . "\" style=\"height:40px;border:none;\" /></a></td>
          <td style=\"padding:0px 10px;\"><a href=\"details/id".$row['id']."-".friendly_title($row['name'])."\">".preg_replace("#($q)#siu", "<span style=\"color: #FF0000\">\\1</span>", $row['name'])."</a><br /><small>Добавлен: " . gmdate('Y-m-d H:i',$row['added'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60) . "</small></td></tr></table></td></tr>\n");
          $i++;
        }
    }
    print("</table></td></tr></table>\n");
    print("</div>\n");
    die();
}
else
    die("Прямой доступ закрыт.");
?> 