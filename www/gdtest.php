<?php
header('Content-Type: text/plain');
echo "GD статус: " . (extension_loaded('gd') ? "включён\n" : "НЕ включён\n");

if (function_exists('gd_info')) {
    print_r(gd_info());
} else {
    echo "Функция gd_info() недоступна\n";
}
?>