<?php
if (!defined('BLOCK_FILE')) {
 Header("Location: ../index.php");
 exit;
}
global $CURUSER ,$lang;
$blocktitle = "Посетители за сегодня";

$todayactive = "";
$usersactivetoday = 0;

if (cache_check("record", 600)) {
    $res = cache_read("record");
} else {
    $query = "SELECT id, gender, username, class FROM users WHERE UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(?) AND hiden = 'no'";
    $now = get_dt_num();
    $today = date("Ymd000000");
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $now, $now, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $res = [];
    while ($row = $result->fetch_assoc()) {
        $res[] = $row;
    }
    cache_write("record", $res);
}

foreach ($res as $arr) 
{  

 

    if ($todayactive) 
        $todayactive .= ", "; 

    $uid = (int)$arr["id"];
    $uname = get_user_class_color($arr["class"], $arr["username"]);
    $todayactive .= "<a href='user/id$uid'>$uname</a>";
   
    $female = $arr["gender"] == "2"; 
     if ($female){ 
$todayactive .= "<img alt=\"Девушка\" src=\"pic/ico_f.gif\">"; 
} 
    $male = $arr["gender"] == "1"; 
     if ($male){ 
$todayactive .= "<img alt=\"Парень\" src=\"pic/ico_m.gif\">"; 
}

    $usersactivetoday++; 
} 

$content .= "<br><center>
<table class=\"main\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\" width=\"100%\"><tr><td class=\"embedded\">"
."<h3>".$usersactivetoday." человек посетили сегодня наш сайт .</h3> <hr>"
."<div align='left'>".$todayactive."</div><hr>"
."</td></tr></table></center>";  




?>