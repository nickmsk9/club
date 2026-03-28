<?php
// Используем уже существующий $mysqli, если он задан, иначе подключаемся
if (!isset($mysqli)) {
    include 'include/secrets.php';
    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error, 3, __DIR__ . "/logs/block-adminka.log");
        die("Ошибка подключения к базе данных.");
    }
    $mysqli->set_charset($mysql_charset);
}

// Выполняем запрос
$query = "SELECT id FROM torrents WHERE modded = 'no'";
$result = $mysqli->query($query);
if (!$result) {
    error_log("Ошибка запроса: " . $mysqli->error . "\n", 3, __DIR__ . "/logs/block-adminka.log");
}

// Проверяем, есть ли результаты
if ($result && $result->num_rows > 0) {
    $b = $result->num_rows;
    $c = '<b><span style="color: red;">(+' . $b . ')</span></b>';
} else {
    $c = ''; // Если результатов нет, переменная $c остается пустой
}
$content='
<table border="0"><tr>
<td id="no_border"><a href="admincp.php">Админка</a>&nbsp;|&nbsp;
<a href="ddos.php">Анти DDOS</a>&nbsp;|&nbsp;
<a href="same.php">ОМА</a>&nbsp;|&nbsp;
<a href="bans.php">Баны</a>&nbsp;|&nbsp;
<a href="staffmess.php">Массовое ЛС</a>&nbsp;|&nbsp;
<a href="memcache.php">Memcache</a>&nbsp;|&nbsp;
<a href="polloverview.php">Обзор опросов</a>&nbsp;|&nbsp;
<a href="makepoll.php">Опросы</a>&nbsp;|&nbsp
<a href="ipcheck.php">Двойники IP</a>&nbsp;|&nbsp
<a href="podarok.php?c=1">Подарки</a>&nbsp;|&nbsp
<a href="massEmail.php">Массовый e-mail</a>

</td></tr>

<tr>
<td id="no_border"><a href="modded.php">Проверка '.$c.'</a>&nbsp;|&nbsp
<a href="spamko.php">Сообщения</a>&nbsp;|&nbsp;
<a href="news.php">Новости</a>&nbsp;|&nbsp;
<a href="commentslast.php">Комментарии</a>&nbsp;|&nbsp;
<a href="log.php">Журнал</a>&nbsp;|&nbsp;
<a href="users.php">Поиск Юзеров</a>&nbsp;|&nbsp;
<a href="usersearch.php">Поиск Юзеров 2</a>&nbsp;|&nbsp;
<a href="uploaders.php">Аплоудеры</a>&nbsp;|&nbsp;
<a href="server-load.php">CPU Инфо</a>&nbsp;|&nbsp;
<a href="unco.php">Юзеры</a>&nbsp;|&nbsp;
<a href="cheaters.php">Читы</a>
</td>
</tr></table>
';

?>