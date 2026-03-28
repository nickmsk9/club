<?php
//made by putyn @ tbade.net Monday morning :]
require_once("include/bittorrent.php");
dbconn();

	$id = isset($_GET["id"]) ? 0+$_GET["id"] : 0;
	$rate = isset($_GET["rate"]) ? 0+$_GET["rate"] : 0;
	$uid = $CURUSER["id"];
	$ajax = isset($_GET["ajax"]) && $_GET["ajax"] == 1 ? true : false ;
	$what = isset($_GET["what"]) && $_GET["what"] == "torrent" ? "torrent" : "topic";
	$ref  = isset($_GET["ref"]) ? $_GET["ref"] : ($what == "torrent" ? "browse.php" : "forum.php");
	
	
	if($id > 0 && $rate >= 1 && $rate <= 5 ) {
		if(mysql_query("INSERT INTO rating(".$what.",rating,user) VALUES ($id,$rate,$uid)")) {
			if($ajax) {
				$q = mysql_query("SELECT sum(r.rating) as sum, count(r.rating) as count, r2.rating as rate FROM rating as r LEFT JOIN rating AS r2 ON (r2.".$what." = ".$id." AND r2.user = ".$uid.") WHERE r.".$what." = ".$id." GROUP BY r.".$what." ") or sqlerr();
				$a = mysql_fetch_assoc($q);
				print("<ul class=\"star-rating\" title=\"Ваша оценка ".$a["rate"]." бал".($a["rate"] >1 ? "ов" : "")."\"  ><li style=\"width: " .(round((($a["sum"] / $a["count"]) * 20), 2)). "%;\" class=\"current-rating\" />.</ul>");
			} 
			else {
				header("Refresh: 2; url=".$ref);
				stderr("Success","Your rate has been added, wait while redirecting! ");
			}
			$table = ($what == "torrent" ? "torrents" : "topics");
			mysql_query("UPDATE ".$table." SET numratings = numratings + 1, ratingsum = ratingsum+".$rate." WHERE id = ".$id) or exit(mysql_error());
		} else {
			if(mysql_errno() == 1062 && $ajax)
				print ("You already rated this ".$what."");
			elseif(mysql_error() && $ajax)
				print ("You cant rate twice, Err - ".mysql_error());
			else
				stderr("Err","You cant rate twice, Err - ".mysql_error());
		}
	}
?>