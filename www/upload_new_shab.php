<?
require_once("include/bittorrent.php");
global $lang;
$charset = isset($lang['language_charset']) ? $lang['language_charset'] : 'UTF-8';
header("Content-Type: text/html; charset=" . $charset);
dbconn(false);
loggedinorreturn();

$CURUSER = $CURUSER ?? null;
include("upload_new_func.php");
global $CURUSER, $form_add, $max_torrent_size;
$shab = '';


if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $CURUSER)
{
?>
<script type="text/javascript" src="markitup/jquery.markitup.pack.js"></script>
<script type="text/javascript" src="markitup/sets/bbcode/set.js"></script>
<link rel="stylesheet" type="text/css" href="markitup/skins/simple/style.css" />
<link rel="stylesheet" type="text/css" href="markitup/sets/bbcode/style.css" />
<script type="text/javascript">
jQuery.noConflict();
jQuery('#description').markItUp(mySettings);			

</script>
<script>
function ch_var(chk, id)
{
	var inp = document.getElementById('f' + id);
	if(!inp) return;
	
	if( chk.checked ) { // add value
		if( inp.value.indexOf(chk.value) < 0 ) {
			inp.value += ((inp.value != '') ? ', ' : '') + chk.value;
		}
	} else {
		str = inp.value;
		
		var regEx = new RegExp (chk.value, 'gi');
		str = str.replace(regEx, '')

		regEx = new RegExp (', , ', 'gi');
		str = str.replace(regEx, ', ')

		regEx = new RegExp (', $', 'gi');
		str = str.replace(regEx, '')

		regEx = new RegExp ('^, ', 'gi');
		str = str.replace(regEx, '')
		
		inp.value = str;
	}
}

</script>
<?
$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='AMV') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(1);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_amv'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_amv'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='DVD') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(2);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_dvd'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_dvd'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Games') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(3);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_games'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_games'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Hentai') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(4);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_hentai'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_hentai'];
$shab .= (isset($form_add['anidb']) ? $form_add['anidb'] : '');
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='J-Music') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(5);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_j-music'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_j-music'];
$shab .= (isset($form_add['anidb']) ? $form_add['anidb'] : '');
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}


$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='LiveAction') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(6);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_live-action'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_liveaction'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Manga') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(7);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_manga'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_manga'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Mobile') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(8);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_mobile'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_mobile'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Movie') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(9);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_movie'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_movie'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='OST') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(10);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_ost'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_ost'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='OVA') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(11);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_ova'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_ova'];
$shab .= (isset($form_add['anidb']) ? $form_add['anidb'] : '');
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Subtitles') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(12);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_subtitles'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_subtitles'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Misc') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(13);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_misc'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_misc'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='TV') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(14);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_tv'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_tv'];
$shab .= (isset($form_add['anidb']) ? $form_add['anidb'] : '');
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Images') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(15);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_images'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_images'];
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}

$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Ongoing') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(16);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_ongoing'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .= $form_add['description_ongoing'];
$shab .= (isset($form_add['anidb']) ? $form_add['anidb'] : '');
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}


$shab_tags = (isset($form_add['tags']) ? $form_add['tags'] : '');
if ($_GET['cats']=='Anthology') {
$shab .=<<<HTML
<form name="upload" enctype="multipart/form-data" action="takeupload.php" method="post" onsubmit="return CheckForm(17);">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_torrent_size}" />
<table border="1" cellspacing="0" width="100%" cellpadding="5">
<tr><td class="colhead" colspan="2">{$lang['upload_torrent']}</td></tr>
HTML;
$shab .=$form_add['announce_url'];
$shab .=$form_add['anonim'];
$shab .=$form_add['type_Anthology'];
$shab .= $shab_tags;
$shab .=$form_add['torrent_file'];
$shab .=$form_add['torrent_name_rus'];
$shab .=$form_add['torrent_name_orig'];
$shab .=$form_add['release_date'];
$shab .=$form_add['kachestvo'];
$shab .=$form_add['torrent_poster'];
$shab .=$form_add['description_Anthology'];
$shab .= isset($form_add['anidb']) ? $form_add['anidb'] : '';
$shab .=$form_add['oppinion'];
$shab .=$form_add['screenshots'];
if(get_user_class() >= UC_ADMINISTRATOR) {$shab .=$form_add['vajniy'];}
$shab .=$form_add['submit'];
$shab .=<<<HTML
<input type="hidden" name="descr">
<input type="hidden" name="name">
</table>
</form>
HTML;
echo $shab;
}
	}
	else
    die("Прямой доступ закрыт.");
?>
