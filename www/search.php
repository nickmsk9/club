<?php
require "include/bittorrent.php";

gzip();

dbconn();

stdhead("Поиск по сайту");

begin_main_frame();

begin_frame(); ?>

<div id="yandex-results-outer" onclick="return {encoding: 'utf-8'}"></div><script type="text/javascript" src="http://site.yandex.net/load/site.js" charset="utf-8"></script>

<?php 

end_frame();

end_main_frame();

stdfoot(); 
?>