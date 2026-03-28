<?php
require "include/bittorrent.php";
dbconn();
stdhead("Вы отказались от оплаты");
$inv_id = $_REQUEST["MERCHANT_ORDER_ID"];
begin_main_frame();
begin_frame("",true);
echo "Вы отказались от оплаты. Заказ# $inv_id <br />";
echo "You have refused payment. Order# $inv_id\n";
echo "<br><p>Вернуться - <a href='vip_shop.php'>Назад</a></p>";
end_frame();
end_main_frame();
stdfoot();
?>
