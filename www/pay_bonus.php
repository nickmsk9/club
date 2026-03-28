<?php
require "include/bittorrent.php";
dbconn(false);
loggedinorreturn();

global $memcached, $CURUSER;
$userid = is_array($CURUSER['id']) ? 0 : (int)$CURUSER['id'];
if ($memcached instanceof Memcached) {
    $memcached->delete('users_' . $userid);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Content-Type: text/html; charset=" . $lang['language_charset']);
    if (empty($_POST["id"])) {
        stdmsg($lang['error'], "Вы не выбрали тип бонуса!");
        die();
    }
    $id = (int) $_POST["id"];
    if (!is_valid_id($id)) {
        stdmsg($lang['error'], $lang['access_denied']);
        die();
    }
    $res = sql_query("SELECT * FROM pay_bonus WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    $points = $arr["points"];
    $type = $arr["type"];
    if ($CURUSER["bal"] < $points) {
        stdmsg($lang['error'], "У вас недостаточно бонусов!");
        die();
    }

    switch ($type) {
        case "traffic":
            $traffic = $arr["quanity"];
            $modcomment = sqlesc(date("Y-m-d") . " - Обменял рубли - $points на трафик.\n");

            if (!sql_query("UPDATE users SET bal = bal - $points, uploaded = uploaded + $traffic, modcomment = CONCAT($modcomment, modcomment) WHERE id = " . sqlesc($CURUSER["id"]))) {
                stdmsg($lang['error'], "Не могу обновить баланс!");
                die();
            }

            stdmsg($lang['success'], "Рубли обменяны на траффик!");
            sql_query("INSERT INTO pay (order_num, order_summ, order_date, order_uid, order_format, order_desc) 
                VALUES ('" . $CURUSER['id'] . "-" . date("dHs") . "', '-$points', '" . time() . "', '" . $CURUSER['id'] . "', 'minus', 'Обменял рубли - $points на трафик')") or sqlerr(__FILE__, __LINE__);
            break;

        case "vip":
            if (get_user_class() >= UC_VIP_P) {
                stdmsg($lang['error'], "Вам что рубли некуда девать!?", 'error');
                die();
            }
            $days = $arr["quanity"];
            $vipuntil = get_date_time(TIMENOW + $days * 86400);
            $modcomment = sqlesc(date("Y-m-d") . " - Обменял рубли - $points на VIP статус.\n");

            if (!sql_query("UPDATE users SET `bal` = bal - $points, `class` = " . sqlesc(UC_VIP_P) . ", `oldclass` = " . sqlesc($CURUSER['class']) . ", `vipuntil` = " . sqlesc($vipuntil) . ", modcomment = CONCAT($modcomment, modcomment) WHERE id=" . $CURUSER['id'])) {
                stdmsg($lang['error'], "Не могу обновить баланс!");
                die();
            }

            sql_query("UPDATE users SET `leechwarn` = 'no', `leechwarnuntil` = '0000-00-00 00:00:00', `warned` = 'no' WHERE id=" . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
            stdmsg($lang['success'], "Рубли обменяны на статус VIP.<br />Действие вашего VIP статуса заканчивается: $vipuntil");

            sql_query("INSERT INTO pay (order_num, order_summ, order_date, order_uid, order_format, order_desc) 
                VALUES ('" . $CURUSER['id'] . "-" . date("dHs") . "', '-$points', '" . time() . "', '" . $CURUSER['id'] . "', 'minus', 'Обменял рубли - $points VIP статус')") or sqlerr(__FILE__, __LINE__);
            break;

        default:
            stdmsg($lang['error'], "Unknown bonus type!");
    }
} else {
    stdhead("Магазин VIP услуг");
    begin_main_frame();
?>
<table>
<tr>
    <div class="c_title" style="text-align:center;">
        <a href="<?=$DEFAULTBASEURL?>/vip_shop.php" title="Пополнить баланс">Пополнить</a> | 
        <a href="<?=$DEFAULTBASEURL?>/pay_bonus.php" title="Обменять рубли на VIP услуги">Обменять</a>
    </div>
</tr>
</table>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">
function send(){
    var frm = document.mybonus;
    var bonus_type = '';
    for (var i = 0; i < frm.elements.length; i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type === 'radio' && elmnt.checked) {
            bonus_type = elmnt.value;
            break;
        }
    }

    var ajax = new tbdev_ajax();
    ajax.onShow('');
    var varsString = "";
    ajax.requestFile = "pay_bonus.php";
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
<?php
    $my_points = $CURUSER["bal"];
    $res = sql_query("SELECT * FROM pay_bonus ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);
    $output = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $id = $arr["id"];
        $bonus = $arr["name"];
        $points = $arr["points"];
        $descr = $arr["description"];
        $color = ($my_points < $points) ? 'red' : 'green';
        $output .= "<tr><td><b>$bonus</b><br />$descr</td><td><center><font style=\"color: $color\">$points&nbsp;/&nbsp;$my_points</font></center></td><td><center><input type=\"radio\" name=\"bonus_id\" value=\"$id\"" . ($color == 'red' ? ' disabled' : '') . " /></center></td></tr>\n";
    }
?>
<tr><td colspan="3"><h2>Мой баланс - <?=$my_points;?> рублей в наличии.</h2></td></tr>
<tr><td class="brd">Тип бонуса</td><td class="brd">Сумма</td><td class="brd">Выбор</td></tr>
<form action="pay_bonus.php" name="mybonus" method="post">
<?=$output;?>
<tr><td colspan="3"><input type="submit" onClick="send(); return false;" value="Обменять" /></td></tr>
</form>
</table>
</div>
<?php
    end_main_frame();
    stdfoot();
}
?>