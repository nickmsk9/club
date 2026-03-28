<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/include/bittorrent.php';
dbconn();

sql_query("UPDATE users_data SET downloaded = 0") or sqlerr(__FILE__, __LINE__);

write_log(
    "Очистка [b]данных о скачивание[/b] была успешно произведена @ " . date("F j, Y, g:i a"),
    "",
    "system"
);
?>