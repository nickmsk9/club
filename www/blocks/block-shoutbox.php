<?php
// Защита от прямого вызова файла
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $CURUSER; // Текущий пользователь (используется, например, в js для прав)
?>
<!-- Подключаем внешние JS и стили -->
<script src="js/chat_mem.js?v=20260329-3" type="text/javascript"></script>
<link rel="stylesheet" href="css/chat.css?v=20260329-1" type="text/css">
<script type="text/javascript">
    function openSmiles(type) {
        var url = (type === 1) ?
            "moresmiles.php?form=shbox&text=text" :
            "moresmiles2.php?form=shbox&text=text";
        var opts = (type === 1) ?
            "height=500,width=500,resizable=yes,scrollbars=yes" :
            "height=450,width=550,resizable=yes,scrollbars=yes";
        window.open(url, "smileWindow" + type, opts);
    }
</script>
<?php
// Заголовок блока
$blocktitle = "Чат <span id=\"loading-chat\"></span>";

// Формируем содержимое блока чата
$content = <<<HTML
<form name="shbox" id="shbox" onsubmit="return false;">
<table cellspacing="1" cellpadding="5" id="chatmain" width="100%">
<tr>
<td style="white-space: nowrap">
    <div class="toolbar">
        <img class="button" src="pic/bold.gif" name="btnBold" onclick="doAddTags('[b]','[/b]','text')" alt="Жирным">
        <img class="button" src="pic/italic.gif" name="btnItalic" onclick="doAddTags('[i]','[/i]','text')" alt="Наклон">
        <img class="button" src="pic/underline.gif" name="btnUnderline" onclick="doAddTags('[u]','[/u]','text')" alt="Подчеркнутый">
        <img class="button" src="pic/link.gif" name="btnLink" onclick="doURL('text')" alt="Вставить ссылку">
        <img class="button" src="pic/picture.gif" name="btnPicture" onclick="doImage('text')" alt="Вставить картинку">
    </div>
    <div style="float:right">
        <span id="date" style="width:20px"></span>,
        <span id="hour_min"></span><span id="sec"></span>
    </div>
    <br /><br />
    <input type="text" id="text" name="shbox_text" class="message" style="width:60%" maxlength="500" required data-jq-watermark="processed" />
    <input type="submit" value="Отправить" name="newshout" class="btn say" />
    <input type="button" value="Смайлы" onclick="openSmiles(1)" class="btn" />
    <input type="button" value="Смайлы2" onclick="openSmiles(2)" class="btn" />
    <input type="button" value="Архив Чата" onclick="window.location.href='shoutbox_history.php'" class="btn" />
</td>
</tr>
<tr>
<td>
    <div id="shout" style="overflow:auto; width:100%; height:250px;">Загрузка ...</div>
</td>
</tr>
</table>
</form>
HTML;
?>
