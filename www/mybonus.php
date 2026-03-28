<?php
require "include/bittorrent.php";
dbconn(false);
loggedinorreturn();
global $memcache_obj, $CURUSER;

// Инициализируем объект Memcached, если он ещё не создан
if (!isset($memcache_obj) || !$memcache_obj instanceof Memcached) {
    $memcache_obj = new Memcached();
    // Добавляем сервер Memcached, если список пуст
    if (empty($memcache_obj->getServerList())) {
        $memcache_obj->addServer('127.0.0.1', 11211);
    }
}

// Удаляем кэш пользователя, если объект успешно создан
if ($memcache_obj instanceof Memcached) {
    $memcache_obj->delete('users_' . $CURUSER['id']);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	header("Content-Type: text/html; charset=".$lang['language_charset']);
	if (empty($_POST["id"])) {
		stdmsg($lang['error'], "Вы не выбрали тип бонуса!");
		die();
	}
	$id = (int) $_POST["id"];
	if (!is_valid_id($id)) {
		stdmsg($lang['error'], $lang['access_denied']);
		die();
	}
	$res = sql_query("SELECT * FROM bonus WHERE id = $id") or sqlerr(__FILE__,__LINE__);
	$arr = mysql_fetch_array($res);
	$points = $arr["points"];
	$type = $arr["type"];
	if ($CURUSER["bonus"] < $points) {
		stdmsg($lang['error'], "У вас недостаточно бонусов!");
		die();
	}
	switch ($type) {
		case "traffic":
			$traffic = $arr["quanity"];
			$modcomment = sqlesc(date("Y-m-d") . " - Обменял бонус - $points на трафик  .\n");

			if (!sql_query("UPDATE users SET bonus = bonus - $points, uploaded = uploaded + $traffic, modcomment = CONCAT($modcomment, modcomment) WHERE id = ".sqlesc($CURUSER["id"]))) {
				stdmsg($lang['error'], "Не могу обновить бонус!");
				die();
			}
			stdmsg($lang['success'], "Бонус обменян на траффик!");
			
			break;

		default:
			stdmsg($lang['error'], "Unknown bonus type!");
	}
} else {
stdhead($lang['my_bonus']);
begin_main_frame();
/*
begin_frame("Пожертвование / Покупка Бонус поинтов",true);
?>
<h3 style="color:green;">Акция ! Пополняй бонус и получи + 15% от суммы !</h3>


<iframe src="http://pay.smsdeluxe.ru/I_852FC5E/?text=<b>bonus+<?=$CURUSER['id']?></b>" width="565" height="301" frameborder=0>
Ваш браузер не поддерживает IFRAME! Для совершения смс-оплаты перейдите на <a href="http://pay.smsdeluxe.ru/852FC5E/?text=<b>bonus+<?=$CURUSER['id']?></b>" target="_blank">эту страницу</a>.
 </iframe>
 <br>
<div style="background-color:#F5F4EA;padding:5px">
<p>Уважаемые пользователи ! Здесь Вы можете сделать пожертвование сайту и получить в награду Бонус поинты .</p>
<p>Делая пожертвование , Вы получаете Бонус в эквиваленте 1$ = 100 поинтов ! </p>
<p>Далее Бонус поинты можно обменять на ГБ роздачи , исходя из ниже перечисленной таблицы</p>
 
 </div>

<br>
<p><i> В случае проблем с сервисом , обратитесь в <a href="/support.php" >Тех Поддержку</a> или к Диктатору <a href="/user/id22">webnet</a> - icq 616 4 090 !</i>

<?

end_frame();
*/
?>

<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">
function send(){

    var frm = document.mybonus;
	var bonus_type = '';

    for (var i=0;i < frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='radio') {
            if(elmnt.checked == true){ bonus_type = elmnt.value; break;}
        }
    }

	var ajax = new tbdev_ajax();
	ajax.onShow ('');
	var varsString = "";
	ajax.requestFile = "mybonus.php";
	ajax.setVar("id", bonus_type);
	ajax.method = 'POST';
	ajax.element = 'ajax';
	ajax.sendAJAX(varsString);
}
</script>
<div id="loading-layer" style="display:none;font-family: Verdana;font-size: 11px;width:200px;height:50px;background:#FFF;padding:10px;text-align:center;border:1px solid #000">
	<div style="font-weight:bold" id="loading-layer-text">Загрузка. Пожалуйста, подождите...</div><br />
	<img src="pic/loading.gif" border="0" />
</div>
<br />
<div id="ajax">
<table class="embedded" border="0" cellspacing="0" cellpadding="5">
<?
	$my_points = $CURUSER["bonus"];
	$res = sql_query("SELECT * FROM bonus") or sqlerr(__FILE__,__LINE__);
	$output = '';
	while ($arr = mysqli_fetch_assoc($res)) {
		$id = $arr["id"];
		$bonus = $arr["name"];
		$points = $arr["points"];
		$descr = $arr["description"];
		$color = 'green';
		if ($CURUSER['bonus'] < $points)
			$color = 'red';
		$output .= "<tr><td><b>$bonus</b><br />$descr</td><td><center><font style=\"color: $color\">$points&nbsp;/&nbsp;$my_points</font></center></td><td><center><input type=\"radio\" name=\"bonus_id\" value=\"$id\"".($color == 'red' ? ' disabled' : '')." /></center></td></tr>\n";
		}
?>
	<tr><td colspan="3">Мой бонус (<?=$CURUSER["bonus"];?> бонусов в наличии / <?=$points_per_hour;?> бонусов в час)</td></tr>
	<tr>
	<td class="brd">Тип бонуса</td>
	<td class="brd">Очки</td>
	<td class="brd">Выбор</td></tr>
	<form action="mybonus.php" name="mybonus" method="post">
<?=$output;?>
		<tr><td colspan="3"><input type="submit" onClick="send(); return false;" value="Обменять" /></td></tr>
	</form>
</table>
</div>
<?
end_main_frame();
stdfoot();
}
?>