<?php
require_once("include/bittorrent.php");
dbconn();
stdhead('Чат');
begin_main_frame();

?>
<iframe src="/torrentchat/modules/chatrooms/index.php?id=2" width="100%" height="500px" frameborder="0" ></iframe>
<?

end_main_frame();
stdfoot();
?>