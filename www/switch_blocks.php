<?php

require_once("include/bittorrent.php");
dbconn();

if ($_GET) {
    $bid = isset($_GET['bid']) ? (int)$_GET['bid'] : 0;
    $type = $_GET['type'] ?? '';

    if (!is_numeric($bid) || !in_array($type, array('hide', 'show')))
        die();

    $hb = unserialize($_COOKIE['hb'] ?? '') ?: array();

    if ($type === 'hide')
        $hb[$bid] = $bid;
    else
        unset($hb[$bid]);

    setcookie('hb', serialize($hb), time() + 32140800); // + 1 год
} else {
    die();
}