<?php
function makesafe($text) {
   return strip_tags($text);
} 
require "include/bittorrent.php";
dbconn(false);

	include('rssatom/rssatom.php');
	$feeds=new FeedGenerator;
	$feeds->setGenerator(new RssGenerator); # or AtomGenerator
	$feeds->setAuthor($ADMINEMAIL." (Site Admin)");
	$feeds->setTitle($SITENAME);
	$feeds->setChannelLink($DEFAULTBASEURL."/rss.php");
	$feeds->setLink($DEFAULTBASEURL);
	$feeds->setDescription($SITENAME." - новости RSS 2.0");
	$feeds->setID($DEFAULTBASEURL."/rss.php");

$res = sql_query("SELECT descr,image1,id,name,size,category,added FROM torrents WHERE visible='yes' AND modded = 'yes' ORDER BY added DESC LIMIT 25") or sqlerr(__FILE__, __LINE__);

while ($row = mysql_fetch_assoc($res)) {
$items=true;

$content='<table width="100%" border="1"><tr><td valign="top"><img src="'.$row['image1'].'" width="250" title="'.makesafe($row['name']).'"></td><td>'.format_comment($row['descr']).'</td></tr></table>';
$feeds->addItem(new FeedItem($DEFAULTBASEURL."/details/id{$row['id']}", $row['name'], $DEFAULTBASEURL."/details/id{$row['id']}", $content));

}
if (!$items)
$feeds->addItem(new FeedItem($DEFAULTBASEURL,$lang['error'], $DEFAULTBASEURL,$lang['no_torrents']));

	$feeds->display();
	
	?>