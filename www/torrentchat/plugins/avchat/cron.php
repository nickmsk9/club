<?php

$time = getTimeStamp()-7200;
$sql = ("delete from cometchat_videochatsessions where timestamp < $time");
$query = mysql_query($sql);