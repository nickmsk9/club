<?
# IMPORTANT: Do not edit below unless you know what you are doing! уаываываы
if(!defined('IN_TRACKER') && !defined('IN_ANNOUNCE'))
  die("Hacking attempt!");

if (!function_exists("htmlspecialchars_uni")) {
	function htmlspecialchars_uni($message) {
		$message = preg_replace("#&(?!\#[0-9]+;)#si", "&amp;", $message); // Fix & but allow unicode
		$message = str_replace("<","&lt;",$message);
		$message = str_replace(">","&gt;",$message);
		$message = str_replace("\"","&quot;",$message);
		$message = str_replace("  ", "&nbsp;&nbsp;", $message);
		return $message;
	}
}

// DEFINE IMPORTANT CONSTANTS
define ('TIMENOW', time());
$url = explode('/', htmlspecialchars_uni($_SERVER['HTTP_HOST'])); 
array_pop($url);
$DEFAULTBASEURL = (($_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://").htmlspecialchars_uni($_SERVER['HTTP_HOST']).implode('/', $url);
$BASEURL = $DEFAULTBASEURL;
$announce_urls = array();
$announce_urls[] = "$DEFAULTBASEURL/announce.php";

// DEFINE TRACKER GROUPS
define ("UC_USER",0);
define ("UC_POWER_USER",10);
define ("UC_SPOWER",15);
define ("UC_VIP_P", 19);
define ("UC_VIP", 20);
define ("UC_UPLOADER",30);
define ("UC_CURATOR",35);
define ("UC_MODERATOR",50);
define ("UC_ADMINISTRATOR",60);
define ("UC_SYSOP",70);
?>