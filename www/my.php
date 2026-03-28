<?php
require_once("include/bittorrent.php");
global $lang;
getlang();
dbconn(false);


// Modern cache clearing approach
if (isset($cache)) {
    $cache->delete('users_'.$CURUSER['id']);
}

loggedinorreturn();

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
    "240"  => "GMT + 4:00 часов (MSK)",
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

stdhead($lang['my_my']);
begin_main_frame();

$idurl = base64_encode($CURUSER['id']);

if (!empty($_GET["edited"])) {
    print("<h1>" . ($lang['my_updated'] ?? 'Профиль обновлён') . "</h1>\n");
    if (!empty($_GET["mailsent"]))
        print("<h2>" . ($lang['my_mail_sent'] ?? 'Письмо отправлено') . "</h2>\n");
} elseif (!empty($_GET["emailch"])) {
    print("<h1>" . ($lang['my_mail_updated'] ?? 'Email обновлён') . "</h1>\n");
} else {
    print("<h1>Добро пожаловать, <a href=user/id$CURUSER[id]>$CURUSER[username]</a>!</h1>\n");
}
$idurl = base64_encode($CURUSER['id']);

?>
<table border="1" cellspacing="" cellpadding="15" align="center">
<tr>
<td align="center" width="20%"><a href="logout.php"><b><?=$lang['logout'];?></b></a></td>
<td align="center" width="20%"><a href="mytorrents.php"><b><?=$lang['my_torrents'];?></b></a></td>
<td align="center" width="20"><a href="mytorrents_off.php"><b>Торренты Офф</b></a></td>
<td align="center" width="20"><a href="friends.php"><b>Мои списки пользователей</b></a></td>
</tr>
<tr>
<td colspan="4">
<form method="post" action="takeprofedit.php">
<table border="1" cellspacing=0 cellpadding="5">
<?

$countries = "<option value='0'>---- " . $lang['my_unset'] . " ----</option>\n";
$ct_r = sql_query("SELECT id, name FROM countries ORDER BY name") or die(mysqli_error($GLOBALS["___mysqli_ston"]));

while ($ct_a = mysqli_fetch_array($ct_r)) {
    $countries .= "<option value='$ct_a[id]'" . ($CURUSER["country"] == $ct_a['id'] ? " selected" : "") . ">$ct_a[name]</option>\n";
}

function format_tz($a) {
    $h = floor($a);
    $m = ($a - floor($a)) * 60;
    return ($a >= 0 ? "+" : "-") . (strlen(abs($h)) > 1 ? "" : "0") . abs($h) .
        ":" . ($m == 0 ? "00" : $m);
}

tr($lang['my_allow_pm_from'],
"<input type=radio name=acceptpms" . ($CURUSER["acceptpms"] == "yes" ? " checked" : "") . " value=\"yes\">Все (исключая блокированных)
<br /><input type=radio name=acceptpms" .  ($CURUSER["acceptpms"] == "friends" ? " checked" : "") . " value=\"friends\">Только друзей
<br /><input type=radio name=acceptpms" .  ($CURUSER["acceptpms"] == "no" ? " checked" : "") . " value=\"no\">Только администрации"
,1);

tr($lang['my_parked'],
"<input type=\"radio\" name=\"parked\"" . ($CURUSER["parked"] == "yes" ? " checked" : "") . " value=\"yes\">".$lang['yes']."
<input type=\"radio\" name=\"parked\"" . ($CURUSER["parked"] == "no" ? " checked" : "") . " value=\"no\">".$lang['no']."
<br /><font class=\"small_text\">".$lang['my_you_can_park'].".</font>"
,1);

if(get_user_class() == UC_SYSOP) {
    tr("Невидимка",
    "<input type=\"radio\" name=\"hiden\"" . ($CURUSER["hiden"] == "yes" ? " checked" : "") . " value=\"yes\">".$lang['yes']."
    <input type=\"radio\" name=\"hiden\"" . ($CURUSER["hiden"] == "no" ? " checked" : "") . " value=\"no\">".$lang['no']."",1);
}

tr("Список друзей", "<input type='radio' name='viewfriends'" . ($CURUSER["viewfriends"] == "yes" ? " checked" : "") . " value=\"yes\">Открыт&nbsp;<input type='radio' name='viewfriends'" .  ($CURUSER["viewfriends"] == "no" ? " checked" : "") . " value=\"no\">Скрыт",1);

tr($lang['my_delete_after_reply'], "<input type=checkbox name=deletepms" . ($CURUSER["deletepms"] == "yes" ? " checked" : "") . ">",1);
tr($lang['my_sentbox'], "<input type=checkbox name=savepms" . ($CURUSER["savepms"] == "yes" ? " checked" : "") . ">",1);
tr($lang['my_country'], "<select name=country>\n$countries\n</select>",1);
$timezone = "";
foreach ($tz as $key => $value) {
    $timezone .= "<option value='$key'" . ($CURUSER["timezone"] == $key ? " selected" : "") . ">$value</option>";
}

tr("Временая зона", "<select name=timezone>$timezone</select> <input type=checkbox name=dst".($CURUSER["dst"] ? " checked" : "").">Корректировка летнего времени", 1);
tr($lang['my_avatar_url'], "<input name=avatar size=50 value=\"" . htmlspecialchars($CURUSER["avatar"]) .
  "\"><br />\n".sprintf($lang['max_avatar_size'], $avatar_max_width, $avatar_max_height),1);
tr($lang['my_gender'],
"<input type=radio name=gender" . ($CURUSER["gender"] == "1" ? " checked" : "") . " value=1>".$lang['my_gender_male']."
<input type=radio name=gender" .  ($CURUSER["gender"] == "2" ? " checked" : "") . " value=2>".$lang['my_gender_female']
,1);

///////////////// BIRTHDAY MOD /////////////////////
$birthday = $CURUSER["birthday"];
$birthday = date("Y-m-d", strtotime($birthday));
list($year1, $month1, $day1) = explode('-', $birthday);

if ($CURUSER["birthday"] == "0000-00-00") {
    // Генерация выпадающего списка для года
    $year = "<select name='year'><option value=\"0000\">" . $lang['my_year'] . "</option>\n";
    $i = 1950;
    while ($i <= (date('Y', time()) - 13)) {
        $year .= "<option value='$i'>$i</option>\n";
        $i++;
    }
    $year .= "</select>\n";

    // Массив месяцев
    $birthmonths = array(
        "01" => $lang['my_months_january'],
        "02" => $lang['my_months_february'],
        "03" => $lang['my_months_march'],
        "04" => $lang['my_months_april'],
        "05" => $lang['my_months_may'],
        "06" => $lang['my_months_june'],
        "07" => $lang['my_months_july'],
        "08" => $lang['my_months_august'],
        "09" => $lang['my_months_september'],
        "10" => $lang['my_months_october'],
        "11" => $lang['my_months_november'],
        "12" => $lang['my_months_december'],
    );

    // Генерация выпадающего списка для месяца
    $month = "<select name='month'><option value=\"00\">" . $lang['my_month'] . "</option>\n";
    foreach ($birthmonths as $month_no => $show_month) {
        $month .= "<option value='$month_no'>$show_month</option>\n";
    }
    $month .= "</select>\n";

    // Генерация выпадающего списка для дня
    $day = "<select name='day'><option value=\"00\">" . $lang['my_day'] . "</option>\n";
    $i = 1;
    while ($i <= 31) {
        $day_value = ($i < 10) ? "0$i" : $i;
        $day .= "<option value='$day_value'>$day_value</option>\n";
        $i++;
    }
    $day .= "</select>\n";

    // Вывод формы
    tr($lang['my_birthdate'], $year . $month . $day, 1);
    echo "<input type='hidden' name='bres' value='no'>";
} else {
    // Если дата рождения уже установлена
    tr($lang['my_birthdate'], "<b>
        <input type='hidden' name='year' value='$year1'>$year1
        <input type='hidden' name='month' value='$month1'>.$month1
        <input type='hidden' name='day' value='$day1'>.$day1
        </b><br>
        Сбросить дату 
        <input type='radio' name='bres' value='yes'>" . $lang['yes'] . "
        <input type='radio' name='bres' value='no'" . ($CURUSER["birthday"] !== "0000-00-00" ? " checked" : "") . ">" . $lang['no'] . "
    ", 1);
}
///////////////// BIRTHDAY MOD /////////////////////

print("<tr><td class=\"tablecat\" colspan=\"2\" align=left><b>".$lang['my_contact']."</b></td></tr>\n");

tr(" ", "    <table cellspacing=\"3\" cellpadding=\"0\" width=\"100%\" border=\"0\">
            <tr>
        <td id=\"no_border\" style=\"font-size: 11px; font-style: normal; font-variant: normal; font-weight: normal; font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif\" colspan=2>
        ".$lang['my_contact_descr']."</td>
      </tr>
      <tr>
        <td id=\"no_border\" style=\"font-size: 11px; font-style: normal; font-variant: normal; font-weight: normal; font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif\">
        ".$lang['my_contact_icq']."<br />
        <img alt src=pic/contact/icq.gif width=\"17\" height=\"17\">
        <input maxLength=\"30\" size=\"40\" name=\"icq\" value=\"" . $CURUSER["icq"] . "\" ></td>
          <td id=\"no_border\" style=\"font-size: 11px; font-style: normal; font-variant: normal; font-weight: normal; font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif\">
        ".$lang['my_contact_skype']."<br />
        <img alt src=pic/contact/skype.gif width=\"17\" height=\"17\">
        <input maxLength=\"32\" size=\"40\" name=\"skype\" value=\"" . $CURUSER["skype"] . "\" ></td>
     </tr>
    </table>",1);
tr($lang['my_show_avatars'], "<input type=checkbox name=avatars" . ($CURUSER["avatars"] == "yes" ? " checked" : "") . "> (Пользователи с маленькими каналами могут отключить эту опцию)",1);
// Ensure textbbcode() is loaded
if (!function_exists('textbbcode')) {
    require_once("include/bbcode_functions.php");
}
ob_start();
textbbcode("profile", "info", $CURUSER["info"]);
$bbcode_editor = ob_get_clean();
tr($lang['my_info'], $bbcode_editor . "<br />Показывается на вашей публичной странице. Может содержать <a href='tags.php' target='_new'>BB коды</a>.", 1);
tr("Рефферальная  ссылка- ","<input type=\"text\" size=65 value=\"$DEFAULTBASEURL/ref.php?ref=$idurl\" readonly />",1);
tr(
  $lang['my_userbar'], 
  "<img src=\"torrentbar/bar.php?id={$CURUSER["id"]}\" border=\"0\"><br />" .
  $lang['my_userbar_descr'] . ":<br />" .
  "<input type=\"text\" size=65 value=\"[url={$DEFAULTBASEURL}][img]{$DEFAULTBASEURL}/torrentbar/bar.php?id={$CURUSER["id"]}[/img][/url]\" readonly />", 
1);
tr($lang['my_mail'], "<input type=\"text\" name=\"email\" size=50 value=\"" . htmlspecialchars($CURUSER["email"]) . "\" />", 1);
print("<tr><td colspan=\"2\" align=left><b>Примечание:</b> Если вы смените ваш Email адрес, то вам придет запрос о подтверждении на ваш новый Email-адрес. Если вы не подтвердите письмо, то Email адрес не будет изменен.</td></tr>\n");
tr("Сменить пасскей","<input type=checkbox name=resetpasskey value=1 /> (Вы должны перекачать все активные торренты после смены пасскея)", 1);

if (strlen($CURUSER['passkey']) != 32) {
    $CURUSER['passkey'] = md5($CURUSER['username'].get_date_time().$CURUSER['passhash']);
    sql_query("UPDATE users SET passkey='$CURUSER[passkey]' WHERE id=$CURUSER[id]");
}
tr("Мой пасскей","<b>$CURUSER[passkey]</b>", 1);
tr("Привязать IP к пасскею", "<input type=checkbox name=passkey_ip" . ($CURUSER["passkey_ip"] != "" ? " checked" : "") . "> Включив эту опцию вы можете защитить себя от неавторизованной закакачки по вашему пасскею привязав его к IP. Если ваш IP динамический - не включайте эту опцию.<br />На данный момент ваш IP: <b>".getip()."</b>", 1);
tr("Старый пароль", "<input type=\"password\" name=\"oldpassword\" size=\"50\" />", 1);
tr("Сменить пароль", "<input type=\"password\" name=\"chpassword\" size=\"50\" />", 1);
tr("Пароль еще раз", "<input type=\"password\" name=\"passagain\" size=\"50\" />", 1);

?>
<tr><td colspan="2" align="center"><input type="submit" value="Обновить профиль" style='height: 25px'> <input type="reset" value="Сбросить изменения" style='height: 25px'></td></tr>
</table>
</form>
</td>
</tr>
</table><br /><br />
<?
///////////////////////////////////////////
//////////////////////////////////////////
echo "<table width=100%  cellpadding='5'>";
    echo '<tr><td valign="center" style="width:300px;" align="center">';
    if($CURUSER['photo'] <= "0")
          print "<img src=\"pic/default_photo.png\" border=0 >";
        else {
        ?><script type="text/javascript" src="fancybox/fancybox.js"></script>
    <script>
    jQuery(document).ready(function() {
             jQuery("a.screen").fancybox({
            'overlayShow' : false,

            });
         });

    </script>
<link rel="stylesheet" type="text/css" href="fancybox/fancybox.css"/>
<? 
           print "<a href=\"".$BASEURL."/photo/".$CURUSER["photo"]."\" class=\"screen\"><img border='0' alt='Фото by ".$CURUSER["username"]."' width=150 src='".$BASEURL."/photo/".$CURUSER["photo"]."' /></a> ";
           print "<p>Вы можете удалить текущую фотографию .</p>

        <form action=\"foto.php?act=del\" method=post>
        
        <input class='btn' type='submit' value='Удалить'/>
        </form>
        ";

     }

    echo '</td><td valign="top" style="padding:5px">';
    
    
    echo '
    <h1>Загрузить личное фото</h1>
        
        <hr class=hr1>
        <p>Вы можете загрузить сюда только собственную фотографию расширения JPG, GIF и PNG. <br><b><font color="red">Загрузка постороннего изображения ЗАПРЕЩЕНА ! Фото должно быть персональным , на котором изображены Вы лично!</font></b><br />
        После загрузки , изображение будет автаматически уменьшено до размеров не превышающих 800 x 600 пикселей !<br>
        <font size=1>Размер файла не должен превышать <b>500 KB</b>.<br></font></p>
        <form action="foto.php?act=add" method=post enctype="multipart/form-data">
        <input class="btn" type=file name="photo" size=40><input class="btn" type="submit" value="Загрузить"/>
        </form>';
    echo '</td></tr></table>';    
        
////////////////////////////////////////////
////////////////////////////////////////////

end_main_frame();
stdfoot();
?>