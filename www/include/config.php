<?
# IMPORTANT: Do not edit below unless you know what you are doing!
if(!defined('IN_TRACKER') && !defined('IN_ANNOUNCE'))
  die('Hacking attempt!');


//$FUNDS = "$2,610.31";

$SITE_ONLINE = true;
//$SITE_ONLINE = local_user();
//$SITE_ONLINE = false;

$max_torrent_size = 1024*1024*2;
$announce_interval = 60 * 30;
$signup_timeout = 86400 * 3;
$minvotes = 1;
$max_dead_torrent_time = 400 * 3600;

// Max users on site
$maxusers = 10000000; // LoL Who we kiddin' here?

// ONLY USE ONE OF THE FOLLOWING DEPENDING ON YOUR O/S!!!
$torrent_dir = "torrents";    # FOR UNIX ONLY - must be writable for httpd user
//$torrent_dir = "C:/web/Apache2/htdocs/tbsource/torrents";    # FOR WINDOWS ONLY - must be writable for httpd user

// Email for sender/return path.
$SITEEMAIL = "donotreply@animeclub.su";

$SITENAME = "Аниме Торрент Трекер Скачать аниме торренты бесплатно Аниме форум , чат , афиша . ";
$SITENAMEL = "AnimeClub.Su - Аниме Торрент Трекер";

$autoclean_interval = 60 * 30;
$pic_base_url = "./pic/";

// [BEGIN] Custom variables from Yuna Scatari
$default_language = "russian"; // Язык трекера по умолчанию.
$avatar_max_width = 100; // Максимальная ширина аватары.
$avatar_max_height = 100; // Максимальная высота аватары.
$points_per_hour = 1; // Сколько добавлять бонусов в час, если пользователь сидирует.
$points_per_cleanup = $points_per_hour*($autoclean_interval/1800); // Don't change it!
$default_theme = "Anime"; // Тема по умолчанию для гостей.
$nc = "no"; // Не пропускать на трекер пиров с закрытыми портами.
$deny_signup = 0; // Запретить регистрацию. 1 = регистрация отключена, 0 = регистрация включена.
$use_email_act = 0; // Использовать активацию по почте, иначе - автоматическая активация при регистрации.
$use_lang = 1; // Включить языковую систему. Выключите если вы хотите перевести шаблоны и другие файлы - тогда все фразы от системы станут пустым местом.
$use_blocks = 1; // Использовать систему блоков. 1 - да, 0 - нет. Если ее отключить то админ-панель и ее блочный модуль не смогут нормально работать при работе с блоками.
$use_gzip = 1; // Использовать сжатие GZip на страницах.
$use_ipbans = 1; // Использовать функцию блокирования IP-адресов. 0 - нет, 1 - да.
$use_sessions = 0; // Использовать сессии. 0 - нет, 1 - да.
$smtptype = "advanced";
$ctracker = "yes";
// [END] Custom variables from Yuna Scatari
$add_tag = true; // true - пользователи могут добавлять собственные тэги при загрузке/редактировании. false - тэги добавляются только с панели управления, а пользователи могут только выбрать.
// Site closed for coding/fixing (0 or false = no; 1 or true = yes)
$SYSOP_TESTING = 1;
$showforumstats = "yes"; 
$use_wait = 1;
$allow_block_hide = true; // Разрешить сворачивание блоков

$ano_site_address = $_SERVER['HTTP_HOST'];
$ano_site_host = $_SERVER['HTTP_HOST'];
$ano_ignore_proto = 'ftp';
$ano_white_list = '';
$ano_set_nofollow = 0;
$ano_set_target = 1;
$sql_error_log = ROOT_PATH.'/logs/sql_err_'.date("d_m_Y").'.log';
$path = "uploads/";
$perpage=10; // Updates perpage
$base_url= $DEFAULTBASEURL;
$gravatar=0; // 0 false 1 true gravatar image
$uid = isset($CURUSER['id']) ? $CURUSER['id'] : 0;

// Сколько можно секономить запросов отключая что-то:
/*

Капча - 1 запрос при регистрации на форме и 1 запрос на проверке регистрации. (мин. 2 запроса)
Блоки - 1 запрос на вызов блоков + все запросы из всех блоков что активны. (мин. 1 запрос. В комплектации по умолчанию сборки - 2 запроса на блоке пользователи, 1 запрос на блоке форум, 2 запроса на блоке релизы)
Сессии - 1 запрос на постоянного юзера и 2 запроса на нового (первый раз на сайте). (мин. 1 запрос)
IP-баны - 1 запрос на любой странице.

Авторизация - пока не готово. Будет возмоность отключать авторизацию т.е. вход пользователей что даст возможность в минимальном режиме работать ВООБЩЕ без запросов на чистой странице.

*/

### security protection by n-sw-bit ::: config ###
$hacker_ban_time = 1;//minutes


/*

test for mac mini m4
*/

?>