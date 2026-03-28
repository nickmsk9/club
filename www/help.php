<?php
require_once("include/bittorrent.php");
dbconn(false);
loggedinorreturn();
stdhead('Подсказка');

begin_main_frame();	

begin_frame('Подсказка по описанию');
begin_table();
?> 
<p>Любые откланения от шаблона описания , могут быть сочтены за грубую ошибку и раздача будет удалены , а Вы получите предупреждение .</p>
<p>После второго предупреждения , Вы будите снаты с дожности .</p>
<p>Для успешного офармления , подробно изучите шаблон .</p>
<p>В полях "Постер" и "Скриншоты" указывайте ссылки на картинки со стороннего фото хостинга . Ссылка должна выглядеть примерно так - http://www.animeclub.lv/poster.jpg </p>
<p></p>
<p></p>
<p></p>
<?
end_table();
end_frame();

begin_frame('Тэги иконок для описания');
begin_table();
?>
<tr><td>
<br><img src="/pic/thq/vo_jap.png" alt="Изображение"> - *vo_jap*<br>
<img src="/pic/thq/vo_rus.png" alt="Изображение"> - *vo_rus*<br>
<img src="/pic/thq/vo_eng.png" alt="Изображение"> - *vo_eng*<br>
<img src="/pic/thq/vo_lat.png" alt="Изображение"> - *vo_lat*<br>
<img src="/pic/thq/vo_other.png" alt="Изображение"> - *vo_other*<br></td><td><br>
<img src="/pic/thq/mvo_jap.png" alt="Изображение"> - *mvo_jap*<br>
<img src="/pic/thq/mvo_rus.png" alt="Изображение"> - *mvo_rus*<br>
<img src="/pic/thq/mvo_eng.png" alt="Изображение"> - *mvo_eng*<br>
<img src="/pic/thq/mvo_lat.png" alt="Изображение"> - *mvo_lat*<br>
<img src="/pic/thq/mvo_other.png" alt="Изображение"> - *mvo_other*<br></td><td><br>
<img src="/pic/thq/dub_jap.png" alt="Изображение"> - *dub_jap*<br>
<img src="/pic/thq/dub_rus.png" alt="Изображение"> - *dub_rus*<br>
<img src="/pic/thq/dub_eng.png" alt="Изображение"> - *dub_eng*<br>
<img src="/pic/thq/dub_lat.png" alt="Изображение"> - *dub_lat*<br>
<img src="/pic/thq/dub_other.png" alt="Изображение"> - *dub_other*<br></td></tr>
<tr><td>
<br>Японский:<br>
<img src="/pic/thq/jap_snd.png" alt="Изображение"> - *jap_snd*<br><br>
</td><td>
<br>Хардсаб: <br>
<img src="/pic/thq/hsub_ru.png" alt="Изображение"> - *hsub_ru*<br>
<img src="/pic/thq/hsub_eng.png" alt="Изображение"> - *hsub_eng*<br>
<img src="/pic/thq/hsub_lv.png" alt="Изображение"> - *hsub_lv*<br>
<img src="/pic/thq/hsub_other.png" alt="Изображение"> - *hsub_other*<br>
</td><td><br>Софтсаб: <br>
<img src="/pic/thq/ssub_ru.png" alt="Изображение"> - *ssub_ru*<br>
<img src="/pic/thq/ssub_eng.png" alt="Изображение"> - *ssub_eng*<br>
<img src="/pic/thq/ssub_lv.png" alt="Изображение"> - *ssub_lv*<br>
<img src="/pic/thq/ssub_other.png" alt="Изображение"> - *ssub_other*
</td></tr><?
end_table();
end_frame();
end_main_frame();
stdfoot();
?>