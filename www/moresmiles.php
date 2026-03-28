<?php
require_once "include/bittorrent.php";
global $lang;
dbconn(false);
loggedinorreturn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смайлики</title>
    <script>
    function SmileIT(privatesmile, form, text) {
        let el = window.opener.document.forms[form].elements[text];
        el.value = el.value + " " + privatesmile + " ";
        el.focus();
        window.close(); // Автоматическое закрытие окна
    }
    </script>
</head>
<body>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<tr align="center">
<?php
$count = 0;
global $privatesmilies;
foreach ($privatesmilies as $code => $url) {
    if ($count % 6 === 0) echo "<tr>\n";

    $codeEsc = str_replace("'", "\\'", $code);
    $form = htmlentities($_GET["form"]);
    $text = htmlentities($_GET["text"]);

    echo "<td align=\"center\">";
    echo "<a href=\"javascript:SmileIT('{$codeEsc}', '{$form}', '{$text}')\">";
    echo "<img border=\"0\" src=\"pic/smilies/{$url}\" alt=\"{$code}\" />";
    echo "</a></td>";

    $count++;

    if ($count % 6 === 0) echo "</tr>\n";
}
if ($count % 6 !== 0) echo "</tr>\n";
?>
</table>
</body>
</html>