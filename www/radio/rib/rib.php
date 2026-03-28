<?php
////////////////////////////////////////////////////////////////////////////
//
// RIB - Radio Info Block
//	by sandel
//	  version 2.0 (19.11.09)
//	
//error_reporting(E_ALL);
//ini_set('display_errors','On');
//
// START SETTINGS //
$cache_lifetime = 30;
$cache_filename = "kagujcjigohmxges.tmp";
$cache_smart = 1;
//$file_whereis = dirname(__FILE__)."/".$cache_filename; // for windows
$file_whereis = "/tmp/".$cache_filename; // for *nix.. Linux, FreeBSD etc...
$users_messages = 0; // Show messages from users in block...
$anime_show_type = 2;
// END SETTINGS //
//
// 
////////////////////////////////////////////////////////////////////////////

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache");
header("Pragma: nocache");
header("Content-Type: text/xml; charset=windows-1251");


if (file_exists($file_whereis) && filemtime($file_whereis) > (time() - $cache_lifetime)) {
	
	$error = FALSE;
	$data = unserialize(implode('', file($file_whereis)));
	if (@$data['1'] != 'ready') {
			$error = TRUE;
		}

} else {
	//
	$error = FALSE;
	$open = @file('http://proxy.animeradio.su/data.esc');
	if ($open) {
		
		$data = implode('', $open);
		$data = explode("\t\t\t",$data);
		//var_dump($open);

		if (@$data['1'] != 'ready') {
			$error = TRUE;
			$data = '<div style="color: red; text-align: center; font-weight: bold;">Proxy is offline or restarting...</div>';
		} else {
			list($prot_ver,) = explode(".",$data['0']);
			if ((int)$prot_ver !== 1) {
				$error = TRUE;
				$data = '<div style="color: red; text-align: center; font-weight: bold;">Protocol is outdated... Contact with administrator!</div>';
			} else {
				// Some actions...
				$tmp_contacts = array();
				$tmp_cts = array();
				$tmp_contacts = explode(";",$data['8']);
				$data['8'] = NULL;
				foreach ($tmp_contacts as $tmp_cval) {

					$tmp_cts = explode("|", $tmp_cval);
					switch ($tmp_cts['1']) {
						case 'icq':
							$data['8'] .= "<a href='http://www.icq.com/people/about_me.php?uin={$tmp_cts['0']}' target='_blank'><img src='http://web.icq.com/whitepages/online?icq={$tmp_cts['0']}&img=5' border='0' width='18' height='18' /></a> ";
							break;
						case 'skype':
							$data['8'] .= "<a href='skype:{$tmp_cts['0']}?call'><img src='http://mystatus.skype.com/smallicon/{$tmp_cts['0']}' style='border: none;' width='16' height='16' alt='ДиДжей статус' /></a> ";
						break;
						case 'html':
							$data['8'] .= $tmp_cts['0']." ";
						default: 
							$data['8'] .= "<a href='{$tmp_cts['0']}'><img src='{$tmp_cts['1']}' style='border: none;' /></a> ";
					}

				}
				if ((int)$data['2'] == 1) $data['2'] = "<b>Запущен</b>"; else 
						$data['2'] = "<b>Отключен</b>";
				if ($users_messages && $data['11']) {
					$data['11'] = '<br />'.htmlspecialchars($data['11'], ENT_QUOTES);
				} else $data['11'] = NULL;
				$data['10'] = trim($data['10']);
				if (!$data['10']) $data['10'] = NULL; else {
					$anime_name_out = htmlspecialchars($data['10'], ENT_QUOTES);
					switch ($anime_show_type) {
					case 3:
						$data['10'] = "<br /><br /><b>Данный трек из аниме:</b><br /><a href='http://www.world-art.ru/search.php?name={$anime_name_out}&global_sector=animation' target='_blank'><img	src='./rib/Search-24x24.png' border='0' /></a>"; //img with url
					break;
					case 2:
						$data['10'] = "<br /><br /><b>Данный трек из аниме:</b><br /><a href='http://www.world-art.ru/search.php?name={$anime_name_out}&global_sector=animation' target='_blank'>{$anime_name_out}</a>";		//text with url
					break;
					case 1:
						$data['10'] = "<br /><br /><b>Данный трек из аниме:</b><br />{$anime_name_out}"; //only text
					break;
					case 0:
					default:
						$data['10'] = NULL; // OFF
					}
				}
			
				if (!$data['12']) $data['12'] = NULL; else {
					$data['12'] = '<br /><br /><B>Объявление:</B><br />'.htmlspecialchars($data['12'], ENT_QUOTES);
				}
			}
		}
			
	} else {		
		$error = TRUE;
		$data = '<div style="text-align: center; font-style: italic;">Connection failed..retrying...</div>';
	}

	$serialized = serialize($data);

	$change_smart_cache_time = 0;

	if (file_exists($file_whereis) && $cache_smart) {
		$time_here_cache = filemtime($file_whereis);
		$time_at_server = $data['7'];
		if ($time_here_cache > $time_at_server) {
			$change_smart_cache_time = $cache_lifetime/2; //was3
			//echo "<h1>Gettted!</h1>";
		}
	}

	

	// file operations..
	if (!$handle = @fopen($file_whereis, 'w')) {
		$error = TRUE;
		$data = "<font color='red'><b><u>CRITICAL ERROR</u>! Cannot open file ({$file_whereis} !!!</b></font>";
	}
	if (@fwrite($handle, $serialized) === FALSE) {
		$error = TRUE;
		$data = "<font color='red'><b><u>CRITICAL ERROR</u>! Cannot write to file ({$file_whereis}) !!!</b></font>";
	}
	@fclose($handle);
	if (!$error && $cache_smart && $change_smart_cache_time) {
		clearstatcache();
		$time_here_cache = filemtime($file_whereis) - ($cache_lifetime - $change_smart_cache_time);
		touch($file_whereis, $time_here_cache);
	}
}



if (!$error) {

	$protocol_version = $data['0'];
	$state = $data['2'];
	$online = $data['3'];
	$maxslots = $data['4'];
	$onlinepick = $data['5'];
	$unique = $data['6'];
	$contacts = $data['8'];
	$tag_artist_title = $data['9'];
	$anime_name = $data['10'];
	$users_messages = $data['11'];
	$announcement = $data['12'];
	

	
// MAIN TEMPLATE
echo <<<HTML
<!-- Hi! Prx Esc v{$protocol_version} {$data['1']} -->

Слушатели: <B>{$online}</B><!-- из {$maxslots} ({$unique} Уникальных)--><br />
Пик слушателей: {$onlinepick}<br /> 
Статус сервера: <b>{$state}</b><br />

<B>Текущий играющий трек:</B><br />
	{$tag_artist_title}
	<!-- Ниже отображаеться название аниме и другие переменные на вывод! В них уже включен тег <br />.  -->
	{$users_messages}
	{$anime_name}
	{$announcement}

HTML;

} else {
// ERROR TEMPLATE
	echo <<<HTML
	{$data}
HTML;
}



?>