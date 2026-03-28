 <?php
 //Srinivas Tamada http://9lessons.info
//Load latest update 
require_once 'include/bittorrent.php';

dbconn();
include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';
$Wall = new Wall_Updates();
if(isSet($_POST['webcam']))
{
$newdata=$Wall->Get_Upload_Image($CURUSER["id"],0);
echo "<img src='uploads/".$newdata['image_path']."'  class='webcam_preview' id='".$newdata['id']."'/>";
}
?>
