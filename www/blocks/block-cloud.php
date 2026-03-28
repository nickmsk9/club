<?php
if (!defined('BLOCK_FILE')) {
 Header("Location: ../index.php");
 exit;
}
global $CURUSER,$lang;

getlang(could);
require_once("include/cloud_func.php");
$blocktitle = $lang['block_could'];
$content = flash_cloud('100%','350','7','15'); // Здесь 100% - ширина облака, 190 - высота, 8 и 12 диапазон размеров шрифтов в пикселах
?>
