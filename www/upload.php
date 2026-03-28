<?
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();
parked();
global $mysqli, $memcache_obj;
if (!isset($memcache_obj)) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer('localhost', 11211);
}

stdhead($lang['upload_torrent']);
?>
<style>
.cats{cursor:pointer;}
.icon-tag img {
  display: inline-block;
  margin: 4px;
}
</style>
<?
include("upload_new_func.php");
	begin_main_frame();
    begin_frame("Загрузка нового торрента <span id=\"loading\"></span>", true);
if (get_user_class() < UC_USER)
{
  stdmsg($lang['error'], $lang['access_denied']);
  stdfoot();
  exit;
}


if (strlen($CURUSER['passkey']) != 32) {
$CURUSER['passkey'] = md5($CURUSER['username'].get_date_time().$CURUSER['passhash']);
sql_query("UPDATE users SET passkey='$CURUSER[passkey]' WHERE id=$CURUSER[id]");
}
?>
<noscript>
<?
stdmsg("Внимание! В Вашем браузере отключена функция JavaScript!!!", "<br>Пожалуйста включите JavaScript, затем обновите страницу.", 'error');
?>
</noscript>
<script type="text/javascript">
function CheckForm(wt)
{
	    d = document.upload;	   

			if (wt==1){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==2){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==3){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==4){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==5){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==6){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==7){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==8){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==9){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==10){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==11){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==12){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==13){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
			if (wt==14){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
					if (wt==16){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
							if (wt==17){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'')+(d.kachestvo_.value !=""?" "+d.kachestvo_.value:'');                 
		}
			if (wt==15){
	 d.descr.value = 
	(d.description.value !=""?" "+d.description.value:'')
+(d.oppinion.value !=""?"\n\n[b]Личная оценка: [/b]"+d.oppinion.value:'');
	d.name.value = (d.torrent_name_orig.value !=""?d.torrent_name_orig.value:'')+((d.torrent_name_orig.value !="")&&(d.torrent_name_rus.value !="")?" / ":'')+(d.torrent_name_rus.value !=""?d.torrent_name_rus.value:'')+(d.release_date.value !=""?" ("+d.release_date.value+")":'');                 
		}
		 return true;
}		
</script>
<?php if($CURUSER["upload"] == "yes") {
 echo $choise_cats;
print"<script language=\"javascript\" type=\"text/javascript\" src=\"js/show_hide.js\"></script>";
?>
<br><br>
<div style="text-align:center;">
    <span style="cursor: pointer;" onclick="javascript: show_hide('s')"><img border="0" src="pic/plus.gif" id="pics" alt="<?= $lang['n_show'] ?? 'Показать'; ?>"></span>&nbsp;
    <span style="cursor: pointer;" onclick="javascript: show_hide('s')">
    <b><font color="blue"><u>[Подсказки]</u></font></b></span>
</div>
<span id="ss" style="display: none;">
<?
begin_frame('Подсказка по описанию');
begin_table();
?> 
<p>Любые отклонения от шаблона описания могут быть сочтены за грубую ошибку, и раздача будет удалена, а Вы получите предупреждение.</p>

<p>После второго предупреждения, Вы будете сняты с должности.</p>

<p>Для успешного оформления, внимательно изучите шаблон.</p>

<p>В полях "Постер" и "Скриншоты" указывайте ссылки на картинки со стороннего фото-хостинга. Ссылка должна выглядеть примерно так - http://www.animeclub.lv/poster.jpg</p>
<?
end_table();
end_frame();

begin_frame('Тэги иконок для описания');
begin_table();
?>
<tr>
<td class="icon-tag" style="text-align: center;">
<br>
<img src="/pic/thq/vo_rus.png" alt="Изображение"> - *vo_rus*<br>
<img src="/pic/thq/vo_eng.png" alt="Изображение"> - *vo_eng*<br>
<img src="/pic/thq/vo_lat.png" alt="Изображение"> - *vo_lat*<br>
<img src="/pic/thq/vo_other.png" alt="Изображение"> - *vo_other*<br>
</td>
<td class="icon-tag" style="text-align: center;">
<br>
<img src="/pic/thq/mvo_rus.png" alt="Изображение"> - *mvo_rus*<br>
<img src="/pic/thq/mvo_eng.png" alt="Изображение"> - *mvo_eng*<br>
<img src="/pic/thq/mvo_lat.png" alt="Изображение"> - *mvo_lat*<br>
<img src="/pic/thq/mvo_other.png" alt="Изображение"> - *mvo_other*<br>
</td>
<td class="icon-tag" style="text-align: center;">
<br>
<img src="/pic/thq/dub_rus.png" alt="Изображение"> - *dub_rus*<br>
<img src="/pic/thq/dub_eng.png" alt="Изображение"> - *dub_eng*<br>
<img src="/pic/thq/dub_lat.png" alt="Изображение"> - *dub_lat*<br>
<img src="/pic/thq/dub_other.png" alt="Изображение"> - *dub_other*<br>
</td>
</tr>
<tr>
<td class="icon-tag" style="text-align: center;">
<br>Японский:<br>
<img src="/pic/thq/jap_snd.png" alt="Изображение"> - *jap_snd*<br><br>
</td>
<td class="icon-tag" style="text-align: center;">
<br>Хардсаб: <br>
<img src="/pic/thq/hsub_ru.png" alt="Изображение"> - *hsub_ru*<br>
<img src="/pic/thq/hsub_eng.png" alt="Изображение"> - *hsub_eng*<br>
<img src="/pic/thq/hsub_lv.png" alt="Изображение"> - *hsub_lv*<br>
<img src="/pic/thq/hsub_other.png" alt="Изображение"> - *hsub_other*<br>
</td>
<td class="icon-tag" style="text-align: center;">
<br>Софтсаб: <br>
<img src="/pic/thq/ssub_ru.png" alt="Изображение"> - *ssub_ru*<br>
<img src="/pic/thq/ssub_eng.png" alt="Изображение"> - *ssub_eng*<br>
<img src="/pic/thq/ssub_lv.png" alt="Изображение"> - *ssub_lv*<br>
<img src="/pic/thq/ssub_other.png" alt="Изображение"> - *ssub_other*
</td>
</tr>
<?
end_table();
end_frame();
?>
</span>

<script type="text/javascript">
     function cats(type){
        jQuery("#loading").html(loading);
        jQuery.get("upload_new_shab.php",{"cats":type}, function (response) {
            jQuery("#upload_form").empty();
            jQuery("#upload_form").append(response);
            jQuery("#loading").empty();
    	});
    }

 var loading = "<img src=\"pic/upload.gif\" alt=\"Загрузка..\" />"; 

</script>

<div style="text-align:center;" id="upload_form">
<h1>Выберите категорию !</h1>
</div>
<? } else 
echo "<h1>Загрузка отключена Администрацией! <br /> Обратитесь за помощью на форум .</h1>";
end_frame();
end_main_frame();
stdfoot();
?>
