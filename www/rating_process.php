<?php
include("include/bittorrent.php");
dbconn();
gzip();
loggedinorreturn();

// Настройки cookie
$expire = time() + 99999999;

// --- Используем mysqli (если $mysqli глобальный), если нет — укажи подключение вручную ---
// global $mysqli;

/**
 * Получение средней оценки торрента
 * @param int $id
 * @return float
 */
function getPerc($id) {
    $total = 0;
    $rows = 0;

    // Используем mysqli для PHP 8, если есть обёртка sql_query — используй её
    $sel = mysqli_query($GLOBALS["mysqli"], "SELECT rating_num FROM ratetorrents WHERE rating_id = '$id'");
    if (mysqli_num_rows($sel) > 0) {
        while ($data = mysqli_fetch_assoc($sel)) {
            $total += (float)$data['rating_num'];
            $rows++;
        }
        if ($rows > 0) {
            $perc = ($total / $rows) * 20;
            $newPerc = round($perc, 2);
            return $newPerc;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

// --- Обработка POST-запроса (ajax) ---
if ($_POST) {
    $id = (int) ($_POST['id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);

    if ($rating <= 5 && $rating >= 1) {
        // Проверка: голосовал ли уже пользователь
        $has_voted = false;
        $user_id = (int)$CURUSER['id'];
        $res = mysqli_query($GLOBALS["mysqli"], "SELECT id FROM ratetorrents WHERE userid = '$user_id' AND rating_id = '$id'");
        if (mysqli_fetch_assoc($res) || isset($_COOKIE['has_voted_' . $id])) {
            $has_voted = true;
        }

        if ($has_voted) {
            echo 'already_voted';
        } else {
            setcookie('has_voted_' . $id, $id, $expire, '/');
            mysqli_query($GLOBALS["mysqli"], "INSERT INTO ratetorrents (rating_id, rating_num, userid) VALUES ('$id', '$rating', '$user_id')");
            mysqli_query($GLOBALS["mysqli"], "UPDATE torrents SET ratio = '" . getPerc($id) . "' WHERE id = '$id'");

            // Повторно считаем для вывода пользователю
            $total = 0;
            $rows = 0;
            $sel = mysqli_query($GLOBALS["mysqli"], "SELECT rating_num FROM ratetorrents WHERE rating_id = '$id'");
            while ($data = mysqli_fetch_assoc($sel)) {
                $total += (float)$data['rating_num'];
                $rows++;
            }
            if ($rows > 0) {
                $perc = ($total / $rows) * 20;
                echo round($perc, 2);
            } else {
                echo 0;
            }
        }
    }
}

// --- Обработка GET-запроса (js отключён) ---
if ($_GET) {
    $id = (int) ($_POST['id'] ?? 0);
    $rating = (int) ($_GET['rating'] ?? 0);
    $user_id = (int)$CURUSER['id'];

    if ($rating <= 5 && $rating >= 1) {
        $has_voted = false;
        $res = mysqli_query($GLOBALS["mysqli"], "SELECT id FROM ratetorrents WHERE userid = '$user_id' AND rating_id = '$id'");
        if (mysqli_fetch_assoc($res) || isset($_COOKIE['has_voted_' . $id])) {
            $has_voted = true;
        }

        if ($has_voted) {
            echo 'already_voted';
        } else {
            setcookie('has_voted_' . $id, $id, $expire, '/');
            mysqli_query($GLOBALS["mysqli"], "INSERT INTO ratetorrents (rating_id, rating_num, userid) VALUES ('$id', '$rating', '$user_id')");
            mysqli_query($GLOBALS["mysqli"], "UPDATE torrents SET ratio = '" . getPerc($id) . "' WHERE id = '$id'");
        }
        header("Location:" . $_SERVER['HTTP_REFERER'] . "");
        die;
    } else {
        echo 'Вы не можете поставить оценку выше 5 или ниже 1 <a href="' . $_SERVER['HTTP_REFERER'] . '">назад</a>';
    }
}
?>