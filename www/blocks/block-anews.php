<?php
if (!defined('BLOCK_FILE')) {
 Header("Location: ../index.php");
 exit;
}

global $lang;

$blocktitle = "Афиша".(get_user_class() >= UC_UPLOADER ? "<font class=\"small\"> - [<a class=\"altlink\" href=\"anews.php\"><b>".$lang['create']."</b></a>]</font>" : "");

if (cache_check("anews", 600)) {
    $resource = cache_read("anews");
} else {
    $result = sql_query("SELECT poster, id, body, subject, added FROM anews GROUP BY id ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    $resource = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $resource[] = $row;
    }
    cache_write("anews", $resource);
}

if ($resource) {
    $content = "";
    foreach($resource as $array) {
        $descr = $array['body'];
        $subject = htmlspecialchars($array['subject']);
        $poster = htmlspecialchars($array['poster']);
        $anid = (int)$array['id'];
        if (strlen($descr) > 800)   
            $descr = substr($descr, 0, 800) . "..."; 
$content .=
     "<table width=100% border=0 cellspacing=0 cellpadding=0 ><tr><td style=\"padding-left:15px; padding-bottom:7px;\" id=\"no_border\">
	  <h2>".$subject."</h2></td></tr>
	  	  <tr><td id=\"no_border\"><div style=\"float:right;\"><img border='0' width=250 src=".$poster." /></div>

	  <div align=\"left\" style=\"margin:8px; width:400px;\">".format_comment($descr)."</div></td></tr>";
      $content .="<tr><td id=\"no_border\"><div align=\"right\" style=\"padding-top:5px;\">";
      			if (get_user_class() >= UC_ADMINISTRATOR) {
		        $content .= "[<a href=\"anews.php?action=edit&anewsid=" . $anid . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "\"><b>E</b></a>]";
		        $content .= "[<a onclick=\"return confirm('Вы уверены?');\" href=\"anews.php?action=delete&anewsid=" . $anid . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "\"><b>D</b></a>] ";
      }
      			$content .= " [<a href=\"anewsoverview.php?id=".$anid."\"><b>Читать далее...</b></a>]</div>";
      $content .= "</div><hr /></td></tr></table><br /><br />";

	}
	$content .= "<p align=\"right\">[<a href=\"anewsarchive.php\">Архив новостей</a>]</p></ul></td></tr>\n";
} else {
	$content .= "<table class=\"main\" align=\"center\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\">";
	$content .= "<div align=\"center\"><h3>".$lang['no_news']."</h3></div>\n";
	$content .= "</td></tr>";
}

?>