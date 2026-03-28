<?php

if (!defined('IN_TRACKER')) {
    // Защита от прямого доступа к файлу
    die('Hacking attempt!');
}

// Директория для файлового кэша
$cacheDir = __DIR__ . '/../cache/';
if (!is_dir($cacheDir)) {
    // Создаём директорию кэша, если она не существует
    mkdir($cacheDir, 0777, true);
}

/**
 * Проверяет, действителен ли кэш-файл
 *
 * @param string $file Имя кэш-файла (без расширения)
 * @param int $time Время жизни кэша в секундах
 * @return bool true, если кэш актуален
 */
function cache_check($file, $time) {
    $path = "cache/$file.cache";

    return file_exists($path)                            // файл существует
        && is_readable($path)                            // доступен для чтения
        && (time() - filemtime($path) < $time)           // не просрочен
        && filesize($path) > 0                           // не пустой
        && (!isset($_GET["no_cache"]) || $_GET["no_cache"] != 1); // не отключён вручную
}

/**
 * Чтение данных из кэша
 *
 * @param string $file Имя кэш-файла (без расширения)
 * @return mixed|false Возвращает данные или false при ошибке
 */
function cache_read($file) {
    $path = "cache/$file.cache";
    if (!is_readable($path)) return false;

    $content = @file_get_contents($path);
    if ($content === false || $content === '') return false;

    $data = @unserialize($content);
    if ($data === false && $content !== 'b:0;') { // Специальная проверка на сериализованный false
        trigger_error("Ошибка: невозможно распаковать кэш-файл: $path", E_USER_WARNING);
        return false;
    }

    return $data;
}

/**
 * Запись данных в кэш
 *
 * @param string $file Имя кэш-файла (без расширения)
 * @param mixed $data Данные для сериализации и записи
 */
function cache_write($file, $data) {
    $path = "cache/$file.cache";
    $content = serialize($data);

    $success = @file_put_contents($path, $content, LOCK_EX);
    if ($success === false) {
        trigger_error("Ошибка записи в кэш-файл: $path", E_USER_WARNING);
    }
}

/**
 * Возвращает оставшееся время жизни кэша
 *
 * @param string $file Имя кэш-файла (без расширения)
 * @param int $time Время жизни в секундах
 * @return int Остаток времени в секундах (или 0, если кэш просрочен)
 */
function cache_left($file, $time) {
    global $rootpath;
    $path = $rootpath . "cache/$file.cache";
    if (!file_exists($path)) return 0;

    return max(0, $time - (time() - filemtime($path)));
}

// Проверка наличия расширения memcached
if (!extension_loaded('memcached')) {
    die('Memcached extension not loaded.');
}

/**
 * Альтернативная запись файла в кэш (например, для HTML или JSON без сериализации)
 *
 * @param string $content Строка для записи
 * @param string $filename Имя файла
 */
function writeCache($content, $filename) {
    $path = './cache/' . $filename;

    $success = @file_put_contents($path, $content, LOCK_EX);
    if ($success === false) {
        trigger_error("Ошибка записи файла кэша: $filename", E_USER_WARNING);
    }
}

/**
 * Альтернативное чтение файла из кэша
 *
 * @param string $filename Имя файла
 * @param int $expiry Время жизни в секундах
 * @return string|false Содержимое файла или false при просрочке/ошибке
 */
function readCache($filename, $expiry) {
    $path = './cache/' . $filename;

    if (!file_exists($path)) return false;
    if ((time() - filemtime($path)) > $expiry) return false;

    $content = @file_get_contents($path);
    if ($content === false) return false;

    return $content;
}