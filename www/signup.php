<?php

require_once("include/bittorrent.php");
dbconn();

$tz = array(
"-720" => "GMT - 12:00 hours (DLW)",
"-660" => "GMT - 11:00 hours (NT)",
"-600" => "GMT - 10:00 hours (HST)",
"-540" => "GMT - 9:00 hours (YST)",
"-480" => "GMT - 8:00 hours (PST)",
"-420" => "GMT - 7:00 hours (MST)",
"-360" => "GMT - 6:00 hours (CST)",
"-300" => "GMT - 5:00 hours (EST)",
"-240" => "GMT - 4:00 hours (AST)",
"-210" => "GMT - 3:30 hours (GST)",
"-180" => "GMT - 3:00 hours (ADT)",
"-120" => "GMT - 2:00 hours (FST)",
"-60"  => "GMT - 1:00 hour (WAT)",
"0"	=> "GMT (Universal Time)",
"60"   => "GMT + 1:00 hour (CET)",
"120"  => "GMT + 2:00 hours (EET)",
"180"  => "GMT + 3:00 hours (MSK)",
"210"  => "GMT + 3:30 hours (NST)",
"240"  => "GMT + 4:00 hours (GST)",
"300"  => "GMT + 5:00 hours (TMT)",
"330"  => "GMT + 5:30 hours (IST)",
"360"  => "GMT + 6:00 hours (BT)",
"420"  => "GMT + 7:00 hours (ICT)",
"480"  => "GMT + 8:00 hours (CCT)",
"540"  => "GMT + 9:00 hours (JST)",
"570"  => "GMT + 9:30 hours (ACST)",
"600"  => "GMT + 10:00 hours (GST)",
"660"  => "GMT + 11:00 hours (AEDT)",
"720"  => "GMT + 12:00 hours (NZST)"
);


if (isset($CURUSER) && $CURUSER)
	stderr($lang['error'], sprintf($lang['signup_already_registered'], $SITENAME));


stdhead($lang['signup_signup']);
begin_main_frame();
$countries = "<option value=\"0\">".$lang['signup_not_selected']."</option>\n";
global $mysqli;
$ct_r = $mysqli->query("SELECT id, name FROM countries ORDER BY name");
if (!$ct_r) die("Ошибка базы данных: " . $mysqli->error);
while ($ct_a = mysqli_fetch_array($ct_r))
  $countries .= "<option value=\"$ct_a[id]\">$ct_a[name]</option>\n";

?>
<p>
<script language="JavaScript" src="js/ajax.js" type="text/javascript"></script>
<form method="post" action="takesignup.php">
<table border="1" cellspacing=0 align="center" cellpadding="10">
<p style="color: red; font-weight: bold;">
Пользователи с никами - 45435435 или dfsfjkgflh будут удаляться без предупреждения ! ! !
</p>
<tr valign=top><td align="right" class="heading"><?=$lang['signup_username'];?></td><td align=left><input type="text" size="60" name="wantusername" id="wantusername" onblur="signup_check('username'); return false;" required="required" /><div id="check_username"></div></td></tr>
<tr valign=top><td align="right" class="heading"><?=$lang['signup_password'];?></td><td align=left><input type="password" size="60" name="wantpassword" id="wantpassword" required="required" /></td></tr>
<tr valign=top><td align="right" class="heading"><?=$lang['signup_password_again'];?></td><td align=left><input type="password" size="60" name="passagain" id="passagain" onblur="signup_check('password'); return false;" required="required" /><div id="check_password"></div></td></tr>
<tr valign=top><td align="right" class="heading"><?=$lang['signup_email'];?></td><td align=left>
<p style="color: red; font-weight: bold;">
Указывайте Ваш настоящий э-маил !!! После регистрации , Вам будет выслано письмо для активации аккаунта !
</p><br />
<input type="text" size="60" name="email" id="email" onblur="signup_check('email'); return false;" required="required" /><div id="check_email"></div>
</td></tr>
<tr><td align="right" class="heading"><?=$lang['signup_gender'];?></td><td align=left><input type=radio name=gender value=1><?=$lang['signup_male'];?><input type=radio name=gender value=2><?=$lang['signup_female'];?></td></tr>
<?
$year = "";
$month = "";
$day = "";
$timezone = "";
foreach ($tz as $key => $value)
  $timezone .= "<option value=$key".($key == 0 ? " selected" : "").">$value</option>";
tr("Временная зона", "<select name=timezone>$timezone</select> <input type=checkbox name=dst>Корректировать летнее время ", 1);

$year .= "<select name=year><option value=\"0000\">".$lang['my_year']."</option>\n";
$i = "1920";
while ($i <= (date('Y',time())-13)) {
	$year .= "<option value=" .$i. ">".$i."</option>\n";
	$i++;
}
$year .= "</select>\n";
$birthmonths = array(
	"01" => $lang['my_months_january'],
	"02" => $lang['my_months_february'],
	"03" => $lang['my_months_march'],
	"04" => $lang['my_months_april'],
	"05" => $lang['my_months_may'],
	"06" => $lang['my_months_june'],
	"07" => $lang['my_months_jule'],
	"08" => $lang['my_months_august'],
	"09" => $lang['my_months_september'],
	"10" => $lang['my_months_october'],
	"11" => $lang['my_months_november'],
	"12" => $lang['my_months_december'],
);
$month .= "<select name=\"month\"><option value=\"00\">".$lang['my_month']."</option>\n";
foreach ($birthmonths as $month_no => $show_month) {
	$month .= "<option value=$month_no>$show_month</option>\n";
}
$month .= "</select>\n";
$day .= "<select name=day><option value=\"00\">".$lang['my_day']."</option>\n";
$i = 1;
while ($i <= 31) {
	if ($i < 10) {
		$day .= "<option value=0".$i.">0".$i."</option>\n";
	} else {
		$day .= "<option value=".$i.">".$i."</option>\n";
	}
	$i++;
}
$day .="</select>\n";
tr($lang['my_birthdate'], $year.$month.$day ,1);
tr($lang['my_country'], "<select name=country>\n$countries\n</select>",1);
/*
echo '<tr><td align="right" class="heading">Код</td><td>
    <div style="width: 400px; float: left; height: 60px">
        <br />
        <img id="captcha_img" src="./captcha/cf.captcha.php?img=' . time() . '" /><br />
        <a href="#" onclick="document.getElementById(\'captcha_img\').src = \'./captcha/cf.captcha.php?img=\' + Math.random(); return false">Обновить картинку</a><br/>
    </div>
    <div style="clear: both"></div>
    <br />
    <input placeholder="' . $lang['user_code'] . '" name="setCaptcha" id="setCaptcha" type="text" size="30" maxlength="60" />
    <script type="text/javascript">inputPlaceholder(document.getElementById("setCaptcha"))</script>
</td></tr>';
*/


?>
<tr><td colspan="2" align="center"><input type="submit" value="<?=$lang['signup_signup'];?>" style='height: 25px'></td></tr>

</table>
</form>
<?
print("<div id='loading-layer'></div>");
end_main_frame();
stdfoot();

?>