<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/include/bittorrent.php';

dbconn();

//delete inactive user accounts
/*$secs = 92*86400;
$dt = sqlesc(get_date_time(gmtime() - $secs));
$maxclass = UC_POWER_USER;
$res = sql_query("SELECT id, username FROM users WHERE status='confirmed' AND class <= $maxclass AND last_access < $dt AND last_access <> '0000-00-00 00:00:00'") or sqlerr(__FILE__,__LINE__);
while ($arr = mysql_fetch_assoc($res)) {
    sql_query("DELETE FROM users WHERE id = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM messages WHERE receiver = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM friends WHERE userid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM friends WHERE friendid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM blocks WHERE userid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM blocks WHERE blockid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM peers WHERE userid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM readtorrents WHERE userid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM checkcomm WHERE userid = ".sqlesc($arr["id"])) or sqlerr(__FILE__,__LINE__);
    write_log("Пользователь $arr[id] ($arr[name]) был удален системой . Не посещал сайт 92 днея .","","tracker");
}*/

// email sender
/*
$secs = 30*86400;
$dt = sqlesc(get_date_time(gmtime() - $secs));
$res = sql_query("SELECT id , username , email FROM users WHERE last_access < $dt AND last_access <> '0000-00-00 00:00:00'") or sqlerr(__FILE__,__LINE__);
while ($arr = mysql_fetch_assoc($res)) {
    $msg = "Добрый день ! <br /> Вы получили это письмо , так как являтесь пользователем на сайте <a href=http://www.animeclub.lv>AnimeClub.Lv</a> и не посещали его больше чем 30 дней. <br /> Ваш логин - ".$arr['username']." <br /> Ваш пароль - <i>неуказывается</i> . <br /> Если Вы забыли пароль , воспользуйтесь формой восстановления пароля - http://www.animeclub.lv/recover.php .";
    @sent_mail($arr['email'],$SITENAME,$SITEEMAIL,$SITENAME.' - Напоминает',$msg);
    write_log("Пользователю ".$arr['username']." был отправлен э-маил . Не посещал сайт $dt дней .","","tracker");
}
*/

// delete unconfirmed users if timeout.
$deadtime = TIMENOW - $signup_timeout;
$res = sql_query(
    "SELECT id FROM users
     WHERE status = 'pending'
       AND added < FROM_UNIXTIME($deadtime)
       AND last_login < FROM_UNIXTIME($deadtime)
       AND last_access < FROM_UNIXTIME($deadtime)"
) or sqlerr(__FILE__,__LINE__);

if (mysqli_num_rows($res) > 0) {
    while ($arr = mysqli_fetch_array($res)) {
        sql_query("DELETE FROM users WHERE id = " . sqlesc($arr["id"]));
    }
}

// ************************************* Удаление приватных сообщений ***********************************************************
//Удаляем все прочтенные системные сообщения старше 10 дней
$secs_system = 10*86400; 
$dt_system  = sqlesc(get_date_time(gmtime() - $secs_system));
sql_query("DELETE FROM messages WHERE poster = '0' AND added < $dt_system") or sqlerr(__FILE__, __LINE__);

//Удаляем ВСЕ прочтенные сообщения старше 30 дней
$secs_all = 25*86400;
$dt_all  = sqlesc(get_date_time(gmtime() - $secs_all));
sql_query("DELETE FROM messages WHERE added < $dt_all") or sqlerr(__FILE__, __LINE__);
// ************************************* Удаление приватных сообщений *********************************************************** 

//remove expired warnings
$now = sqlesc(get_date_time());
$modcomment = date("Y-m-d") . " - Предупреждение снято системой по таймауту.";
$msg = sqlesc("Ваше предупреждение снято по таймауту. Постарайтесь больше не получать предупреждений и сделовать правилам.\n");
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster)
     SELECT 0, id, $now, $msg, 0 FROM users
     WHERE warned='yes'
       AND warneduntil < NOW()
       AND warneduntil <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET warned='no',
         warneduntil = '0000-00-00 00:00:00',
         modcomment = CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE warned='yes'
       AND warneduntil < NOW()
       AND warneduntil <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

//remove expired chat bans
$now = sqlesc(get_date_time());
$modcomment = date("Y-m-d") . " - Бан в Чате снято системой по таймауту.";
$msg = sqlesc("Ваш Бан в Чате снят по таймауту. Постарайтесь больше не получать Бан и сделовать правилам.\n");
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster)
     SELECT 0, id, $now, $msg, 0 FROM users
     WHERE chat_ban ='yes'
       AND chat_ban_until < NOW()
       AND chat_ban_until <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET chat_ban ='no',
         chat_ban_until = '0000-00-00 00:00:00',
         modcomment=CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE chat_ban='yes'
       AND chat_ban_until < NOW()
       AND chat_ban_until <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

// promote power users
$limit   = 25 * 1024 * 1024 * 1024;
$minratio = 1.05;
$maxdt   = sqlesc(get_date_time(gmtime() - 86400 * 28));
$now     = sqlesc(get_date_time());
$msg     = sqlesc("Наши поздравления, вы были авто-повышены до ранга [b]Опытный пользовать[/b].");
$subject = sqlesc("Вы были повышены");
$modcomment = date("Y-m-d") . " - Повышен до уровня " . $lang["class_power_user"] . " системой.";
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster, subject)
     SELECT 0, id, $now, $msg, 0, $subject FROM users
     WHERE class = 0
       AND uploaded >= $limit
       AND uploaded / downloaded >= $minratio
       AND added < $maxdt"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET class = " . UC_POWER_USER . ",
         modcomment = CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE class = " . UC_USER . "
       AND uploaded >= $limit
       AND uploaded / downloaded >= $minratio
       AND added < $maxdt"
) or sqlerr(__FILE__,__LINE__);

// demote power users
$minratio = 0.95;
$now = sqlesc(get_date_time());
$msg = sqlesc("Вы были авто-понижены с ранга [b]Опытный пользователь[/b] до ранга [b]Пользователь[/b] потому-что ваш рейтинг упал ниже [b]{$minratio}[/b].");
$subject = sqlesc("Вы были понижены");
$modcomment = date("Y-m-d") . " - Понижен до уровня " . $lang["class_user"] . " системой.";
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster, subject)
     SELECT 0, id, $now, $msg, 0, $subject FROM users
     WHERE class = 10
       AND uploaded / downloaded < $minratio"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET class = " . UC_USER . ",
         modcomment = CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE class = " . UC_POWER_USER . "
       AND uploaded / downloaded < $minratio"
) or sqlerr(__FILE__,__LINE__);

// promote super power users
$limitu  = 50 * 1024 * 1024 * 1024;
$minratio = 1.50;
$maxdt   = sqlesc(get_date_time(gmtime() - 86400 * 61));
$now     = sqlesc(get_date_time());
$msg     = sqlesc("Наши поздравления, вы были авто-повышены до ранга [b]Продвинутый пользователь[/b]. ");
$subject = sqlesc("Вы были повышены");
$modcomment = date("Y-m-d") . " - Повышен до уровня Продвинутый пользователь системой.";
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster, subject)
     SELECT 0, id, $now, $msg, 0, $subject FROM users
     WHERE class < '15'
       AND uploaded >= $limitu
       AND uploaded / downloaded >= $minratio
       AND added < $maxdt"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET class = '15',
         modcomment = CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE class < '15'
       AND uploaded >= $limitu
       AND uploaded / downloaded >= $minratio
       AND added < $maxdt"
) or sqlerr(__FILE__,__LINE__);

// demote super power users
$minratio = 1.40;
$now = sqlesc(get_date_time());
$msg = sqlesc("Вы были авто-понижены с ранга [b]Продвинутый пользователь[/b] до ранга [b]Опытный пользователь[/b] потому-что ваш рейтинг упал ниже [b]{$minratio}[/b].");
$subject = sqlesc("Вы были понижены");
$modcomment = date("Y-m-d") . " - Понижен до уровня Опытный пользователь системой.";
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, poster, subject)
     SELECT 0, id, $now, $msg, 0, $subject FROM users
     WHERE class = '15'
       AND uploaded / downloaded < $minratio"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET class = '10',
         modcomment = CONCAT(modcomment," . sqlesc($modcomment . "\n") . ")
     WHERE class = '15'
       AND uploaded / downloaded < $minratio"
) or sqlerr(__FILE__,__LINE__);

sleep(5);

// LEECHWARN USERS WITH LOW RATIO
$minratio = 0.40;
$downloaded = 20 * 1024 * 1024 * 1024;
$leechwarn_length = 2;
$length = $leechwarn_length * 7;
$leechwarn_remove_ratio = 0.70;
$reg = sqlesc(get_date_time(gmtime() - (30*86400)));
$maxclass = UC_VIP_P;
$res = sql_query(
    "SELECT id, modcomment FROM users
     WHERE class < " . sqlesc($maxclass) . "
       AND enabled = 'yes'
       AND leechwarn = 'no'
       AND uploaded / downloaded < $minratio
       AND downloaded >= $downloaded
       AND added < $reg"
) or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0) {
    $dt = sqlesc(get_date_time());
    $msg = sqlesc("Вы были предупреждены из-за низкого рейтинга. Вам необходимо получить соотношение $leechwarn_remove_ratio в течении $leechwarn_length недель или Ваш аккаунт будет заблокирован.");
    $subject = sqlesc("Вам было выставленно предупреждение!");
    $until = sqlesc(get_date_time(gmtime() + ($length*86400)));

    while ($arr = mysqli_fetch_array($res)) {
        $modcomment = $arr['modcomment'];
        $modcomment = gmdate("Y-m-d") . " - Предупрежден системой за низкий рейтинг.\n" . $modcomment;
        $modcom = sqlesc($modcomment);
        @sql_query(
            "UPDATE LOW_PRIORITY users
             SET leechwarn = 'yes',
                 warned = 'yes',
                 leechwarnuntil = $until,
                 modcomment = $modcom
             WHERE id = $arr[id]"
        ) or sqlerr(__FILE__, __LINE__);
        @sql_query(
            "INSERT INTO messages (sender, subject, receiver, added, msg, poster)
             VALUES(0, $subject, $arr[id], $dt, $msg, 0)"
        ) or sqlerr(__FILE__, __LINE__);
    }
}

// REMOVE LEECHWARNING
$res = sql_query(
    "SELECT id, modcomment FROM users
     WHERE class < " . sqlesc($maxclass) . "
       AND enabled = 'yes'
       AND leechwarn = 'yes'
       AND uploaded / downloaded >= $minratio"
) or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0) {
    $dt = sqlesc(get_date_time());
    $msg = sqlesc("Ваши предупреждения о низком рейтинге были сняты. Мы рекомендуем вам сохранить Ваш рейтинг в избежании последующего предупреждения.");
    $subject = sqlesc("Выставленные Вам предупреждения были удалены!");

    while ($arr = mysqli_fetch_array($res)) {
        $modcomment = htmlspecialchars($arr['modcomment']);
        $modcomment = gmdate("Y-m-d") . " - Предупреждение сняла система.\n" . $modcomment;
        $modcom = sqlesc($modcomment);
        sql_query(
            "UPDATE LOW_PRIORITY users
             SET leechwarn = 'no',
                 warned = 'no',
                 leechwarnuntil = '0000-00-00 00:00:00',
                 modcomment = $modcom
             WHERE id = $arr[id]"
        ) or sqlerr(__FILE__, __LINE__);
        sql_query(
            "INSERT INTO messages (sender, subject, receiver, added, msg, poster)
             VALUES(0, $subject, $arr[id], $dt, $msg, 0)"
        ) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE users_data SET down = 'yes' WHERE userid = $arr[id]") or sqlerr(__FILE__, __LINE__);
    }
}

// BAN USERS WITH LEECHWARNING EXPIRED
$res = sql_query(
    "SELECT u.id, u.modcomment, ud.userid
     FROM users u
     LEFT JOIN users_data ud ON u.id = ud.userid
     WHERE u.class < " . sqlesc($maxclass) . "
       AND ud.userid IS NULL
       AND u.enabled = 'yes'
       AND u.leechwarn = 'yes'
       AND u.leechwarnuntil < ".$dt
) or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0) {
    $dt = sqlesc(get_date_time());
    $msg = sqlesc("Вам была отключена возможность скачивания торрентов. Мы рекомендуем Вам поднять Ваш рейтинг для возможности качать с нашего трекера.");
    $subject = sqlesc("Отключена возможность скачивания торрентов!");

    while ($arr = mysqli_fetch_array($res)) {
        $modcomment = htmlspecialchars($arr['modcomment']);
        $modcomment = gmdate("Y-m-d") . " - Отключена возможность скачивания торрентов системой за низкий рейтинг и просроченое предупреждение.\n" . $modcomment;
        $modcom = sqlesc($modcomment);
        sql_query("INSERT INTO users_data (down,userid) VALUES ('no','$arr[id]')");
        sql_query(
            "INSERT INTO messages (sender, subject, receiver, added, msg, poster)
             VALUES(0, $subject, $arr[id], $dt, $msg, 0)"
        ) or sqlerr(__FILE__, __LINE__);
    }
}

sleep(5);

//remove vip statuses
$now = sqlesc(get_date_time());
$modcomment = sqlesc(date("Y-m-d") . " - Статус VIP истек по дате.\n");
$msg = sqlesc("Действие вашего статуса VIP истекло. Ваш статус автоматически изменен на прежний (до установки VIP).\n");
$subject = sqlesc("Ваш статус VIP истек");
sql_query(
    "INSERT INTO messages (sender, receiver, added, msg, subject, poster)
     SELECT 0, id, $now, $msg, $subject, 0 FROM users
     WHERE vipuntil < $now
       AND vipuntil <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

sql_query(
    "UPDATE LOW_PRIORITY users
     SET class = oldclass,
         oldclass = 0,
         donor = 'no',
         vipuntil = '0000-00-00 00:00:00',
         modcomment = CONCAT($modcomment, modcomment)
     WHERE vipuntil < $now
       AND vipuntil <> '0000-00-00 00:00:00'"
) or sqlerr(__FILE__,__LINE__);

sql_query("DELETE FROM visitor_history WHERE time < '" . (time() - (60*60*5)) . "'");  

// Update seed bonus
sql_query("UPDATE users SET bonus = bonus + $points_per_cleanup WHERE users.id IN (SELECT userid FROM peers WHERE seeder = 'yes')") or sqlerr(__FILE__,__LINE__);
sql_query("UPDATE karma SET old = 'yes' WHERE added < '".(time() - 1209600)."'") or sqlerr(__FILE__,__LINE__);

write_log(
    "Очистка [b]пользователей[/b] была успешно произведена @ ".date("F j, Y, g:i a")."",
    "",
    "system"
);
?>