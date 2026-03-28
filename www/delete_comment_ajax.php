 <?php
 //Srinivas Tamada http://9lessons.info
//Load latest update 
require_once 'include/bittorrent.php';

dbconn();
include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';

$Wall = new Wall_Updates();
if(isSet($_POST['com_id']))
{
$com_id=mysql_real_escape_string($_POST['com_id']);
$data=$Wall->Delete_Comment($CURUSER["id"],$com_id);
echo $data;

}
?>
