<?
/* 
 *	AnimeDNA.Ru
 *	Прием данных от нашего сайта
*/

/* 
 *	В базу данных выполнить запрос: 
 *	ALTER TABLE users ADD `animedna_bonus` date NOT NULL default '0000-00-00';
 *	Это будет защита от накрутки ваших бонусов
*/
require_once("include/bittorrent.php");
require_once("include/animedna-1.0.php");
dbconn();

/* 
 *	Настройки
 *	Лучше ввести все данные и оставить класс
 *	Так как класс делает дополнительную защиту
 *  от накрутки
*/
//Сколько начислять
$bonus = 10; 

//Создаем объект
$animedna = new animedna;

//Проверяем реферер
if(!$animedna->check_referer() ) {
	die('Проблема с реферером');
}

//Ключ (обязательно)
$animedna->key = '724d385cd581ce73edb94b3a365aab2f';
if(!$animedna->check_key($_POST['key']) ) { 
	die('Неправильный ключ');
}


/* 
 *	Объявляем переменные
*/
$id_user = (int)$_POST['id_user'];
$date = (int)$_POST['date'];
$type = ($_POST['type'] == 'like' ? 'like' : 'dislike');

/* 
 *	Ищем пользователя по IP
*/
$sql = sql_query("SELECT * FROM users WHERE id=".sqlesc($id_user) );
if(mysql_num_rows($sql) ) {
	$arr = mysql_fetch_assoc($sql);
	sql_query("UPDATE users SET bonus = bonus + 10 WHERE id=".$arr['id']." AND animedna_bonus < CURDATE()");	
}
?>
