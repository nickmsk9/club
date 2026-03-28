<?php
require_once "include/bittorrent.php";
global $lang, $privatesmilies2;
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
        const field = window.opener.document.forms[form].elements[text];
        field.value += " " + privatesmile + " ";
        field.focus();
        window.close(); // Закрытие окна после выбора
    }
    </script>
</head>
<body>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<tr align="center">
<?php
$count = 0;
$form = htmlentities($_GET["form"]);
$text = htmlentities($_GET["text"]);

foreach ($privatesmilies2 as $code => $url) {
    if ($count % 6 === 0) echo "<tr>\n";

    $escapedCode = str_replace("'", "\\'", $code);

    echo "<td align=\"center\">";
    echo "<a href=\"javascript:SmileIT('{$escapedCode}', '{$form}', '{$text}')\">";
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