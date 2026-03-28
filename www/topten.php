<?php
require "include/bittorrent.php";

gzip();

dbconn(false);

loggedinorreturn();

  function usertable($res, $frame_caption)
  {
  	global $CURUSER;
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>
<td class=colhead align=center>Место</td>
<td class=colhead align=center>Пользователь</td>
<td class=colhead align=center >Раздал</td>
<td class=colhead align=center>Скорость раздачи</td>
<td class=colhead align=center>Скачал</td>
<td class=colhead align=center>Скорость скачивания</td>
<td class=colhead align=center>Рейтинг</td>
<td class=colhead align=center>Зарегистрирован</td>

</tr>
<?
    $num = 0;
    while ($a = mysqli_fetch_assoc($res))
    {
      ++$num;
      $highlight = $CURUSER["id"] == $a["userid"] ? " bgcolor=#BBAF9B" : "";
      if ($a["downloaded"])
      {
        $ratio = $a["uploaded"] / $a["downloaded"];
        $color = get_ratio_color($ratio);
        $ratio = number_format($ratio, 2);
        if ($color)
          $ratio = "<font color=$color>$ratio</font>";
      }
      else
        $ratio = "Inf.";
      print("<tr$highlight><td align=center>$num</td><td align=left$highlight><a href=user/id" .
      		$a["userid"] . "><b>" .get_user_class_color( $a['class'],$a["username"]) . "</b>" .
      		"</td><td align=right$highlight>" . mksize($a["uploaded"]) .
					"</td><td align=right$highlight>" . mksize($a["upspeed"]) . "/s" .
         	"</td><td align=right$highlight>" . mksize($a["downloaded"]) .
      		"</td><td align=right$highlight>" . mksize($a["downspeed"]) . "/s" .
      		"</td><td align=right$highlight>" . $ratio .
      		"</td><td align=left>" . date("Y-m-d",strtotime($a["added"])) . " (" .
      		get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])) . " назад)</td></tr>");
    }
    end_table();
    end_frame();
  }

  function _torrenttable($res, $frame_caption)
  {
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>

<td class=colhead align=left>Название</td>
<td class=colhead align=right>Скачено</td>
<td class=colhead align=right>Раздающих</td>
<td class=colhead align=right>Качающих</td>
<td class=colhead align=right>Всего</td>

</tr>
<?
  
    while ($a = mysqli_fetch_assoc($res))
    {
    

      print("<tr><td align=left><a href=details/id" . $a["id"] . "&hit=1><b>" .
        $a["name"] . "</b></a></td><td align=right>" . number_format($a["times_completed"]) .
				"</td><td align=right>" . number_format($a["seeders"]) .
        "</td><td align=right>" . number_format($a["leechers"]) . "</td><td align=right>" . ($a["leechers"] + $a["seeders"]) .
        "</td>\n");
    }
    end_table();
    end_frame();
  }



  function peerstable($res, $frame_caption)
  {
    begin_frame($frame_caption, true);
    begin_table();

		print("<tr><td class=colhead>Позиция</td><td class=colhead>Логин</td><td class=colhead>Раздача</td><td class=colhead>Скачивание</td></tr>");

		$n = 1;
		while ($arr = mysqli_fetch_assoc($res))
		{
      $highlight = $CURUSER["id"] == $arr["userid"] ? " bgcolor=#BBAF9B" : "";
			print("<tr><td$highlight>$n</td><td$highlight><a href=user/id" . $arr["userid"] . "><b>" . $arr["username"] . "</b></td><td$highlight>" . mksize($arr["uprate"]) . "/s</td><td$highlight>" . mksize($arr["downrate"]) . "/s</td></tr>\n");
			++$n;
		}

    end_table();
    end_frame();
  }

  stdhead("Топ 10");
  begin_main_frame();

	$type = (isset($_GET["type"]) && is_numeric($_GET["type"]))  ? 0 + $_GET["type"] : 0;
	if (!in_array($type,array(1,2,3,4)))
		$type = 1;
	$limit = (isset($_GET["lim"]) && is_numeric($_GET["lim"])) ? 0 + $_GET["lim"] : false;
	$subtype = isset($_GET["subtype"]) ? $_GET["subtype"] : false;

	print("<p align=center>"  .
		($type == 1 && !$limit ? "<b>Пользователи</b>" : "<a href=topten.php?type=1>Пользователи</a>") .	" | " .
 		($type == 2 && !$limit ? "<b>Торренты</b>" : "<a href=topten.php?type=2>Торренты</a>") 

		);

	$pu = get_user_class() >= UC_USER;

  if (!$pu)
  	$limit = 10;

  if ($type == 1)
  { 
  
    $mainquery = "SELECT id as userid, username, class, added, uploaded, downloaded, uploaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed, downloaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed FROM users WHERE enabled = 'yes'";

  	if (!$limit || $limit > 250)
  		$limit = 10;

  	if ($limit == 10 || $subtype == "ul")
  	{
		
		$order = "uploaded DESC";
			$r = sql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  usertable($r, "Топ $limit Раздающих" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=ul>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=ul>Топ 250</a>]</font>" : ""));


	 }

    if ($limit == 10 || $subtype == "dl")
  	{
			$order = "downloaded DESC";
		  $r = sql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  usertable($r, "Топ $limit качающих" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=dl>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=dl>Топ 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "uls")
  	{
			$order = "upspeed DESC";
			$r = sql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  	usertable($r, "Топ $limit быстрейших Раздающих <font class=small>(среднее, включая период неактивности)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=uls>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=uls>Топ 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "dls")
  	{
			$order = "downspeed DESC";
			$r = sql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  	usertable($r, "Топ $limit быстрейших качающих <font class=small>(среднее, включая период неактивности)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=dls>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=dls>Топ 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "bsh")
  	{
			$order = "uploaded / downloaded DESC";
			$extrawhere = " AND downloaded > 1073741824";
	  	$r = sql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  	usertable($r, "Топ $limit лучших раздающих <font class=small>(минимум 1 GB скачано)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=bsh>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=bsh>Топ 250</a>]</font>" : ""));
		}

    if ($limit == 10 || $subtype == "wsh")
  	{
			$order = "uploaded / downloaded ASC, downloaded DESC";
  		$extrawhere = " AND downloaded > 1073741824";
	  	$r = sql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  	usertable($r, "Топ $limit худших раздающих <font class=small>(минимум 1 GB скачано)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=wsh>Топ 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=wsh>Топ 250</a>]</font>" : ""));
	  }
	  
	  
	  
  }

  elseif ($type == 2)
  {
   	if (!$limit || $limit > 50)
  		$limit = 10;

   	if ($limit == 10 || $subtype == "act")
  	{
		  $r = sql_query("SELECT t.* FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  _torrenttable($r, "Топ $limit Наиболее активных раздач" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=act>Топ 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=act>Топ 50</a>]</font>" : ""));
	  }

   	if ($limit == 10 || $subtype == "sna")
   	{
	  	$r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY times_completed DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  _torrenttable($r, "Топ $limit Самые скаченные" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=sna>Топ 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=sna>Топ 50</a>]</font>" : ""));
	  }

   	if ($limit == 10 || $subtype == "mdt")
   	{
		  $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  _torrenttable($r, "Топ $limit Перенесенных торрентов" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=mdt>Топ 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=mdt>Топ 50</a>]</font>" : ""));
		}

   	if ($limit == 10 || $subtype == "bse")
   	{
		  $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND seeders >= 5 GROUP BY t.id ORDER BY seeders / leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
	  	_torrenttable($r, "Топ $limit Лучшие раздчи <font class=small>(минимум 5 сидеров)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=bse>Топ 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=bse>Топ 50</a>]</font>" : ""));
    }

   	if ($limit == 10 || $subtype == "wse")
   	{
		  $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  _torrenttable($r, "Топ $limit Худших торрентов <font class=small>(минимум 5 личеров)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=wse>Топ 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=wse>Топ 50</a>]</font>" : ""));
		}
  }
  elseif ($type == 3)
  {
  	if (!$limit || $limit > 25)
  		$limit = 10;

   	if ($limit == 10 || $subtype == "us")
   	{
		  $r = sql_query("SELECT name, flagpic, COUNT(users.country) as num FROM countries LEFT JOIN users ON users.country = countries.id GROUP BY name ORDER BY num DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  countriestable($r, "Топ $limit по Сранам <font class=small> (пользователей)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=us>Топ 25</a>]</font>" : ""),"Пользователи");
    }

   	if ($limit == 10 || $subtype == "ul")
   	{
	  	$r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name ORDER BY ul DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  countriestable($r, "Топ $limit по Странам<font class=small> (Всего роздано)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=ul>Топ 25</a>]</font>" : ""),"Раздача");
    }

		if ($limit == 10 || $subtype == "avg")
		{
		  $r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/count(u.id) AS ul_avg FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY ul_avg DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  countriestable($r, "Топ $limit по Странам<font class=small> (минимум 1TB розданного или 100 пользователей)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=avg>Топ 25</a>]</font>" : ""),"Среднее");
    }

		if ($limit == 10 || $subtype == "r")
		{
		  $r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/sum(u.downloaded) AS r FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND sum(u.downloaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY r DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
		  countriestable($r, "Топ $limit по Странам<font class=small> (рейтинг 1TB розданного, 1TB скаченного или 100 пользоватей)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=r>Топ 25</a>]</font>" : ""),"Рейитнг");
	  }
  }
	elseif ($type == 4)
	{
		print("<h1 align=center><font color=red>Информация не точна!</font></h1>\n");
  	if (!$limit || $limit > 250)
  		$limit = 10;

	    if ($limit == 10 || $subtype == "ul")
  		{
//				$r = sql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, peers.uploaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS uprate, peers.downloaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
//				peerstable($r, "Топ $limit Средняя скорость раздачи" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Топ 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Топ 250</a>]</font>" : ""));

				$r = sql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
				peerstable($r, "Топ $limit Средняя скорость раздачи (timeout corrected)" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Топ 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Топ 250</a>]</font>" : ""));

				$r = sql_query( "SELECT users.id AS userid, username, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
				peerstable($r, "Топ $limit Средняя скорость раздачи" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Топ 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Топ 250</a>]</font>" : ""));
	  	}

	    if ($limit == 10 || $subtype == "dl")
  		{
				$r = sql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
				peerstable($r, "Топ $limit Средняя загрузка (timeout corrected)" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=dl>Топ 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=dl>Топ 250</a>]</font>" : ""));

				$r = sql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded,(peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr(__FILE__, __LINE__);
				peerstable($r, "Топ $limit Средняя загрузка" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=dl>Топ 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=dl>Топ 250</a>]</font>" : ""));
	  	}
	}
  end_main_frame();
  //print("<p><font class=small>Started recording account xfer stats on 2003-08-31</font></p>");
  stdfoot();
?>


