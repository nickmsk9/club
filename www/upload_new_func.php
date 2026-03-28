<?
global $lang;
$trtd = "<tr><td class=\"heading\" valign=\"top\" align=\"right\">";
$tdtd = "</td><td valign=\"top\" align=left>";
$kachestvo = array ('DVDRip','BDRip','DVD 5','DVD 9', 'DVDScr','Scr', 'TVRip', 'TS', 'TC', 'CAMRip', 'TVRip', 'SATRip', 'HDTV','HDTVRip', 'HD-DVDRip', 'VHSRip','WP','HWP');

//--------------------------------------- СПИСКИ -----------------------------------------
$kachestvo_ = "<select name=\"kachestvo_\"><option value=\"\">--- Выберите ---</option>";
foreach ($kachestvo as $val) {
  $kachestvo_ .= "<option value=\"$val\">$val</option>";
}
$kachestvo_ .= "</select>";

$razdel = "<select name=\"type\">\n<option value=\"0\">(".$lang['choose'].")</option>";
$cats = genrelist();
foreach ($cats as $row)
	$razdel .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>";
$razdel .= "</select>\n";
/////////////

$form_add['kachestvo'] = $trtd."Качество видео".$tdtd.$kachestvo_."</td></tr>";
$form_add['vajniy'] = $trtd."Важный".$tdtd." <input type=\"checkbox\" name=\"sticky\" value=\"yes\">Прикрепить этот торрент (всегда наверху)</td></tr>";
$form_add['release_date'] = $trtd."Год выхода".$tdtd."<input type=text name=release_date size=5></td></tr>";
$form_add['announce_url'] = $trtd.$lang['announce_url'].$tdtd.$announce_urls[0]."</td></tr>";
$form_add['torrent_file'] = $trtd.$lang['torrent_file'].$tdtd."<input type=file name=tfile size=60 ></td></tr>";
$form_add['torrent_name_orig'] =$trtd.$lang['torrent_name']." латиницей (ромадзи)".$tdtd."<input type=\"text\" name=\"torrent_name_orig\" size=\"60\"/></td></tr>";
$form_add['torrent_name_rus'] =$trtd.$lang['torrent_name']." (русское)".$tdtd."<input type=\"text\" name=\"torrent_name_rus\" size=\"60\" /></td></tr>";
$form_add['torrent_poster'] =$trtd."Обложка".$tdtd."<input type=text name=images size=60></td></tr>";
$form_add['screenshots'] =$trtd."Скриншоты".$tdtd."<b> №1:</b>&nbsp&nbsp<input type=\"text\" name=image1 size=60><br /><b> №2:</b>&nbsp&nbsp<input type=\"text\" name=image2 size=60><br /><b> №3:</b>&nbsp&nbsp<input type=\"text\" name=image3 size=60></td></tr>";
$form_add['submit'] = "<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" class=btn  value=\"".$lang['upload']."\"/></td></tr>";

$form_add['cats'] = $trtd . ($lang['razdel'] ?? 'Раздел') . $tdtd . $razdel . "</td></tr>";

/////******************/////
$form_add['type_amv'] = <<<HTML
<input type="hidden" name="type" value="1">
HTML;
$form_add['type_dvd'] = <<<HTML
<input type="hidden" name="type" value="3">
HTML;
$form_add['type_games'] = <<<HTML
<input type="hidden" name="type" value="5">
HTML;
$form_add['type_hentai'] = <<<HTML
<input type="hidden" name="type" value="6">
HTML;
$form_add['type_j-music'] = <<<HTML
<input type="hidden" name="type" value="7">
HTML;
$form_add['type_live-action'] = <<<HTML
<input type="hidden" name="type" value="8">
HTML;
$form_add['type_manga'] = <<<HTML
<input type="hidden" name="type" value="9">
HTML;
$form_add['type_mobile'] = <<<HTML
<input type="hidden" name="type" value="10">
HTML;
$form_add['type_movie'] = <<<HTML
<input type="hidden" name="type" value="11">
HTML;
$form_add['type_ost'] = <<<HTML
<input type="hidden" name="type" value="12">
HTML;
$form_add['type_ova'] = <<<HTML
<input type="hidden" name="type" value="13">
HTML;
$form_add['type_subtitles'] = <<<HTML
<input type="hidden" name="type" value="18">
HTML;
$form_add['type_misc'] = <<<HTML
<input type="hidden" name="type" value="19">
HTML;
$form_add['type_tv'] = <<<HTML
<input type="hidden" name="type" value="20">
HTML;
$form_add['type_images'] = <<<HTML
<input type="hidden" name="type" value="21">
HTML;
$form_add['type_ongoing'] = <<<HTML
<input type="hidden" name="type" value="22">
HTML;
$form_add['type_Anthology'] = <<<HTML
<input type="hidden" name="type" value="23">
HTML;

////////////***************/////////////


$form_add['free'] = <<<HTML

{$trtd} Скидка {$tdtd}
<select name="free">
<option value="00">Скидка 0%</option>
<option value="10">Скидка 10%</option>
<option value="20">Скидка 20%</option>
<option value="30">Скидка 30%</option>
</select>
</td></tr>

HTML;


//--------------------------------------- ОЦЕНКА -----------------------------------------
$form_add['oppinion'] = <<<HTML

{$trtd} Ваша оценка {$tdtd}
<select name="oppinion"><option value="">--- Выберите ---</option><option value="10 из 10">10 из 10</option><option value="9 из 10">9 из 10</option><option value="8 из 10">8 из 10</option><option value="7 из 10">7 из 10</option><option value="6 из 10">6 из 10</option><option value="5 из 10">5 из 10</option><option value="4 из 10">4 из 10</option><option value="3 из 10">3 из 10</option><option value="2 из 10">2 из 10</option><option value="1 из 10">1 из 10</option><option value="0 из 10">0 из 10</option></select>
</td></tr>
HTML;
//--------------------------------------- МЕНЮ КАТЕГОРИЙ -----------------------------------------
 
$choise_cats = <<<HTML
<table><tr>
<td style="border:none;"><div onclick="cats('AMV'); return false;" ><img src="/pic/cats/1.gif" title="Загрузить AMV" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('DVD'); return false;" ><img src="/pic/cats/4.gif" title="Загрузить DVD" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Games'); return false;" ><img src="/pic/cats/7.gif" title="Загрузить Games" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Hentai'); return false;" ><img src="/pic/cats/10.gif" title="Загрузить Hentai" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('J-Music'); return false;" ><img src="/pic/cats/13.gif" title="Загрузить J-Music" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('LiveAction'); return false;" ><img src="/pic/cats/15.gif" title="Загрузить LiveAction" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Manga'); return false;" ><img src="/pic/cats/2.gif" title="Загрузить Manga" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Mobile'); return false;" ><img src="/pic/cats/5.gif" title="Загрузить Mobile" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Movie'); return false;" ><img src="/pic/cats/8.gif" title="Загрузить Movie" class="cats"></div></td></tr><tr>
<td style="border:none;"><div onclick="cats('OST'); return false;" ><img src="/pic/cats/11.gif" title="Загрузить OST" class="cats"></div></td>

<td style="border:none;"><div onclick="cats('OVA'); return false;" ><img src="/pic/cats/14.gif" title="Загрузить OVA" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Subtitles'); return false;" ><img src="/pic/cats/3.gif" title="Загрузить Subtitles" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Misc'); return false;" ><img src="/pic/cats/6.gif" title="Загрузить Misc" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('TV'); return false;" ><img src="/pic/cats/9.gif" title="Загрузить TV" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Images'); return false;" ><img src="/pic/cats/12.gif" title="Загрузить Images" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Ongoing'); return false;" ><img src="/pic/cats/17.gif" title="Загрузить Ongoing" class="cats"></div></td>
<td style="border:none;"><div onclick="cats('Anthology'); return false;" ><img src="/pic/cats/18.gif" title="Загрузить Anthology" class="cats"></div></td>
</tr></table>
HTML;

$form_add['anonim'] = $trtd."Аноним".$tdtd."<input type=\"checkbox\" name=\"anonim\" value=\"yes\"><span style=\"color: red;\"><b>Сделать релиз полностью анонимным</b></span></td></tr>";  


///////////////////////*****************************//////////////////////
$form_add['description_amv'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=38>
[b]Год выпуска:[/b] {год}
[b]Страна:[/b] {страна}
[b]Источник:[/b] {автор, группа, конкурс (что применимо)}
[b]Количество клипов:[/b] {количество}
[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";
$form_add['description_dvd'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[color=red][b]Возрастное ограничение:[/b][/color] {опционально, обязательно для хентая!}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Композитор:[/b] {опционально}
[b]Сейю:[/b] {опционально}

[b]Дорожки[/b] {список пиктограмм дорожек}

[b]Описание:[/b] {описание}

[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Названия эпизодов:[/b] {опционально} (спрятано в спойлер)

[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {xx DVD5|xx DVD9}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}:
[b]Субтитры 2:[/b] {RU|EN|JP}:

[b]Авторы и участники:[/b]
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";
$form_add['description_games'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Русское название:[/b] {опционально}
[b]Английское название:[/b] {опционально}

[b]Год:[/b] {год выпуска}
[b]Жанр:[/b] {жанры}
[color=red][b]Возрастное ограничение:[/b][/color] {опционально, обязательно для хентая!}
[b]Язык интерфейса:[/b] {JP|EN|RU}

[b]Разработчик:[/b] {разработчик}
[b]Издатель:[/b] {издатель}
[b]Офсайт:[/b] {опционально}

[b]Описание:[/b] {описание}

[b]Формат релиза:[/b] {MDS|ISO|CUE+BIN|архив|инсталляция}

[b]Источник:[/b] {релиз-группа или источник}

</textarea></td></tr>";
$form_add['description_hentai'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}

</textarea></td></tr>";
$form_add['description_j-music'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Название:[/b] {название}
[b]Год:[/b] {год}

[b]Продолжительность:[/b] {чч:мм:сс}
[b]Исполнитель:[/b] {опционально для сборников: указываются в списке композиций}

[b]Технические характеристики:[/b] {формат, каналы, битрейт, частота}

[b]Названия композиций:[/b]
{список композиций}

[b]Источник:[/b] {источник}

</textarea></td></tr>";
$form_add['description_liveaction'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}

</textarea></td></tr>";
$form_add['description_manga'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Русское название:[/b] {опционально}
[b]Английское название:[/b] {опционально}

[b]Жанр:[/b] {жанры}
[b]Автор:[/b] {автор}
[b]Кол-во томов:[/b] {xx из yy}
[b]Год:[/b] {год}
[b]Язык:[/b] {JP|RU|EN|LV}
[b]Перевод:[/b] {автор перевода}

[b]Описание:[/b] {описание}

[b]World Art:[/b] {ссылка}

</textarea></td></tr>";
$form_add['description_mobile'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}

</textarea></td></tr>";
$form_add['description_movie'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";
$form_add['description_ost'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Название:[/b] {название}
[b]Год:[/b] {год}

[b]Продолжительность:[/b] {чч:мм:сс}
[b]Исполнитель:[/b] {опционально для сборников: указываются в списке композиций}

[b]Технические характеристики:[/b] {формат, каналы, битрейт, частота}

[b]Названия композиций:[/b]
{список композиций}

[b]Источник:[/b] {источник}

</textarea></td></tr>";
$form_add['description_ova'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";
$form_add['description_subtitles'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>

</textarea></td></tr>";
$form_add['description_misc'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>

</textarea></td></tr>";
/////////**********************
$form_add['description_tv'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";

$form_add['description_images'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Описание:[/b] {описание}
[b]Источник:[/b] {источник}

</textarea></td></tr>";

/////////**********************
$form_add['description_ongoing'] = $trtd.$lang['description'].$tdtd."<textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";

/////////**********************
$form_add['description_Anthology'] = $trtd.$lang['description'].$tdtd." <textarea name=description id=description  cols=86 rows=18>
[b]Оригинальное название:[/b] {название}
[b]Английское название:[/b] {название}
[b]Русское название:[/b] {название}

[b]Год выпуска:[/b] {год}
[b]Жанр:[/b] {жанры}
[b]Продолжительность:[/b] {xx эпизодов по yy мин.}
[b]Студия:[/b] {студия}
[b]Режиссёр:[/b] {режиссёр}
[b]Дорожки[/b] {список пиктограмм дорожек}
[b]Описание:[/b] {описание}
[b]Автор оригинала:[/b] {опционально}
[b]Снято по манге[/b]: {опционально}
[b]Ссылка на AniDB:[/b] {ссылка}
[b]Ссылка на World Art:[/b] {опционально}
   
[u][b]Техданные:[/b][/u]
[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}
[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}
[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}
[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}
[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}

[b]Авторы и участники:[/b]
[b]Рип:[/b] {автор или группа}
[b]Озвучка:[/b] {автор или группа}
[b]Субтитры:[/b] {автор или группа}

[b]Источник:[/b] {источник}

[b]Дополнительная информация:[/b] {опционально}
</textarea></td></tr>";
/////////////////////*****************************/////////////////////////


?>