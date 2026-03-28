<?php

include dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."config.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
global $CURUSER;
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
	include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
}

if ($rtl == 1) {
	$rtl = "_rtl";
} else {
	$rtl = "";
}

if (!file_exists(dirname(__FILE__)."/themes/".$theme."/announcements".$rtl.".css")) {
	$theme = "default";
}

$extra = "";

if (!empty($userid)) {
	$extra = "`to` = '".mysql_real_escape_string($userid)."'";
}

$sql = ("select id,announcement,time,`to` from cometchat_announcements where ".$extra." order by id desc limit ".$noOfAnnouncements);
$query = mysql_query($sql);
if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysql_error(); }

$announcementdata = '';

while ($announcement = mysql_fetch_array($query)) {
    
	$time = gmdate("d.m H:i",$announcement['time'] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);

	
	$class = 'highlight';

	if ($announcement['to'] == 0 || $announcement['to'] == -1) {
		$class = '';
	}

	$announcementdata .= <<<EOD
		<li class="announcement"><span class="{$class}">{$announcement['announcement']}</span><br/><small>{$time}</small></li>
EOD;
}

if (empty($announcementdata)) {
	$announcementdata = '<li class="announcement">'.$announcements_language[0].'</li>';
}

echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="-1">
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/> 
<link type="text/css" rel="stylesheet" media="all" href="themes/{$theme}/announcements{$rtl}.css" /> 
</head>
<body>
<div style="width:100%;margin:0 auto;margin-top: 0px;">
<div class="container">
<div style="float:left;width: 100%; height: 300px;overflow:auto">
<ul>
<ul>{$announcementdata}</ul>
</ul>
</div>
<div style="clear:both">&nbsp;</div>
</div>
</body>
</html>
EOD;
?>