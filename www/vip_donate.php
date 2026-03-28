<?php
require "include/bittorrent.php";
dbconn(false);

loggedinorreturn();
global $memcached, $CURUSER;
$memcached->delete('users_'.$CURUSER['id']);

$content = ''; // Инициализация переменной, чтобы не было ошибки

$currentdate = date("Y-m");
$result = cache_get("donate");
if ($result === false) {
    $title_who = array();

    $resm = sql_query("SELECT DISTINCT d.msg, u.username, u.class, u.warned,
                u.gender,
                u.enabled,
                u.parked,
                u.donor 
                FROM donatedelux d 
                LEFT JOIN users u ON d.msg = u.id 
                WHERE date LIKE '".$currentdate."-%' AND test = '0'") or sqlerr(__FILE__, __LINE__);
    $donate_cache = array();
    while ($cache_data = mysqli_fetch_assoc($resm)) {
        $donate_cache[] = $cache_data;
    }

    cache_set("donate", $donate_cache, 300);
    $result = $donate_cache;
}

$title_who = [];
foreach ($result as $arr) {
    $uid = $arr['msg'];
    $uname = $arr['username'];
    $class = $arr['class'];
    // Вставляем ссылку на пользователя с цветом и иконками
    $title_who[] = "<a href=\"user/id".$uid."\" class=\"online\">".get_user_class_color($class, $uname). get_user_icons($arr)."</a>";
}

if (count($title_who)) {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\">".implode(", ", $title_who)."</td></tr></table>\n";
} else {
    $content .= "<table border=\"0\" width=\"100%\"><tr valign=\"middle\"><td align=\"left\" class=\"embedded\"> Никто еще не помогал =(</td></tr></table>\n";
}

stdhead('Покупка VIP аккаунта');

begin_main_frame();
?>

<h2>Покупка VIP аккаунтов / Пожертвование </h2>

<div  style="float:left; width:630px;">
<br /><br />
<p align="center">
<!-- SMS Deluxe -->
<iframe src="http://pay.smsdeluxe.ru/I_7C098BF/?text=vip <?=$CURUSER['id']?>" frameborder="0" height="353" width="565">
Ваш браузер не поддерживает IFRAME! Для совершения смс-оплаты перейдите на <a href="http://pay.smsdeluxe.ru/FA4B21A/?text=vip <?=$CURUSER['id']?>" target="_blank">эту страницу</a>.
</iframe>
<!--/ SMS Deluxe -->
</p>
<p>Уважаемые пользователи, данная функция позволяет Вам приобрести VIP аккаунт и качать любимое аниме без ограничений.</p>
<p>При наличии VIP аккаунта система не будет учитывать скачанное, только разданное, что способствует быстрому росту рейтинга.</p>
<p>Приобретая VIP аккаунт или делая пожертвование, Вы также получаете бонусы $, которые будут отображаться в Вашем профиле.</p>
<br>

<div style="background-color:#F5F4EA;padding:5px">
<ul>
<li><font color="#317bd7"><font size= "2">Стоимость услуги на 7 дней ~ 1$. В конечном итоге, со всех перечислений мы получаем примерно 0.40$. Полученные деньги пойдут на оплату хостинга и дальнейшее развитие проекта.</font></font></li>
<li><font color="#317bd7"><font size= "2">Статус приобрести могут все пользователи уровнем ниже Ниндзя (VIP). Если Ваш уровень выше, то СМС сообщение будет засчитано как пожертвование.</font></font></li>
<li><font color="#317bd7"><font size= "2">Если Вы купили статус, следующий сможете приобрести по окончанию срока. Система не суммирует срок действия!</font></font></li> 
<li><font color="#317bd7"><font size= "2">Если цена VIP аккаунта для Вашей страны указана выше чем 1$, то срок действия умножается пропорционально цене!</font></font></li> 
<li><font color="#317bd7"><font size= "2">Если Вы повторно оплатите VIP аккаунт при уже имеющемся статусе, система примет платеж как пожертвование!</font></font></li>
</ul>
</div>

<br>
<br>
<br>
<p><i> В случае проблем с сервисом обратитесь в <a href="/support.php" >Тех Поддержку</a> или к Диктатору <a href="/user/id22">webnet</a> - ICQ 616 4 090!</i></p>

</div><div style="float:right; width:200px">
<img src="/pic/donate.jpg" border="0"/>

<?php
$res = sql_query("SELECT SUM(profit) as pribol FROM donatedelux WHERE date LIKE '".$currentdate."-%' AND test = '0'") or sqlerr(__FILE__, __LINE__);

$row = mysqli_fetch_assoc($res);
$amount = $row['pribol'] ?? 0;
$start = $amount;
$finish = 3100;

$end = $finish - $start;
$perc1 = ($start * 100) / $finish;
$perc = round($perc1, 2);
if ($perc >= 100) $perc = 100;

if ($perc >= 66) {
    $pic = 'loadbargreen.gif';
} elseif ($perc >= 33) {
    $pic = 'loadbaryellow.gif';
} else {
    $pic = 'loadbarred.gif';
}

print "<br /><br /><table width=\"100%\" id=\"no_border\" cellspacing=\"0\" cellpadding=\"0\">";
print "<span style='text-align:center; font-size:12px;'>Собрано: $start руб. из $finish руб.<br>Осталось: $end руб.</span>";
print "<tr><td><img height=\"25\" width=\"$perc%\" src=\"pic/$pic\" alt=\"Собрано на оплату сервера $start руб. из $finish руб., осталось $end руб.\"></td></tr>"; 
print "</table>";
?>

</div>
<div style="clear:both;"></div>
<?php
begin_frame("В этом месяце нам помогли:");
echo $content;
end_frame();

end_main_frame();
stdfoot();
?>