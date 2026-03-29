<?php
require_once("include/bittorrent.php");
dbconn(false);

loggedinorreturn();
function bark($msg) {
    global $tracker_lang;
    stdhead($tracker_lang['error']);
    stdmsg($tracker_lang['error'], $msg);
    stdfoot();
    exit;
}

global $BASEURL, $tracker_lang;

// Инициализация Memcached
$memcache_obj = false;
if (class_exists('Memcached')) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('localhost', 11211) or die ("Could not connect to Memcached");
} elseif (class_exists('Memcache')) {
    $memcache_obj = new Memcache();
    $memcache_obj->connect('localhost', 11211) or die ("Could not connect to Memcached");
}

#### ДАННЫЕ --> ####
$id = (int)$_GET["id"];

if (!is_valid_id($id))
    bark($tracker_lang['invalid_id']);

// Try to get user data from cache
$user = false;
if ($memcache_obj) {
    $user = $memcache_obj->get('user_details_'.$id);
}

if ($user === false) {
    // Cache miss or Memcached not available, fetch from database
    $res = sql_query("SELECT * FROM users WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($res);
    
    if ($user && $memcache_obj) {
        // Cache user data with random expiration between 5-10 minutes
        $memcache_obj->set('user_details_'.$id, $user, rand(300, 600));
    }
}

if (!$user) {
    header("HTTP/1.0 404 Not Found"); 
    stderr($lang['error'], $lang['invalid_id']);
} else {
    // Остальной код остается без изменений
    if ($user["ip"] && (get_user_class() >= UC_MODERATOR || $user["id"] == $CURUSER["id"])) {
        $ip = $user["ip"];
        $addr = "$ip";
    }

    if ($user["downloaded"] > 0) {
        $rating = $user["uploaded"] / $user["downloaded"];
        $rating = floor($rating * 1000) / 1000;
        $rating = "<font color=\"" . get_ratio_color($rating) . "\">" . number_format($rating, 2) . "</font>";
    } else {
        $rating = "N/A";
    }
    
    if ($user["added"] == "0000-00-00 00:00:00") {
        $joindate = "N/A";
    } else {
        $elapsed = get_elapsed_time(strtotime($user["added"]));
        $joindate = nicetime(display_date_time($user["added"]))." ($elapsed назад)";
    }
    
    if ($user["added"] == $user["last_access"] || $user["last_access"] == "0000-00-00 00:00:00") {
        $lastseen = "Never";
    } else {
        $elapsed = get_elapsed_time(strtotime($user["last_access"]));
        $lastseen = display_date_time($user["last_access"])." ($elapsed назад)";
    }
    
    $tz = array(
        "-720" => "GMT - 12:00 часов (DLW)",
        "-660" => "GMT - 11:00 часов (NT)",
        "-600" => "GMT - 10:00 часов (HST)",
        "-540" => "GMT - 9:00 часов (YST)",
        "-480" => "GMT - 8:00 часов (PST)",
        "-420" => "GMT - 7:00 часов (MST)",
        "-360" => "GMT - 6:00 часов (CST)",
        "-300" => "GMT - 5:00 часов (EST)",
        "-240" => "GMT - 4:00 часа (AST)",
        "-210" => "GMT - 3:30 часа (GST)",
        "-180" => "GMT - 3:00 часа (ADT)",
        "-120" => "GMT - 2:00 часа (FST)",
        "-60"  => "GMT - 1:00 час (WAT)",
        "0"    => "GMT (Universal Time)",
        "60"   => "GMT + 1:00 час (CET)",
        "120"  => "GMT + 2:00 часа (EET)",
        "180"  => "GMT + 3:00 часа (MSK)",
        "210"  => "GMT + 3:30 часа (NST)",
        "240"  => "GMT + 4:00 часов (GST)",
        "300"  => "GMT + 5:00 часов (TMT)",
        "330"  => "GMT + 5:30 часов (IST)",
        "360"  => "GMT + 6:00 часов (BT)",
        "420"  => "GMT + 7:00 часов (ICT)",
        "480"  => "GMT + 8:00 hчасов (CCT)",
        "540"  => "GMT + 9:00 часов (JST)",
        "570"  => "GMT + 9:30 часов (ACST)",
        "600"  => "GMT + 10:00 часов (GST)",
        "660"  => "GMT + 11:00 часов (AEDT)",
        "720"  => "GMT + 12:00 часов (NZST)"
    );

    if ($user["gender"] == "1") {
        $gender = "<img src=\"".$DEFAULTBASEURL."/pic/male.gif\" alt=\"Парень\" title=\"Парень\">";
    } else {
        $gender = "<img src=\"".$DEFAULTBASEURL."/pic/female.gif\" alt=\"Девушка\" title=\"Девушка\">";
    }

    $tzoffset = isset($CURUSER['tzoffset']) ? (int)$CURUSER['tzoffset'] : 0;

if ($user['birthday'] != "0000-00-00") {
    $current = date("Y-m-d", time() + $tzoffset * 60);
    list($year2, $month2, $day2) = explode('-', $current);
    $birthday = $user["birthday"];
    $birthday = date("Y-m-d", strtotime($birthday));
    list($year1, $month1, $day1) = explode('-', $birthday);

    if ($month2 < $month1) {
        $age = $year2 - $year1 - 1;
    } elseif ($month2 == $month1) {
        if ($day2 < $day1) {
            $age = $year2 - $year1 - 1;
        } else {
            $age = $year2 - $year1;
        }
    } else { // $month2 > $month1
        $age = $year2 - $year1;
    }
}

   // Try to get country data from Memcached
$countryarr = $memcache_obj->get('country_'.$user['country']);
if ($countryarr === false) {
    $country_res = sql_query("SELECT name, flagpic FROM countries WHERE id = ".sqlesc($user['country'])." LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $countryarr = mysqli_fetch_assoc($country_res);
    if ($countryarr) {
        // Cache country data indefinitely (until manually cleared)
        $memcache_obj->set('country_'.$user['country'], $countryarr, 0);
    }
}
    if (!empty($countryarr['flagpic']) && !empty($countryarr['name'])) {
        $country = "<img src=\"{$DEFAULTBASEURL}/pic/flag/{$countryarr['flagpic']}\" alt=\"{$countryarr['name']}\" style=\"margin-left: 8pt\">";
    } else {
        $country = '';
    }

    if (!empty($user['avatar'])) {
        $avatar = "<img src=\"" . $user['avatar'] . "\" style=\"width:100px;border:3px double #ccc;\" title=\"\" alt=\"\" />";
    } else {
        $avatar = "<img src=\"".$DEFAULTBASEURL."/pic/default_avatar.gif\" style=\"width:100px;border:3px double #ccc;\" title=\"\" alt=\"\" />";
    }

    if ($user['last_access'] > (get_date_time(gmtime() - 900))) {
        $status = "<font color=\"#008000\">Онлайн</font>";
    } else {
        $status = "<font color=\"#FF0000\">Оффлайн</font>";
    }
}

	
	
#### <-- ДАННЫЕ ####

stdhead("Просмотр профиля " . $user["username"]);
begin_main_frame();
print("<link rel=\"stylesheet\" href=\"".$DEFAULTBASEURL."/css/user.css\" type=\"text/css\">\n");
print("<div id=\"profile_container\">");
print("<div id=\"profile_left\">");

begin_frame(get_user_class_color($user['class'], $user['username']) . get_user_icons($user). $country);
?>
<table class="inlay" width="100%">
    <tr valign="top">
        <td width="100px"><?=$avatar?></td>
        <td>
            <p class=small><u><?=get_user_class_color($user['class'], get_user_class_name($user['class']))?></u></p>

            <p class=small><b>Скачал:</b> <?=str_replace(" ", "&nbsp;", mksize($user['downloaded']))?></p>
            <p class=small><b>Раздал:</b> <?=str_replace(" ", "&nbsp;", mksize($user['uploaded']))?></p>
            <p class=small><b>Бонус:</b> <?=$user['bonus']?></p>
            <p class=small><b>Рейтинг:</b> <?=$rating?></p>
            <p class=small><b>Статус:</b> <?=$status?></p>
        </td>
    </tr>
</table>
<?
end_frame();
if ($CURUSER){
if ($user['id'] != $CURUSER['id'])
{
begin_frame("Подарить бонус");
    print("<table class=\"inlay\" align=\"center\">\n");
    print("<tr>\n");
    print("<td style=\"border:none;cursor:pointer;\"><img src=\"".$DEFAULTBASEURL."/pic/10.png\" alt=\"10\" title=\"Подарить 10 бонусов\" onclick=\"javascript:present('" . $CURUSER['id'] . "', '$id', '10');\" /></td>\n");
    print("<td style=\"border:none;cursor:pointer;\"><img src=\"".$DEFAULTBASEURL."/pic/25.png\" alt=\"25\" title=\"Подарить 25 бонусов\" onclick=\"javascript:present('" . $CURUSER['id'] . "', '$id', '25');\" /></td>\n");
    print("<td style=\"border:none;cursor:pointer;\"><img src=\"".$DEFAULTBASEURL."/pic/50.png\" alt=\"50\" title=\"Подарить 50 бонусов\" onclick=\"javascript:present('" . $CURUSER['id'] . "', '$id', '50');\" /></td>\n");
    print("<td style=\"border:none;cursor:pointer;\"><img src=\"".$DEFAULTBASEURL."/pic/100.png\" alt=\"100\" title=\"Подарить 100 бонусов\" onclick=\"javascript:present('" . $CURUSER['id'] . "', '$id', '100');\" /></td>\n");
    print("</tr>\n");
    print("</table>\n");
    end_frame();
}

if ($user["icq"] || $user["skype"])
{
    begin_frame("Связь");
    print("<table class=\"inlay\" width=\"100%\">\n");
    print("<tr>\n<td width=\"50%\">\n");
    if ($user["icq"])
        print("<p><img style=\"vertical-align: middle;\" src=\"".$DEFAULTBASEURL."/pic/contact/icq.gif\" alt=\"icq\" border=\"0\" /> " . $user['icq'] . "</p>\n");
if ($user["skype"])
        print("<p><img style=\"vertical-align: middle;\" src=\"".$DEFAULTBASEURL."/pic/contact/skype.gif\" alt=\"skype\" border=\"0\" /> " . $user['skype'] . "</p>\n");
    print("</td>\n<tr>\n</table>\n");
    end_frame();
}

?>

<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/fancybox/fancybox.js"></script>
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/js/rep.js"></script>
<script>
	jQuery(document).ready(function() {
		 	 jQuery("a.screen").fancybox({
			'overlayShow' : false,
			});
			
	});
		

</script>
<link rel="stylesheet" type="text/css" href="<?=$DEFAULTBASEURL?>/fancybox/fancybox.css"/>


<? 

if ($user["photo"])
{
    begin_frame("Фото");
    print("<table class=\"inlay\" width=\"100%\">\n");
    print("<tr>\n<td width=\"50%\" style=text-align:center; align='center'>\n");
    $photo_path = $BASEURL . "/photo/" . $user["photo"];
    $photo_file = __DIR__ . "/photo/" . $user["photo"];
    if (!empty($user["photo"]) && file_exists($photo_file)) {
        print("<a href=\"{$photo_path}\" target=\"_blank\"><img border=0 style='max-width:200px; max-height:160px' src=\"{$photo_path}\" /></a>");
    } else {
        print("Фото отсутствует или не найдено.");
    }
    if(get_user_class() >= UC_MODERATOR)
        echo "<br /><a href='".$DEFAULTBASEURL."/foto.php?act=deladmin&id=".$id."'>Удалить Фото</a>";
    print("</td>\n<tr>\n</table>\n");
    end_frame();
}
}
print("</div>");
print("<div id=\"profile_right\">");


begin_frame("Информация");
?>
<table class="inlay" width="100%">
    <tr>
        <td class="rowhead" width="40%">Регистрация:</td>
        <td align="left" width="60%"><?=$joindate?></td>
    </tr>
    <tr>
        <td class=rowhead>Активность:</td>
        <td align=left><?=$lastseen?></td>
    </tr>
<?

if (get_user_class() >= UC_MODERATOR) {
	print("<tr><td class=\"rowhead\">Email:</td><td align=\"left\"><a href=\"mailto:$user[email]\">$user[email]</a></td></tr>\n");
if ($addr)
	print("<tr><td class=\"rowhead\">IP:</td><td align=\"left\"><a href='http://whatismyipaddress.com/ip/$addr' target='_blank'>$addr</a> </td></tr>\n");

	}
  $timezone = '';
  $timezone .= $tz[$user["timezone"]];
print("<tr><td class=\"rowhead\" width=\"40%\">Часовой пояс:</td><td align=\"left\" width=\"60%\">".$timezone ."</td></tr>\n");
print("<tr><td class=\"rowhead\">Пол:</td><td align=\"left\">$gender</td></tr>\n");
if ($CURUSER){
if($user["birthday"]!= '0000-00-00')
{
    print("<tr><td class=\"rowhead\">Возраст:</td><td align=\"left\">$age</td></tr>\n");
    $birthday = nicetime($user["birthday"]);
    print("<tr><td class=\"rowhead\">Родился:</td><td align=\"left\">$birthday</td></tr>\n");

    $month_of_birth = substr($user["birthday"], 5, 2);
    $day_of_birth = substr($user["birthday"], 8, 2);
    for($i = 0; $i < count($zodiac); $i++)
    {
        if (($month_of_birth == substr($zodiac[$i][2], 3, 2)))
        {
            if ($day_of_birth >= substr($zodiac[$i][2], 0, 2))
            {
                $zodiac_img = $zodiac[$i][1];
                $zodiac_name = $zodiac[$i][0];
            }
            else
            {
                if ($i == 11)
                {
                    $zodiac_img = $zodiac[0][1];
                    $zodiac_name = $zodiac[0][0];
                }
                else
                {
                    $zodiac_img = $zodiac[$i+1][1];
                    $zodiac_name = $zodiac[$i+1][0];
                }
            }
        }
    }
    print("<tr><td class=\"rowhead\">По&nbsp;зодиаку:</td><td align=\"left\"><img src=\"".$DEFAULTBASEURL."/pic/zodiac/" . $zodiac_img . "\" alt=\"" . $zodiac_name . "\" title=\"" . $zodiac_name . "\"></td></tr>\n");
}
}

    print("<tr><td class=\"rowhead\">Репутация:</td><td align=\"left\">".karma($user['karma'])."&nbsp;&nbsp;"); 
	if ($CURUSER && $CURUSER['id'] !== $user['id'])
	print ("&nbsp;&nbsp;<a id=\"set_rep\" href=\"/user/id".$user['id']."#rep_form\">изменить</a>");
	
	print("</td></tr>\n");


	if (get_user_class() >= UC_MODERATOR){
	print("<tr><td class=\"rowhead\">Соседи:</td><td align=\"left\"><a href=\"".$DEFAULTBASEURL."/memnet.php?id=".$user['id']."\">посмотреть</a></td></tr>\n");
}


print("</table>\n");

end_frame();
if ($CURUSER){
visitorsHistory($id,5);
    begin_frame("Действия", true);
    print("<p>\n");
	if ($CURUSER['id'] != $id)
{
    print("<a href=\"".$DEFAULTBASEURL."/message.php?receiver=$id&action=sendmessage\"><img src='".$DEFAULTBASEURL."/pic/user/user_l.png' border='0px' alt='Личное сообщение' /></a>&nbsp;|\n");
    $res = sql_query("SELECT id FROM friends WHERE userid=" . sqlesc($CURUSER['id']) . " AND friendid = $id AND status = 'yes'") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0)
        print("<a href=\"javascript:void(0);\" onclick=\"javascript:addtofriends('$id', 'delete');\"><img src='".$DEFAULTBASEURL."/pic/user/user_df.png' border='0px' alt='Удалить из друзей' /></a>&nbsp;|\n");
    else
        print("<a href=\"javascript:void(0);\" onclick=\"javascript:addtofriends('$id', 'add');\"><img src='".$DEFAULTBASEURL."/pic/user/user_af.png' border='0px' alt='Добавить в друзья' /></a>&nbsp;|\n");
}		
    print("<a href=\"javascript:void(0);\" onclick=\"javascript:stat('$id');\"><img src='".$DEFAULTBASEURL."/pic/user/user_s.png' border='0px' alt='Статистика' /></a>\n");
    if (get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class())
        print("&nbsp;|&nbsp;<a href=\"".$DEFAULTBASEURL."/edituser.php?id=$id\"><img src='".$DEFAULTBASEURL."/pic/user/user_m.png' border='0px' alt='Модерирование' /></a>\n");
    print("</p>\n");
    print("<div id=\"actions\"></div>\n");
    end_frame();

}


print("</div>");

print("<div style=\"clear: both;\"></div>");
print("</div>");
if ($CURUSER){
begin_frame("Информация");
print("<div id=\"tabs\">\n");
print("<span class=\"tab active\" id=\"info\">О себе</span>\n");
print("<span class=\"tab\" id=\"friends\">Друзья</span>\n");
print("<span class=\"tab\" id=\"reputation\">Репутация</span>\n");
print("<span class=\"tab\" id=\"presents\">Подарки</span>\n");
print("<span class=\"tab\" id=\"downloaded\">Скачал</span>\n");
print("<span class=\"tab\" id=\"uploaded\">Загрузил</span>\n");
print("<span class=\"tab\" id=\"downloading\">Сейчас качает</span>\n");
print("<span class=\"tab\" id=\"uploading\">Сейчас раздает</span>\n");
if($CURUSER['id'] == $id OR get_user_class() == UC_SYSOP)
//print("<span class=\"tab\" id=\"guests\">Гости профиля</span>\n");
print("<span id=\"loading\"></span>\n");
print("<div id=\"user_tab_body\" data-user=\"$id\">\n");
if (empty($user['info']))
    print("<div class=\"tab_error\">Пользователь не сообщил эту информацию.</div>\n");
else {
    require_once("include/class/class.bbcode.php");
    $parsed = format_comment($user['info'], true);
    $parsed = preg_replace('/\[img\](.*?)\[\/img\]/i', '<img src="$1" alt="user image" />', $parsed);
    print($parsed);
}
print("</div>\n");
print("</div>\n");
print("<script src=\"".$DEFAULTBASEURL."/js/user.js?v=3\" type=\"text/javascript\"></script>\n");

print(
  visitorsList("

  <tr valign=\"top\">
    <td class=\"colhead\" colspan=\"12\" align=\"center\">Сейчас эту страницу просматривают :</td>
  </tr>
  <tr valign=\"top\">
    <td align=\"rowhead\" bgcolor=\"#F4F4F0\" colspan=\"3\">
      <div id=\"visitors\">
        [VISITORS]
      </div>
    </td>
  </tr>\n
", $VISITORS));

end_frame();
if ($CURUSER['id'] !== $user['id']){
		$canres = sql_query(" SELECT added FROM karma WHERE userid = " . sqlesc($user['id']) . " AND fromid = " . sqlesc($CURUSER['id'])." AND old = 'no'")or sqlerr(__FILE__ , __LINE__);
		$canarr = mysqli_fetch_array($canres);
		if ($canarr == 0) {
		?>
<div style="display: none;">
		<form id="rep_form" action="" method="post">
		<div id="rep_error" style="display: none;background:red;color:#fff;padding:10px;text-align:center;font-size:14px;font-weight:bold;">Введите причину изменения репутации!</div>
		<div style="font-size:14px;font-weight:bold;"> Изменить репутацию для <?=$user['username']; ?></div>
		<table cellspacing='1' class="text">
        <tr>
        <td class="text" align="left"><b>Изменить</b> - <input name="type" checked="checked" value="plus" type="radio">Плюс + <input name="type" value="minus" type="radio">Минус -</td>
        </tr>
        <tr>
		<td class="text" align='left' valign='top'><b>Сообщение</b>(Причина)<br />
		<textarea name='descr' id='descr' style='width:300px;height:80px;'></textarea></td>
        </tr>
        <tr>
		<input id="from" type="hidden" value="<?=$CURUSER['id'];?>" name="from"/>
		<input id="userid" type="hidden" value="<?=$user['id'];?>" name="userid"/>
        <td class="text" align='center' style='padding: 0px;'>
		<input type='submit' name='submit' value='Изменить'>
		</td>
        </tr>
		</table>
		</form>
		</div>	

		<?
		} else {
?>


	

<div style="display:none;">
<form id="rep_form" action="" method="post">		<table cellspacing='1' class="text">
        <tr>
        <td class="text" style="padding:0px;text-align:center;font-size:15px;font-weight:bold;">
		Вы не можете изменить репутацию  пользователю <br /> до : <?=get_date_time($canarr['added'] + 1209600);?></td>
		</tr>
		</table>
			</form>
		</div>
<?
}
}
}
end_main_frame();
if($CURUSER)
prof_guest($CURUSER['id'], $id, time());

stdfoot();

?>

