<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
stdhead("Магазин Бонусов / Донейт ");

global $memcached, $CURUSER;
$userid = is_array($CURUSER['id']) ? 0 : (int)$CURUSER['id'];
$memcached->delete('users_' . $userid);

$currentdate = date("Y-m");
$title_who = array();
$content = '';

if (cache_check("donate", 300))  {
    $result = cache_read("donate");
} else {
    $resm = sql_query("SELECT DISTINCT d.msg, u.username, u.class, u.warned,
                u.gender,
                u.enabled,
                u.parked,
                u.donor
                FROM donatedelux d
                LEFT JOIN users u ON d.msg = u.id 
                WHERE d.date LIKE '".$currentdate."-%' AND d.test = '0'") or sqlerr(__FILE__, __LINE__);

    $donate_cache = array();
    while ($cache_data = mysqli_fetch_array($resm)) {
        $donate_cache[] = $cache_data;
    }

    cache_write("donate", $donate_cache);
    $result = $donate_cache;
}

foreach ($result as $arr) {
    list($uid, $uname, $class, $warned, $gender, $enabled, $parked, $donor) = $arr;
    $title_who[] = "<a href=\"user/id".$uid."\" class=\"online\">".get_user_class_color($class, $uname). get_user_icons($arr)."</a>";
}

if (count($title_who)) {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\">".implode(", ", $title_who)."</td></tr></table>\n";
} else {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\"> Никто еще не помогал =(</td></tr></table>\n";
}

begin_main_frame();
begin_frame();

require_once('pay_conf.php'); 
$l = $CURUSER['id']."-".date("dHs");
?>

<table>
<tr>
<div class="c_title" style="text-align:center;margin: 15px 15px 15px 15px;">
<a href="<?=$DEFAULTBASEURL?>/vip_shop.php" title="Пополнить баланс">Пополнить</a> | <a href="<?=$DEFAULTBASEURL?>/pay_bonus.php" title="Обменять рубли на VIP услуги">Обменять</a></div>
</tr>
</table>

<script type="text/javascript">
var min = 50;
function calculate(sum) {
    var re = /[^0-9\.]/gi;
    if (re.test(sum)) {
        sum = sum.replace(re, '');
        jQuery('#oa').val(sum);
    }
    
    if (sum < min) {
        jQuery('#error').html('<b style="color:red;">Сумма должна быть больше '+min+'</b>');
        jQuery('#submit').attr("disabled", "disabled");
        return false;
    } else {
        jQuery('#error').html('');
    }
    
    jQuery.get('pay_ajax.php?prepare_once=1&l=<?=$l?>&oa='+sum, function(data) {
         jQuery('#s').val(data);
         jQuery('#submit').removeAttr("disabled");
    });
}
</script>

<div style="float:left; width:547px;padding-top:50px;">
<h2>Электронные деньги</h2>
<div style="margin:10px 5px 10px 5px;color:black;font-size:12px;border:1px solid #AEAEAE;padding:10px;width:520px;border-radius:0.6em;">
<p>Введите сумму в рублях и перейдите на сайт оплаты. Там Вы сможете выбрать один из способов платежа.</p>
<div id="error"></div>

<form method="GET" action="http://www.free-kassa.ru/merchant/cash.php">
    <input type="hidden" name="m" value="<?=$fk_merchant_id?>">
    <input type="text" name="oa" id="oa" style="width:80%" onchange="calculate(this.value)" onkeyup="calculate(this.value)" onfocusout="calculate(this.value)">
    <input type="hidden" name="s" id="s" value="0">
    <input type="hidden" name="o" value="<?=$l?>">
    <input type="hidden" name="us_userid" value="<?=$CURUSER['id']?>">
    <input type="submit" id="submit" value="Оплатить" disabled>
</form>
<br />

<table>
<tbody>
<tr>
<td style="vertical-align:top;border:none;">
<span style="font-weight:bold;">•&nbsp;&nbsp;Webmoney (WMZ, WMR, WME, WMU)</span><br>
<span style="font-weight:bold;">•&nbsp;&nbsp;Яндекс.Деньги</span><br>
•&nbsp;&nbsp;QIWI кошелек<br>
•&nbsp;&nbsp;Liberty Reserve (USD, EUR)<br>
•&nbsp;&nbsp;LiqPAY Reserve (USD, RUB)<br>
•&nbsp;&nbsp;Perfect Money (USD, EUR, RUB)<br>
</td>
<td style="vertical-align:top;border:none;">
•&nbsp;&nbsp;OKPay (USD, RUB, EUR)<br>
•&nbsp;&nbsp;TeleMoney (RUB)<br>
•&nbsp;&nbsp;W1 (USD, RUB)<br>
•&nbsp;&nbsp;MoneyBookers<br>
•&nbsp;&nbsp;VISA <br>
•&nbsp;&nbsp;Альфа-Банк <br>
•&nbsp;&nbsp;SMS (зависит от оператора)<br>
</td>
</tr>
</tbody>
</table>
<br>

<h2>СМС сообщения</h2>
<!-- SMS Deluxe -->
<iframe src="http://pay.smsdeluxe.ru/I_7C098BF/?text=<b>vip <?=$CURUSER['id']?></b>" frameborder="0" height="353" width="100%">
Ваш браузер не поддерживает IFRAME! Для совершения смс-оплаты перейдите на <a href="http://pay.smsdeluxe.ru/FA4B21A/?text=vip <?=$CURUSER['id']?>" target="_blank">эту страницу</a>.
</iframe>
<!--/ SMS Deluxe -->

<br><br>
<small>* Примечание: <br>
1. Для того чтобы полноценно испробовать бонус рекомендуется депозит от 200 руб.<br>
2. В случае возникновения трудностей с оплатой, пожалуйста, обращайтесь к нам через обратную связь - мы постараемся урегулировать
любые сложности в кротчайшие сроки.
</small>
</div>

<div style="float:right; width:285px;">
<img src="/pic/donate.jpg" border="0" style="margin-left:30px;margin:10px 0px 10px 30px;"/>

<?php
$res = sql_query("SELECT SUM(profit) as pribol FROM donatedelux WHERE date LIKE '".$currentdate."-%' AND test = '0'") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
$amount = $row['pribol'];
$start = $amount;
$finish = 5200;

$end = $finish - $start;
if ($start >= $finish){
    $end = "124";
    $start = $finish - $end;
}
$perc1 = ($start * 100)/$finish;
$perc = round($perc1, 2);
if ($perc >= 100) $perc = 100;

if ($perc >= 66) 
    $pic = 'loadbargreen.gif';
elseif ($perc >= 33) 
    $pic = 'loadbaryellow.gif';
else 
    $pic = 'loadbarred.gif';

print "<br /><br /><table width=\"100%\" id=\"no_border\" cellspacing=\"0\" style=\"margin-left:30px;margin-bottom:20px;color:black;font-size:12px;border:1px solid #AEAEAE;padding:5px;width:245px;border-radius:0.4em;\" cellpadding=\"0\">";
print "<p style='text-align:center;margin-left:30px; font-size:12px;'>Собрано  : $start руб. из $finish руб.</p>
<p style='text-align:center;margin-left:30px; font-size:12px;'>Осталось : $end руб.</p>";
print "<tr><td><img height=\"25\" width=\"$perc%\" src=\"pic/$pic\" alt=\"Собранно на оплату сервера $start руб.  из $finish руб. осталось $end руб.\"></td></tr>"; 
print "</table>";
?>

<div style="margin-left:30px;margin:10px 0px 10px 30px;color:black;font-size:12px;border:1px solid #AEAEAE;padding:5px;width:245px;border-radius:0.4em;">
<p>После пополнения счета , Вы сможете :</p>

•&nbsp;&nbsp;Купить VIP статус <br>
•&nbsp;&nbsp;Купить Бонус поинты<br>
•&nbsp;&nbsp;Увеличить фотоальбомы<br>
•&nbsp;&nbsp;Улучшить рейтинг<br>
</div>

</div>
<div style="clear:both;"></div>
<div style="background-color:#F5F4EA;padding:15px;margin: 5px 5px 5px 5px;">
<ul>
<li><p>Данная функция позволяет Вам пополнять свой счет на сайте путем электронных переводов популярными платежными системами.</p></li>
<li><p>Для пополнения введите сумму и перейдите на сайт оплаты счета.</p></li>
<li><p>После успешного платежа Вы сможете приобрести <strong>VIP услуги</strong>, оплатив их со своего счета.</p></li>
<li><p>Все денежные средства полученые от Вас идут на оплату сервера, разработку нового функционала и поддержку сайта.</p></li>
<li><p>Все денежные переводы являются добровольным пожертвованием.</p></li>
<li><p>Денежные средства возврату не подлежат. Осуществляя пополнение счета, Вы соглашаетесь с этим условием.</p></li>
<li><p><i> В случае проблем с сервисом, обратитесь в <a href="/support.php">Тех Поддержку</a> или к Диктатору <a href="<?=$DEFAULTBASEURL?>/user/id22">webnet</a> - ICQ 616 4 090!</i></p></li>
</ul>
</div>

<div style="clear:both;"></div>

<div>
<h2>История пополнений и списание:</h2> 
<?php
echo "<link rel=\"stylesheet\" href=\"".$DEFAULTBASEURL."/css/user.css\" type=\"text/css\">\n";

echo "<table class=\"tt\">
    <tr style='color:#FFFFFF;'>
        <td class=\"tt\" width='100px' align=\"center\">ИД операции</td>
        <td class=\"tt\" width='100px' align=\"center\">Дата</td>
        <td width='60px' class=\"tt\">Сумма</td>
        <td class=\"tt\" align=\"center\">Описание</td>
    </tr>";

$res = sql_query("SELECT * FROM pay WHERE order_uid = ".$CURUSER['id']." ORDER BY id DESC LIMIT 15") or sql_error(__FILE__,__LINE__);
while ($arr = mysqli_fetch_array($res)) {
    if ($arr != "") {
        $date = gmdate("d.m H:i",$arr["order_date"] + ($CURUSER["timezone"] + $CURUSER['dst']) * 60);
        $summ = $arr['order_summ'];
        $num = $arr['order_num'];
        $format = $arr['format'];
        $desc= $arr['order_desc'];

        echo "<tr>
            <td align=\"center\">#$num</td>
            <td align=\"center\">$date</td>
            <td align=\"center\">$summ руб.</td>
            <td align=\"center\">$desc</td>
        </tr>";
    } else {
        echo "<tr><td align=center colspan=4><h3>Нет данных</h3></td></tr>";
    }
}
echo "</table>";
?>
</div>

<?php
end_frame();
end_main_frame();
stdfoot();
?>