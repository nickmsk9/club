<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/include/secrets.php");

function getGuestID($userid) {
    global $cookiePrefix, $mysqli;

    $_SESSION['guestMode'] = 1;
    $userid = 0;

    if (!empty($_COOKIE[$cookiePrefix . 'guest'])) {
        $checkId = base64_decode($_COOKIE[$cookiePrefix . 'guest']);
        $safeId = $mysqli->real_escape_string($checkId);

        $sql = "SELECT id FROM cometchat_guests WHERE id = '$safeId'";
        $query = mysqli_query($mysqli, $sql);
        $result = mysqli_fetch_array($query);

        if (!empty($result['id'])) {
            $userid = (int)$result['id'];
        }
    }

    if (empty($userid)) {
        $random = rand(10000, 99999);
        $name = 'Guest ' . $random;
        $time = getTimeStamp();
        $safeName = $mysqli->real_escape_string($name);
        $sql = "INSERT INTO cometchat_guests (name, lastactivity) VALUES ('$safeName', '$time')";
        mysqli_query($mysqli, $sql);
        $userid = mysqli_insert_id($mysqli);

        setcookie($cookiePrefix . 'guest', base64_encode($userid), time() + 3600 * 24 * 365, "/");
    }

    return $userid;
}

function getGuestsList($userid, $time, $originalsql) {
    global $guestsList, $guestsUsersList, $mysqli;

    $safeUserId = $mysqli->real_escape_string($userid);
    $timeout = (ONLINE_TIMEOUT) * 2;
    $sql = "(SELECT DISTINCT cometchat_guests.id AS userid, cometchat_guests.name AS username, cometchat_guests.lastactivity AS lastactivity, '' AS avatar, '' AS link, cometchat_status.message, cometchat_status.status
             FROM cometchat_guests
             LEFT JOIN cometchat_status ON cometchat_guests.id = cometchat_status.userid
             WHERE cometchat_guests.id <> '$safeUserId'
               AND ('$time' - lastactivity < '$timeout')
               AND (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')
             ORDER BY username ASC)";

    if (!empty($_SESSION['guestMode']) && $_SESSION['guestMode'] == 0) {
        if ($guestsUsersList == 2) {
            $sql = $originalsql;
        } elseif ($guestsUsersList == 3) {
            $sql = "($originalsql) UNION $sql";
        }
    } else {
        if ($guestsList == 2) {
            $sql = $originalsql;
        } elseif ($guestsList == 3) {
            $sql = "($originalsql) UNION $sql";
        }
    }

    return $sql;
}

function getGuestDetails($userid) {
    global $mysqli;
    $safeId = $mysqli->real_escape_string($userid);

    $sql = "SELECT cometchat_guests.id AS userid, cometchat_guests.name AS username, cometchat_guests.lastactivity AS lastactivity, '' AS link, '' AS avatar, cometchat_status.message, cometchat_status.status
            FROM cometchat_guests
            LEFT JOIN cometchat_status ON cometchat_guests.id = cometchat_status.userid
            WHERE cometchat_guests.id = '$safeId'";

    return $sql;
}

function updateGuestLastActivity($userid) {
    global $mysqli;
    $safeId = $mysqli->real_escape_string($userid);
    $time = getTimeStamp();

    $sql = "UPDATE cometchat_guests SET lastactivity = '$time' WHERE id = '$safeId'";
    return $sql;
}