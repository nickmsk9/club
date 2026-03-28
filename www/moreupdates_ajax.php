
 <?php
 //Srinivas Tamada http://9lessons.info
//Load latest comment 
require_once 'include/bittorrent.php';

dbconn();
include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';
include_once 'include/htmlcode.php';
include_once 'include/textlink.php';
include_once 'include/Expand_URL.php';

$Wall = new Wall_Updates();
if(isSet($_POST['lastid']))
{
$lastid=mysql_real_escape_string($_POST['lastid']);
$lastmsg=mysql_real_escape_string($lastmsg);
include('load_messages.php');
}
?>
