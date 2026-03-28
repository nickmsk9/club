<?php


if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
       header("HTTP/1.1 304 Not Modified");
       exit;
}


include_once (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
$logPath = dirname(__FILE__).'/../logs/cometchat_debug.log';
file_put_contents($logPath, date("Y-m-d H:i:s")." [CSS] Загружен cometchatcss.php\n", FILE_APPEND);

if (BAR_DISABLED == 1) { exit; }

$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;

if (phpversion() >= '4.0.4pl1' && (strstr($useragent,'compatible') || strstr($useragent,'Gecko'))) {
	if (extension_loaded('zlib') && GZIP_ENABLED == 1) {
		ob_start('ob_gzhandler');
	}
}

header('Content-type: text/css;');
header("Last-Modified: ".gmdate("D, d M Y H:i:s", time() - 3600*24*365)." GMT");
header('Expires: '.gmdate("D, d M Y H:i:s", time() + 3600*24*365).' GMT');

if (!isset($rtl) || !isset($theme)) {
    file_put_contents($logPath, date("Y-m-d H:i:s")." [CSS] Ошибка: переменные \$rtl или \$theme не заданы\n", FILE_APPEND);
    exit;
}

if ($rtl == 1) {
	include_once (dirname(__FILE__)."/themes/".$theme."/css/cometchat_rtl.css");
} else {
	include_once (dirname(__FILE__)."/themes/".$theme."/css/cometchat.css");
}

