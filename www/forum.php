<?php
require "include/bittorrent.php";
gzip();
dbconn(false);
global $mysqli;

	/**
	* The width of the forum, in percent, 100% is the full width
	*
	* Note: the width is also set in the function begin_main_frame()
	*/
	$forum_width = '100%';

	
		/**
	* The readpost expiry date, default 14 days
	*
	* Note: if you already have it, delete this one
	*/
	$READPOST_EXPIRY = 7*86400;
	
		/**
	* Set to true if you want to use the flood mod
	*/
	$use_flood_mod = true;
	
		/**
		* If there are more than $limit(default 10) posts in the last $minutes(default 5) minutes, it will give them a error...
		*
		* Requires the flood mod set to true
		*/
		$minutes = 3;
		$limit = 2;
		
		
	/**
	* Get's the users posts per page, no need to change
	*/
	$postsperpage = 10;
	
	
	
$action = (isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : ''));

if (!function_exists('highlight'))
{
	function highlight($search, $subject, $hlstart = '<b><font color="red">', $hlend = '</font></b>')
	{
		$srchlen = strlen($search);    // lenght of searched string
		if ($srchlen == 0)
			return $subject;
		
		$find = $subject;
		while ($find = stristr($find, $search)) // find $search text in $subject -case insensitiv
		{
			$srchtxt = substr($find,0,$srchlen);    // get new search text
			$find = substr($find,$srchlen);
			$subject = str_replace($srchtxt, $hlstart.$srchtxt.$hlend, $subject);    // highlight founded case insensitive search text
		}
		
		return $subject;
	}
}

function forum_menu_bottom(){
global $CURUSER ;
print("<br><br><p align=center><a href=forum><b>Главная</b></a> | <a href=forum?action=search><b>Поиск по форуму</b></a> | <a href=forum?action=getdaily><b>Сообщения за сегодня</b></a> | <a href=forum?catchup><b>Отметить все, как прочитанные</b></a> ".($CURUSER['class'] >= UC_ADMINISTRATOR ? "| <a href=forummanage.php#add><b>Админка</b></a>":"")."</p>");

}
function forum_stats()
{
	global $pic_base_url, $forum_width, $DEFAULTBASEURL;

	if (cache_check("forum_stats", 60))  {
		$topic_post_res = cache_read("forum_stats");
	} else {
		$topic_post_res = sql_query("SELECT SUM(topiccount) AS stopics, SUM(postcount) AS sposts , (SELECT COUNT(*) FROM posts WHERE  posts.added >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)) AS dayposts FROM forums");
		$forum_stats_cache = array();
		while ($cache_data = mysqli_fetch_array($topic_post_res))
			$forum_stats_cache[] = $cache_data;

		cache_write("forum_stats", $forum_stats_cache);
		$topic_post_res = $forum_stats_cache;
	}
	foreach ($topic_post_res as $topic_post_arr) {
		$sposts = number_format($topic_post_arr['sposts']);
		$stopics = number_format($topic_post_arr['stopics']);
		$dayposts = number_format($topic_post_arr['dayposts']);
	}

	// Initialize variable to fix "Undefined variable $who_online"
	$who_online = '';

	if (cache_check("online_forum", 2))  {
		$result = cache_read("online_forum");
	} else {
		$title_who = array();

		$result = sql_query("SELECT u.id,
				u.username, 
				u.class,
				u.warned,
				u.gender,
				u.enabled,
				u.parked,
				u.donor FROM users AS u WHERE u.forum_access > ".sqlesc(get_date_time(time() - 300))." ORDER BY u.class DESC");

		$online_forum_cache = array();
		while ($cache_data = mysqli_fetch_array($result))
			$online_forum_cache[] = $cache_data;

		cache_write("online_forum", $online_forum_cache);
		$result = $online_forum_cache;
	}

	foreach ($result as $arr) {
		list($uid, $uname, $class ,$warned ,$gender ,$enabled ,$parked ,$donor) = $arr;

		if (!empty($uname)) {
			$title_who[] = "<a href=\"user/id".$uid."\" class=\"online\">".get_user_class_color($class, $uname). get_user_icons($arr)."</a>";
		}
		if (empty($uname))
			continue;
		else
			$who_online .= implode(', ', $title_who);
	}

	?>

	<br />
	<table border=0 cellspacing=3 cellpadding=5>
		<tr><br><span class="c_title">Онлайн</span>
		<br>
		<td align="left" class="embedded"><?=@implode(", ", $title_who)?><hr></td></tr>
	</table>
	<table border=0 cellspacing=3 cellpadding=5>
	<tr>
	<br />
	<span class="c_title">Статистика</span><br>
		<td class='rowhead' id="no_border" align='center'>Пользователи оставили <b><?php echo $sposts ?></b> сообщений в <b><?php echo $stopics ?></b> темах . 
		Новых сообщений за сутки <?php echo $dayposts ?> .<hr></td>
	</tr>
	</table>

	<?php
}

function show_forums($forid)
{
	global $CURUSER, $pic_base_url, $READPOST_EXPIRY, $DEFAULTBASEURL;
	
	$forums_res = sql_query("SELECT f.id, f.name, f.description, f.postcount, f.topiccount, f.sort, p.added, p.topicid, p.userid, p.id AS pid, u.username, u.class, t.subject, t.lastpost, r.lastpostread ".
							  "FROM forums AS f ".
							  "LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f.id) ".
							  "LEFT JOIN users AS u ON u.id = p.userid ".
							  "LEFT JOIN topics AS t ON t.id = p.topicid ".
							  "LEFT JOIN readposts AS r ON r.userid = ".sqlesc($CURUSER['id'])." AND r.topicid = p.topicid ".
							  "WHERE f.forid = $forid ".
							  "ORDER BY f.sort ASC") or sqlerr(__FILE__, __LINE__);
	
	while ($forums_arr = mysqli_fetch_assoc($forums_res))
	{

	
		$forumid = (int)$forums_arr["id"];
		$lastpostid = (int)$forums_arr['lastpost'];
		
		if (is_valid_id($forums_arr['pid']))
		{
			$lastpost = "<nobr><a href='$DEFAULTBASEURL/user/id".(int)$forums_arr["userid"]."'>".get_user_class_color($forums_arr["class"],$forums_arr["username"])."</a>
			&nbsp;<a href='/forum/view/topic/id".(int)$forums_arr["topicid"]."&amp;page=p$lastpostid#$lastpostid'><img src='$pic_base_url/latest.gif' border='0px' alt='Читать сообщение'></a><br />&nbsp;".display_date_time($forums_arr["added"])."</nobr>";

			$img = 'unlocked'.((($forums_arr['added']>(get_date_time(gmtime()-$READPOST_EXPIRY)))?((int)$forums_arr['pid'] > $forums_arr['lastpostread']):0)?'new':'');
		}
		else
		{
			$lastpost = "N/A";
			$img = "unlocked";
		}
	
		?><tr>
			<td align='left'>
				<table border=0 cellspacing=0 cellpadding=0>
					<tr>
						<td class=embedded style='padding-right: 5px'><img src="<?php echo $pic_base_url.$img; ?>.gif"></td>
						<td class=embedded>
							<a href='/forum/view/forum/id<?php echo $forumid; ?>'><b><?php echo htmlspecialchars($forums_arr["name"]); ?></b></a><?php
						
						if (!empty($forums_arr["description"]))
						{
							?><br /><?php echo htmlspecialchars($forums_arr["description"]);
						}
						?></td>
					</tr>
				</table>
			</td>
			<td align='center'><?php echo number_format($forums_arr["topiccount"]); ?></td>
			<td align='center'><?php echo number_format($forums_arr["postcount"]); ?></td>
			<td align='left'>&nbsp;<?php echo $lastpost; ?></td>
		</tr><?php
	}
}


function catch_up($id = 0)
{	
	global $CURUSER, $READPOST_EXPIRY;
	
	$userid = (int)$CURUSER['id'];
	
	$res = sql_query("SELECT t.id, t.lastpost, r.id AS r_id, r.lastpostread ".
					   "FROM topics AS t ".
					   "LEFT JOIN posts AS p ON p.id = t.lastpost ".
					   "LEFT JOIN readposts AS r ON r.userid=".sqlesc($userid)." AND r.topicid=t.id ".
					   "WHERE p.added > ".sqlesc(get_date_time(gmtime() - $READPOST_EXPIRY)).
					   (!empty($id) ? ' AND t.id '.(is_array($id) ? 'IN ('.implode(', ', $id).')' : '= '.sqlesc($id)) : '')) or sqlerr(__FILE__, __LINE__);

	while ($arr = mysqli_fetch_assoc($res))
	{
		$postid = (int)$arr['lastpost'];
		
		if (!is_valid_id($arr['r_id']))
			@sql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, ".(int)$arr['id'].", $postid)") or sqlerr(__FILE__, __LINE__);
		else if ($arr['lastpostread'] < $postid)
			@sql_query("UPDATE LOW_PRIORITY readposts SET lastpostread = $postid WHERE id = ".$arr['r_id']) or sqlerr(__FILE__, __LINE__);
	}
	mysqli_free_result($res);
}

  //-------- Returns the minimum read/write class levels of a forum



  //-------- Returns the forum ID of a topic, or false on error

  function get_topic_forum($topicid)
  {
    $res = sql_query("SELECT forumid FROM topics WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    if (mysqli_num_rows($res) != 1)
      return false;

    $arr = mysqli_fetch_row($res);

    return $arr[0];
  }

  //-------- Returns the ID of the last post of a forum

  function update_topic_last_post($topicid)
  {
    $res = sql_query("SELECT id FROM posts WHERE topicid=".sqlesc($topicid)." ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

    $arr = mysqli_fetch_row($res) or die("No post found");

    $postid = $arr[0];

    @sql_query("UPDATE LOW_PRIORITY topics SET lastpost=$postid WHERE id=".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
  }

  function get_forum_last_post($forumid)
  {
    $res = sql_query("SELECT lastpost FROM topics WHERE forumid=".sqlesc($forumid)." ORDER BY lastpost DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

    $arr = mysqli_fetch_row($res);

    $postid = $arr[0];

    if ($postid)
      return $postid;

    else
      return 0;
  }



  //-------- Inserts a compose frame
function insert_compose_frame($id, $newtopic = true, $quote = false)
{
	global $maxsubjectlength, $CURUSER, $pic_base_url ,$forum_pics, $DEFAULTBASEURL;
	
	if ($newtopic)
	{
		$res = sql_query("SELECT name FROM forums WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
		$arr = mysqli_fetch_assoc($res) or die("Bad forum ID!");
		
		?><h3>Создание новой Темы в Форуме <a href='/forum/view/forum/id<?php echo $id; ?>'><?php echo htmlspecialchars($arr["name"]); ?></a></h3><?php
	}
	else
	{
		$res = sql_query("SELECT subject, locked FROM topics WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
		$arr = mysqli_fetch_assoc($res) or die("Forum error, Topic not found.");
		
		if ($arr['locked'] == 'yes')
		{
			stdmsg("Sorry", "The topic is locked.");
			
			end_table(); end_main_frame(); stdfoot();
			exit();
		}
		
		?><h3 align="center">Ответ в Теме: <a href='/forum?action=viewtopic&topicid=<?php echo $id; ?>'><?php echo htmlspecialchars($arr["subject"]); ?></a></h3><?php
	}
	
	begin_frame("Редактор", true);
	
	?>



	<form method='post' name='compose' id='compose' action='/forum' enctype='multipart/form-data'>
	<input type="hidden" name="action" value="post" />
	<input type='hidden' name='<?php echo ($newtopic ? 'forumid' : 'topicid'); ?>' value='<?php echo $id; ?>'><?php
	
	begin_table(true);
	
	if ($newtopic)
	{
		?>
		<tr>
			<td class='coolhead'><center>Тема<br />
				<input type='text' size='100' maxlength='<?php echo $maxsubjectlength; ?>' name='subject' style='height: 19px'>
			</center></td>
		</tr><?php
	}
		
	if ($quote)
	{
		$postid = (int)$_GET["postid"];
		if (!is_valid_id($postid))
		{
			stdmsg("Error", "Invalid ID!");
			
			end_table(); end_main_frame(); stdfoot();
			exit();
		}
		
		$res = sql_query("SELECT posts.*, users.username FROM posts JOIN users ON posts.userid = users.id WHERE posts.id = $postid") or sqlerr(__FILE__, __LINE__);
		
		if (mysqli_num_rows($res) == 0)
		{
			stdmsg("Error", "No post with this ID");
			
			end_table(); end_main_frame(); stdfoot();
			exit();
		}
		
		$arr = mysqli_fetch_assoc($res);
	}
		
	?><tr>
		<td><center><?php
		$qbody = ($quote ? "[quote=".htmlspecialchars($arr["username"])."]".htmlspecialchars(unesc($arr["body"]))."[/quote]" : '');
			textbbcode("compose", "body", $qbody);
	
		?><tr>
        	<td colspan='2' align='center'>
            <input type='submit' value='Отправить'>
			<input type="button" value="Смайлы" onClick="javascript:winop()" />
<input type="button" value="Смайлы2" onClick="javascript:winop2()" /></center>
</b>

			</td>
		</tr>
        
		</td>
        </tr><?php
		
		end_table();
		
		?></form><?php
				
		end_frame();
		
		//------ Get 10 last posts if this is a reply
		if (!$newtopic)
		{
			$postres = sql_query("SELECT p.id, p.added, p.body, u.id AS uid, u.username, u.avatar ".
								   "FROM posts AS p ".
								   "LEFT JOIN users AS u ON u.id = p.userid ".
								   "WHERE p.topicid = ".sqlesc($id)." ".
								   "ORDER BY p.id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
			if (mysqli_num_rows($postres) > 0)
			{
				?><br /><?php
				begin_frame("10 постов , в обратном порядке ");
				
				while ($post = mysqli_fetch_assoc($postres))
				{
					$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($post["avatar"]) : '');
					
					if (empty($avatar))
						$avatar = $pic_base_url."default_avatar.gif";
					
					?><p class=sub>#<?php echo $post["id"]; ?> от <?php echo (!empty($post["username"]) ? $post["username"] : "unknown[{$post['uid']}]"); ?> в <?php echo $post["added"]; ?></p><?php
					
					begin_table(true);
					
					?>
					<tr>
						<td height='100' width='100' align='center' style='padding: 0px' valign="top"><img height='100' width='100' src="<?php echo $avatar; ?>" /></td>
						<td class='comment' valign='top'><?php echo format_comment($post["body"]); ?></td>
					</tr><?php
					
					end_table();
				}
				
				end_frame();
			}
		}
		

}

  //-------- Global variables

  $maxsubjectlength = 350;


 

  //-------- Action: New topic

  if ($action == "newtopic")
  {
    $forumid = (int)$_GET["forumid"];

    stdhead("Создание новой темы");

    begin_main_frame();

    insert_compose_frame($forumid,true,false);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Post

  if ($action == "post")
  {
		$forumid = (int)$_POST["forumid"];
		$topicid = (int)$_POST["topicid"];

    $newtopic = $forumid > 0;

    $subject = $_POST["subject"] ?? '';

    if ($newtopic)
    {
      $subject = trim($subject);

      if (!$subject)
        stderr("Error", "You must enter a subject.");

      if (strlen($subject) > $maxsubjectlength)
        stderr("Error", "Subject is limited.");
    }
    else
      $forumid = get_topic_forum($topicid) or die("Bad topic ID");
      if (($CURUSER["forumpost"] ?? '') == 'no')
		{
				stdhead();
				stdmsg("Sorry...", "You are not authorized to Post.",false);
				stdfoot();
				exit;
		}

    //------ Make sure sure user has write access in forum

    $arr = $forumid or die("Bad forum ID");



    $body = trim($_POST["body"]);

    if ($body == "")
      stderr("Error", "No body text.");
 loggedinorreturn();

    $userid = (int)$CURUSER["id"];
   
    if ($CURUSER['class'] < UC_MODERATOR)
	{
		$res = sql_query("SELECT COUNT(id) AS c FROM posts WHERE userid = ".$CURUSER['id']." AND added > '".get_date_time(gmtime() - ($minutes * 60))."'");
		$arr = mysqli_fetch_assoc($res);
		
		if ($arr['c'] > $limit)
			stderr("Flood", "More than ".$limit." posts in the last ".$minutes." minutes.");
	}
    if ($newtopic)
    {
      //---- Create topic 

      $subject = sqlesc($subject);
    loggedinorreturn();

      @sql_query("INSERT INTO topics (userid, forumid, subject) VALUES($userid, $forumid, $subject)") or sqlerr(__FILE__, __LINE__);

      $topicid = mysqli_insert_id() or stderr("Error", "No topic ID returned");
          }
    else
    {
      //---- Make sure topic exists and is unlocked

      $res = sql_query("SELECT * FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

      $arr = mysqli_fetch_assoc($res) or die("Topic id n/a");

      if ($arr["locked"] == 'yes' && get_user_class() < UC_MODERATOR)
        stderr("Error", "This topic is locked.");

      //---- Get forum ID

      $forumid = $arr["forumid"];
    }	 
    
    //------ Insert post
    $added = "'" . get_date_time() . "'";
    $body = sqlesc($body);
	$secsdp = 1*3600;
	$dtdp = sqlesc(get_date_time(gmtime() - $secsdp)); // calculate date.
    
     //------ Check double post     
     $doublepost = sql_query("SELECT posts.id, posts.added, posts.userid, posts.body, topics.lastpost, topics.id FROM posts INNER JOIN topics on posts.id = topics.lastpost WHERE topics.id=$topicid AND posts.userid = $userid AND posts.added > $dtdp ORDER BY added DESC	LIMIT 1") or sqlerr(__FILE__, __LINE__);
     $results = mysqli_fetch_assoc($doublepost);
     if (!$results) {
	      loggedinorreturn();

			@sql_query("INSERT INTO posts (topicid, userid, added, body) VALUES($topicid, $userid, $added, $body)") or sqlerr(__FILE__, __LINE__);
			$postid = mysqli_insert_id() or die("Post id n/a");
			update_topic_last_post($topicid);
			
	}
	else {
			$oldbody = trim($results['body']);
			$newbody =  trim($_POST["body"]);
			$updatepost = sqlesc("$oldbody\n\n$newbody");
			$editedat = sqlesc(get_date_time());
			    if($userid <= 0)
	die("fuck u !");
	      	@sql_query("UPDATE LOW_PRIORITY posts SET body=$updatepost, editedat=$editedat, editedby=$userid WHERE id=$results[lastpost]") or sqlerr(__FILE__, __LINE__);	      	
	}	
 
//------ All done, redirect user to the post

    $headerstr = "Location: $BASEURL/forum/view/topic/id$topicid&page=last";
    		
    if ($newtopic)
      header($headerstr);

    else
      header("$headerstr#$postid");

    die;
  }

  //-------- Action: View topic

  if ($action == "viewtopic")
  {
		unset($count);
		
    $topicid = (int)$_GET["topicid"];

    $page = (int)$_GET["page"];

    $userid = (int)$CURUSER["id"];

	//------ Get topic info
	$res = sql_query("SELECT t.locked, t.subject, t.sticky, t.userid AS t_userid, t.forumid, f.name AS forum_name 
					   FROM topics AS t 
					   LEFT JOIN forums AS f ON f.id = t.forumid 
					   WHERE t.id = ".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res) or stderr("Error", "Topic not found");
	
	$t_userid = (int)$arr['t_userid'];
	$locked = ($arr['locked'] == 'yes' ? true : false);
	$subject = $arr['subject'];
	$sticky = ($arr['sticky'] == "yes" ? true : false);
	$forumid = (int)$arr['forumid'];
	$forum = $arr["forum_name"];
	
	
	//------ Update hits column	

    @sql_query("UPDATE LOW_PRIORITY topics SET views = views + 1 WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    //------ Get forum

    //------ Get post count

    $res = sql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    $arr = mysqli_fetch_row($res);

    $postcount = $arr[0];

    //------ Make page menu

    $pagemenu1 = "<p class=success align=center>\n";
    $perpage = $postsperpage;
    $pages = ceil($postcount / $perpage);
    if ($page[0] == "p")
  	{
	    $findpost = substr($page, 1);
	    $res = sql_query("SELECT id FROM posts WHERE topicid=$topicid ORDER BY added") or sqlerr(__FILE__, __LINE__);
	    $i = 1;
	    while ($arr = mysqli_fetch_row($res))
	    {
	      if ($arr[0] == $findpost)
	        break;
	      ++$i;
	    }
	    $page = ceil($i / $perpage);
	  }

    if ($page == "last")
      $page = $pages;
    else
    {
      if($page < 1)
        $page = 1;
      elseif ($page > $pages)
        $page = $pages;
    }

    $offset = $page * $perpage - $perpage;

    for ($i = 1; $i <= $pages; ++$i)
    {
      if ($i == $page)
        $pagemenu2 .= "<span style='font-size:1.4em'><b>[<u>$i</u>]</b></span>\n";

      else
        $pagemenu2 .= "<span style='font-size:1.4em'><a href=/forum/view/topic/id$topicid&page=$i><b>$i</b></a></span>\n";
    }

    if ($page == 1)
      $pagemenu1 .= "<img src='/pic/prev.gif' border='0px'></a>";

    else
      $pagemenu1 .= "<span style='font-size:1.4em'><a href=/forum/view/topic/id$topicid&page=" . ($page - 1) ."><img src='/pic/prev.gif' border='0px'></a></span>";

    $pmlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($page == $pages)
      $pagemenu3 .= "<img src='/pic/next.gif' border='0px'></a></p>\n";

    else
      $pagemenu3 .= "<span style='font-size:1.4em'><a href=/forum/view/topic/id$topicid&page=" . ($page + 1) ."><img src='/pic/next.gif' border='0px'></a></p></span>\n";
		
		
		
    stdhead("Форум :: Тема - $subject");    
    begin_main_frame();
?>

<link type="text/css" rel="stylesheet" href="<?=$DEFAULTBASEURL?>/css/rating_style.css" />
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/sack.js" ></script>
<script type="text/javascript">
	var e = new sack();
function do_rate(rate,id,what) {
		var box = document.getElementById('rate_'+id);
		e.setVar('rate',rate);
		e.setVar('id',id);
		e.setVar('ajax','1');
		e.setVar('what',what);
		e.requestFile = 'rating.php';
		e.method = 'GET';
		e.element = 'rate_'+id;
		e.onloading = function () {
			box.innerHTML = 'Loading ...'
		}
		e.onCompletion = function() {
			if(e.responseStatus)
				box.innerHTML = e.response();
		}
		e.onerror = function () {
			alert('That was something wrong with the reques!');
		}
		e.runAJAX();
}
</script>
<a name='top'></a>
<table width="97%" border="0" cellpadding="0" cellspacing="0" style="border:none;" align="center">
		<tr>
			<td align="left" width="80%" style="border:none;">
			    <h1><a href="/forum" title="На главную форума">Форум</a> - <a href="/forum/view/forum/id<?php echo $forumid; ?>"><?php echo $forum; ?></a> - <?php echo htmlspecialchars($subject); ?></h1>
</td> <? if ($CURUSER){ ?>
			<td align="right" width="17%" style="border:none;">
			<?php print(getRate($topicid,"topic")); visitorsHistory($topicid,5);?>
			</td> <? } ?>
		</tr>
	</table><?php
	$res = sql_query(
	"SELECT p.id, p.added, p.userid, p.added, p.body, p.editedby, p.editedat, u.id as uid, u.username as uusername, u.class AS uclass, u.avatar, u.donor,
	u.title, u.enabled, u.warned,
	u.last_access, (SELECT COUNT(id) FROM posts WHERE userid = u.id) AS posts_count, u2.username as u2_username 
	, (SELECT lastpostread FROM readposts WHERE userid = ".sqlesc((int)$CURUSER['id'])." AND topicid = p.topicid LIMIT 1) AS lastpostread
 FROM posts AS p 
	LEFT JOIN users AS u ON p.userid = u.id 
	LEFT JOIN users AS u2 ON u2.id = p.editedby
	WHERE p.topicid = ".sqlesc($topicid)." 
	ORDER BY id LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);
	$pc = mysqli_num_rows($res);
	$pn = 0;
	
	while ($arr = mysqli_fetch_assoc($res))
	{
		++$pn;
		
		$lpr = $arr['lastpostread'];
		$postid = (int)$arr["id"];
		$postadd = $arr['added'];
		$posterid = (int)$arr['userid'];
		$added = display_date_time($arr['added']) . " , <i>(" . get_elapsed_time(strtotime($arr['added'])) . ") назад</i>";
	
		//---- Get poster details		
		$last_access = $arr['last_access'];
	
		$postername = get_user_class_color($arr['uclass'],$arr['uusername']).get_user_icons($arr);
		$avatar =  htmlspecialchars($arr['avatar']);
		$title = (!empty($postername) ? (empty($arr['title']) ? "(".get_user_class_name($arr['uclass']).")" : "(".format_comment($arr['title']).")") : '');
		$forumposts = (!empty($postername) ? ($arr['posts_count'] != 0 ? $arr['posts_count'] : 'N/A') : 'N/A');
		$by = (!empty($postername) ? "<a href='$DEFAULTBASEURL/user/id$posterid'>".$postername."</a>" : "");
	
      if (!$avatar)
        $avatar = "pic/default_avatar.gif";

		echo " <a name=$postid></a>";
		echo ($pn == $pc ? '<a name=last></a>' : '');
      print("<div id=\"rounded-box-3\">
    <b class=\"r3\"></b><b class=\"r1\"></b><b class=\"r1\"></b><div class=\"inner-box\"><table border=0 cellspacing=0 width=97% align=\"center\" cellpadding=0><tr><td class=embedded width=97%>#$postid by $by $title в $added");      

      print("</td></tr></table>");

      print("\n");

      print ("<table class=\"maibaugrand\" width=\"97%\" border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"3\" >");

		$highlight = (isset($_GET['highlight']) ? $_GET['highlight'] : '');
		$body = (!empty($highlight) ? highlight($highlight, format_comment($arr['body'])) : format_comment($arr['body']));
      		if (is_valid_id($arr['editedby']))
			$body .= "<br><p><font size=1 class=small_com><i>Отредактировал(а) <a href='$DEFAULTBASEURL/user/id".$arr['editedby']."'><b>".$arr['u2_username']."</b></a> в ".display_date_time($arr['editedat'])." </i></font></p>";

      $stats = "<br>&nbsp;&nbsp;Сообщений: $forumposts<br>";
      	unset($onoffpic,$dt);
      	$dt = get_date_time(gmtime() - 180);
		if (get_user_class() < UC_MODERATOR AND $posterid != $CURUSER[id])
			$onoffpic = "<img src='".$DEFAULTBASEURL."/pic/button_offline.gif' border='0' />";
		elseif ($last_access > $dt OR $posterid == $CURUSER[id])
			$onoffpic = "<img src='".$DEFAULTBASEURL."/pic/button_online.gif' border='0' />";
		else
			$onoffpic = "<img src=".$DEFAULTBASEURL."/pic/button_offline.gif border=0>";
     print("<tr valign=top><td width='150px' align='left' style='padding: 0px'><br>"."&nbsp; " .
       ($avatar ? "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img width=100 src=\"$avatar\">": ""). "<br>"."&nbsp;$stats<br><br></td><td class=text><div>$body</div></td></tr>\n");
	print("<tr><td>".$onoffpic." <a href=\"message.php?receiver=".htmlspecialchars($posterid)."&action=sendmessage\"><img src=\"".$DEFAULTBASEURL."/pic/button_pm.gif\" border=\"0\" alt=\"Отправить сообщеньку\"></a></td>");
	print("<td style=text-align:right>");
	if (!$locked && $CURUSER || get_user_class() >= UC_SYSOP)				
		print("<a href=forum?action=quotepost&topicid=$topicid&postid=$postid><b>[ цитировать ]</b>&nbsp;</a>");

if (get_user_class() >= UC_SYSOP || !$locked && $CURUSER)	
		print("<a href=forum?action=reply&topicid=$topicid><b>[ ответить ]</b>&nbsp;</a>");
	    
				
	if (get_user_class() >= UC_MODERATOR)
        print("<a href=forum?action=deletepost&postid=$postid><b>[ удалить ]</b>&nbsp;</a>");
	
	if (($CURUSER["id"] == $posterid && !$locked) || get_user_class() >= UC_MODERATOR)
        print("<a href=forum?action=editpost&postid=$postid><b>[ редактировать ]</b>&nbsp;</a>");
	print("</td></tr></table></div><b class=\"r1\"></b><b class=\"r1\"></b><b class=\"r3\"></b></div>
<br>");
	

}
	if ($CURUSER){
	if (($postid > $lpr) && ($postadd > (get_date_time(gmtime() - $READPOST_EXPIRY))))
	{
		if ($lpr)
			@sql_query("UPDATE LOW_PRIORITY readposts SET lastpostread = $postid WHERE userid = $userid AND topicid = $topicid") or sqlerr(__FILE__, __LINE__);
		else
			@sql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);
	} }

  	if (get_user_class() >= UC_SYSOP || !$locked && $CURUSER){
?>

<table id="no_border" width=100%><tr>
<td colspan=2 class=colhead><center><b>Быстрый ответ</b></td></tr>
<tr><td id="no_border">
<center><form name='compose' id='compose' method='post' action='/forum'  enctype='multipart/form-data'>
<input type="hidden" name="action" value="post" />
<input type=hidden name=topicid value=<? echo $topicid;?>>
<?
textbbcode("compose","body","", 1)
?>
<center><input type=submit class=gobutton value="Добавить ответ">
<input type="button" value="Смайлы" onClick="javascript:winop()" />
<input type="button" value="Смайлы2" onClick="javascript:winop2()" /></center>
</form>
</td></tr>
</table>
<?
print(visitorsList("<table width=\"100%\" class=\"tt\" cellpadding=\"5\">\n<tr> <td class=\"colhead\" colspan=\"12\"><b>Сейчас эту страницу просматривают </b></td></tr>
  <tr><td align=\"rowhead\" bgcolor=\"#F4F4F0\" colspan=\"3\"><div id=\"visitors\">[VISITORS]</div></td></tr></table>\n", $VISITORS));
}
  //------ Mod options

  	  	print("$pagemenu1 $pmlb $pagemenu2 $pmlb $pagemenu3");


	  if (get_user_class() >= UC_MODERATOR)
	  {

	    
	    print("<table border=0 cellspacing=0 cellpadding=0>\n");

	    print("<form method=post action=forum?action=setsticky>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Важный:</td>\n");
	    print("<td class=embedded><input type=radio name=sticky value='yes' " . ($sticky ? " checked" : "") . "> да <input type=radio name=sticky value='no' " . (!$sticky ? " checked" : "") . "> нет\n");
	    print("<input type=submit value='Да' class=btn></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forum?action=setlocked>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Закрыть:</td>\n");
	    print("<td class=embedded><input type=radio name=locked value='yes' " . ($locked ? " checked" : "") . "> да <input type=radio name=locked value='no' " . (!$locked ? " checked" : "") . "> нет\n");
	    print("<input type=submit value='Да' class=btn></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forum?action=renametopic>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=returnto value=$_SERVER[REQUEST_URI]>\n");
	    print("<tr><td class=embedded align=right>Переименовать:</td><td class=embedded><input type=text name=subject size=60 maxlength=$maxsubjectlength value=\"" . htmlspecialchars($subject) . "\">\n");
	    print("<input type=submit value='вперед' class=btn></td></tr>");
	    print("</form>\n");

	    print("<form method=post action=forum?action=movetopic&topicid=$topicid>\n");
	    print("<tr><td class=embedded>Переместить на:&nbsp;</td><td class=embedded><select name=forumid>");
		$res = mysql_query("SELECT id, name FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);
	    while ($arr = mysqli_fetch_assoc($res))
	      if ($arr["id"] != $forumid)
	        print("<option value=" . $arr["id"] . ">" . $arr["name"] . "\n");

	    print("</select> <input type=submit value='вперед' class=btn></form></td></tr>\n");
	    print("<tr><td class=embedded>Удалить</td><td class=embedded>\n");
	    print("<form method=get action=forum>\n");
	    print("<input type=hidden name=action value=deletetopic>\n");
	    print("<input type=hidden name=topicid value=$topicid>\n");
	    print("<input type=hidden name=forumid value=$forumid>\n");
	    print("<input type=checkbox name=sure value=1>Я уверен\n");
	    print("<input type=submit value='вперед' class=btn>\n");
	    print("</form>\n");
	    print("</td></tr>\n");
	    print("</table>\n");
	  }


    //------ Forum quick jump drop-down

    
 	end_main_frame();
    stdfoot();

    die;
  }

  //-------- Action: Quote

	if ($action == "quotepost")
	{
	loggedinorreturn();

		$topicid = (int)$_GET["topicid"];

    stdhead("Ответить");

    begin_main_frame();

    insert_compose_frame($topicid, false, true);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Reply

  if ($action == "reply")
  {
  loggedinorreturn();
    $topicid = (int)$_GET["topicid"];

    int_check($topicid,true);

    stdhead("Ответить");

    begin_main_frame();

    insert_compose_frame($topicid, false, false);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Move topic

  if ($action == "movetopic")
  {
  loggedinorreturn();

    $forumid = (int)$_POST["forumid"];
    $topicid = (int)$_GET["topicid"];

    if (!is_valid_id($forumid) || !is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    $res = @sql_query("SELECT forumid FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
   if (mysqli_num_rows($res) != 1)
     stderr("Error", "Topic not found.");
   $arr = mysqli_fetch_row($res);
   $old_forumid=$arr[0];

   // get posts count
   $res = sql_query("SELECT COUNT(id) AS nb_posts FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);
   if (mysqli_num_rows($res) != 1)
     stderr("Error", "Couldn't get posts count.");
   $arr = mysqli_fetch_row($res);
   $nb_posts = $arr[0];

   // move topic
   if ($old_forumid != $forumid)
   {
     @sql_query("UPDATE LOW_PRIORITY topics SET forumid=$forumid WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
	 
     // update counts
     @sql_query("UPDATE LOW_PRIORITY forums SET topiccount=topiccount-1, postcount=postcount-$nb_posts WHERE id=$old_forumid") or sqlerr(__FILE__, __LINE__);
     @sql_query("UPDATE LOW_PRIORITY forums SET topiccount=topiccount+1, postcount=postcount+$nb_posts WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);
   }

    // Redirect to forum page

    header("Location: $BASEURL/forum/view/forum/id$forumid");

    die;
  }

  //-------- Action: Delete topic

  if ($action == "deletetopic")
  {
  loggedinorreturn();
    $topicid = (int)$_GET["topicid"];
    $forumid = (int)$_GET["forumid"];
    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    $sure = (int)$_GET["sure"];

    if (!$sure)
    {
	begin_main_frame();
      stderr("Удалить Тему", "Вы уверены что хотите удалить Тему ?\n" .
      "Нажмите <a href=forum?action=deletetopic&topicid=$topicid&sure=1>да</a> если уверены .",false);
    end_main_frame();
	}
	
    sql_query("DELETE FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    sql_query("DELETE FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: $BASEURL/forum/view/forum/id$forumid");

    die;
  }

  //-------- Action: Edit post

  if ($action == "editpost")
  {
  loggedinorreturn();
    $postid = (int)$_GET["postid"];

    $res = sql_query("SELECT * FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

		if (mysqli_num_rows($res) != 1)
			stderr("Error", "No post with this ID");

		$arr = mysqli_fetch_assoc($res);

    $res2 = sql_query("SELECT locked FROM topics WHERE id = " . $arr["topicid"]) or sqlerr(__FILE__, __LINE__);
		$arr2 = mysqli_fetch_assoc($res2);

 		if (mysqli_num_rows($res) != 1)
			stderr("Error", "No topic associated with this post ID");

		$locked = ($arr2["locked"] == 'yes');

    if (($CURUSER["id"] != $arr["userid"] || $locked) && get_user_class() < UC_SYSOP)
      stderr("Error", "Denied!");

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
    	$body = $_POST['body'];

    	if ($body == "")
    	  stderr("Error", "Body cannot be empty!");

      $body = sqlesc($body);

      sql_query("UPDATE LOW_PRIORITY posts SET body=$body, editedat=NOW(), editedby=$CURUSER[id] WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

		$returnto = $_POST["returnto"];

			if ($returnto != "")
			{
				$returnto .= "&page=p$postid#$postid";
				header("Location: $returnto");
			}
			else
			begin_main_frame();
				stderr("Готово", "Сообщение успешно изменено .");
			end_main_frame();
	}

    stdhead("Редактирование");
	begin_main_frame();
    print("<h3>Редактирование</h3>\n");
?>



 <tr><td id="no_border">
   <center><form name=edit id=edit method=post action="<?=$DEFAULTBASEURL?>/forum?action=editpost&postid=<?=$postid?>">
  <input type=hidden name=returnto value="<?=htmlspecialchars($HTTP_SERVER_VARS["HTTP_REFERER"])?>">
 <?      
   textbbcode("edit","body",htmlspecialchars(unesc($arr["body"])));
   ?>          
<input type=submit class=gobutton value="Сохранить">
<input type="button" value="Смайлы" onClick="javascript:winop()" />
<input type="button" value="Смайлы2" onClick="javascript:winop2()" />
</form>
   </td></tr>
<?	end_main_frame();
       stdfoot();

  	die;
  }

  //-------- Action: Delete post

  if ($action == "deletepost")
  {
    loggedinorreturn();
    $postid = (int)$_GET["postid"];

    $sure = (int)$_GET["sure"];
    if (get_user_class() < UC_MODERATOR || !is_valid_id($postid))
      die;

    //------- Get topic id

    $res = sql_query("SELECT topicid FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);

    $arr = mysqli_fetch_row($res) or stderr("Error", "Post not found");

    $topicid = $arr[0];

    //------- We can not delete the post if it is the only one of the topic

    $res = sql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    $arr = mysqli_fetch_row($res);

    if ($arr[0] < 2){
	stderr("Error", "Can't delete post; it is the only post of the topic. You should\n" .
    "<a href=forum?action=deletetopic&topicid=$topicid&sure=1>delete the topic</a> instead.\n",false);
	  }

    //------- Get the id of the last post before the one we're deleting

    $res = sql_query("SELECT id FROM posts WHERE topicid=$topicid AND id < $postid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
		if (mysqli_num_rows($res) == 0)
			$redirtopost = "";
		else
		{
			$arr = mysqli_fetch_row($res);
			$redirtopost = "&page=p$arr[0]#$arr[0]";
		}

    //------- Make sure we know what we do :-)

    if (!$sure)
    {
      stderr("Удалить сообщение ?", "Вы действительно хотите удалить сообщение ?\n" .
      "Нажмите <a href=forum?action=deletepost&postid=$postid&sure=1>да</a> если уверены .",false);
    }

    //------- Delete post

    sql_query("DELETE FROM posts WHERE id=$postid") or sqlerr(__FILE__, __LINE__);
    
     //------- Delete attachments
    

    //------- Update topic

    update_topic_last_post($topicid);
    
   
    header("Location: $BASEURL/forum/view/topic/id$topicid$redirtopost");

    die;
  }

  //-------- Action: Lock topic

  if ($action == "locktopic")
  {
    loggedinorreturn();
    $forumid = (int)$_GET["forumid"];
    $topicid = (int)$_GET["topicid"];
    $page = (int)$_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    sql_query("UPDATE LOW_PRIORITY topics SET locked='yes' WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: $BASEURL/forum/view/forum/id$forumid&page=$page");

    die;
  }

  //-------- Action: Unlock topic

  if ($action == "unlocktopic")
  {
    loggedinorreturn();
    $forumid = (int)$_GET["forumid"];

    $topicid = (int)$_GET["topicid"];

    $page = (int)$_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    sql_query("UPDATE LOW_PRIORITY topics SET locked='no' WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: $BASEURL/forum/view/forum/id$forumid&page=$page");

    die;
  }

  //-------- Action: Set locked on/off

  if ($action == "setlocked")
  {
    loggedinorreturn();
    $topicid = (int)$_POST["topicid"];

    if (!$topicid || get_user_class() < UC_MODERATOR)
      die;

	$locked = sqlesc($_POST["locked"]);
    sql_query("UPDATE LOW_PRIORITY topics SET locked=$locked WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: $_POST[returnto]");

    die;
  }

  //-------- Action: Set sticky on/off

  if ($action == "setsticky")
  {
    loggedinorreturn();
    $topicid = (int)$_POST["topicid"];

    if (!topicid || get_user_class() < UC_MODERATOR)
      die;

	$sticky = sqlesc($_POST["sticky"]);
    sql_query("UPDATE LOW_PRIORITY topics SET sticky=$sticky WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: $_POST[returnto]");

    die;
  }

  //-------- Action: Rename topic

  if ($action == 'renametopic')
  {
    loggedinorreturn();
  	if (get_user_class() < UC_MODERATOR)
  	  die;

  	$topicid = (int)$_POST['topicid'];

  	$subject = $_POST['subject'];

  	if ($subject == '')
  	  stderr('Error', 'You must enter a new title!');

  	$subject = sqlesc($subject);

  	sql_query("UPDATE LOW_PRIORITY topics SET subject=$subject WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

  	$returnto = $_POST['returnto'];

  	if ($returnto)
  	  header("Location: $returnto");

  	die;
  }

   //-------- Action: View forum

if ($action == "viewforum") //-------- Action: View forum
{
	$forumid = (int)$_GET['forumid'];
	if (!is_valid_id($forumid))
		stderr('Error', 'Invalid ID!');
		
	$userid = (int)$CURUSER["id"];

	//------ Get forum details
	$res = sql_query("SELECT f.name AS forum_name FROM forums AS f ".

					   "WHERE f.id = ".sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res) or stderr('Error', 'No forum with that ID!');
	
	$res1 = sql_query("SELECT COUNT(id) FROM topics WHERE forumid = $forumid"); 
	$row1 = mysqli_fetch_array($res1); 
	$count = $row1[0]; 

	$perpage = $postsperpage;
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "/forum/view/forum/id$forumid&" ); 




	
	$topics_res = sql_query(
	"SELECT t.id, t.userid,t.ratingsum,t.numratings, t.views, t.locked, t.lastpost AS tlast, t.sticky, t.subject, u1.username, u1.class, r.lastpostread, p.id AS p_id, p.userid AS p_userid, p.added AS p_added, 
	(SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count,  u2.class AS u2_class , u2.username AS u2_username ".
	"FROM topics AS t ".
	"LEFT JOIN users AS u1 ON u1.id=t.userid ".
	"LEFT JOIN readposts AS r ON r.userid = ".sqlesc($userid)." AND r.topicid = t.id ".
	"LEFT JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topicid = t.id) ".
	"LEFT JOIN users AS u2 ON u2.id = p.userid ".

	"WHERE t.forumid = ".sqlesc($forumid)." ORDER BY t.sticky, p_added DESC $limit") or sqlerr(__FILE__, __LINE__);
	
	stdhead("Форум - ".htmlspecialchars($arr["forum_name"])); 
	begin_main_frame();
	
	?>
	<link type="text/css" rel="stylesheet" href="<?=$DEFAULTBASEURL?>/css/rating_style.css" />
	<h1><a href="/forum">Форум</a> - <?php echo htmlspecialchars($arr["forum_name"]); ?></h1><?php
	
		echo $pagertop; 
	if (mysqli_num_rows($topics_res) > 0)
	{
		?><table border="0px" cellspacing=0 cellpadding=5 width=<?php echo $forum_width; ?>>
		<tr>
			<td class=colhead align=left>Тема</td>
			<td class=colhead>Ответов</td>
			<td class=colhead>Просмотров</td>
			<td class=colhead align=left>Автор</td>
			<td class=colhead align=left><nobr>Последние сообщение</nobr></td>
		</tr>
		<?php
		while ($topic_arr = mysqli_fetch_assoc($topics_res))
		{
			$topicid = (int)$topic_arr['id'];
			$topic_userid = (int)$topic_arr['userid'];
			$sticky = ($topic_arr['sticky'] == "yes");
			$lpost = (int)$topic_arr["tlast"];
			
			$tpages = floor($topic_arr['p_count'] / $postsperpage);
			
			if (($tpages * $postsperpage) != $topic_arr['p_count'])
				++$tpages;
			
			if ($tpages > 1)
			{
				$topicpages = "&nbsp;(<img src='".$pic_base_url."multipage.gif' alt='Много страничная тема' title='Много страничная тема'>";
				$split = ($tpages > 10) ? true : false;
				$flag = false;
				
				for ($i = 1; $i <= $tpages; ++$i)
				{
					if ($split && ($i > 4 && $i < ($tpages - 3)))
					{
						if (!$flag)
						{
							$topicpages .= '&nbsp;...';
							$flag = true;
						}
						continue;
					}
					$topicpages .= "&nbsp;<a href=/forum/view/topic/id$topicid&page=$i>$i</a>";
				}
				$topicpages .= ")";
			}
			else
				$topicpages = '';
			$lpusername = (is_valid_id($topic_arr['p_userid']) && !empty($topic_arr['u2_username']) ? "<a href='$DEFAULTBASEURL/user/id".(int)$topic_arr['p_userid']."'>".get_user_class_color($topic_arr['u2_class'],$topic_arr['u2_username'])."</b></a>" : "unknown[$topic_userid]");
			$lpauthor = (is_valid_id($topic_arr['userid']) && !empty($topic_arr['username']) ? "<a href='$DEFAULTBASEURL/user/id$topic_userid'>".get_user_class_color($topic_arr['class'],$topic_arr['username'])."</b></a>" : "unknown[$topic_userid]");
			$new = ($topic_arr["p_added"] > (get_date_time(gmtime() - $READPOST_EXPIRY))) ? ((int)$topic_arr['p_id'] > $topic_arr['lastpostread']) : 0;
			$topicpic = ($topic_arr['locked'] == "yes" ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));
			
			?>
			<tr>
				<td align=left width="100%">
					<table border=0 cellspacing=0 cellpadding=0>
						<tr>
							<td class=embedded style='padding-right: 5px'><img src='<?php echo $pic_base_url.$topicpic; ?>.gif'></td>
							<td class=embedded align=left width="100%"><?php echo ($sticky ? '<img src='.$pic_base_url.'/fsticky.gif border=0px />&nbsp;' : ''); ?><a href='/forum/view/topic/id<?php echo $topicid; ?>' title="<?php echo htmlspecialchars($topic_arr['subject']); ?>"><?php echo htmlspecialchars($topic_arr['subject']); ?></a><?php echo $topicpages; ?></td>
							<td class="embedded" align="right"><?php echo(showRate($topic_arr["ratingsum"],$topic_arr["numratings"]))?></td>

						</tr>
					</table>
				</td>
				<td align="center"><?php echo max(0, $topic_arr['p_count'] - 1); ?></td>
				<td align="center"><?php echo number_format($topic_arr['views']); ?></td>
				<td align="center"><?php echo $lpauthor; ?></td>
				<td align='left'>&nbsp;<?php echo $lpusername; ?>&nbsp;<a href="/forum/view/topic/id<?=$topicid;?>&amp;page=p<?=$lpost;?>#<?=$lpost;?>"><img src='<?=$pic_base_url;?>/latest.gif' border='0px' alt='Читать сообщение'></a><br />&nbsp;<?php echo display_date_time($topic_arr["p_added"]); ?></td>
			</tr>
			<?php
		}
		
		end_table();
	}
	else
	{
		?><p align=center>No topics found</p><?php
	}
	
		echo $pagerbottom; 
	?>
	<table class=main border=0 cellspacing=0 cellpadding=0 align=center>
	<tr valing=center>
		<td class=embedded><img src='<?php echo $pic_base_url; ?>unlockednew.gif' style='margin-right: 5px'></td>
		<td class=embedded>Новое сообщение</td>
		<td class=embedded><img src='<?php echo $pic_base_url; ?>locked.gif' style='margin-left: 10px; margin-right: 5px'></td>
		<td class=embedded>Тема закрыта</td>
	</tr>
	</table>
	<?php
	$arr = ($forumid) or die();
	
	$maypost = ($CURUSER);
	
	if (!$maypost)
	{
		?><p><i>У Вас нету прав открывать новые Темы.</i></p><?php
	}
	
	?>
	<table border=0 class=main cellspacing=0 cellpadding=0 align=center>
	<tr>
	<?php
	if ($maypost)
	{
		?>
		<td class=embedded><form method=get action='/forum'><input type=hidden name=action value=newtopic><input type=hidden name=forumid value=<?php echo $forumid; ?>><input type=submit value='Новая Тема' class=gobutton style='margin-left: 10px'></form></td>
		<?php
	}
	
	?></tr></table><?php
	
	
	forum_menu_bottom();
	end_main_frame(); 
	stdfoot();
	exit();
}


if ($action == "getdaily") {
loggedinorreturn();

	stdhead("Сообщения за последние 24 ч.");
	begin_main_frame();
	begin_frame("Сообщения за последние 24 ч.");
	$page = 0 + (int)$_GET["page"];
	$perpage = 10;
	$r = sql_query("SELECT COUNT(*) FROM posts WHERE  posts.added >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)") or sqlerr(__FILE__,__LINE__);
	$r1 = mysqli_fetch_array($r); 
	$countrows = $r1[0];
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $countrows, "forum?action=getdaily&");
	print("<table width=100% border=0 cellspacing=0 cellpadding=5><tr>".
	"<td class=colhead align=left>Тема</td>".
	"<td class=colhead align=center>Просмотров</td>".
	"<td class=colhead align=center>Автор</td>".
	"<td class=colhead align=center>Добавлено</td>".
	"</tr>");
	$res = sql_query("SELECT posts.id AS pid, posts.topicid, posts.userid AS userpost, posts.added, topics.id AS tid, topics.subject, topics.forumid, topics.lastpost, topics.views, forums.name, forums.topiccount, users.username
	FROM posts, topics, forums, users, users AS topicposter
	WHERE posts.topicid = topics.id AND posts.added >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY) AND topics.forumid = forums.id AND posts.userid = users.id AND topics.userid = topicposter.id
	ORDER BY posts.added DESC $limit") or sqlerr(__FILE__,__LINE__);
	while ($getdaily = mysqli_fetch_assoc($res))
	{		
		print("<tr><td><a href=\"/forum/view/topic/id{$getdaily["tid"]}&page=p{$getdaily["pid"]}#{$getdaily["pid"]}\"><b>".htmlspecialchars($getdaily["subject"])."</b></a><br />в <a href=\"/forum/view/forum/id{$getdaily["forumid"]}\">{$getdaily["name"]}</a></td>".
		"<td align=center>{$getdaily["views"]}</td>".
		"<td align=center><a href=user/id{$getdaily["userpost"]}><b>{$getdaily["username"]}</b></a></td>".
		"<td><center>".display_date_time($getdaily["added"])."</td></tr>");
	}
	print("</table></br>");
	print("$pagerbottom");
	forum_menu_bottom();
	end_frame();
	end_main_frame();
	stdfoot();
	die;
	}

if ($action == "search") //-------- Action: Search
{
	stdhead("Forum Search");
	begin_main_frame();
	begin_table();
	$error = false;
	$found = '';
	$keywords = (isset($_GET['keywords']) ? trim($_GET['keywords']) : '');
		$keywords = htmlspecialchars($keywords);
		//$keywords = urlencode($keywords);
		?><style type="text/css">
<!--
.search{
	width:159px;
	
	margin:5px 0 5px 0;
	text-align:left;
}
.search_title{
	color:#0062AE;
	background-color:#DAF3FB;
	font-size:12px;
	font-weight:bold;
	text-align:left;
	padding:7px 0 0 15px;
}

.search_table {
  border-collapse: collapse;
  border: none;
   
}
-->
</style>
<?
begin_frame("Поиск по форуму",70);
?>
<center>
 <?=($error ? "[<b><font color=red> Ничего не найдено</font></b> ]" : $found)?></div>
<form method="get" action="forum" id="search_form" style="margin: 0pt; padding: 0pt; font-family: Tahoma,Arial,Helvetica,sans-serif; font-size: 11px;">
<input type="hidden" name="action" value="search">

			<input name="keywords" type="text" value="<?=$keywords?>" size="65" />
            <input type=submit value=Поиск class=gobutton>
    </form>
<?
	end_frame();
		$error = false;
	$found = '';
	$keywords = (isset($_GET['keywords']) ? trim($_GET['keywords']) : '');
		$keywords = htmlspecialchars($keywords);
	//	$keywords = urlencode($keywords);
	if (!empty($keywords))
	{
		$res = sql_query("SELECT COUNT(id) AS c FROM posts WHERE body LIKE ".sqlesc("%".sqlwildcardesc($keywords)."%")) or sqlerr(__FILE__, __LINE__);
		$arr = mysqli_fetch_assoc($res);
		$count = (int)$arr['c'];

		
		if ($count == 0)
			$error = true;
		else
		{
			list($pagertop, $pagerbottom, $limit) = pager(10, $count, '/forum?action='.$action.'&amp;keywords='.$keywords.'&');
			
			$res = sql_query(
			"SELECT p.id, p.topicid, p.userid, p.added, t.forumid, t.subject, f.name, u.username ".
			"FROM posts AS p ".
			"LEFT JOIN topics AS t ON t.id=p.topicid ".
			"LEFT JOIN forums AS f ON f.id=t.forumid ".
			"LEFT JOIN users AS u ON u.id=p.userid ".
			"WHERE p.body LIKE ".sqlesc("%".$keywords."%")." $limit");
	
			$num = mysqli_num_rows($res);
			echo "<p>$pagertop</p>";
			begin_main_frame();
			
			?>
            <table border=0 cellspacing=0 cellpadding=5 width='100%'>
			<tr align="left">
            	<td class=colhead>Сообщение</td>
                <td class=colhead>Тема</td>
                <td class=colhead>Форум</td>
                <td class=colhead>Автор</td>
			</tr>
            <?php
			for ($i = 0; $i < $num; ++$i)
			{
				$post = mysqli_fetch_assoc($res);

	
				echo "<tr>".
					 	"<td align='center'>".$post['id']."</td>".
						"<td align=left width='100%'><a href=/forum/view/topic/id".$post['topicid']."&amp;highlight=$keywords&amp;page=p".$post['id']."#".$post['id']."><b>" . htmlspecialchars($post['subject']) . "</b></a></td>".
						"<td align=left><nobr>".(empty($post['name']) ? 'unknown['.$post['forumid'].']' : "<a href=/forum/view/forum/id".$post['forumid']."><b>" . htmlspecialchars($post['name']) . "</b></a>")."</nobr></td>".
						"<td align=left><nobr>".(empty($post['username']) ? 'unknown['.$post['userid'].']' : "<b><a href='$DEFAULTBASEURL/user/id".$post['userid']."'>".$post['username']."</a></b>")."<br />&nbsp;".display_date_time($post['added'])."</nobr></td>".
					 "</tr>";
			}
			end_table();
			
			end_main_frame();
			echo "<p>$pagerbottom</p>";
			echo "[<b><font color=red> Найдено $count сообщений </font></b> ]";
			
		}
	}
	forum_menu_bottom();

	end_main_frame();
	stdfoot();
	exit();
}

 if ($action == 'forumview')
{
	$ovfid = (isset($_GET["forid"]) ? (int)$_GET["forid"] : 0);
	if (!is_valid_id($ovfid))
		stderr('Error', 'Invalid ID!');

		
	$res = sql_query("SELECT name FROM overforums WHERE id = $ovfid") or sqlerr(__FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res) or stderr('Sorry', 'No forums with that ID!');
	
	
		if ($CURUSER)
	sql_query("UPDATE LOW_PRIORITY users SET forum_access = ".sqlesc(get_date_time())." WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
	
	stdhead("Форум - ".htmlspecialchars($arr["name"]));
	begin_main_frame();
	
	?>
	<h1 align="center"><b><a href='/forum'>Форум</a></b> - <?php echo htmlspecialchars($arr["name"]); ?></h1>
	
	<table border=0 cellspacing=0 cellpadding=3 width='<?php echo $forum_width; ?>'>
		<tr>
        	<td class=colhead align=left>ФОРУМ</td>
            <td class=colhead align=right>ТЕМ</td>
		<td class=colhead align=right>СООБЩЕНИЙ</td>
		<td class=colhead align=left>ПОСЛЕДНИЕ СООБЩЕНИЕ</td>
	</tr>
	<?php
	
	show_forums($ovfid);
		
	end_table();
forum_menu_bottom();
	end_main_frame(); 
	stdfoot();
	exit();
}
  //-------- Handle unknown action

  if ($action != "")
    stderr("Forum Error", "Unknown action");

  //-------- Default action: View forums

	if (isset($_GET["catchup"]))
	{
		catch_up();
		
		header('Location: /forum');
		exit();
	}
	
	
  //-------- ФОРУМ ГЛАВНАЯ
  	if ($CURUSER)
  sql_query("UPDATE LOW_PRIORITY users SET forum_access='" . get_date_time() . "' WHERE id={$CURUSER["id"]}");

   stdhead("Аниме Форум"); 
   begin_main_frame();
	
	?><h1 align="center"><b><?php echo $SITENAME; ?> - Форум</b></h1>
	<br />
	<table border=0 cellspacing=0 cellpadding=5 width='<?php echo $forum_width; ?>'><?php
	
	
	if (cache_check("overforums", 3600))  {
	$ovf_res = cache_read("overforums");
    }
    else {
	$ovf_res = sql_query("SELECT id, name FROM overforums ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);
			$overforums_cache = array();
	while ($cache_data = mysqli_fetch_array($ovf_res))
		$overforums_cache[] = $cache_data;

	cache_write("overforums", $overforums_cache);
	$ovf_res = $overforums_cache;
     }
	 foreach ($ovf_res as $ovf_arr)
	 {


		$ovfid = (int)$ovf_arr["id"];
		$ovfname = $ovf_arr["name"];
	
		?><tr>
			<td align='left' id="no_border" class='colhead' width="100%">
				<a href='/forum/view/forid/id<?php echo $ovfid; ?>'><span class="c_title"><?php echo htmlspecialchars($ovfname); ?></span></a>
			</td>
			<td id="no_border" align='right'><b>ТЕМЫ</b></td>
			<td id="no_border" align='right'><b>СООБЩЕНИЯ</b></td>
			<td id="no_border" align='left'><nobr><b>ПОСЛЕДНЕЕ СООБЩЕНИЕ</b></nobr></td>
		</tr><?php
	
		show_forums($ovfid);
	}
	

print("</table>");
forum_menu_bottom();

forum_stats();
end_main_frame();
stdfoot();
?>