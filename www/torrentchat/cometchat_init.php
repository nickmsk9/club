<?php

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php");

// Инициализация сессии для CometChat (PHP 8)
if (defined('SET_SESSION_NAME') && SET_SESSION_NAME !== '') {
    session_name(SET_SESSION_NAME);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "cometchat_guests.php");
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "cometchat_shared.php");
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "php4functions.php");

if (USE_COMET == 1) {
    include_once(dirname(__FILE__) . '/transports/' . TRANSPORT . '/config.php');
    include_once(dirname(__FILE__) . '/transports/' . TRANSPORT . '/comet.php');
}

if (CROSS_DOMAIN == 1) {
    header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
}

if (empty($_REQUEST['basedata'])) {
    $_REQUEST['basedata'] = 'null';
} else {
    if (CROSS_DOMAIN == 1 && $_REQUEST['basedata'] !== 'null') {
        session_id(md5($_REQUEST['basedata']));
    }
}

// Подключение к базе через include/secrets.php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/secrets.php");
$dbh = $mysqli;

$_SESSION['guestMode'] = 0;
$userid = getUserID();

if ($guestsMode && $userid == 0) {
    $userid = getGuestID($userid);
}

if (empty($_SESSION['cometchat']['timedifference'])) {
    $_SESSION['cometchat']['timedifference'] = 0;
}