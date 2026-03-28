<?php
ini_set('memory_limit','512M');

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
global $memcached;
include('class/class.upload.php');

function bark($text) {
    stdhead("Ошибка");
    stderr("Ошибка", $text);
    stdfoot();
    die();
}

$allowed_types = array(
    "image/gif"    => "gif",
    "image/pjpeg"  => "jpg",
    "image/jpeg"   => "jpg",
    "image/jpg"    => "jpg",
    "image/png"    => "png",
    "image/bmp"    => "bmp"
);

$act = $_GET['act'] ?? '';
if (empty($act)) bark("Произошла ошибка", "");

////////////////////////////////////////////////////////////
// Добавление фотографии
////////////////////////////////////////////////////////////
if ($act == "add") {
    if (!empty($_FILES["photo"]['name'])) {
        if (!array_key_exists($_FILES['photo']['type'], $allowed_types)) {
            bark("Неверный тип файла");
        }

        $memcached->delete('users_' . $CURUSER['id']);
        $memcached->delete('photo');

        $dir_dest = 'photo/';
        $sql = sql_query("SELECT photo FROM users WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_array($sql);
        if ($arr['photo']) {
            // Delete existing main photo if it exists
            $oldPhotoPath = __DIR__ . '/photo/' . $arr['photo'];
            if (file_exists($oldPhotoPath) && is_file($oldPhotoPath)) {
                unlink($oldPhotoPath);
            }
            @sql_query("UPDATE users SET photo = '' WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        }

        $photo = new Upload($_FILES['photo']);
        if (!is_object($photo)) {
            bark("Ошибка создания объекта Upload");
        }

        if ($photo->uploaded) {
            $photo->file_max_size = 1024 * 1024 * 5; // 5MB
            $photo->image_convert = 'jpg';

            $photo->image_text = $_SERVER['HTTP_HOST'];
            $photo->image_text_position = 'RB';
            $photo->image_text_padding = 5;
            $photo->Process($dir_dest);

            $name = $photo->file_dst_name;

            if ($photo->processed) {
                @sql_query("UPDATE users SET photo=" . sqlesc($name) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                header("Location: my.php");
                exit();
            } else {
                bark("Ошибка обработки изображения: " . $photo->error);
            }

            $photo->Clean();
        } else {
            bark("Ошибка загрузки файла: " . $photo->error);
        }
    } else {
        bark("Вы не выбрали фотографию");
    }

////////////////////////////////////////////////////////////
// Удаление фотографии
////////////////////////////////////////////////////////////
} elseif ($act == "del") {
    $memcached->delete('users_' . $CURUSER['id']);
    $memcached->delete('photo');

    $sql = sql_query("SELECT photo FROM users WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_array($sql);
    if ($arr['photo']) {
        // Delete main photo if it exists
        $photoPath = __DIR__ . '/photo/' . $arr['photo'];
        if (file_exists($photoPath) && is_file($photoPath)) {
            unlink($photoPath);
        }
        // Delete thumbnail if it exists
        $thumbPath = __DIR__ . '/cache/thumbs/' . $arr['photo'];
        if (file_exists($thumbPath) && is_file($thumbPath)) {
            unlink($thumbPath);
        }
        @sql_query("UPDATE users SET photo = '' WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        header("Location: my.php");
        exit();
    } else {
        bark("Произошла ошибка в запросе!");
    }

////////////////////////////////////////////////////////////
// Удаление админом
////////////////////////////////////////////////////////////
} elseif ($act == "deladmin") {
    $id = (int)$_GET["id"];
    $memcached->delete('users_' . $id);

    if (get_user_class() < UC_MODERATOR) {
        die('WTF ?');
    }

    $sql = sql_query("SELECT photo FROM users WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_array($sql);
    if ($arr['photo']) {
        // Delete main photo if it exists
        $photoPath = __DIR__ . '/photo/' . $arr['photo'];
        if (file_exists($photoPath) && is_file($photoPath)) {
            unlink($photoPath);
        }
        // Delete thumbnail if it exists
        $thumbPath = __DIR__ . '/cache/thumbs/' . $arr['photo'];
        if (file_exists($thumbPath) && is_file($thumbPath)) {
            unlink($thumbPath);
        }
        @sql_query("UPDATE users SET photo = '' WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        if (!empty($_SERVER['HTTP_REFERER'])) {
            header("Location:" . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: photo.php");
        }
        exit();
    } else {
        bark("Произошла ошибка в запросе!");
    }

} else {
    die('Че нах ???');
}
?>