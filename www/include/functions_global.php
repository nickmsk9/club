<?php
# IMPORTANT: Do not edit below unless you know what you are doing!
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');


 
function checklocals($localurls, $parsie, &$links, $key) {
	foreach ($localurls AS $localurl) {
		if (($localurl[0] == '.' && preg_match('/' . $localurl . '$/i', $parsie['host'])) || $localurl == $parsie['host']) {
			unset($links[$key]);
		}
	}
}

function checkwhite($whiteurls, $parsie, &$links, $key) {
	foreach ($whiteurls AS $whiteurl) {
		if (($whiteurl[0] == '.' && preg_match('/' . $whiteurl . '$/i', $parsie['host'])) || $whiteurl == $parsie['host']) {
			unset($links[$key]);
		}
	}
}


function anonymize($text = '') {
	global $BASEURL;
	global $ano_site_address, $ano_site_host, $ano_ignore_proto, $ano_white_list, $ano_set_nofollow, $ano_set_target;

	// Если текст пустой — возвращаем его как есть
	if (!$text) {
		return $text;
	}

	$search = $replace = $whitelist = array();

	// Экранируем <a href=" для регулярного выражения
	$anchor = preg_quote('<a href="');

	// Ищем все ссылки в тексте
	if (preg_match_all('/' . $anchor . '([^"]+)/i', $text, $links)) {
		if (!$links) {
			return $text;
		}

		// Удаляем дубликаты ссылок
		$links = array_unique($links[1]);

		// Получаем локальные адреса
		$localurls = $_SERVER['SERVER_NAME'];
		if ($ano_site_address) {
			$localurls = explode(' ', $ano_site_address);
		} elseif ($ano_site_host) {
			$localurls = $ano_site_host;
		}
		if (!is_array($localurls)) {
			$localurls = array($localurls);
		}

		// Разбиваем белый список
		if ($ano_white_list)
			$whitelist = explode(' ', $ano_white_list);

		$localurls = array_map('strtolower', $localurls);
		$parsed = array_map('parse_url', array_map('strtolower', $links));
		$ignores = explode(',', $ano_ignore_proto);

		// Проверяем ссылки
		foreach ($parsed as $key => $parsie) {
		    // Проверяем наличие ключей 'scheme' и 'host'
		    $scheme = $parsie['scheme'] ?? '';
		    $host = $parsie['host'] ?? '';

		    // Удаляем ссылку, если она без схемы или хоста или входит в список игнорируемых протоколов
		    if (in_array($scheme, $ignores) || empty($scheme) || empty($host)) {
		        unset($links[$key]);
		        continue;
		    }

		    // Проверка на локальные адреса
		    checklocals($localurls, $parsie, $links, $key);
		    // Проверка на белый список
		    checkwhite($whitelist, $parsie, $links, $key);
		}

		if (!$links) {
			return $text;
		}

		// Путь до редиректора
		$scriptpath = $BASEURL . '/redirector.php?url=';

		// Инициализируем переменные nofollow и target
		$nofollow = ''; // по умолчанию пустая строка
		$target = '';   // по умолчанию пустая строка

		// Если включён параметр rel="nofollow"
		if ($ano_set_nofollow != 0) {
			$nofollow = '" rel="nofollow';
		}

		// Если включён параметр target="_blank"
		if ($ano_set_target != 0) {
			$target = '" target="_blank';
		}

		// Обрабатываем ссылки
		foreach ($links as $url) {
			$oldurl = str_replace('&amp;', '&', $url);
			$search[] = '<a href="' . $url;
			$replace[] = '<a href="' . $scriptpath . rawurlencode($oldurl) . $target . $nofollow;
		}
	}

	// Заменяем найденные ссылки
	if ($search) {
		$text = str_replace($search, $replace, $text);
	}

	return $text;
}
  
function get_user_class_color($class, $username)
{
  global $lang;

  switch ($username)
{
case 'mahara':
return "<span style=\"color:#fc55a8\" title=\"".$lang['class_moderator']."\">mahara</span>&nbsp;<img src='/pic/star.gif' border='0px' title='Звезда Олимпа'/>";
break;
case 'webnet':
return "<span style=\"color:#1E90FF\" title=\"Волшебный Кролик\">webnet</span>&nbsp;<img src='/pic/star.gif' border='0px' title='Звезда Олимпа'/>";
break;
case 'L_unconnue':
return "<span style=\"color:#389b85\" title=\"Эклерчик\">L_unconnue</span>";
break;
case 'dR1mEr':
return "<span style=\"color:#0F6CEE\" title=\"Солнечный Зайчег\">dR1mEr</span>&nbsp;<img src='/pic/star.gif' border='0px' title='Звезда Олимпа'/>";
break;

}
  
  switch ($class)
  {
    case UC_SYSOP:
      return "<span style=\"color:#0F6CEE\" title=\"".$lang['class_sysop']."\">" . $username . "</span>";
      break;
    
	case UC_ADMINISTRATOR:
      return "<span style=\"color:green\" title=\"".$lang['class_administrator']."\">" . $username . "</span>";
      break;
    
	case UC_MODERATOR:
      return "<span style=\"color:red\" title=\"".$lang['class_moderator']."\">" . $username . "</span>";
      break;
	 case UC_UPLOADER:
      return "<span style=\"color:#fb780f\" title=\"".$lang['class_uploader']."\">" . $username . "</span>";
      break;
	  
	  case UC_CURATOR:
      return "<span style=\"color:#389b85\" title=\"".$lang['class_curator']."\">" . $username . "</span>";
      break;
	  
	  case UC_VIP_P:
      return "<span style=\"color:#100596\" title=\"".$lang['class_vip_p']."\">" . $username . "</span>";
      break;
	  	 
      case UC_VIP:
      return "<span style=\"color:#7503C9\" title=\"".$lang['class_vip']."\">" . $username . "</span>";
      break;      

	  case UC_SPOWER:
      return "<span style=\"color:#567B9A\" title=\"".$lang['class_spower']."\">" . $username . "</span>";
      break;
	  
     case UC_POWER_USER:
      return "<span style=\"color:#D21E36\" title=\"".$lang['class_power_user']."\">" . $username . "</span>";
      break;
     case UC_USER:
      return "<span title=\"".$lang['class_user']."\">" . $username . "</span>";
      break;
	  }

  return "$username";
}




function display_date_time($time) {
  global $CURUSER;

  $userTimezone = (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['timezone'])) ? (int)$CURUSER['timezone'] : 0;
  $userDst = (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['dst'])) ? (int)$CURUSER['dst'] : 0;
  $timestamp = strtotime($time);

  if ($timestamp === false) {
    return '';
  }

  return gmdate("Y-m-d H:i:s", $timestamp + (($userTimezone + $userDst) * 60));
}
// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0) {
	if ($timestamp)
		return date("Y-m-d H:i:s", $timestamp);
	else
		return date("Y-m-d H:i:s");
}

function cut_text($txt, $car) {
	if (strlen($txt) > $car) {
		return substr($txt, 0, $car) . "...";
	}
	return $txt;
}

function get_row_count($table, $suffix = "")
{
  global $mysqli;
  if ($suffix)
    $suffix = " $suffix";
  $r = mysqli_query($mysqli, "SELECT COUNT(*) FROM $table$suffix") or die(mysqli_error($mysqli));
  $a = mysqli_fetch_row($r) or die(mysqli_error($mysqli));
  return $a[0];
}

/*function stdmsg($heading = '', $text = '') {
	print("<table class=\"main\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"embedded\">\n");
	if ($heading)
		print("<h2>$heading</h2>\n");
	print("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td class=\"text\">\n");
	print($text . "</td></tr></table></td></tr></table>\n");
}*/

function stdmsg($heading = '', $text = '', $div = 'success', $htmlstrip = false) {
    if ($htmlstrip) {
        $heading = htmlspecialchars_uni(trim($heading));
        $text = htmlspecialchars_uni(trim($text));
    }
    print("<table class=\"main\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"embedded\">\n");
    print("<div class=\"$div\">".($heading ? "<b>$heading</b><br />" : "")."$text</div></td></tr></table>\n");
}

function stderr($heading = '', $text = '') {
	stdhead($heading);
	begin_main_frame();
	stdmsg($heading, $text, 'error');
	end_main_frame();
	stdfoot();
	die;
}

function stdsucc($heading = '', $text = '') {
	stdhead($heading);
	begin_main_frame();
	stdmsg($heading, $text, 'success');
	end_main_frame();
	stdfoot();
	die;
}

function newerr($heading = '', $text = '', $head = true, $foot = true, $die = true, $div = 'error', $htmlstrip = true) {
	if ($head)
		stdhead($heading);

	newmsg($heading, $text, $div, $htmlstrip);

	if ($foot)
		stdfoot();

	if ($die)
		die;
}

function sqlerr2($file = '', $line = '') {
	global $queries;
	print("<table border=\"0\" bgcolor=\"blue\" align=\"left\" cellspacing=\"0\" cellpadding=\"10\" style=\"background: blue\">" .
	"<tr><td class=\"embedded\"><font color=\"white\"><h1>Ошибка в SQL</h1>\n" .
	"<b>Ответ от сервера MySQL: " . htmlspecialchars_uni(mysql_error()) . ($file != '' && $line != '' ? "<p>в $file, линия $line</p>" : "") . "<p>Запрос номер $queries.</p></b></font></td></tr></table>");
	die;
}
function htmlsafechars($txt='') {

  $txt = preg_replace("/&(?!#[0-9]+;)(?:amp;)?/s", '&amp;', $txt );
  $txt = str_replace( array("<",">",'"',"'"), array("&lt;", "&gt;", "&quot;", '&#039;'), $txt );

  return $txt;
}

function myErrorHandler($errno, $msg, $file, $line) {
    global $CURUSER, $lang;

    // Пропускаем подавленные ошибки
    if (error_reporting() === 0) return true;

    // Инициализируем переменную ошибки
    $_error_string = "";

    // Проверка: можно ли ещё послать заголовки
    if (!headers_sent()) {
        header("Content-Type: text/html; charset=UTF-8");
    }

    // Составляем подробную информацию об ошибке
    $_error_string .= "\n Дата: " . date('r');
    $_error_string .= "\n Номер ошибки: " . $errno;
    $_error_string .= "\n Ошибка: " . $msg;
    $_error_string .= "\n IP Адресс: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестно');
    $_error_string .= "\n в файле " . $file . " линия " . $line;
    $_error_string .= "\n URL: " . ($_SERVER['REQUEST_URI'] ?? 'неизвестно');

    if (!empty($CURUSER['username']) && !empty($CURUSER['id'])) {
        $_error_string .= "\n Имя пользователя: {$CURUSER['username']}[{$CURUSER['id']}]";
    } else {
        $_error_string .= "\n Имя пользователя: [не авторизован]";
    }

    // HTML-вывод ошибки
    $out = "<html>\n<head>\n<title>PHP Error</title>\n
           <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
           <blockquote>\n<h1>PHP Error</h1><b>Случилась ошибка при выполнении PHP скрипта.</b><br />
           Попробуйте перезагрузить страницу <a href=\"javascript:window.location=window.location;\">нажав здесь</a>.
           <br /><br /><b>Обработка ошибки</b><br />
           <form name='php'><textarea rows=\"15\" cols=\"60\">" . htmlsafechars($_error_string) . "</textarea></form>
           <br>Извиняемся за причинённые неудобства</blockquote></body></html>";

    echo $out;
    exit;
}

// Устанавливаем обработчик ошибок
set_error_handler("myErrorHandler", E_ALL ^ E_NOTICE);


function sqlerr(string $file = '', string $line = ''): void {
    global $sql_error_log, $CURUSER, $mysqli;

    header("Content-Type: text/html; charset=UTF-8");

    $the_error = mysqli_error($mysqli);
    $the_error_no = mysqli_errno($mysqli);

    $_error_string  = "\n===================================================";
    $_error_string .= "\n Дата: " . date('r');
    $_error_string .= "\n Код ошибки: " . $the_error_no;
    $_error_string .= "\n Ошибка: " . $the_error;
    $_error_string .= "\n IP адрес: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестен');
    $_error_string .= "\n Файл: " . $file . " на строке " . $line;
    $_error_string .= "\n URL: " . ($_SERVER['REQUEST_URI'] ?? 'неизвестен');
    $_error_string .= "\n Пользователь: " . ($CURUSER['username'] ?? 'Гость') . "[" . ($CURUSER['id'] ?? '0') . "]";

    if ($FH = @fopen($sql_error_log, 'a')) {
        @fwrite($FH, $_error_string);
        @fclose($FH);
    }

    $error_display  = "\nОшибка SQL: " . $the_error . "\n";
    $error_display .= "Код ошибки: " . $the_error_no . "\n";
    $error_display .= "Дата: " . date("d.m.Y H:i:s") . "\n";

    $out = "<html>
<head>
    <title>Ошибка базы данных</title>
    <style>body, p { font-family: Arial, sans-serif; font-size: 13px; color: #333; }</style>
</head>
<body>
    <blockquote>
        <h1>Ошибка базы данных</h1>
        <b>Произошла ошибка при обращении к базе данных.</b><br />
        Вы можете попробовать <a href=\"javascript:window.location=window.location;\">обновить страницу</a>.<br /><br />
        <b>Техническая информация</b><br />
        <form name='mysql'><textarea rows=\"15\" cols=\"70\" readonly>" . htmlsafechars($error_display) . "</textarea></form>
        <br />Приносим извинения за неудобства.
    </blockquote>
</body>
</html>";

    echo $out;
    exit();
}


function encodehtml($s, $linebreaks = true) {
	$s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
	if ($linebreaks)
		$s = nl2br($s);
	return $s;
}

function get_dt_num() {
	return date("YmdHis");
}


function format_urls($s)
{
	return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i","\\1<a href=\"\\2\">\\2</a>", $s);
}

/*

// Removed this fn, I've decided we should drop the redir script...
// it's pretty useless since ppl can still link to pics...
// -Rb

function format_local_urls($s)
{
	return preg_replace(
    "/(<a href=redir\.php\?url=)((http|ftp|https|ftps|irc):\/\/(www\.)?torrentbits\.(net|org|com)(:8[0-3])?([^<>\s]*))>([^<]+)<\/a>/i",
    "<a href=\\2>\\8</a>", $s);
}
*/

//Finds last occurrence of needle in haystack
//in PHP5 use strripos() instead of this
function _strlastpos ($haystack, $needle, $offset = 0)
{
	$addLen = strlen ($needle);
	$endPos = $offset - $addLen;
	while (true)
	{
		if (($newPos = strpos ($haystack, $needle, $endPos + $addLen)) === false) break;
		$endPos = $newPos;
	}
	return ($endPos >= 0) ? $endPos : false;
}






// Format quote
function encode_quote($text) {
	$start_html = "<div align=\"left\"><div style=\"padding: 7px 5px 5px 7px; overflow: auto; \">"
	."<table width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\" align=\"center\" class=\"bgcolor4\">"
	."<tr bgcolor=\"#FFE5E0\"><td><font class=\"block-title\">Цитата</font></td></tr><tr class=\"bgcolor1\"><td style=\"background-color:#fff;\">";
	$end_html = "</td></tr></table></div></div>";
	$text = preg_replace("#\[quote\](.*?)\[/quote\]#si", "".$start_html."\\1".$end_html."", $text);
	return $text;
}

// Format quote from
function encode_quote_from($text) {
	$start_html = "<div align=\"left\"><div style=\"padding: 7px 5px 5px 7px; overflow: auto; \">"
	."<table width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\" align=\"center\" class=\"bgcolor4\">"
	."<tr bgcolor=\"#FFE5E0\"><td><font class=\"block-title\">Сообщение от  \\1</font></td></tr><tr class=\"bgcolor1\"><td style=\"background-color:#fff;\">";
	$end_html = "</td></tr></table></div></div>";
	$text = preg_replace("#\[quote=(.+?)\](.*?)\[/quote\]#si", "".$start_html."\\2".$end_html."", $text);
	return $text;
}





function format_comment($text, $strip_html = true) {
	global $smilies, $thq, $privatesmilies, $privatesmilies2, $privatesmilies3 ,$format_urls , $DEFAULTBASEURL;
	$smiliese = $smilies;
	$s = $text;

  // This fixes the extraneous ;) smilies problem. When there was an html escaped
  // char before a closing bracket - like >), "), ... - this would be encoded
  // to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
  // the ;) one, and replace all genuine ;) by :wink: before escaping the body.
  // (What took us so long? :blush:)- wyz

	$s = str_replace(";)", ":wink:", $s);

### security protection by n-sw-bit ::: XSS ###
/*if(strpos($s,'.cookie')!==false){
	hacker("BBCode XSS [.cookie] {".$s."}");
}

if(strpos($s,'<script')!==false){
	hacker("BBCode XSS [<script] {".$s."}");
}

if(strpos($s,'document.')!==false){
	hacker("BBCode XSS [document.] {".$s."}");
}
$b = array('<script','alert','document.');
$s=str_ireplace($b,"",$s);
*/

	if ($strip_html)
		$s = htmlspecialchars($s);

  $counter=0;
    $match_count = preg_match_all("#\[code\](.*?)\[/code\]#si", $s, $matches);

    if ($match_count)
    {
         for ($mout = 0; $mout < $match_count; ++$mout)
         {
         $start_html = "<div align=\"left\"><div style=\"padding: 7px 5px 5px 7px; overflow: auto; \">"
	."<table width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\" align=\"center\" class=\"bgcolor4\">"
	."<tr bgcolor=\"#FFE5E0\"><td><font class=\"block-title\">BB Code</font></td></tr><tr class=\"bgcolor1\"><td style=\"background-color:#fff;\">";
          $end_html = "</td></tr></table></div></div>";
                         $temp = str_replace("\n", "<br />", $matches[1][$mout]);
         $add_text[]= $start_html. $temp. $end_html;
         $counter++;

         }

    }
	$bb[] = "#\[url\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
	$html[] = "<a href=\"http://\\1\"  title=\"\\1\">\\1</a>";
	$bb[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$html[] = "<a href=\"\\1\" title=\"\\1\">\\2</a>";
	$bb[] = "#\[url=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$html[] = "<a href=\"http://\\1\" title=\"\\1\">\\3</a>";
	$bb[] = "/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i";
	$html[] = "<a href=\"\\1\">\\2</a>";
	$bb[] = "/\[url\]([^()<>\s]+?)\[\/url\]/i";
	$html[] = "<a href=\"\\1\">\\1</a>";

	$bb[] = "#\[img\]([^?](?:[^\[]+|\[(?!url)).(gif|jpg|jpeg|png))\[/img\]#i";
	$html[] = "<img class=\"linked-image\" src=\"\\1\" border=\"0\" alt=\"\\1\" title=\"\\1\"/>";
	$bb[] = "#\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*?)\[/color\]#si";
	$html[] = "<span style=\"color: \\1\">\\2</span>";
	$bb[] = "#\[(font|family)=([A-Za-z ]+)\](.*?)\[/\\1\]#si";
	$html[] = "<span style=\"font-family: \\2\">\\3</span>";
	$bb[] = "#\[size=([0-9]+)\](.*?)\[/size\]#si";
	$html[] = "<span style=\"font-size: \\1\">\\2</span>";
	$bb[] = "#\[(left|right|center|justify)\](.*?)\[/\\1\]#is";
	$html[] = "<div align=\"\\1\">\\2</div>";
	$bb[] = "#\[b\](.*?)\[/b\]#si";
	$html[] = "<strong>\\1</strong>";
	$bb[] = "#\[i\](.*?)\[/i\]#si";
	$html[] = "<i>\\1</i>";
	$bb[] = "#\[u\](.*?)\[/u\]#si";
	$html[] = "<u>\\1</u>";
	$bb[] = "#\[s\](.*?)\[/s\]#si";
	$html[] = "<s>\\1</s>";
	$bb[] = "#\[li\]#si";
	$html[] = "<li>";
	$bb[] = "#\[hr\]#si";
	$html[] = "<hr>";

	$s = preg_replace($bb, $html, $s);


	// Linebreaks
	$s = nl2br($s);

    $s = preg_replace(
       "/\[flash=([0-9]+),([0-9]+)\]([^()<>\s]+?)\[\/flash\]/i",
       "<object width=\"\\1\" height=\"\\2\"><param name=\"movie\" type='application/x-shockwave-flash' value=\"\\3\"><embed width=\"\\1\" height=\"\\2\" src=\\3></embed></object>", $s);

	
  //[spoiler]Text[/spoiler]
$s = str_replace("[spoiler]","<div class=\"news-wrap\"><div class=\"news-head folded clickable\"><i>Скрытый текст</i></div><div class=\"news-body\">", $s);
// continue below //

    //[spoiler=name]Text[/spoiler]
$s = preg_replace("#\[spoiler=\s*((\s|.)+?)\s*\]#si",
"<div style=\"position: static;\" class=\"news-wrap\"><div class=\"news-head folded clickable\"><i>\\1</i></div><div class=\"news-body\">", $s);

$s = str_replace("[/spoiler]","</div></div>",$s);

/////////////////////////////////Tag [youtube][/youtube] 
while (preg_match("/\[youtube\]((\s|.)+?)\[\/youtube\]/i", $s)) {
$s = str_replace("watch?v=","v/", $s);
$s = preg_replace ("/\[youtube\]((\s|.)+?)\[\/youtube\]/i", "<object width='640' height='505'><param name=movie value='\\1&hl=ru&fs=1&'></param><param name='allowFullScreen' value='true'></param><param name='allowscriptaccess' value='always'></param><embed src='\\1&hl=ru&fs=1&' type='application/x-shockwave-flash' allowscriptaccess='always' allowfullscreen='true' width='640' height='505'></embed></object>", $s);
}
///////////////////////////////////end tag youtube  

/////////////////////////////////Tag [rutube][/rutube] 
while (preg_match("/\[rutube\]((\s|.)+?)\[\/rutube\]/i", $s)) {
$s = preg_replace("/http:\/\/rutube.ru\/tracks\/([0-9]+)\.html\?v\=/","http://video.rutube.ru/", $s);
$s = preg_replace ("/\[rutube\]((\s|.)+?)\[\/rutube\]/i", "<object width='640' height='505'><param name=movie value='\\1'></param><param name='allowFullScreen' value='true'></param><param name='allowscriptaccess' value='always'></param><embed src='\\1' type='application/x-shockwave-flash' allowscriptaccess='always' allowfullscreen='true' width='640' height='505'></embed></object>", $s);
}
///////////////////////////////////end tag rutube  


$enter = 0;
    while ((preg_match("#\[quote\](.*?)\[/quote\]#si", $s))&&( $enter < 99))
    {
        $s = encode_quote($s);
        $enter++;
    }
    $enter = 0;
    while ((preg_match("#\[quote=(.+?)\](.*?)\[/quote\]#si", $s))&&( $enter < 99))
    {
        $s = encode_quote_from($s);
        $enter++;
    }

	$s = format_urls($s);
	$s = anonymize($s);
	
 //   $s = str_replace("  ", " ", $s);

	
	
	foreach ($smiliese as $code => $url)
		$s = str_replace($code, "<img border=\"0px\" src=\"".$DEFAULTBASEURL."/pic/smilies/$url\" alt=\"" . htmlspecialchars_uni($code) . "\" />", $s);

	foreach ($privatesmilies as $code => $url)
		$s = str_replace($code, "<img border=\"0px\" src=\"".$DEFAULTBASEURL."/pic/smilies/$url\" />", $s);

	foreach ($privatesmilies2 as $code => $url)
		$s = str_replace($code, "<img border=\"0px\" src=\"".$DEFAULTBASEURL."/pic/smilies/$url\" />", $s);

	foreach ($thq as $code => $url)
		$s = str_replace($code, "<img border=\"0px\" src=\"".$DEFAULTBASEURL."/pic/thq/$url\" />", $s);

if     ($counter>0)
{
 $is=0;
   while($is<$counter)
 {

    $s = preg_replace("#\[code\](.*?)\[/code\]#si", $add_text[$is], $s,1);
    $is=$is+1;
 }
}  

	return $s;
}

function get_user_class() {
    global $CURUSER;
    // Проверка: если $CURUSER не массив или не содержит ключа 'class', возвращаем 0 (гость)
    return (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['class'])) ? $CURUSER['class'] : 0;
}

function get_user_icons($arr) {
    global $DEFAULTBASEURL;

    $pics = (isset($arr["donor"]) && $arr["donor"] == "yes") ? "<img src=\"".$DEFAULTBASEURL."/pic/coin.gif\" alt='Особый' border=\"0\" style=\"margin-left: 1pt\">" : "";
    if (isset($arr["enabled"]) && $arr["enabled"] == "yes")
        $pics .= (isset($arr["warned"]) && $arr["warned"] == "yes") ? "<img src=\"".$DEFAULTBASEURL."/pic/warned.gif\" alt=\"Предупрежден\" border=0 style=\"margin-left: 1pt\">" : "";
    else
        $pics .= "<img src=\"".$DEFAULTBASEURL."/pic/disabled.gif\" alt=\"Отключен\" border=\"0\" style=\"margin-left: 2pt\">\n";
    $pics .= (isset($arr["parked"]) && $arr["parked"] == "yes") ? "<img src=\"".$DEFAULTBASEURL."/pic/parked.gif\" alt=\"Припаркован\" border=\"0\" style=\"margin-left: 1pt\">" : "";
    $pics .= (isset($arr["gender"]) && $arr["gender"] == "1") ? "<img src=\"".$DEFAULTBASEURL."/pic/ico_m.gif\" alt=\"Парень\" border=\"0\" style=\"margin-left: 1pt\">" : "";
    $pics .= (isset($arr["gender"]) && $arr["gender"] == "2") ? "<img src=\"".$DEFAULTBASEURL."/pic/ico_f.gif\" alt=\"Девушка\" border=\"0\" style=\"margin-left: 1pt\">" : "";
    return $pics;
}

function get_user_icons_chat($arr) {
global $DEFAULTBASEURL;
		$pics = $arr["donor"] == "yes" ? "<img src=\"".$DEFAULTBASEURL."/pic/coin.gif\" alt='Особый'  border=\"0\" style=\"margin-left: 1pt\">" : "";
		$pics .= $arr["warned"] == "yes" ? "<img src=\"".$DEFAULTBASEURL."/pic/warned.gif\" alt=\"Предупрежден\" border=0 style=\"margin-left: 1pt\">" : "";
		$pics .= $arr["parked"] == "yes" ? "<img src=\"".$DEFAULTBASEURL."/pic/parked.gif\" alt=\"Припаркован\" border=\"0\" style=\"margin-left: 1pt\">" : "";

		return $pics;
}

function get_user_class_name($class) {
  global $lang;
  switch ($class) {
    case UC_USER: return $lang['class_user'];

    case UC_POWER_USER: return $lang['class_power_user'];
	
	case UC_SPOWER: return $lang['class_spower'];

	case UC_VIP_P: return $lang['class_vip_p'];

    case UC_VIP: return $lang['class_vip'];
    
    case UC_CURATOR: return $lang['class_curator'];

    case UC_UPLOADER: return $lang['class_uploader'];
		
    case UC_MODERATOR: return $lang['class_moderator'];

    case UC_ADMINISTRATOR: return $lang['class_administrator'];

    case UC_SYSOP: return $lang['class_sysop'];
  }
  return "";
}

function is_valid_user_class($class) {
  return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_SYSOP;
}

//----------------------------------
//---- Security function v0.1 by xam
//----------------------------------
function int_check($value,$stdhead = false, $stdfood = true, $die = true, $log = true) {
	global $CURUSER;
	$msg = "Invalid ID Attempt: Username: ".$CURUSER["username"]." - UserID: ".$CURUSER["id"]." - UserIP : ".getip();
	if ( is_array($value) ) {
        foreach ($value as $val) int_check ($val);
    } else {
	    if (!is_valid_id($value)) {
		    if ($stdhead) {
			    if ($log)
		    		write_log($msg);
		    	stderr("ERROR","Invalid ID! For security reason, we have been logged this action.");
	    }else {
			    Print ("<h2>Error</h2><table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>");
				Print ("Invalid ID! For security reason, we have been logged this action.</td></tr></table>");
				if ($log)
					write_log($msg);
	    }
			
		    if ($stdfood)
		    	stdfoot();
		    if ($die)
		    	die;
	    }
	    else
	    	return true;
    }
}
//----------------------------------
//---- Security function v0.1 by xam
//----------------------------------

function is_valid_id($id) {
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function sql_timestamp_to_unix_timestamp($s) {
  return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

  function get_ratio_color($ratio) {
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    return "#000000";
  }

  function get_slr_color($ratio) {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    return "#000000";
  }

function write_log($text, $color = "transparent", $type = "tracker") {
  $type = sqlesc($type);
  $color = sqlesc($color);
  $text = sqlesc($text);
  $added = sqlesc(get_date_time());
  sql_query("INSERT INTO sitelog (added, color, txt, type) VALUES($added, $color, $text, $type)");
}

function get_elapsed_time($ts) {
  $mins = floor((time() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
  $t = "";
  if ($weeks > 0)
    return "$weeks недел" . ($weeks > 1 ? "и" : "я");
  if ($days > 0)
    return "$days д" . ($days > 1 ? "ней" : "ень");
  if ($hours > 0)
    return "$hours час" . ($hours > 1 ? "ов" : "");
  if ($mins > 0)
    return "$mins минут" . ($mins > 1 ? "" : "а");
  return "< 1 минуты";
}

### security protection by n-sw-bit ::: logging ###
### Для отправки на емейл сообщений об атаках раскомментируйте строку mail
function hacker($event="")
{
	global $hacker_ban_time , $memcache_obj,$sqlesc;
	$event = serialize($_GET)."||".serialize($_POST)."||".$event."||".$_SERVER['HTTP_REFERER']."||".$_SERVER['REQUEST_URI'];
	//sql_query("INSERT INTO hackers (ip,system,event) VALUES ('".getip()."','".$_SERVER['HTTP_USER_AGENT']."',".sqlesc($event).")") or sqlerr(__FILE__, __LINE__);
	$first = ip2long(getip());
	//sql_query("INSERT INTO bans (added, addedby, first, last, comment,until) VALUES(NOW(), 0, $first, $first, 'Temporal hacker ban',DATE_ADD(NOW(), INTERVAL ".$hacker_ban_time." MINUTE))") or sqlerr(__FILE__, __LINE__);
	define('BOTUIN', 581750729); // номер ICQ для скрипта
	define('BOTPASSWORD', 'qwqwqwqw'); // пароль для этого номера
	define('ADMINUIN', 6164090); // номер аськи админа
	include("icq.php");
	$icq = new WebIcqLite();
	if(!$icq->connect(BOTUIN, BOTPASSWORD))
	{
		exit();
	}
	$msg = "Зафиксирована попытка взлома!\n\nIP: ".getip()."\n==================\n\nСобытие:\n\n".$event."\n==================\n\nСистема: ".$_SERVER['HTTP_USER_AGENT']."\n\n==================\n\nURL: ".$_SERVER['REQUEST_URI'];
	$icq->send_message(ADMINUIN, iconv("UTF-8","CP1251",$msg));
	//$memcache_obj->delete('bans',1);

	//sent_mail('webnetbt@gmail.com',$SITENAME,$SITEEMAIL,$SITENAME.' - under attack','serialized event: '.$event,false);
	
}
    function fileExists($filename) {
       return (file_exists($filename) && is_file($filename)); 
    }

    function loadCSS($dir, $filename, $media = 'all', $query_string = '') {
        global $DEFAULTBASEURL;

        // Prepare query string
        if ($query_string !== '') $qs = '?'.$query_string; else $qs = '';

        if(fileExists($dir.$filename)) {
            if(!fileExists($dir.'minify.'.$filename) or filemtime($dir.$filename) > filemtime($dir.'minify.'.$filename)) {
                
                // Get css file
                $buffer = file_get_contents($dir.$filename);

                // And compress it!
                $buffer = compressCSS($buffer);

                // Save compressed css file
                file_put_contents($dir.'minify.'.$filename, $buffer);
                
                // GZip styles 
                if(TEMPLATE_CMS_GZIP_STYLES) file_put_contents("compress.zlib://".$dir."minify.".$filename.".gz",$buffer);
            }
            echo '<link rel="stylesheet" type="text/css" href="'.$DEFAULTBASEURL.'/'.$dir.'minify.'.$filename.$qs.'" media="'.$media.'" />';
        }
    }


    function loadJS($dir, $filename) {
        global $DEFAULTBASEURL;

        // Check if $filename is array of js files then go through this array and load them
        if(is_array($filename)) {
            foreach($filename as $file) {
                echo '<script type="text/javascript" src="'.$DEFAULTBASEURL.'/'.$dir.$file.'"></script>'."\n";
            }
        } else {
            echo '<script type="text/javascript" src="'.$DEFAULTBASEURL.'/'.$dir.$filename.'"></script>';
        }
    }
if (!function_exists('compressCSS')) {
    function compressCSS($buffer) {        
        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

        // Remove tabs, spaces, newlines, etc.
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        // Preserve empty comment after '>' http://www.webdevout.net/css-hacks#in_css-selectors
        $buffer = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $buffer);

        // Preserve empty comment between property and value
        // http://css-discuss.incutio.com/?page=BoxModelHack
        $buffer = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $buffer);
        $buffer = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $buffer);
        
        // Remove ws around { } and last semicolon in declaration block
        $buffer = preg_replace('/\\s*{\\s*/', '{', $buffer);
        $buffer = preg_replace('/;?\\s*}\\s*/', '}', $buffer);

        // Remove ws surrounding semicolons
        $buffer = preg_replace('/\\s*;\\s*/', ';', $buffer);

        // Remove ws around urls
        $buffer = preg_replace('/url\\(\\s*([^\\)]+?)\\s*\\)/x', 'url($1)', $buffer);

        // Remove ws between rules and colons
        $buffer = preg_replace('/\\s*([{;])\\s*([\\*_]?[\\w\\-]+)\\s*:\\s*(\\b|[#\'"])/x', '$1$2:$3', $buffer);

        // Minimize hex colors
        $buffer = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i', '$1#$2$3$4$5', $buffer);

        // Replace any ws involving newlines with a single newline
        $buffer = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $buffer);

        return $buffer;
    }
}


?>