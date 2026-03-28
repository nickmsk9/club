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

require_once 'include/bittorrent.php';
if (!isset($tracker_lang) || !isset($tracker_lang['language_charset'])) {
    $lang_dir = __DIR__ . '/languages/lang_russian/';
    foreach (glob($lang_dir . 'lang_*.php') as $lang_file) {
        require_once($lang_file);
    }
}
dbconn(false);
loggedinorreturn();

if (!isset($tracker_lang['language_charset'])) {
    $tracker_lang['language_charset'] = 'utf-8';
}
header("Content-Type: text/html; charset=".$tracker_lang['language_charset']);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
{
	$do = ( isset($_REQUEST['do']) ? strip_tags(trim($_REQUEST['do'])) : '' );
	$torrentid = isset($_REQUEST['tid']) && is_numeric($_REQUEST['tid']) ? intval($_REQUEST['tid']) : 0;
	
	switch( $do )
	{
		case 'show_thanks' :
		
		  if( $torrentid != 0 ) {
		      $thanksby = '';
			  list($count) = @mysqli_fetch_row(sql_query("SELECT COUNT(*) FROM thanks WHERE torrentid = $torrentid")) or sqlerr(__FILE__,__LINE__);

	          if ($count <= 0) {
		        $thanksby = '<i>Еще никто не поблагодарил</i>';
	          } else { 
			    $thanked_sql = sql_query("SELECT thanks.userid, thanks.added, users.username, users.class FROM thanks INNER JOIN users ON thanks.userid = users.id WHERE thanks.torrentid = $torrentid ORDER BY thanks.id ASC") or sqlerr(__FILE__,__LINE__);
			  
			  if($thanked_sql && mysqli_num_rows($thanked_sql) > 0) {
		      while ($thanked_row = mysqli_fetch_assoc($thanked_sql)) {
			        $userid   = intval($thanked_row['userid']);
			        $username = htmlspecialchars($thanked_row['username']);
			        $class    = intval($thanked_row['class']);
			        // Generate output html //
					if($thanked_row['added'] != '0000-00-00 00:00:00') {
						$date = date( 'Y.m.d', strtotime($thanked_row['added']));
					} else {
						$date = 'N/A';
					}
				    $thanksby .= "".get_user_class_color($class, $username)." , ";
			  }
			  } else {
				  $thanksby = '<i>Еще никто не поблагодарил</i>';
			  }
			  }
		// Output html //	  
		if (!empty($thanksby))
			echo "<div style=\"margin-bottom:5px;\"><a href=\"javascript:;\" onClick=\"SE_HideThanks('{$torrentid}')\" class=\"show_thanks\">Скрыть список поблагодаривших</a></div>{$thanksby}";
		  }
		  
		break;
		case 'send_thanks' :
		if( $torrentid)
		{
		  $userid     = intval($CURUSER['id']);
		  $count_author = sql_query("SELECT owner FROM torrents WHERE id = $torrentid LIMIT 1") or sqlerr(__FILE__,__LINE__);
		  $author_row = mysqli_fetch_array($count_author);
		  $is_author  = ($author_row['owner'] == $userid ? true : false);
		  $count_sql  = sql_query("SELECT COUNT(*) FROM thanks WHERE torrentid = $torrentid AND userid = $userid");
	      $count_row  = mysqli_fetch_row($count_sql);
	      $count      = intval($count_row['0']);
		  	  
		  if($count <= 0 && !$is_author) {  
		  sql_query("INSERT INTO thanks (`torrentid`, `userid`, `added`, `touserid`) VALUES ($torrentid, $userid, ".sqlesc(date( 'Y-m-d H:i:s ') ).", $author_row[owner])") or sqlerr(__FILE__,__LINE__);
		  }
		}
		
		break;
		case 'remove_thanks' :
		 $userid = intval($CURUSER['id']);
		 list($count) = mysqli_fetch_row(sql_query("SELECT COUNT(*) FROM thanks WHERE userid = $userid AND torrentid = $torrentid LIMIT 1")) or sqlerr(__FILE__,__LINE__);
		 
		 if($count > 0)
		 {
			 sql_query("DELETE FROM thanks WHERE torrentid = $torrentid AND userid = $userid LIMIT 1") or sqlerr(__FILE__,__LINE__);
		 }
	
		break;
	}
}
?>