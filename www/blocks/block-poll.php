<?
if (!defined('BLOCK_FILE')) {
 Header("Location: ../index.php");
 exit;
}
//$cacheStatFile = "cache/polls/block-polls-".$CURUSER[id].".txt"; 
global $CURUSER , $lang, $ss_uri;


$blocktitle = $lang['poll'];

if ($CURUSER)
{
?>
<script type="text/javascript" src="js/poll.core.js"></script>
<link href="js/poll.core.css" type="text/css" rel="stylesheet" />
<script type="text/javascript">jQuery(document).ready(function(){loadpoll();});</script>
<?
 $content.="
<table width=\"95%\" class=\"main\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\">
<tr>
<td class=\"text\" align=\"center\">
<div id=\"poll_container\">
<div id=\"loading_poll\" style=\"display:none\"></div>
<noscript>
<b>Пожалуйста включите показ скриптов</b>
</noscript>
</div>
<br/>
</td>
</tr>
</table> ";
}
else
{
$content .= "<table class=\"main\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\" style=\"border:none;\"><tr><td class=\"text\">";
$content .= "<div align=\"center\"><h3>Для гостя опроссы отключенны</h3></div>\n";
$content .= "</td></tr></table>";
}

?>