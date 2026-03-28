<?php

require "include/bittorrent.php";

dbconn();
loggedinorreturn();

error_reporting(E_ALL ^ E_NOTICE); // Report all errors except E_NOTICE warnings
ini_set('display_errors', 1); // turn error reporting on
ini_set('log_errors', 1); // log errors
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); // where to log errors

$Version = 'Version: 1.01 - 01-Jul-2025'; // see changelog.txt for changes

//------------------------------------------------------------------
// begin settings:
//------------------------------------------------------------------

// If you know your account path, this can check the disk usage
// If you do not know your server path, set to false
define('ENABLE_MY_ACCOUNT', true);

// Универсальное определение путей к основным папкам проекта (автоматически)
define('MY_PATHS', [
    dirname(__DIR__),                   // Корневая директория сайта
    dirname(__DIR__) . '/logs',         // Логи (если есть)
]);

// Файлы логов для очистки (универсальный вариант)
define('LOG_FILES', [
    dirname(__DIR__) . '/logs/*.log',
]);

//------------------------------------------------------------------
// end settings. Do not alter any code below this point in the script or it may not run properly.
//------------------------------------------------------------------

// Безопасное получение параметра 'do' из GET-запроса
$do = isset($_GET['do']) ? $_GET['do'] : '';

function cleanLogs() {
    foreach (LOG_FILES as $pattern) {
        foreach (glob($pattern) as $file) {
            file_put_contents($file, '');
        }
    }
}

if ($do == "clean") {
    cleanLogs();
}

stdhead("Статистика Сервера");
begin_main_frame();
begin_frame();

//SERVER UPTIME (Windows)
$uptime = shell_exec('powershell -Command "$bootTime = (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime; $uptime = (Get-Date) - $bootTime; \'{0} дней, {1} часов, {2} минут, {3} секунд\' -f $uptime.Days, $uptime.Hours, $uptime.Minutes, $uptime.Seconds"');
echo "<b>Время работы:</b> $uptime<br /><br />\n";

// DISK SPACE (Whole server)
echo "<b>Использовано ресурсов HDD:</b><br />\n";
$output = shell_exec('wmic logicaldisk get size,freespace,caption');
echo "<pre>\n";
echo htmlspecialchars($output) . "\n";
echo "</pre>\n";

if (ENABLE_MY_ACCOUNT) {
    // Подсчёт размера директорий из MY_PATHS (кроссплатформенно)
    echo "<b>Объем:</b><br />\n";
    $result = [];
    foreach (MY_PATHS as $path) {
        if (is_dir($path)) {
            // Для Windows и Mac/Linux использовать подходящий способ
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $size = shell_exec('powershell -Command "(Get-ChildItem -Path ' . escapeshellarg($path) . ' -Recurse -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1GB"');
            } else {
                $size = shell_exec('du -s -B1G ' . escapeshellarg($path) . ' | awk \'{print $1}\'');
            }
            $size = is_numeric(trim($size)) ? round($size, 2) : 'n/a';
            $result[] = "$path: $size GB";
        } else {
            $result[] = "$path: (нет такой папки)";
        }
    }
    echo "<pre>\n";
    foreach ($result as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>\n";
}

?>
<center>
    <input type=button onClick="document.location='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>';" value="Обновить"> | 
    <input type=button onClick="document.location='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?do=clean';" value="Очистить логи"> | 
    <input type=button onClick="document.location='<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?do=update';" value="Обновить списки">
</center>

<?php

if ($do == "update") {
    // Предполагаем, что у вас уже есть соединение с базой данных через mysqli
    $res = sql_query("SELECT users.class, torrents.owner FROM torrents LEFT JOIN users ON torrents.owner = users.id") or sqlerr(__FILE__, __LINE__);
    
    // Используем fetch_assoc() вместо fetch()
    while ($row = $res->fetch_assoc()) {
        $ownerclass = $row["class"];
        $ownerid = $row["owner"];
        
        // Обновляем запись в таблице torrents
        sql_query("UPDATE torrents SET owner_class = " . sqlesc($ownerclass) . " WHERE owner = " . sqlesc($ownerid)) or sqlerr(__FILE__, __LINE__);
    }
}

end_frame();
end_main_frame();
stdfoot();

?>