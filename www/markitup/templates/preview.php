<?php
require "/include/bittorrent.php";

$text = $_POST['data'];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>markItUp! preview template</title>
<link rel="stylesheet" type="text/css" href="~/templates/preview.css" />
</head>
<body>
<?
echo format_comment($text);
?>
</body>
</html>
