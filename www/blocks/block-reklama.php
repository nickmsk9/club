<?php

if (!defined('BLOCK_FILE')) {
header("Location: ../index.php");
exit;
}
srand((double)microtime()*1000000);
$rnd = rand(1,3);


$content="<center><noindex><a href=\"http://www.urban-rivals.com/landing/video/?from=ruanimeclub&locale=ru\" rel=\"nofollow\" target=\"_blank\" title=\"Онлайн игра с элементами аниме\">Онлайн игра с элементами аниме<br/><img src=\"/banner/".$rnd.".gif\" alt=\"Онлайн игра с элементами аниме\" width=\"850px\" border=\"0px\"></a></noindex></center>";

?>