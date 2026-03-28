<?php
// include/class/class.mcache.php

/**
 * Класс CACHE — расширяет Memcached и реализует кэширование страниц и данных
 */
class CACHE extends Memcached {

    // Включено ли кэширование
    var $isEnabled = 0;

    // Флаг принудительной очистки кэша
    var $clearCache = 0;

    // Язык текущей сессии/страницы
    var $language = 'en';

    // Структура данных для кэширования страниц (таблицы)
    var $Page = [];

    // Номер текущей строки (строка может содержать несколько "частей")
    var $Row = 1;

    // Номер части внутри строки
    var $Part = 0;

    // Ключ, по которому сохраняется страница в Memcached
    var $MemKey = "";

    // Время жизни кэша (секунды)
    var $Duration = 0;

    // Статистика: сколько раз читался кэш
    var $cacheReadTimes = 0;

    // Статистика: сколько раз писался кэш
    var $cacheWriteTimes = 0;

    // Массив с количеством обращений к ключам
    var $keyHits = ['read' => [], 'write' => []];

    // Используется для мультиязычного контента
    var $languageFolderArray = [];

    function __construct() {
        require_once(__DIR__ . '/../secrets.php');

        // Хост и порт Memcached из настроек
        $host = isset($memcached_host) ? $memcached_host : 'localhost';
        $port = isset($memcached_port) ? $memcached_port : 11211;

        parent::__construct();

        // Настройки производительности Memcached
        $this->setOption(self::OPT_COMPRESSION, true);
        $this->setOption(self::OPT_DISTRIBUTION, self::DISTRIBUTION_CONSISTENT);
        $this->setOption(self::OPT_CONNECT_TIMEOUT, 200);
        $this->setOption(self::OPT_RETRY_TIMEOUT, 1);

        // Подключение к серверу и активация
        $success = $this->addServer($host, $port);
        $this->isEnabled = $success ? 1 : 0;
    }

    // -------------------- Сеттеры и геттеры --------------------

    function getIsEnabled() {
        return $this->isEnabled;
    }

    function setClearCache($isEnabled) {
        $this->clearCache = $isEnabled;
    }

    function getClearCache() {
        return $this->clearCache;
    }

    function setLanguage($language) {
        $this->language = $language;
    }

    function getLanguage() {
        return $this->language;
    }

    function setLanguageFolderArray($arr) {
        $this->languageFolderArray = $arr;
    }

    function getLanguageFolderArray() {
        return $this->languageFolderArray;
    }

    // -------------------- Работа со страницами --------------------

    /**
     * Подготовка новой страницы для кэширования
     */
    function new_page($MemKey = '', $Duration = 3600, $Lang = true) {
        $this->MemKey = $Lang ? $this->language . "_" . $MemKey : $MemKey;
        $this->Duration = $Duration;
        $this->Row = 1;
        $this->Part = 0;
        $this->Page = [];
    }

    function add_row() {
        $this->Part = 0;
        $this->Page[$this->Row] = [];
    }

    function end_row() {
        $this->Row++;
    }

    function add_part() {
        ob_start(); // Начинаем буферизацию части вывода
    }

    function end_part() {
        $this->Page[$this->Row][$this->Part++] = ob_get_clean(); // Сохраняем буферизированную часть
    }

    function add_whole_row() {
        $this->Part = 0;
        $this->Page[$this->Row] = [];
        ob_start();
    }

    function end_whole_row() {
        $this->Page[$this->Row++][$this->Part] = ob_get_clean();
    }

    function set_row_value($Key, $Value) {
        $this->Page[$this->Row][$Key] = $Value;
    }

    function set_constant_value($Key, $Value) {
        $this->Page[$Key] = $Value;
    }

    function break_loop() {
        // Маркер конца цикла — ставится false в текущую строку
        if (!empty($this->Page)) {
            $this->Page[$this->Row++] = false;
        }
    }

    // -------------------- Кэш-операции --------------------

    function lock($Key) {
        $this->cache_value('lock_' . $Key, 'true', 3600);
    }

    function unlock($Key) {
        $this->delete_value('lock_' . $Key);
    }

    /**
     * Сохраняем собранную страницу в кэш
     */
    function cache_page() {
        $this->cache_value($this->MemKey, $this->Page, $this->Duration);
        $this->Row = 0;
        $this->Part = 0;
    }

    function setup_page() {
        $this->Row = 0;
        $this->Part = 0;
    }

    /**
     * Запись значения в кэш
     */
    function cache_value($Key, $Value, $Duration = 3600) {
        if (!$this->isEnabled) return;

        $this->cacheWriteTimes++;
        $this->set($Key, $Value, $Duration);
        $this->keyHits['write'][$Key] = ($this->keyHits['write'][$Key] ?? 0) + 1;
    }

    /**
     * Чтение значения из кэша
     */
    function get_value($Key) {
        if (!$this->isEnabled) return false;

        if ($this->getClearCache()) {
            $this->delete_value($Key);
            return false;
        }

        $value = $this->get($Key);
        $this->cacheReadTimes++;
        $this->keyHits['read'][$Key] = ($this->keyHits['read'][$Key] ?? 0) + 1;

        return $this->getResultCode() === self::RES_SUCCESS ? $value : false;
    }

    function delete_value($Key) {
        if ($this->isEnabled) {
            $this->delete($Key);
        }
    }

    // -------------------- Навигация по странице --------------------

    function next_row() {
        $this->Row++;
        $this->Part = 0;

        if (!isset($this->Page[$this->Row]) || $this->Page[$this->Row] === false) {
            return false;
        }

        return count($this->Page[$this->Row]) === 1
            ? $this->Page[$this->Row][0]
            : $this->Page[$this->Row];
    }

    function next_part() {
        return $this->Page[$this->Row][$this->Part++] ?? null;
    }

    function get_row_value($Key) {
        return $this->Page[$this->Row][$Key] ?? null;
    }

    function get_constant_value($Key) {
        return $this->Page[$Key] ?? null;
    }

    /**
     * Пытаемся загрузить закэшированную страницу
     */
    function get_page() {
        $result = $this->get_value($this->MemKey);
        if ($result !== false) {
            $this->Page = $result;
            $this->Row = 0;
            $this->Part = 0;
            return true;
        }
        return false;
    }

    // -------------------- Статистика --------------------

    function getCacheReadTimes() {
        return $this->cacheReadTimes;
    }

    function getCacheWriteTimes() {
        return $this->cacheWriteTimes;
    }

    function getKeyHits($type = 'read') {
        return $this->keyHits[$type] ?? [];
    }
}