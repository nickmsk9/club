<?php

require_once("include/bittorrent.php");
// Ensure private message inbox constant is defined
if (!defined('PM_INBOX')) {
    define('PM_INBOX', 1);
}
dbconn(false);
global $mysqli;
loggedinorreturn();

if (isset($_GET['id']))
    $id = (int)$_GET['id'];
else
    $id = (int)$CURUSER['id'];
if (isset($_GET['user']))
    $user = (int)$_GET['user'];
if (isset($_GET['act']))
    $action = (string)htmlspecialchars($_GET['act']);
else
    $action = "view";

if (empty($id) || empty($action))
    stderr("Ошибка", "Нет доступа");

// action: add -------------------------------------------------------------

if ($action == 'add')
{

    // Use the currently logged-in user as the actor
    $userid = (int)$CURUSER['id'];

    $res = sql_query("SELECT * FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_array($res) or stderr("Error", "No user with ID.");

    $targetid = (int)$_GET['targetid'];
    $type = $_GET['type'];

    if (!is_valid_id($targetid))
        stderr("Error", "Invalid ID.");

    if ($type == 'friend')
    {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
    }
    elseif ($type == 'block')
    {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    }
    else
        stderr("Error", "Unknown type.");

    $r = sql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($r) == 1)
        stderr("Error", "User ID is already in your ".htmlentities($table_is)." list.");

    sql_query("INSERT INTO $table_is(id,userid, $field_is) VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);
    $r = sql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
    $a = mysqli_fetch_array($r);
    $newid = (int)$a['id'];

    if ($type == 'friend') {
        $body = "Пользователь [url=user/id{$CURUSER['id']}]{$CURUSER['username']}[/url] желает добавить Вас в список друзей. [url=friends.php?act=accept&id={$newid}&user={$CURUSER['id']}]Принять[/url] [url=friends.php?act=surrender&id={$newid}&user={$CURUSER['id']}]Отклонить[/url]";
        $subject = "Предложение дружбы.";
        $dt = sqlesc(get_date_time());
        sql_query(
            "INSERT INTO messages 
                (sender, receiver, subject, msg, added, saved, location, spam, unread) 
             VALUES ("
                . sqlesc($CURUSER['id']) . ", $targetid, " 
                . sqlesc($subject) . ", " 
                . sqlesc($body) . ", $dt, " 
                . sqlesc('no') . ", " 
                . (defined('PM_INBOX') ? PM_INBOX : 1) . ", 0, " 
                . sqlesc('yes') . 
             ")"
        ) or sqlerr(__FILE__, (string)__LINE__);
    }
    stderr("Подтверждение", "Дождитесь подтверждения пользователя.");
    die;
}


// action: delete ----------------------------------------------------------

if ($action == 'delete')
{
	$targetid = (int)$_GET['targetid'];
	$sure = htmlentities($_GET['sure']);
	$type = htmlentities($_GET['type']);

  if (!is_valid_id($targetid))
		stderr("Error", "Invalid ID.");

  if (!$sure)
    stderr("Delete $type","Do you really want to delete a $type? Click\n" .
    	"<a href=?id=$userid&action=delete&type=$type&targetid=$targetid&sure=1>here</a> if you are sure.");

  if ($type == 'friend')
  {
    sql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($mysqli) == 0)
      stderr("Error", "No friend found with ID");
    $frag = "friends";
  }
  elseif ($type == 'block')
  {
    sql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($mysqli) == 0)
      stderr("Error", "No block found with ID");
    $frag = "blocks";
  }
  else
    stderr("Error", "Unknown type.");

  header("Location: $BASEURL/friends.php");
  die;
}
	
if ($_SERVER["REQUEST_METHOD"] == "GET" && in_array($action, array('accept', 'surrender', 'view')))
{
    if ($action == "accept" && !empty($user))
    {
	
		$res = sql_query("SELECT * FROM friends WHERE userid = " . sqlesc($CURUSER['id']) . "  AND friendid = $user");
		$row = mysqli_fetch_array($res);
		if ($row > 0){
		        header("Refresh: 2; url=" . $DEFAULTBASEURL . "/friends.php");
        stderr("Ошибка", "Пользователь добавлен в список друзей ранее");
		}
		
        sql_query("UPDATE friends SET status = 'yes' WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        sql_query("INSERT INTO friends (userid, friendid, status) VALUES (" . sqlesc($CURUSER['id']) . ", $user, 'yes')") or sqlerr(__FILE__, __LINE__);
        $dt = sqlesc(get_date_time());
        $msg = sqlesc("Пользователь [url=user/id" . $CURUSER['id'] . "]" . $CURUSER['username'] . "[/url] согласился на дружбу.");
        $subj = sqlesc("Ответ на предложение дружбы.");
        sql_query(
            "INSERT INTO messages 
             (sender, receiver, subject, msg, added, saved, location, spam, unread)
             VALUES ("
                . sqlesc($CURUSER['id']) . ", $user, "
                . sqlesc($subj) . ", "
                . sqlesc($msg) . ", "
                . $dt . ", "
                . sqlesc('no') . ", "
                . PM_INBOX . ", 0, "
                . sqlesc('yes') .
             ")"
        ) or sqlerr(__FILE__, (string)__LINE__);
        header("Refresh: 2; url=" . $DEFAULTBASEURL . "/friends.php");
        stderr("Успешно", "Пользователь добавлен в список друзей");
    }
    elseif ($action == "surrender" && !empty($user))
    {
        sql_query("UPDATE friends SET status = 'no' WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM friends WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        $dt = sqlesc(get_date_time());
        $msg = sqlesc("Пользователь [url=user/id" . $CURUSER['id'] . "]" . $CURUSER['username'] . "[/url] отказался от дружбы.");
        $subj = sqlesc("Ответ на предложение дружбы.");
        sql_query(
            "INSERT INTO messages 
             (sender, receiver, subject, msg, added, saved, location, spam, unread)
             VALUES ("
                . sqlesc($CURUSER['id']) . ", $user, "
                . sqlesc($subj) . ", "
                . sqlesc($msg) . ", "
                . $dt . ", "
                . sqlesc('no') . ", "
                . PM_INBOX . ", 0, "
                . sqlesc('yes') .
             ")"
        ) or sqlerr(__FILE__, (string)__LINE__);
        header("Refresh: 2; url=" . $DEFAULTBASEURL . "/friends.php");
        stderr("Успешно", "Пользователю отказано в дружбе");
    }
    elseif ($action == "view" && !empty($id))
    {
        $check_res = sql_query("SELECT username, class, viewfriends FROM users WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($check_res) > 0)
        {
            $check = mysqli_fetch_array($check_res);
            if ($check['viewfriends'] == 'yes' || $id == $CURUSER['id'])
            {
                stdhead("Друзья " . $check['username']);
				begin_main_frame();
                begin_frame("Друзья " . get_user_class_color($check['class'], $check['username']));
                ?>
                  <link rel="stylesheet" href="css/user.css" type="text/css">
                  <script language="JavaScript" type="text/javascript">
                    /*<![CDATA[*/
                    addtofriends = function(user, type, element) {
                      jQuery.post("user.php",{"user":user,"type":type,"act":"addtofriends"},function (response) {
                        jQuery("#" + element).empty();
                        jQuery("#" + element).append(response);
                      });
                    };
                    /*]]>*/
                  </script>
                <?
                $res = sql_query("SELECT f.friendid as id, u.username AS name, u.class, u.avatar, u.gender, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE f.userid=$id AND f.status = 'yes' ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
                if (mysqli_num_rows($res) > 0)
                {
                    print("<div id=\"friends\">\n");
                    while ($row = mysqli_fetch_array($res))
                    {
                        if (empty($row['avatar']))
                            $avatar = "pic/default_avatar.gif";
                        else
                            $avatar = $row['avatar'];
                        $dt = get_date_time(gmtime() - 300);
                        if ($row['last_access'] > $dt)
                            $status = "<font color=\"#008000\">Онлайн</font>";
                        else
                            $status = "<font color=\"#FF0000\">Оффлайн</font>";
                        if ($row["gender"] == "1")
                            $gender = "<img src=\"pic/male.gif\" alt=\"Парень\" title=\"Парень\" />";
                        else
                            $gender = "<img src=\"pic/female.gif\" alt=\"Девушка\" title=\"Девушка\" />";
                        print("<div class=\"friend\" id=\"friend_" . $row['id'] . "\">\n");
                        print("<div class=\"avatar\"><a href=\"user/id" . $row['id'] . "\"><img src=\"$avatar\" alt=\"\" /></a></div>\n");
                        print("<div class=\"finfo\" align=\"left\">\n");
                        print("<p><b>Имя:</b>&nbsp;<a href=\"user/id" . $row['id'] . "\">" . get_user_class_color($row['class'], $row['name']) . "</a></p>\n");
                        print("<p><b>Пол:</b>&nbsp;$gender</p>\n");
                        print("<p><b>Класс:</b>&nbsp;" . get_user_class_name($row['class']) . "</p>\n");
                        print("<p><b>Статус:</b>&nbsp;$status</p>\n");
                        print("</div>\n");
                        print("<div class=\"actions\" align=\"right\">\n");
                        print("<p><a href=\"message.php?action=sendmessage&receiver=" . $row['id'] . "\">Отправить сообщение</a></p>\n");
                        print("<p><a href=\"friends.php?id=" . $row['id'] . "\">Друзья " . get_user_class_color($row['class'], $row['name']) . "</a></p>\n");
                        if ($CURUSER['id'] == $id)
                            print("<p><a href=\"javascript: addtofriends('" . $row['id'] . "', 'delete', 'friend_" . $row['id'] . "')\">Убрать из друзей</a></p>\n");
                        print("</div>\n");
                        print("<div style=\"clear:both;\"></div>\n");
                        print("</div>\n");
                    }
                    print("</div>\n");
                }
                else
                    print("<div class=\"error\">У пользователя нет друзей.</div>\n");
                end_frame();
				end_main_frame();
                stdfoot();
            }
            else
                stderr("Ошибка","Пользователь предпочел скрыть эту страницу.");
        }
        else
            stderr("Ошибка", "Нет пользователя с таким идентификатором");
    }
    else
        stderr("Ошибка", "Нет доступа");
}

?>