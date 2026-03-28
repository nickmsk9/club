<?php
require "include/bittorrent.php";
dbconn();
//Переменная содержит значение указанного в настройках сервиса
//секретного ключа, необходимого для проверки достоверности
//полученного запроса. Вам необходимо изменить ее значение на
//то, что Вы указали в настройках сервиса.
$SecretKey="12904";
//Генерация строки для проверки подписи строки
$md5CheckSrc=$_REQUEST['operator'].$_REQUEST['phone'].$_REQUEST['num'].$_REQUEST['smsid'].$SecretKey;
$md5Check=md5($md5CheckSrc);
//Записываем в базу данные поступившей смс
//Вставляем запись в существующую таблицу

//Входящие переменные
/*
date - Дата и время получения смс
msg - Текст без префикса
operator - Название оператора сотовой связи абонента
phone - Номер телефона абонента в международном формате
smsid - Уникальный номер смс в системе SMS Deluxe
num - Короткий номер на который была отправлена смс
country - Страна абонента
cost - Стоимость смс для абонента
currency - Валюта стоимости смс для абонента
profit - Доход партнера за смс
dollarcost - Примерная стоимость смс в долларах США
test - Индикатор теста
skey - Контрольное значение, вычисленное на основе основных параметров, а также значения секретного ключа, заданного для платежного счета. Примеры
*/
		$descr = strtolower($_GET['msg']);
		$text = strtolower($_GET['msg']);
		$text = explode("vip", $text);
		$text = sqlesc($text[1]);
		$days =  $_GET['profit'] ;
		$bal = $_GET['dollarcost'] * 29;
		$bonus = $_GET['dollarcost'] * 29 / 4 ;
		$cred =  $bal;
		$date = time();

	if (strcasecmp($md5Check,$_REQUEST['skey']) == 0) {

	sql_query("UPDATE users SET `bal` = bal + ".sqlesc($cred)." WHERE id=".$text) or sqlerr(__FILE__, __LINE__);
	$res = sql_query("INSERT INTO pay (order_num,order_summ,order_date,order_uid,order_format,order_desc) VALUES ('".$descr."','".$cred."','$date','$text','plus','Пополнение счета СМС - " . $cred . " .руб ')") or sqlerr(__FILE__, __LINE__);
	sql_query("INSERT INTO donatedelux (date,msg, operator, phone, smtext, num, country, cost, currency, profit,dollarcost, test)
VALUES (".sqlesc($_GET['date']).",".$text.", ".sqlesc($_GET['operator']).", ".sqlesc($_GET['phone'])." , ".sqlesc($_GET['smtext']).", ".sqlesc($_GET['num']).", ".sqlesc($_GET['country']).", ".sqlesc($_GET['cost']).", ".sqlesc($_GET['currency']).",
 ".sqlesc($_GET['profit']).",".sqlesc($_GET['dollarcost']).", ".sqlesc($_GET['test']).")") or sqlerr(__FILE__, __LINE__);
 	$answer = iconv("UTF-8", "CP1251","Вам на счет зачислено - " .$cred ." руб ");


//Выводим заголовок ответа
header("Content-Type: text/html; charset=WINDOWS-1251");

//Вывод согласно спецификации
print "status: reply\n\n";
//Сравнение полученной и сгенерированной подписей

//Достоверность запроса подтверждена, выводим текст ответной смс.
//Также на данном этапе можно добавлять данные в базы данных итд.
print $answer;
} else {
//Запрос получен не от SMS Deluxe
print iconv("UTF-8", "CP1251","MD5 проверка не пройдена.");

} 
?>