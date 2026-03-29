<?php

# IMPORTANT: Do not edit below unless you know what you are doing!
	if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

	require_once($rootpath . 'include/functions_global.php');
	require_once($rootpath . 'include/functions_blogs.php');

if (!function_exists('getlang')) {
    function getlang(string $name = 'main'): void {
        global $lang;

        if (!isset($lang) || !is_array($lang)) {
            $lang = [];
        }

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
        }

        $language = 'lang_russian';
        $langfile = ROOT_PATH . "languages/$language/lang_{$name}.php";

        if (file_exists($langfile)) {
            include_once $langfile;
        } else {
            error_log("Языковой файл не найден: $langfile", 3, ROOT_PATH . 'logs/lang_errors.log');
        }
    }
}

// Проверка: функция уже объявлена?
if (!function_exists('local_user')) {
    /**
     * Определяет, является ли пользователь локальным (например, работает на localhost)
     *
     * @return bool
     */
    function local_user(): bool {
        return ($_SERVER["SERVER_ADDR"] ?? '') === ($_SERVER["REMOTE_ADDR"] ?? '');
    }
}

function sql_query($query) {
    global $mysqli, $queries, $query_stat, $querytime;

    $queries++;
    $query_start_time = microtime(true);

    $result = $mysqli->query($query);

    if (!$result) {
        error_log("SQL Error: " . $mysqli->error . " in query: " . $query);
        return false;
    }

    $query_end_time = microtime(true);
    $query_time = $query_end_time - $query_start_time;
    $querytime += $query_time;

    $query_stat[] = [
        "seconds" => number_format($query_time, 8),
        "query" => $query
    ];

    return $result;
}

function dbconn($autoclean = false, $lightmode = false) {
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $mysql_charset, $mysqli;

    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
    if ($mysqli->connect_error) {
        die("dbconn: mysqli_connect_error: " . $mysqli->connect_error);
    }
    if (!$mysqli->set_charset($mysql_charset)) {
        die("dbconn: set_charset: " . $mysqli->error);
    }
    userlogin($lightmode);
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'index.php') {
        register_shutdown_function("autoclean");
    }
    register_shutdown_function(function() use ($mysqli) {
        $mysqli->close();
    });
    
    return $mysqli;
}


function userlogin(bool $lightmode = false): void {
    global $SITE_ONLINE, $default_language, $lang, $use_lang, $use_ipbans, $mcache, $mysqli;

    // Сбрасываем текущего пользователя
    unset($GLOBALS["CURUSER"]);

    // Получаем IP пользователя и преобразуем в long
    $ip = getip();
    $nip = ip2long($ip);

    // Проверка IP-блокировок
    if ($use_ipbans && !$lightmode) {
        $row = $mcache->get_value('bans');
        if ($row === false) {
            $res = sql_query("SELECT first, last FROM bans");
            $cache = [];
            while ($ban_row = $res->fetch_assoc()) {
                $cache[] = $ban_row;
            }
            $mcache->cache_value('bans', $cache, rand(900, 2300));
            $row = $cache;
        }

        if (is_array($row)) {
            foreach ($row as $array) {
                if ($nip >= $array['first'] && $nip <= $array['last']) {
                    header("HTTP/1.0 403 Forbidden");
                    echo "<h1>403 Forbidden</h1><p>Доступ с вашего IP-адреса запрещён.</p>";
                    exit;
                }
            }
        }
    }

    // Если сайт выключен или куки отсутствуют — считаем как гость
    if (!$SITE_ONLINE || empty($_COOKIE["uid"]) || empty($_COOKIE["pass"])) {
        getlang();
        return;
    }

    $id = (int)$_COOKIE["uid"];
    $cookiePass = $_COOKIE["pass"];

    // Проверка корректности данных из куки
    if ($id <= 0 || strlen($cookiePass) !== 32) {
        getlang();
        return;
    }

    // Загружаем данные пользователя
    $res = sql_query("SELECT * FROM users WHERE id = " . sqlesc($id) . " LIMIT 1");
    $row = $res->fetch_assoc();

    if (!$row) {
        getlang();
        return;
    }

    // Хеш из базы должен совпасть с тем, что в куки
    $expectedPass = cookieFromPasshash($row["passhash"]);

    if ($cookiePass !== $expectedPass) {
        getlang();
        return;
    }

    // Если есть override_class — использовать его
    if (isset($row['override_class']) && $row['override_class'] < $row['class']) {
        $row['class'] = $row['override_class'];
    }

    // Устанавливаем глобального пользователя
    $GLOBALS["CURUSER"] = $row;

    // Загружаем язык
    getlang();

    // Инициализируем сессию, если она ещё не активна
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Обновляем доступ раз в 2.5 минуты
    if (!isset($_SESSION['last_access']) || $_SESSION['last_access'] < time() - 150) {
        $_SESSION['last_access'] = time();
        sql_query("UPDATE LOW_PRIORITY users SET last_access = " . sqlesc(get_date_time()) . ", ip = " . sqlesc($ip) . " WHERE id=" . $row["id"]);
    }
}

function get_server_load() {
	global $lang, $phpver;
	if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
		return 0;
	} elseif (@file_exists("/proc/loadavg")) {
		$load = @file_get_contents("/proc/loadavg");
		$serverload = explode(" ", $load);
		$serverload[0] = round($serverload[0], 4);
		if(!$serverload) {
			$load = @exec("uptime");
			$load = preg_split('/load averages?: /', $load);
			$serverload = explode(",", $load[1]);
		}
	} else {
		$load = @exec("uptime");
		$load = preg_split('/load averages?: /', $load);
		$serverload = explode(",", $load[1]);
	}
	$returnload = trim($serverload[0]);
	if(!$returnload) {
		$returnload = $lang['unknown'];
	}
	return $returnload;
}

// Функция экранирования строки. В PHP 8.0 magic_quotes удалены, stripslashes больше не нужен.
function unesc($x) {
    return $x;
}

function gzip() {

}  

// IP Validation
function validip($ip) {
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r) {
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

function getip() {

		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];

		} else if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		} else {

			$ip_address = '';
		}

		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}

   return $ip_address;
 }

function autoclean() {
    global $autoclean_interval, $rootpath, $mysqli;

    $now = time();
    $docleanup = 0;

    // Выполняем запрос для получения времени последней очистки
    $res = sql_query("SELECT value_u FROM avps WHERE arg = 'lastcleantime'");
    if (!$res) {
        die("SQL Error: " . $mysqli->error);
    }

    $row = $res->fetch_assoc(); // Используем fetch_assoc() вместо mysqli_fetch_array()
    if (!$row) {
        // Если запись не найдена, создаем новую
        sql_query("INSERT INTO avps (arg, value_u) VALUES ('lastcleantime', $now)");
        return;
    }

    $ts = $row['value_u']; // Получаем значение value_u
    if ($ts + $autoclean_interval > $now) {
        return; // Если интервал очистки еще не истек, выходим
    }

    // Обновляем время последней очистки
    sql_query("UPDATE LOW_PRIORITY avps SET value_u = $now WHERE arg = 'lastcleantime' AND value_u = $ts");
    if ($mysqli->affected_rows == 0) {
        return; // Если не было изменений, выходим
    }

    // Подключаем файл cleanup.php и выполняем очистку
    require_once($rootpath . 'include/cleanup.php');
    docleanup();
}


function mksize($bytes) {
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	else
		return number_format($bytes / 1099511627776, 2) . " TB";
}

function mksizeint($bytes) {
		$bytes = max(0, $bytes);
		if ($bytes < 1000)
				return floor($bytes) . " B";
		elseif ($bytes < 1000 * 1024)
				return floor($bytes / 1024) . " kB";
		elseif ($bytes < 1000 * 1048576)
				return floor($bytes / 1048576) . " MB";
		elseif ($bytes < 1000 * 1073741824)
				return floor($bytes / 1073741824) . " GB";
		else
				return floor($bytes / 1099511627776) . " TB";
}

function deadtime() {
	global $announce_interval;
	return time() - floor($announce_interval * 1.3);
}

function mkprettytime($s) {
    if ($s < 0)
	$s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
		$y = explode(":", $x);
		if ($y[0] > 1) {
		    $v = $s % $y[0];
		    $s = floor($s / $y[0]);
		} else
		    $v = $s;
	$t[$y[1]] = $v;
    }

    if ($t["day"])
	return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
	return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	return sprintf("%d:%02d", $t["min"], $t["sec"]);
}

// Импортирует переменные из запроса в глобальное пространство
function mkglobal($vars) {
    // Если пришла строка (например, "username:password") — разбиваем её в массив
    if (is_string($vars)) {
        $vars = explode(":", $vars);
    }

    foreach ($vars as $var) {
        if (isset($_POST[$var])) {
            $GLOBALS[$var] = unesc($_POST[$var]);
        } elseif (isset($_GET[$var])) {
            $GLOBALS[$var] = unesc($_GET[$var]);
        } elseif (isset($_COOKIE[$var])) {
            $GLOBALS[$var] = unesc($_COOKIE[$var]);
        } else {
            return false;
        }
    }
    return true;
}

function tr($x, $y, $noesc=0, $prints = true, $width = "", $relation = '') {
	if ($noesc)
		$a = $y;
	else {
		$a = htmlspecialchars_uni($y);
		$a = str_replace("\n", "<br />\n", $a);
	}
	if ($prints) {
	  $print = "<td width=\"". $width ."\" class=\"heading\" valign=\"top\" align=\"right\">$x</td>";
	  $colpan = "align=\"left\"";
	} else {
		$colpan = "colspan=\"2\"";
	}

	print("<tr".( $relation ? " relation=\"$relation\"" : "").">$print<td valign=\"top\" $colpan>$a</td></tr>\n");
}

function validfilename($name) {
	return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function send_mime_mail($name_from, // имя отправителя
                        $email_from, // email отправителя
                        $name_to, // имя получателя
                        $email_to, // email получателя
                        $data_charset, // кодировка переданных данных
                        $send_charset, // кодировка письма
                        $subject, // тема письма
                        $body, // текст письма
                        $html = FALSE // письмо в виде html или обычного текста
                        ) {
  $to = mime_header_encode($name_to, $data_charset, $send_charset)
                 . ' <' . $email_to . '>';
  $subject = mime_header_encode($subject, $data_charset, $send_charset);
  $from =  mime_header_encode($name_from, $data_charset, $send_charset)
                     .' <' . $email_from . '>';
  if($data_charset != $send_charset) {
    $body = iconv($data_charset, $send_charset, $body);
  }
  $headers = "From: $from\r\n";
  $headers     .= "To: $to\n"; 
  $headers     .= "Subject: $subject\n"; 
  $type = ($html) ? 'html' : 'plain';
  $headers .= "Content-type: text/$type; charset=$send_charset\r\n";
  $headers .= "Mime-Version: 1.0\r\n";

  return mail($to, $subject, $body, $headers);
}

function mime_header_encode($str, $data_charset, $send_charset) {
  if($data_charset != $send_charset) {
    $str = iconv($data_charset, $send_charset, $str);
  }
  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}





function sent_mail($to,$subject,$body,$fromemail) {
	global $SITENAME,$SITEEMAIL;
	$result = true;
	@mail($to, $subject, $body, "From: $SITEEMAIL") or $result = false;
	return $result;
}

function sqlesc($value, $mysqli = null) {
    // Если значение является числом, возвращаем его без изменений
    if (is_numeric($value)) {
        return $value;
    }

    // Если объект mysqli не передан, используем глобальный
    if ($mysqli === null) {
        global $mysqli;
    }

    // Экранируем значение с помощью mysqli_real_escape_string
    $value = "'" . $mysqli->real_escape_string($value) . "'";
    return $value;
}

// Обязательно нужно глобальное подключение к MySQL через mysqli
// Пример: $mysqli = new mysqli("localhost", "user", "pass", "db");

function sqlwildcardesc($x) {
    global $mysqli;
    if (!isset($mysqli)) {
        die("Нет соединения с БД (mysqli не определён)");
    }
    return str_replace(["%", "_"], ["\\%", "\\_"], mysqli_real_escape_string($mysqli, $x));
}

function urlparse($m) {
    $t = $m[0];
    if (preg_match('#^\w+://#', $t)) {
        return '<a href="' . htmlspecialchars($t, ENT_QUOTES) . '">' . htmlspecialchars($t) . '</a>';
    }
    return '<a href="http://' . htmlspecialchars($t, ENT_QUOTES) . '">' . htmlspecialchars($t) . '</a>';
}

function parsedescr($d, $html = false) {
    if (!$html) {
        $d = htmlspecialchars_uni($d);
        $d = nl2br($d); // заменяет \n на <br>
    }
    return $d;
}

// Примерная реализация htmlspecialchars_uni, если у тебя ее нет
function htmlspecialchars_uni($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function stdhead($title = "", $msgalert = true, $all_title = false) {
    global $CURUSER, $SITE_ONLINE, $SITENAME, $DEFAULTBASEURL, $SYSOP_TESTING, $ss_uri, $lang, $default_theme, $get_user_class, $mcache, $link;

    // Если сайт выключен — прекращаем работу сразу
    if (!$SITE_ONLINE) {
        die("Site is down for maintenance, please check back again later... thanks<br />");
    }

    // Проверяем, активен ли пользователь
   if (!empty($CURUSER) && is_array($CURUSER) && !empty($CURUSER['id'])) {
    if (isset($CURUSER['enabled']) && $CURUSER['enabled'] === 'no') {
        header("HTTP/1.0 403 Forbidden");
        header("Content-Type: text/html; charset=UTF-8");
        echo "<html><body><h1>403 Forbidden</h1>Аккаунт отключен системой! Для выяснения причины свяжитесь с Администрацией - admin@animeclub.lv</body></html>";
        exit;
    }
}

    // Ограничение доступа во время тестов системы (кроме админов)
    if (!$SYSOP_TESTING) {
        if (function_exists('get_user_class') && get_user_class() < UC_SYSOP && $CURUSER) {
            header("Content-Type: text/html; charset=" . ($lang['language_charset'] ?? 'UTF-8'));
            header("Refresh: 60; url=$DEFAULTBASEURL");
            echo '<br><br><br><center><b>На сайте ведутся обновления! Это займет некоторое время.<br /><br /><br /><img src="pic/loader.gif" border="0" /></b></center>';
            exit;
        }
    }

    // Формируем заголовок страницы
    if ($title === "") {
        $title = isset($_GET['debug']) ? "" : '';
    } else {
        $title = (isset($_GET['debug']) ? "" : '') . "  " . htmlspecialchars_uni($title);
    }

    $ss_uri = 'Anime';

    // Проверяем наличие пользователя и кешируем количество непрочитанных сообщений
    $unread = 0;
    if (!empty($CURUSER['id'])) {
        if ($mcache) {
            $cache_key = 'unread_msgs_user_' . $CURUSER['id'];
            $unread = $mcache->get($cache_key);
            if ($unread === false) {
                $res = sql_query("SELECT COUNT(*) FROM messages WHERE receiver = " . (int)$CURUSER["id"] . " AND unread='yes' LIMIT 1");
                if ($res) {
                    $arr = mysqli_fetch_row($res);
                    $unread = (int)$arr[0];
                    // Кешируем на 30 секунд (или сколько нужно)
                    $mcache->set($cache_key, $unread, 30);
                }
            }
        } else {
            $res = sql_query("SELECT COUNT(*) FROM messages WHERE receiver = " . (int)$CURUSER["id"] . " AND unread='yes' LIMIT 1");
            if ($res) {
                $arr = mysqli_fetch_row($res);
                $unread = (int)$arr[0];
            }
        }
    }

    // Подключаем шаблоны (используем переменную для пути)
    $theme_path = "themes/" . $ss_uri . "/";
    require_once($theme_path . "template.php");
    require_once($theme_path . "stdhead.php");
}

function stdfoot() {
    global $CURUSER, $ss_uri, $lang, $queries, $tstart, $query_stat, $querytime, $get_user_class, $mcache;

    require_once("themes/" . $ss_uri . "/template.php");
    require_once("themes/" . $ss_uri . "/stdfoot.php");
}

function genbark($x,$y) {
	stdhead($y);
	print("<h2>" . htmlspecialchars_uni($y) . "</h2>\n");
	print("<p>" . htmlspecialchars_uni($x) . "</p>\n");
	stdfoot();
	exit();
}

function mksecret($length = 20) {
    $length = (int)$length;
    if ($length <= 0) {
        return '';
    }

    static $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    static $alphabetLength = 61;

    $secret = '';
    $bytes = random_bytes($length);

    for ($i = 0; $i < $length; $i++) {
        $secret .= $alphabet[ord($bytes[$i]) % $alphabetLength];
    }

    return $secret;
}

function httperr($code = 404) {
	$sapi_name = php_sapi_name();
	if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi') {
		header('Status: 404 Not Found');
	} else {
		header('HTTP/1.1 404 Not Found');
	}
	exit;
}

function gmtime() {
	return strtotime(get_date_time());
}

// Устанавливает куки при логине
function logincookie(int $id, string $passhash, string $lang): void {
    $host = $_SERVER['HTTP_HOST'];
    $domain = explode(':', $host)[0]; // Убираем порт из домена

    // Удаляем старые куки
    setcookie("uid", "", time() - 3600, "/", $domain);
    setcookie("pass", "", time() - 3600, "/", $domain);
    setcookie("lang", "", time() - 3600, "/", $domain);

    file_put_contents(ROOT_PATH . "/logs/login_debug.log",
        "[" . date("Y-m-d H:i:s") . "] Старые куки удалены\n",
        FILE_APPEND
    );

    // Устанавливаем новые куки
    $cookiePass = cookieFromPasshash($passhash);
    setcookie("uid", $id, time() + 86400 * 365, "/", $domain);
    setcookie("pass", $cookiePass, time() + 86400 * 365, "/", $domain);
    setcookie("lang", $lang, time() + 86400 * 365, "/", $domain);

    $log = "[" . date("Y-m-d H:i:s") . "] Куки установлены: uid=$id, pass=$cookiePass, lang=$lang, домен=$domain\n";
    $log .= "[" . date("Y-m-d H:i:s") . "] Заголовки: " . print_r(headers_list(), true) . "\n";
    file_put_contents(ROOT_PATH . "/logs/login_debug.log", $log, FILE_APPEND);
}

// Функция для разлогинивания и удаления куки
function logoutcookie(): void {
    $ip = getip();
    $logPath = ROOT_PATH . '/logs/logout.log';

    $domains = [
        '',
        false,
        $_SERVER['HTTP_HOST'] ?? '',
        parse_url("http://{$_SERVER['HTTP_HOST']}", PHP_URL_HOST),
        'localhost',
        '127.0.0.1',
        'animeclub.local',
    ];

    $expires = time() - 3600;
    foreach (['uid', 'pass', 'lang'] as $cookie) {
        foreach ($domains as $domain) {
            setcookie($cookie, '', $expires, '/', $domain, false, true);
        }
    }

    unset($_COOKIE['uid'], $_COOKIE['pass'], $_COOKIE['lang']);

    // Убиваем сессию
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    // Логируем
    $log = "[" . date('Y-m-d H:i:s') . "] Разлогинивание IP: $ip | Домены перебраны: " . implode(', ', array_unique($domains)) . "\n";
    $log .= "Оставшиеся куки после удаления:\n" . print_r($_COOKIE, true);
    $log .= "Сессия после уничтожения:\n" . print_r($_SESSION ?? [], true);

    file_put_contents($logPath, $log . "\n", FILE_APPEND);
}

function loggedinorreturn($nowarn = false) {
    global $CURUSER, $DEFAULTBASEURL;

    if (!$CURUSER) {
        $returnTo = isset($_SERVER["REQUEST_URI"]) ? urlencode($_SERVER["REQUEST_URI"]) : '';
        $redirectUrl = $DEFAULTBASEURL . '/login.php?returnto=' . $returnTo . ($nowarn ? '&nowarn=1' : '');
        header("Location: $redirectUrl");
        exit();
    }

    return true; // Возвращаем true, если пользователь авторизован
}

function deletetorrent($id) {
	global $torrent_dir;

	sql_query("DELETE FROM torrents WHERE id = $id");
	sql_query("DELETE FROM snatched WHERE torrent = $id");
	sql_query("DELETE FROM bookmarks WHERE id = $id");

	foreach(explode(".","peers.files.comments") as $x)
		sql_query("DELETE FROM $x WHERE torrent = $id");
	@unlink("$torrent_dir/$id.torrent");
}

function pager($rpp, $count, $href, $opts = array()) {
    $pages = ceil($count / $rpp);

    if (empty($opts["lastpagedefault"]))
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    $page = isset($_GET["page"]) ? max(0, (int)$_GET["page"]) : $pagedefault;

    $pager = "<div id='Pager'>";
    $pager2 = ""; // <-- добавлено
    $bregs = "";  // <-- добавлено

    $mp = $pages - 1;

    $as = "<b>«««</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\" style=\"text-decoration: none;\">$as</a>";
    }

    $as = "<b>»»»</b>";
    if ($page < $mp && $mp >= 0) {
        $pager2 .= "<a href=\"{$href}page=" . ($page + 1) . "\" style=\"text-decoration: none;\">$as</a>";
        $pager2 .= "$bregs";
        $pager2 .= "</div><br clear=\"all\">";
    } else {
        $pager2 .= $bregs . "</div>";
    }

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 5;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "<a>...</a>";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = min($start + $rpp - 1, $count);

            $text = $i + 1;
            if ($i != $page)
                $pagerarr[] = "<a title=\"$start&nbsp;-&nbsp;$end\" href=\"{$href}page=$i\" style=\"text-decoration: none;\">$text</a>";
            else
                $pagerarr[] = "<div>$text</div>";
        }

        $pagerstr = join("", $pagerarr);
        $pagertop = "$pager $pagerstr $pager2\n";
        $pagerbottom = "$pager $pagerstr $pager2\n";
    } else {
        $pagertop = $pager;
        $pagerbottom = $pager;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

function downloaderdata($res) {
	$rows = array();
	$ids = array();
	$peerdata = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$rows[] = $row;
		$id = $row["id"];
		$ids[] = $id;
		$peerdata[$id] = array(downloaders => 0, seeders => 0, comments => 0);
	}

	if (count($ids)) {
		$allids = implode(",", $ids);
		$res = sql_query("SELECT COUNT(*) AS c, torrent, seeder FROM peers WHERE torrent IN ($allids) GROUP BY torrent, seeder");
		while ($row = mysqli_fetch_assoc($res)) {
			if ($row["seeder"] == "yes")
				$key = "seeders";
			else
				$key = "downloaders";
			$peerdata[$row["torrent"]][$key] = $row["c"];
		}
		$res = sql_query("SELECT COUNT(*) AS c, torrent FROM comments WHERE torrent IN ($allids) GROUP BY torrent");
		while ($row = mysqli_fetch_assoc($res)) {
			$peerdata[$row["torrent"]]["comments"] = $row["c"];
		}
	}

	return array($rows, $peerdata);
}



function searchfield($s) {
	return preg_replace(array('/^[a-zа-яё0-9 ]$/iu', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
    $cats = array();
    $read = readCache("cats.cache", "1800");

    if ($read == FALSE) {
        $cats_res = sql_query("SELECT id, name FROM categories ORDER BY sort ASC");
        if ($cats_res) {
            while ($cats_row = mysqli_fetch_assoc($cats_res)) {
                $cats[] = $cats_row;
            }
            ksort($cats);
        }
        $text = serialize($cats);
        writeCache($text, "cats.cache");
    } else {
        $cats = unserialize($read);
    }
    return $cats;
}

function taggenrelist($cat) {
    $ret = array();
    $res = sql_query("SELECT id, name, howmuch FROM tags WHERE category=$cat ORDER BY name ASC");

    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $ret[] = $row;
        }
    } else {
        $ret = '';
    }
    return $ret;
}

function decode_to_utf8($int = 0) {
    $t = '';
    if ( $int < 0 ) {
         return chr(0);
    } else if ( $int <= 0x007f ) {
         $t .= chr($int);
    } else if ( $int <= 0x07ff ) {
         $t .= chr(0xc0 | ($int >> 6));
         $t .= chr(0x80 | ($int & 0x003f));
    } else if ( $int <= 0xffff ) {
         $t .= chr(0xe0 | ($int  >> 12));
         $t .= chr(0x80 | (($int >> 6) & 0x003f));
         $t .= chr(0x80 | ($int  & 0x003f));
    } else if ( $int <= 0x10ffff ) {
         $t .= chr(0xf0 | ($int  >> 18));
         $t .= chr(0x80 | (($int >> 12) & 0x3f));
         $t .= chr(0x80 | (($int >> 6) & 0x3f));
         $t .= chr(0x80 | ($int  &  0x3f));
    } else {
         return chr(0);
    }
    return $t;
}

 function convert_unicode($t, $to = 'utf8')
{
        $t = preg_replace( '#%u([0-9A-F]{1,4})#ie', "'&#' . hexdec('\\1') . ';'", $t );
        $t = urldecode ($t);
        $t = @html_entity_decode($t, ENT_NOQUOTES, $to);
        return $t;
}


function addtags($addtags, $category) {
    $addtags = str_replace(" ", "", $addtags);
    $tags = ""; // Инициализация переменной перед использованием
    foreach (explode(",", $addtags) as $tag) {
        if ($tag !== '') {
            $tags .= "<font color=#bc5349><a style=\"font-weight:normal\" href=\"/browse?tag=$tag\">" . htmlspecialchars($tag) . "</a></font>, ";
        }
    }
    if ($tags) {
        $tags = substr($tags, 0, -2); // Убираем последний ", "
    }
    if (empty($addtags)) {
        $tags = "Нет тэгов";
    }
    return $tags;
}


function linkcolor($num) {
	if (!$num)
		return "red";
//	if ($num == 1)
//		return "yellow";
	return "green";
}

function ratingpic($num) {
	global $pic_base_url, $lang;
	$r = round($num * 2) / 2;
	if ($r < 1 || $r > 5)
		return;
	return "<img src=\"$pic_base_url$r.gif\" border=\"0\" alt=\"".$lang['rating'].": $num / 5\" />";
}

function writecomment($userid, $comment) {
	$res = sql_query("SELECT modcomment FROM users WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res);

	$modcomment = date("d-m-Y") . " - " . $comment . "" . ($arr[modcomment] != "" ? "\n" : "") . "$arr[modcomment]";
	$modcom = sqlesc($modcomment);

	return sql_query("UPDATE LOW_PRIORITY users SET modcomment = $modcom WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
}

function hash_pad($hash) {
	return str_pad($hash, 20);
}

function hash_where($name, $hash) {
	$shhash = preg_replace('/ *$/s', "", $hash);
	return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function validemail ($email) {
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function parked() {
    global $CURUSER, $lang;
    // Если пользователь не авторизован или параметр parked не задан — выходим без проверки
    if (empty($CURUSER) || !isset($CURUSER['parked'])) {
        return;
    }
    // Если аккаунт припаркован, выводим сообщение об ошибке
    if ($CURUSER['parked'] === 'yes') {
        stderr($lang['error'], "Ваш аккаунт припаркован.");
    }
}

function nicetime($input, $time = false) {
    $search = array('January','February','March','April','May','June','July','August','September','October','November','December');
    $replace = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
    $seconds = strtotime($input);
    if ($time == true)
        $data = date("j F Y в H:i:s", $seconds);
    else
        $data = date("j F Y", $seconds);
    $data = str_replace($search, $replace, $data);
    return $data;
} 

function mysqli_modified_rows($mysqli) {
    $info_str = $mysqli->info;
    $a_rows = $mysqli->affected_rows;

    if (preg_match('/Rows matched: (\d+)/', $info_str, $r_matched)) {
        return ($a_rows < 1) ? (int)$r_matched[1] : $a_rows;
    }

    return $a_rows;
}



if( !function_exists('memory_get_usage') )
{


function memory_get_usage()
{
//If its Windows
//Tested on Win XP Pro SP2. Should work on Win 2003 Server too
//Doesn't work for 2000
//If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memo...usage.php#54642
if ( substr(PHP_OS,0,3) == 'WIN')
{
if ( substr( PHP_OS, 0, 3 ) == 'WIN' )
{
$output = array();
exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );

return preg_replace( '/[\D]/', '', $output[5] ) * 1024;
}

}else
{


//We now assume the OS is UNIX
//Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
//This should work on most UNIX systems
$pid = getmypid();
exec("ps -eo%mem,rss,pid | grep $pid", $output);
$output = explode(" ", $output[0]);
//rss is given in 1024 byte units
return $output[1] * 1024;
}

}
}

/**
 * Конвертация строки из одной кодировки в другую.
 * Если нет ни mbstring, ни iconv, возвращает исходную строку.
 *
 * @param string $str Исходная строка
 * @param string $charsetFrom Кодировка исходной строки
 * @param string $charsetTo Целевая кодировка
 * @return string Конвертированная строка
 */
function convertEncoding(string $str, string $charsetFrom, string $charsetTo): string {
    if (extension_loaded('mbstring')) {
        return mb_convert_encoding($str, $charsetTo, $charsetFrom);
    } elseif (extension_loaded('iconv')) {
        return iconv($charsetFrom, $charsetTo, $str);
    }
    return $str;
}


function cookieFromPasshash($p)
{
    return md5('gsankjukdg' . $p . '=='); // Статичная генерация!
}

function getRate($id,$what) { 
		GLOBAL $CURUSER;
		if($id == 0 || !in_array($what,array("topic","torrent")))
			return;
			
	$q = sql_query("SELECT sum(r.rating) as sum, count(r.rating) as count, r2.id as rated, r2.rating  FROM rating as r  LEFT JOIN rating as r2 ON (r2.".$what." = ".$id." AND r2.user = ".$CURUSER["id"].") WHERE r.".$what." = ".$id." GROUP BY r.".$what );
	$a = mysqli_fetch_assoc($q);
	
		$p = ($a["count"] > 0 ? round((($a["sum"] / $a["count"]) * 20), 2) : 0);
		if($a["rated"])
			$rate = "<ul class=\"star-rating\"  title=\"Ваша оценка ".$what." ".$a["rating"]." бал".($a["rating"] >1 ? "ов" : "")."\" ><li style=\"width: ".$p."%;\" class=\"current-rating\" >.</li></ul>";
		else {
			$i=1;
			$rate = "<ul class=\"star-rating\"><li style=\"width: ".$p."%;\" class=\"current-rating\">.</li>";
		foreach(array("one-star","two-stars","three-stars","four-stars","five-stars") as $star) {
			$rate .= "<li><a href=\"rating.php?id=".$id."&amp;rate=".$i."&amp;ref=".urlencode($_SERVER["REQUEST_URI"])."&amp;what=".$what."\" class=\"".$star."\" onclick=\"do_rate(".$i.",".$id.",'".$what."'); return false\" title=\"".$i." бал".($i > 1 ? "ов" : "" )." из 5\" >$i</a></li>";
			$i++;
		}
			$rate .="</ul>";
		}
		switch($what) {
			case "torrent" : $return = "<div id=\"rate_".$id."\">".$rate."</div>";
			break;
			case "topic" : $return = "<table cellpadding=\"0\" cellspacing=\"0\" style=\"border:none;\">
				  <tr>
					<td align=\"center\" id=\"rate_".$id."\" style=\"border:none;\">".$rate."</td>
				  </tr>
				</table>";
			break;
		}
		return $return;
	}
	
	function showRate($rate_sum,$rate_count)
	{
		$p = ($rate_count > 0 ? round((($rate_sum/ $rate_count) * 20), 2) : 0);
		return "<ul class=\"star-rating\"><li style=\"width: ".$p."%;\" class=\"current-rating\" >.</li></ul>";
	}


function show_news (){
if (cache_check("news", 600))
    $res = cache_read("news");
else {
$res = sql_query("SELECT id ,added, subject FROM news ORDER BY id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$news_cache = array();
    while ($cache_data = mysqli_fetch_array($res))
        $news_cache[] = $cache_data;

    cache_write("news", $news_cache);
    $res = $news_cache;
    }
foreach($res as $arr) 
	{	
	$newsid = $arr["id"];
	$subject = $arr["subject"];
	$added = $arr["added"];
	print("<a class=\"news_show\" href=\"/news_view.php?newsid=".$newsid."\" title=\"".$subject."\">".$subject."</a> - <i>".$added."</i><hr>\n");
	}

}

// show visitors were on the page last x minuts (by qwertzuiop)
/*
   $id - идентификатор страницы (например details/idID)
   $timeout - время показа бывших на странице юзеров в минутах
*/
function visitorsHistory($id = "", $timeout = 15, $notAdd = false, $url = "") {
    global $CURUSER, $mysqli;

    $timeout = $timeout * 60;

    // Подготовка URL
    if ($url == "") {
        $url = htmlspecialchars(urldecode($_SERVER['REQUEST_URI'] . ($id != "" ? "?" . $id : "")));
    } else {
        $url = htmlspecialchars(urldecode($url));
    }

    $v = [];
    $curUpdated = false;

    // Запрос к базе данных
    $query = "SELECT v.* FROM visitor_history AS v WHERE v.url = " . sqlesc($url) . " ORDER BY time DESC";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        while ($res = $result->fetch_assoc()) {
            if ($res["uid"] == $CURUSER["id"] && !$notAdd) {
                // Обновление времени для текущего пользователя
                $updateQuery = "UPDATE visitor_history SET time = '" . time() . "' WHERE url = " . sqlesc($url) . " AND ip = '" . ($CURUSER["ip"] == "" ? getip() : $CURUSER['ip']) . "' AND uid = '" . $CURUSER["id"] . "'";
                $mysqli->query($updateQuery) or die($mysqli->error);
                $curUpdated = true;
            } elseif ($res["uid"] != $CURUSER["id"] && time() - $res["time"] <= $timeout) {
                // Добавление посетителя в список
                $v[$res["time"] . "_" . $res["uid"]] = ($res["uid"] != "0" ? "<a href='user/id" . $res["uid"] . "'>" . get_user_class_color($res["uclass"], $res["uname"]) . "</a> " : get_user_class_color($res["uclass"], $res["uname"]));
            } else {
                // Удаление устаревших записей
                $deleteQuery = "DELETE FROM visitor_history WHERE id = '" . $res["id"] . "'";
                $mysqli->query($deleteQuery) or die($mysqli->error);
            }
        }
    }

    // Добавление текущего пользователя, если он еще не добавлен
    if (!$curUpdated && !$notAdd) {
        $insertQuery = "INSERT INTO visitor_history (url, uid, uname, uclass, time, ip) VALUES (
            " . sqlesc($url) . ", 
            " . sqlesc(($CURUSER["id"] == "" ? "0" : $CURUSER["id"])) . ", 
            " . sqlesc(($CURUSER["username"] == "" ? "Гость" : $CURUSER["username"])) . ", 
            " . sqlesc(($CURUSER["class"] == "" ? "0" : $CURUSER["class"])) . ", 
            '" . time() . "', 
            " . sqlesc(($CURUSER["ip"] == "" ? getip() : $CURUSER["ip"])) . "
        )";
        $mysqli->query($insertQuery) or die($mysqli->error);
    }

    // Добавление текущего пользователя в список
    $v[time() . "_" . $CURUSER["id"]] = "<a href='user/id" . $CURUSER["id"] . "'>" . get_user_class_color($CURUSER["class"], $CURUSER["username"]) . "</a>";

    // Сохранение данных в глобальных переменных
    $GLOBALS["VIS_URL"] = $url;
    $GLOBALS["VIS_TIMEOUT"] = $timeout / 60;
    krsort($v);
    $GLOBALS["VISITORS"] = $v;

    return true;
}

function visitorsList($form, $visitors) {
    $visList = implode(", ", $visitors);
    $formBack = preg_replace("/\[VISITORS\]/", $visList, $form);
    return $formBack;
}


function karma($karma) {
    if ($karma == 0)
        $color = "#000000";
    elseif ($karma < 0)
        $color = "#FF0000";
    elseif ($karma > 0 && $karma < 10)
    {
        $color = "#000080";
        $karma = "+$karma";
    }
    elseif ($karma > 10)
    {
        $color = "#008000";
        $karma = "+$karma";
    }
    return "<font style=\"color:$color;vertical-align:top;font-size:13px;\"><b>$karma</b></font>";
}


function cat_name($id, $name = false)
{
    global $mcache, $pic_base_url;

    if (false === ($row = $mcache->get_value('cats_' . $id))) {
        $res = sql_query("SELECT * FROM categories WHERE id = " . (int)$id) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            // Кэшируем на 1 час (3600 секунд)
            $mcache->cache_value('cats_' . $id, $row, 3600);
        } else {
            // Если категории нет — возврат пустой строки или "Неизвестно"
            return $name ? "Неизвестно" : "";
        }
    }

    if ($name == false) {
        return "<img border=\"0\" src=\"" . htmlspecialchars($pic_base_url) . "/cats/" . htmlspecialchars($row["image"]) . "\" alt=\"" . htmlspecialchars($row["name"]) . "\" />";
    } else {
        return htmlspecialchars($row['name']);
    }
}


function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		'1' => '', '2' => '', '3' => '', '4' => '', 
		'5' => '', '6' => '', '7' => '', '8' => '', 
		'9' => '', '0' => '', 'из' => '', '-' => '', 
  
    );
    return strtr($string, $converter);
}
function friendly_title($str) {
//global $mcache;
//$url = md5($str);
//$mem_url = $mcache->get_value('url_'.$url);
//if ($mem_url === false) {
 // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~', '_', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "_");
	//$mcache->cache_value('url_'.$url, $str, rand(2800 , 6000 )); } 
	//else  $str = $mem_url;
    return $str;
}

function prof_guest($uid, $profid, $time){
    $uid = (int)$uid;
    $profid = (int)$profid;
    // Убираем эти две строки, они не нужны:
    // $uname = htmlspecialchars($uname);
    // $uclass = (int)$uclass;
    
    if($uid != $profid) {
        sql_query("INSERT INTO prof_guest (uid, profid, time) VALUES ('$uid', '$profid', '$time') ON DUPLICATE KEY UPDATE time = $time") or sqlerr(__FILE__, __LINE__);
    } else {
        return;
    }
}


function new_ann($to, $text) {
    global $mysqli; // подключение к БД через mysqli

    if (!empty($to) && !empty($text)) {
        $stmt = $mysqli->prepare("INSERT INTO cometchat_announcements (announcement, time, `to`) VALUES (?, ?, ?)");
        if ($stmt) {
            $time = time();
            $stmt->bind_param("sis", $text, $time, $to); // s = string, i = integer
            $stmt->execute();
            $stmt->close();
        } else {
            sqlerr(__FILE__, __LINE__);
        }
    }
}
