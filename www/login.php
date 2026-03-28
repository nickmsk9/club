<?php

require "include/bittorrent.php";

// Запускаем сессию
session_start();

// Если уже вошёл — перекидываем на главную
if (isset($CURUSER)) {
    header("Location: index.php");
    exit;
}

stdhead("Вход");
?>

<h1>Вход</h1>

<form method="post" action="takelogin.php">
<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <td>Логин:</td>
    <td><input type="text" size="40" name="username" required></td>
</tr>
<tr>
    <td>Пароль:</td>
    <td><input type="password" size="40" name="password" required></td>
</tr>
<tr>
    <td colspan="2" align="center">
        <input type="submit" value="Войти">
    </td>
</tr>
</table>
</form>

<p>Если вы забыли пароль, вы можете <a href="recover.php">восстановить его</a>.</p>

<?php
stdfoot();