<?php 
if (!defined('BLOCK_FILE')) { 
 Header("Location: ../index.php"); 
 exit; 
} 




$users = array();
$currentdate = date("m-d");
if (cache_check("bd", 600)) {
    $res = cache_read("bd");
} else {
    $query = "SELECT id, username, class, warned, gender, enabled, parked, donor FROM users WHERE birthday LIKE ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Ошибка подготовки запроса в block-bd: " . $mysqli->error . "\n", 3, __DIR__ . "/logs/block-bd.log");
        $res = [];
    } else {
    $like = "%-" . $currentdate;
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $res = [];
    while ($row = $result->fetch_assoc()) {
        $res[] = $row;
    }
    }
    cache_write("bd", $res);
}

$content = "";
foreach($res as $arr) 
{
    $username = get_user_class_color($arr["class"], $arr["username"]). get_user_icons($arr,false);
    $id = (int)$arr["id"];
    $users[] = "<a href='user/id$id'>" . $username . "</a>";
}
if (count($users) === 0) {
    $content = "Никто сегодня не родился";
} else {
    $content = implode(", ", $users);
}




?> 
