<?php
ob_start();
// Включение отображения всех ошибок и логирование
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '1');
require_once("include/bittorrent.php");

// Функция вывода ошибки и завершения скрипта
function bark(string $msg): void {
    global $tracker_lang, $memcache_obj;
    stdhead($tracker_lang['error']);
    stdmsg($tracker_lang['error'], $msg);
    stdfoot();
    exit;
}

gzip();
dbconn(false);
$addparam  = $_GET['addparam']  ?? '';
$pagerlink = $_GET['pagerlink'] ?? '';
parked();
global $CURUSER;

// Функция вывода CSS для блока подарков
function cssstile(): void {
    ?>
    <style type="text/css">
        .smilies {
            display: inline-block;
            width: 98px;
            height: 120px;
            background: #ecf3fd;
            border: 1px solid #b8d6fb;
            margin: 2px;
            -moz-border-radius: 3px;
            -khtml-border-radius: 3px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            cursor: pointer;
        }
        .smilies:hover {
            background: #c2dcfc;
            border: 1px solid #7da2ce;
        }
        .smilies div { height: 98px; }
        .smilies img {
            max-height: 90px;
            max-width: 90px;
            margin-top: 5px;
            -moz-box-shadow: 1px 1px 3px 1px #96a6b9;
            -khtml-box-shadow: 1px 1px 3px 1px #96a6b9;
            -webkit-box-shadow: 1px 1px 3px 1px #96a6b9;
            box-shadow: 1px 1px 3px 1px #96a6b9;
        }
        .podarki {
            background: #ecf3fd;
            border: 1px solid #b8d6fb;
            margin: 3px;
            -moz-border-radius: 3px;
            -khtml-border-radius: 3px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
        }
        .podarki:hover {
            background: #c2dcfc;
            border: 1px solid #7da2ce;
        }
        .bro input {
            height: 25px;
            width: 100%;
        }
    </style>
    <?php
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0 && !empty($CURUSER['id'])) {
    stdhead("Подарки");
    cssstile();

    if ($id === (int)$CURUSER['id']) {
        stdmsg("Внимание", 'Вы не можете дарить подарки сами себе!');
    } else {
        $res = sql_query("SELECT id FROM users WHERE id = " . sqlesc($id))
            or sqlerr(__FILE__, __LINE__);
        while ($arr = mysqli_fetch_assoc($res)) {
            $userid = (int)$arr['id'];
        }

        if (isset($userid) && $userid === $id) {
            ?>
            <script type="text/javascript">
            function getpod(get) {
                jQuery.get(
                    "/podarok.php?idu=<?= $userid ?>",
                    { p: get },
                    function(data) {
                        jQuery("#podarki").empty().append(data);
                    },
                    'html'
                );
                s();
            }
            </script>
            <?php
            begin_main_frame();
            begin_frame("Подарки");
            print "<div class=\"win_post\" align=\"center\" id=\"podarki\">";

            $perpage = 24;
            $res     = sql_query("SELECT COUNT(*) FROM podarki")
                or sqlerr(__FILE__, __LINE__);
            $arr     = mysqli_fetch_row($res);
            $count   = (int)$arr[0];

            if ($addparam !== '') {
                if ($pagerlink !== '') {
                    if (substr($addparam, -1) !== ';') {
                        $addparam .= "&" . $pagerlink;
                    } else {
                        $addparam .= $pagerlink;
                    }
                }
            } else {
                $addparam = $pagerlink;
            }

            list($pagertop, $pagerbottom, $limit) = pager(
                $perpage,
                $count,
                $_SERVER['PHP_SELF'] . "?id={$id}&amp;{$addparam}"
            );

            $res = sql_query("SELECT * FROM podarki ORDER BY bonus DESC $limit")
                or sqlerr(__FILE__, __LINE__);
            print $pagertop;

            while ($arr = mysqli_fetch_assoc($res)) {
                if ($CURUSER['bonus'] > $arr['bonus']) {
                    $style = "onclick=\"getpod('{$arr['id']}');\"";
                } else {
                    $style = "style=\"background:#ffc3c3;border:1px solid #ff7777;cursor:default;\"";
                }
                print "<div class=\"smilies\" {$style}>"
                        . "<div><img src=\"{$arr['pic']}\" "
                        . "alt=\"{$arr['bonus']}\" "
                        . "title=\"Цена - {$arr['bonus']} БП\" /></div>"
                        . "Цена - {$arr['bonus']} БП</div>";
            }
            print $pagerbottom;
            print "</div>";
            end_frame();
            end_main_frame();
        } else {
            stdmsg("Внимание", 'Такого пользователя не существует!');
        }
    }
    stdfoot();
$idu = (int)($_GET['idu'] ?? 0); 
$p   = htmlspecialchars($_GET['p'] ?? '', ENT_QUOTES);

if ($idu > 0 && (int)$p > 0 && !empty($CURUSER['id'])) {
    $pid = (int)$p;

    if ($idu === (int)$CURUSER['id']) {
        echo "Нельзя подарить себе";
        exit;
    }

    $res = sql_query("SELECT * FROM podarki WHERE id = " . sqlesc($pid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) === 0) {
        echo "Подарок не найден!";
        exit;
    }

    $gift = mysqli_fetch_assoc($res);

    if ($CURUSER['bonus'] < $gift['bonus']) {
        echo "Недостаточно бонусов!";
        exit;
    }

    sql_query("UPDATE users SET bonus = bonus - {$gift['bonus']} WHERE id = " . (int)$CURUSER['id']) or sqlerr(__FILE__, __LINE__);
    sql_query("INSERT INTO podarok (podarokid, userid, useradd, date, text) VALUES (
        {$gift['id']}, $idu, {$CURUSER['id']}, NOW(), '')") or sqlerr(__FILE__, __LINE__);

    echo "<b>Подарок успешно отправлен!</b>";
    exit;
}



 stdhead("Подарки");



cssstile();
?>
<script language="javascript" type="text/javascript">
function ShowEpisodes(n){jQuery('div[id^="row_'+n+'_"]').each(function(){if(jQuery(this).is(':hidden'))jQuery(this).slideDown(); else jQuery(this).slideUp();});}
</script>
<STYLE type="text/css" >
.bro {background:#edfbfe;margin:2px 0px 0px 0px;border: 1px solid #9CA4B0;font-size: 14px;}
.bro:hover {background:#c9e0f0;}
.broi {float: left; padding: 10px 15px 10px 0px;width: 200px;}
.brontv {width: 150px;}
.brd1 {min-height:200px;padding: 0px 0px 0px 220px;}
</STYLE>
<?
begin_main_frame();
begin_frame ( "Подаренные пользователю - вернуться <a href=\"/user/id". $id ."\"><u>в профиль</u></a>" );
print ( "<div class='win_post'>" );
$res = sql_query ( "
SELECT 
podarok.id, 
podarok.podarokid, 
podarok.userid, 
podarok.useradd, 
podarok.date, 
podarok.text, 
(SELECT podarki.pic FROM podarki WHERE podarki.id=podarok.podarokid) AS picp, 
(SELECT users.username FROM users WHERE users.id=podarok.useradd) AS odd 
FROM podarok 
WHERE userid = ". sqlesc($id)."" ) or sqlerr ( __FILE__, __LINE__ );
$rr=0;
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
$rr++;
print ( "<div class='bro' onClick=\"ShowEpisodes({$rr})\">
<table width='100%'><tr>
<td align='left' width='42'><img width='38' src=\"{$arr ["picp"]}\" /></td>
<td align='left'>От: <a href='/user/id{$arr ['useradd']}'>{$arr ["odd"]}</a></td>
<td align='right' class='brontv'>" );
if(get_user_class() >= UC_SYSOP){
print ( "<a href='podarok.php?c=all&dell={$arr ['id']}'><img title=\"Удалить\" src=\"/pic/bb/d.gif\" border=\"0\"/></a><br/>" );}
print ( "<b>{$arr ["date"]}</b></td>
</tr></table></div>
<div style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\">
<div class='broi' style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\"><img width='200' src=\"" . $arr ["picp"] . "\" /></div>
<div class='brd1'>" . format_comment ( $arr ['text'] ) . "</div>
</div>" );
}
print ( "</div>" );
end_frame();
end_main_frame();
begin_main_frame();
begin_frame ( "Подаренные пользователем" );
print ( "<div class='win_post'>" );
$res = sql_query ( "
SELECT 
podarok.id, 
podarok.podarokid, 
podarok.userid, 
podarok.useradd, 
podarok.date, 
podarok.text, 
(SELECT podarki.pic FROM podarki WHERE podarki.id=podarok.podarokid) AS picp, 
(SELECT users.username FROM users WHERE users.id=podarok.userid) AS odd 
FROM podarok 
WHERE useradd = ". sqlesc($id)."" ) or sqlerr ( __FILE__, __LINE__ );
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
$rr++;
print ( "<div class='bro' onClick=\"ShowEpisodes({$rr})\">
<table width='100%'><tr>
<td align='left' width='42'><img width='38' src=\"{$arr ["picp"]}\" /></td>
<td align='left'>Кому: <a href='/userdetails.php?id={$arr ['userid']}' target=\"_blank\" >{$arr ["odd"]}</a></td>
<td align='right' class='brontv'>" );
if(get_user_class() >= UC_SYSOP){
print ( "<a href='podarok.php?c=all&dell={$arr ['id']}'><img title=\"Удалить\" src=\"/pic/bb/d.gif\" border=\"0\"/></a><br/>" );}
print ( "<b>{$arr ["date"]}</b></td>
</tr></table></div>
<div style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\">
<div class='broi' style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\"><img width='200' src=\"" . $arr ["picp"] . "\" /></div>
<div class='brd'>" . format_comment ( $arr ['text'] ) . "</div>
</div>" );
}
print ( "</div>" );
end_frame();
end_main_frame();

stdfoot();
} elseif (!empty($_GET['c']) && get_user_class() >= UC_SYSOP) {
stdhead("Подарки");
cssstile();
$c = $_GET['c'] ?? '';
if( $_GET['c'] == 'edit' and $_GET['p']){
begin_main_frame();

begin_frame("Настройка подарка {$_GET['p']}");
print ( "<div class='win_post' align='center'>" );
$res = sql_query ( "SELECT * FROM podarki WHERE id = ". sqlesc((int)$_GET['p'])."" ) or sqlerr ( __FILE__, __LINE__ );
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
?>
<form name="podarki" action="podarok.php?c=edit&po=<?=$arr['id']?>" method="post" class="bro"><table width="100%">
<tr><td width="50">URL</td><td><input type="text"  name="url" value="<?=$arr['pic']?>"></td></tr>
<tr><td>Бонус</td><td><input type="text"  name="bonus" value="<?=$arr['bonus']?>"></td></tr>
</table><input type="submit" value=" Отредактировать подарок "></form>
<?}
print ( "</div>" );
end_frame();
end_main_frame();

}elseif($_GET['c'] == 'edit' and $_GET['po'] and $_POST['url'] and $_POST['bonus'] ){
sql_query ( "UPDATE podarki SET pic='{$_POST['url']}', bonus='{$_POST['bonus']}' WHERE id='{$_GET['po']}'" ) or sqlerr ( __FILE__, __LINE__ );
stdmsg("Внимание", "Подарок №{$_POST['pod']} успешно обновлен. <a href=\"podarok.php?c=1\" >Вернуться к конфигурации.</a>");
} elseif (isset($_GET['c']) && $_GET['c'] === 'all') {

    // Удаление одного подарка
    if (isset($_GET['dell'])) {
        $dell = (int) $_GET['dell'];
        sql_query("DELETE FROM podarok WHERE id = {$dell}") or sqlerr(__FILE__, __LINE__);
        stdmsg("Внимание", "Подарок успешно удален.");
    }

    // Очистка всех подарков
    if (isset($_GET['alldell'])) {
        sql_query("TRUNCATE TABLE podarok") or sqlerr(__FILE__, __LINE__);
        stdmsg("Внимание", "Все подарки удалены");
    }

    begin_main_frame();
    begin_frame("Все подарки");
print ( "<div class='win_post'>" );
?>
<a class="podarki" href="podarok.php?c=all&alldell=1" title="Очистить список всех подаренных подарков">Очистить список всех подаренных подарков</a>
<script language="javascript" type="text/javascript">
function ShowEpisodes(n){jQuery('div[id^="row_'+n+'_"]').each(function(){if(jQuery(this).is(':hidden'))jQuery(this).slideDown(); else jQuery(this).slideUp();});}
</script>
<STYLE type="text/css" >
.bro {background:#edfbfe;margin:2px 0px 0px 0px;border: 1px solid #9CA4B0;font-size: 14px;}
.bro:hover {background:#c9e0f0;}
.broi {float: left; padding: 10px 15px 10px 0px;width: 200px;}
.brontv {width: 150px;}
.brd1 {min-height:200px;padding: 0px 0px 0px 220px;}
</STYLE>
<?
    // Пагинация
    $perpage = 20;
    $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // Подсчитываем общее число записей
    $res   = sql_query("SELECT COUNT(*) FROM podarok") or sqlerr(__FILE__, __LINE__);
    $row   = mysqli_fetch_row($res);
    $total = (int)$row[0];

    // Считаем количество страниц
    $pages = (int) ceil($total / $perpage);

    // Корректируем номер текущей страницы
    if ($pages > 0 && $page > $pages) {
        $page = $pages;
    }

    // Вычисляем смещение
    $offset = ($page - 1) * $perpage;

    // Навигация
    if ($page <= 1) {
        $browsemenul = "<b style=\"color:#ff0000\">Назад</b>";
    } else {
        $browsemenul = "<a href=\"podarok.php?c=all&page=" . ($page - 1) . "\"><b>Назад</b></a>";
    }
    if ($page >= $pages) {
        $browsemenur = "<b style=\"color:#ff0000\">Вперед</b>";
    } else {
        $browsemenur = "<a href=\"podarok.php?c=all&page=" . ($page + 1) . "\"><b>Вперед</b></a>";
    }
$res = sql_query ( "
SELECT 
podarok.id, 
podarok.podarokid, 
podarok.userid, 
podarok.useradd, 
podarok.date, 
podarok.text, 
(SELECT podarki.pic FROM podarki WHERE podarki.id=podarok.podarokid) AS picp, 
(SELECT users.username FROM users WHERE users.id=podarok.userid) AS odd 
FROM podarok 
LIMIT {$offset},{$perpage}" ) or sqlerr ( __FILE__, __LINE__ );
$rr=0;
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
$res3 = sql_query("SELECT username FROM users WHERE id='{$arr ["useradd"]}'") or sqlerr(__FILE__,__LINE__);
$arr3 = mysqli_fetch_array($res3);
$rr++;
print ( "<div class='bro' onClick=\"ShowEpisodes({$rr})\">
<table width='100%'><tr>
<td align='left' width='42'><img width='38' src=\"{$arr ["picp"]}\" /></td>
<td align='left' width='150'>От: <a href='/userdetails.php?id={$arr ['useradd']}' target=\"_blank\" >{$arr3["username"]}</a></td>
<td align='left'>Кому: <a href='/userdetails.php?id={$arr ['userid']}' target=\"_blank\" >" . $arr ["odd"] . "</a></td>
<td align='right' class='brontv'>
<a href='podarok.php?c=all&dell={$arr ['id']}'>
<img title=\"Удалить\" src=\"/pic/bb/d.gif\" border=\"0\"/></a><br/><b>{$arr ["date"]}</b></td>
</tr></table></div>
<div style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\">
<div class='broi' style=\"display:none;\" id=\"row_{$rr}_{$arr ['id']}\"><img width='200' src=\"" . $arr ["picp"] . "\" /></div>
<div class='brd1'>" . format_comment ( $arr ['text'] ) . "</div>
</div>" );
}
print ( "</div>" );
print ( "<div class='win_info'><div class='win_infot'></div>" );
print ( "<table width=100% height=20><tr><td width='50%' align='left'>$browsemenul</td><td width='50%' align='right'>$browsemenur</td></tr></table>" );
?>
<script language="javascript" type="text/javascript">
var Paginator = function(paginatorHolderId, pagesTotal, pagesSpan, pageCurrent, baseUrl){if(!document.getElementById(paginatorHolderId) || !pagesTotal || !pagesSpan) return false;this.inputData = {paginatorHolderId: paginatorHolderId,pagesTotal: pagesTotal,pagesSpan: pagesSpan < pagesTotal ? pagesSpan : pagesTotal,pageCurrent: pageCurrent,baseUrl: baseUrl ? baseUrl : '<? print ( "?c=all&page=" ); ?>'};this.html = {holder: null,table: null,trPages: null,trScrollBar: null,tdsPages: null,scrollBar: null,scrollThumb: null,pageCurrentMark: null};this.prepareHtml();this.initScrollThumb();this.initPageCurrentMark();this.initEvents();this.scrollToPageCurrent();} 
Paginator.prototype.prepareHtml = function(){this.html.holder = document.getElementById(this.inputData.paginatorHolderId);this.html.holder.innerHTML = this.makePagesTableHtml();this.html.table = this.html.holder.getElementsByTagName('table')[0];var trPages = this.html.table.getElementsByTagName('tr')[0];this.html.tdsPages = trPages.getElementsByTagName('td');this.html.scrollBar = getElementsByClassName(this.html.table, 'div', 'scroll_bar')[0];this.html.scrollThumb = getElementsByClassName(this.html.table, 'div', 'scroll_thumb')[0];this.html.pageCurrentMark = getElementsByClassName(this.html.table, 'div', 'current_page_mark')[0];if(this.inputData.pagesSpan == this.inputData.pagesTotal){addClass(this.html.holder, 'fullsize');}}
Paginator.prototype.makePagesTableHtml = function(){var tdWidth = (100 / this.inputData.pagesSpan) + '%';var html = ''+'<table width="100%">'+'<tr>';for (var i=1; i<=this.inputData.pagesSpan; i++){html += '<td width="' + tdWidth + '"></td>';}html += ''+'</tr>'+'<tr>'+'<td colspan="'+ this.inputData.pagesSpan + '">'+'<div class="scroll_bar">'+'<div class="scroll_trough"></div>'+'<div class="scroll_thumb">'+'<div class="scroll_knob"> </div>'+'</div>'+'<div class="current_page_mark"></div>'+'</div>'+'</td>'+'</tr>'+'</table>';return html;}
Paginator.prototype.initScrollThumb = function(){this.html.scrollThumb.widthMin = '8';this.html.scrollThumb.widthPercent = this.inputData.pagesSpan/this.inputData.pagesTotal * 100;this.html.scrollThumb.xPosPageCurrent = (this.inputData.pageCurrent - Math.round(this.inputData.pagesSpan/2))/this.inputData.pagesTotal * this.html.table.offsetWidth;this.html.scrollThumb.xPos = this.html.scrollThumb.xPosPageCurrent;this.html.scrollThumb.xPosMin = 0;this.html.scrollThumb.xPosMax;this.html.scrollThumb.widthActual;this.setScrollThumbWidth();}
Paginator.prototype.setScrollThumbWidth = function(){this.html.scrollThumb.style.width = this.html.scrollThumb.widthPercent + "%";this.html.scrollThumb.widthActual = this.html.scrollThumb.offsetWidth;if(this.html.scrollThumb.widthActual < this.html.scrollThumb.widthMin){this.html.scrollThumb.style.width = this.html.scrollThumb.widthMin + 'px';}this.html.scrollThumb.xPosMax = this.html.table.offsetWidth - this.html.scrollThumb.widthActual;}
Paginator.prototype.moveScrollThumb = function(){this.html.scrollThumb.style.left = this.html.scrollThumb.xPos + "px";};Paginator.prototype.initPageCurrentMark = function(){this.html.pageCurrentMark.widthMin = '3';this.html.pageCurrentMark.widthPercent = 100 / this.inputData.pagesTotal;this.html.pageCurrentMark.widthActual;this.setPageCurrentPointWidth();this.movePageCurrentPoint();};Paginator.prototype.setPageCurrentPointWidth = function(){this.html.pageCurrentMark.style.width = this.html.pageCurrentMark.widthPercent + '%';this.html.pageCurrentMark.widthActual = this.html.pageCurrentMark.offsetWidth;if(this.html.pageCurrentMark.widthActual < this.html.pageCurrentMark.widthMin){this.html.pageCurrentMark.style.width = this.html.pageCurrentMark.widthMin + 'px';}}
Paginator.prototype.movePageCurrentPoint = function(){if(this.html.pageCurrentMark.widthActual < this.html.pageCurrentMark.offsetWidth){this.html.pageCurrentMark.style.left = (this.inputData.pageCurrent - 1)/this.inputData.pagesTotal * this.html.table.offsetWidth - this.html.pageCurrentMark.offsetWidth/2 + "px";} else {this.html.pageCurrentMark.style.left = (this.inputData.pageCurrent - 1)/this.inputData.pagesTotal * this.html.table.offsetWidth + "px";}}
Paginator.prototype.initEvents = function(){var _this = this;this.html.scrollThumb.onmousedown = function(e){if (!e) var e = window.event;e.cancelBubble = true;if (e.stopPropagation) e.stopPropagation();var dx = getMousePosition(e).x - this.xPos;document.onmousemove = function(e){if (!e) var e = window.event;_this.html.scrollThumb.xPos = getMousePosition(e).x - dx;_this.moveScrollThumb();_this.drawPages();};document.onmouseup = function(){document.onmousemove = null;_this.enableSelection();};_this.disableSelection();};this.html.scrollBar.onmousedown = function(e){if (!e) var e = window.event;if(matchClass(_this.paginatorBox, 'fullsize')) return;_this.html.scrollThumb.xPos = getMousePosition(e).x - getPageX(_this.html.scrollBar) - _this.html.scrollThumb.offsetWidth/2;_this.moveScrollThumb();_this.drawPages();};addEvent(window, 'resize', function(){Paginator.resizePaginator(_this)});}
Paginator.prototype.drawPages = function(){var percentFromLeft = this.html.scrollThumb.xPos/(this.html.table.offsetWidth);var cellFirstValue = Math.round(percentFromLeft * this.inputData.pagesTotal);var html = "";if(cellFirstValue < 1){cellFirstValue = 1;this.html.scrollThumb.xPos = 0;this.moveScrollThumb();} else if(cellFirstValue >= this.inputData.pagesTotal - this.inputData.pagesSpan) {cellFirstValue = this.inputData.pagesTotal - this.inputData.pagesSpan + 1;this.html.scrollThumb.xPos = this.html.table.offsetWidth - this.html.scrollThumb.offsetWidth;this.moveScrollThumb();}for(var i=0; i<this.html.tdsPages.length; i++){var cellCurrentValue = cellFirstValue + i;if(cellCurrentValue == this.inputData.pageCurrent){html = "<span>" + "<strong>" + cellCurrentValue + "</strong>" + "</span>";} else {html = "<span>" + "<a href='" + this.inputData.baseUrl + cellCurrentValue + "'>" + cellCurrentValue + "</a>" + "</span>";}this.html.tdsPages[i].innerHTML = html;}}
Paginator.prototype.scrollToPageCurrent = function(){this.html.scrollThumb.xPosPageCurrent = (this.inputData.pageCurrent - Math.round(this.inputData.pagesSpan/2))/this.inputData.pagesTotal * this.html.table.offsetWidth;this.html.scrollThumb.xPos = this.html.scrollThumb.xPosPageCurrent;this.moveScrollThumb();this.drawPages();};Paginator.prototype.disableSelection = function(){document.onselectstart = function(){return false;};this.html.scrollThumb.focus();};Paginator.prototype.enableSelection = function(){ document.onselectstart = function(){return true;}};Paginator.resizePaginator = function (paginatorObj){ paginatorObj.setPageCurrentPointWidth();paginatorObj.movePageCurrentPoint();paginatorObj.setScrollThumbWidth();paginatorObj.scrollToPageCurrent();}
function getElementsByClassName(objParentNode, strNodeName, strClassName){var nodes = objParentNode.getElementsByTagName(strNodeName);if(!strClassName){return nodes;}var nodesWithClassName = [];for(var i=0; i<nodes.length; i++){if(matchClass( nodes[i], strClassName )){nodesWithClassName[nodesWithClassName.length] = nodes[i];}}return nodesWithClassName;}function addClass( objNode, strNewClass ) {replaceClass( objNode, strNewClass, '' );}function removeClass( objNode, strCurrClass ) {replaceClass( objNode, '', strCurrClass );}
function replaceClass( objNode, strNewClass, strCurrClass ) {var strOldClass = strNewClass;if ( strCurrClass && strCurrClass.length ){strCurrClass = strCurrClass.replace( /\s+(\S)/g, '|$1' );if ( strOldClass.length ) strOldClass += '|';strOldClass += strCurrClass;}objNode.className = objNode.className.replace( new RegExp('(^|\\s+)(' + strOldClass + ')($|\\s+)', 'g'), '$1' );objNode.className += ( (objNode.className.length)? ' ' : '' ) + strNewClass;}function matchClass( objNode, strCurrClass ) {return ( objNode && objNode.className.length && objNode.className.match( new RegExp('(^|\\s+)(' + strCurrClass + ')($|\\s+)') ) );}
function addEvent(objElement, strEventType, ptrEventFunc) {if (objElement.addEventListener)objElement.addEventListener(strEventType, ptrEventFunc, false);else if (objElement.attachEvent)objElement.attachEvent('on' + strEventType, ptrEventFunc);}function removeEvent(objElement, strEventType, ptrEventFunc) {if (objElement.removeEventListener) objElement.removeEventListener(strEventType, ptrEventFunc, false);else if (objElement.detachEvent) objElement.detachEvent('on' + strEventType, ptrEventFunc);}
function getPageY( oElement ) {var iPosY = oElement.offsetTop;while ( oElement.offsetParent != null ) {oElement = oElement.offsetParent;iPosY += oElement.offsetTop;if (oElement.tagName == 'BODY') break;}return iPosY;}function getPageX( oElement ) {var iPosX = oElement.offsetLeft;while ( oElement.offsetParent != null ) {oElement = oElement.offsetParent;iPosX += oElement.offsetLeft;if (oElement.tagName == 'BODY') break;}return iPosX;}function getMousePosition(e){if (e.pageX || e.pageY){var posX = e.pageX;var posY = e.pageY;}else if (e.clientX || e.clientY){var posX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;var posY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;}return {x:posX, y:posY}}
</script>
<STYLE type="text/css">
.paginator {font-size:1em;width:100%;}
.paginator table {border-collapse:collapse;table-layout:fixed;width:100%;}
.paginator table td {padding:0;white-space:nowrap;text-align:center;}
.paginator span {display:block;padding:3px 0;color:#fff;}
.paginator span strong,.paginator span a {padding:2px 6px;}
.paginator span strong {background:#ff0000;font-style:normal;font-weight:normal; }
.paginator .scroll_bar {width:100%;	height:20px;position:relative;margin-top:10px; }
.paginator .scroll_trough {width:100%;	height:3px;background:#ccc;overflow:hidden;}
.paginator .scroll_thumb {position:absolute;z-index:2;width:0; height:3px;top:0; left:0;font-size:1px;background:#363636;}
.paginator .scroll_knob {position:absolute;left:50%;margin-left:-10px;width:20px; height:15px;overflow:hidden;cursor:pointer; cursor:hand;background:#363636;}
.paginator .current_page_mark {position:absolute;z-index:1;top:0; left:0;width:0; height:3px;overflow:hidden;background:#ff0000;}
.fullsize .scroll_thumb {display:none;}
</STYLE>
<div class="paginator" id="paginator_b"></div><script type="text/javascript">pag1 = new Paginator('paginator_b', <? print ( "$pages" ); ?>, 20, <? print ( "$page" ); ?>, "");</script>
<?
print ( "</div>" );
end_frame ();
end_main_frame();

}elseif($_GET['c'] == 'del' and $_GET['p'] ){
sql_query ( "DELETE FROM podarki WHERE id = '{$_GET['p']}'" ) or sqlerr ( __FILE__, __LINE__ );
stdmsg("Внимание", "Подарок успешно удален. <a href=\"podarok.php?c=1\" >Вернуться к конфигурации.</a>");
}elseif($_GET['c'] == 'genere' ){
begin_main_frame();

begin_frame("Поиск и генерация подарков");
$dir = opendir ( "pic/podarki" );
while (false !== ($file = readdir($dir))) {
if ($file != "." && $file != "..") {
sql_query ( "INSERT INTO podarki (pic, bonus) values ('/pic/podarki/{$file}', '50')" ) or sqlerr ( __FILE__, __LINE__ );
print ( "Найден: {$file} и записан!<br/>" );
}}
end_frame();
end_main_frame();
}elseif($_GET['c'] == 'trun' ){
sql_query ( "TRUNCATE TABLE podarki" ) or sqlerr ( __FILE__, __LINE__ );
stdmsg("Внимание", "Очистка прошла успешно. <a href=\"podarok.php?c=1\" >Вернуться к конфигурации.</a>");
}else{
if (isset($_POST['url']) && isset($_POST['bonus'])) {
sql_query ( "INSERT INTO podarki (pic, bonus) values ('{$_POST['url']}', '{$_POST['bonus']}')" ) or sqlerr ( __FILE__, __LINE__ );
stdmsg("Внимание", "Подарок успешно создан. <a href=\"podarok.php?c=1\" >Вернуться к конфигурации.</a>");
sql_query ( "INSERT INTO shoutbox (userid, date, text) values ('0', '".time ()."', '[color=#3366FF]Появился новый подарок стоимостью: [b]{$_POST['bonus']}[/b] бонусов[/color][center][img]{$_POST['url']}[/img][/center]')" ) or sqlerr ( __FILE__, __LINE__ );
}
begin_main_frame();

begin_frame("Подарки");
print ( "<div class='win_post' align='center'>" );

?>
<form name="podarki" action="podarok.php?c=1" method="post" class="bro podarki"><table width="100%">
<tr><td width="50">URL</td><td><input type="text"  name="url" value=""></td></tr>
<tr><td>Бонус</td><td><input type="text"  name="bonus" value=""></td></tr>
</table><input type="submit" value=" Создать новый подарок "></form>
<a class="podarki" href="podarok.php?c=all" title="Все подарки">Подаренные</a> <a class="podarki" href="podarok.php?c=trun" title="Очистка всех подарков">Очистка всех подарков для генерации из папки</a> <a class="podarki" href="podarok.php?c=genere" title="Поиск и генерация подарков">Поиск и генерация подарков в папке</a><br/>
<?
$res = sql_query("SELECT * FROM podarki") or sqlerr(__FILE__, __LINE__);
while ( $arr = mysqli_fetch_assoc ( $res ) ) {
print ( "<div class=\"smilies\"><div><a href=\"{$arr['pic']}\" onclick=\"return hs.expand(this)\"><img src=\"{$arr['pic']}\" alt=\"{$arr['bonus']}\" title=\"{$arr['bonus']}\"/></a></div>
[ <a href=\"podarok.php?c=edit&p={$arr['id']}\" title=\"Изменить\">E</a> <a href=\"podarok.php?c=del&p={$arr['id']}\" title=\"Удалить\">D</a> ] - {$arr['bonus']}</div>" );}
print ( "</div>" );
end_frame();
end_main_frame();
}

}

?>