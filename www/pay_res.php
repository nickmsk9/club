<?php
require "include/bittorrent.php";
global $mcache;
dbconn();
function getIP2() {
if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
   return $_SERVER['REMOTE_ADDR'];
}

if (getIP2() != '217.65.9.86') {
    die("hacking attempt!");
}
// регистрационная информация (пароль #2)
// registration info (password #2)
include ("pay_conf.php");

//установка текущего времени
//current date

$date=time();
$bal = $_POST['AMOUNT'];
$bonus = $bal / 4;
$cred = $bal;
$user_id = $_POST["us_userid"];
$hash = md5($fk_merchant_id.":".$_POST['AMOUNT'].":".$fk_merchant_key2.":".$_POST['MERCHANT_ORDER_ID']);

if ($hash == $_POST['SIGN']) {

// запись в файл информации о прведенной операции
// save order info to file
$res = sql_query("INSERT INTO pay (order_num,order_summ,order_date,order_uid,order_format,order_desc) VALUES ('".$_POST['MERCHANT_ORDER_ID']."','".$cred."','$date','$user_id','plus','Пополнение счета Free-kassa.ru $cred руб')") or sqlerr(__FILE__, __LINE__);
$up = sql_query("UPDATE users SET bal = bal + '".$cred."' WHERE id = ".sqlesc($user_id)) or sqlerr(__FILE__,__LINE__);
$body = "Пополнение счета на сумму - ".$_POST['AMOUNT']." руб n\ Пользователем - id $user_id";

send_mime_mail('AnimeClub Mail Robot',
               $SITEEMAIL,
               'AnimeClub Mail Robot',
               "webnetbt@gmail.com",
               'UTF8',  // кодировка, в которой находятся передаваемые строки
               'KOI8-R', // кодировка, в которой будет отправлено письмо
               'Осуществлен платеж ',
               $body,true);
 $mcache->delete_value('users_'.$user_id);
 echo 'YES';
 } else {
 
 echo 'NO';
 }
?>


