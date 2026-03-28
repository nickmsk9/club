<?php
/*
///////////////////////////////////////////////////////////////////////////////
//
// [AJAX] Thanks mod by Strong v0.2Stable jQuery v1.3.2 [END]
// -----------------------------------------------------------------------------
// UPD: Добавлена возможность удалять свою благодарность (v0.2)
//
///////////////////////////////////////////////////////////////////////////////
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'include/bittorrent.php';
dbconn(false);
global $CURUSER, $tracker_lang;

$charset = isset($tracker_lang['language_charset']) ? $tracker_lang['language_charset'] : 'utf-8';
header("Content-Type: text/html; charset=" . $charset);
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
{
	$do = ( isset($_REQUEST['do']) ? strip_tags(trim($_REQUEST['do'])) : '' );
	$aid = isset($_POST['aid']) && is_numeric($_POST['aid']) ? intval($_POST['aid']) : 0;
	
	switch( $do )
	{

		case 'send_vote' :
		if( $aid)
		{
		  $userid     = intval($CURUSER['id']);
		  $count_author = sql_query("SELECT uid FROM konkurs WHERE id = $aid LIMIT 1") or sqlerr(__FILE__,__LINE__);
		  $author_row = mysqli_fetch_array($count_author);
		  $is_author  = ($author_row['uid'] == $userid ? true : false);
		  $count_sql  = sql_query("SELECT COUNT(*) FROM vote WHERE anketid = $aid AND userid = $userid");
	      $count_row  = mysqli_fetch_row($count_sql);
	      $count      = intval($count_row['0']);
		  	  
		  if($count == 0 && !$is_author) {  
		  sql_query("INSERT INTO vote (`userid`, `anketid`) VALUES (".sqlesc($userid).", ".sqlesc($aid)." )")or sqlerr(__FILE__,__LINE__);
		  
		  }
		}
		echo "OK";
		exit;
		break;

	}
}
else {
    echo "Invalid request or not AJAX.";
}
?>