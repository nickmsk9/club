 <?php
 //Srinivas Tamada http://9lessons.info
//Load latest update 
require_once 'include/bittorrent.php';

dbconn();

include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';

$Wall = new Wall_Updates();
if(isSet($_POST['msg_id']))
{
$msg_id=mysql_real_escape_string($_POST['msg_id']);
$data=$Wall->Delete_Update($CURUSER["id"],$msg_id);
echo $data;

}
?>
