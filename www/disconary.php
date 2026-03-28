<?php
include_once("include/bittorrent.php");
dbconn();

	$act = (string)$_GET['act'];
	$id = (int)$_GET['id'];
	$letter = (string)$_GET['letter'];
	
	
	stdhead();
	switch($act){
	case 'add':
		begin_main_frame();
		begin_frame('Добавить новое');
		echo "<form name='add_new' method='post' id='add_new' action='' enctype=multipart/form-data>";
		echo "<td>Слово (англ.)<input type='text' name='word_en' size='70'></td>";
		echo textbbcode('adddescr','text','',1);
		echo "</form>";
		end_frame();
		end_main_frame();
	break;
	
	
	}
	stdfoot();