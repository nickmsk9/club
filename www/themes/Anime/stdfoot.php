<?php
// Глобальные переменные
global $mcache;

// Защита от прямого доступа
if (!defined('UC_SYSOP')) {
    die('Прямой доступ запрещён.');
}

// Загрузка языкового файла и блоков
getlang('stdfoot');
show_blocks('d');
?>
</div>
</td><td class="brd" width="58">&nbsp;</td></tr></table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td class="footer">

<?php
// Время генерации страницы и статистика PHP/SQL
$seconds = timer() - $tstart;
$phptime = $seconds - $querytime;
$query_time = $querytime;
$percentphp = number_format(($phptime / $seconds) * 100, 2);
$percentsql = number_format(($query_time / $seconds) * 100, 2);
$seconds = substr($seconds, 0, 8);

// Вывод отладочной информации для SYSOP
if (get_user_class() == UC_SYSOP) {
    echo "<center>" . sprintf($lang["page_generated"], $seconds, $queries, $percentphp, $percentsql)
        . " | Из кеша - " . $mcache->getCacheReadTimes()
        . " | В кеш - " . $mcache->getCacheWriteTimes()
        . " | Память - " . mksize(memory_get_usage())
        . " | PHP: " . phpversion() . "</center>";
}

// Учёт гостей (для статистики)
if (!$CURUSER) {
    if (!isset($_SESSION['guest_access']) || $_SESSION['guest_access'] < time() - 300) {
        $_SESSION['guest_access'] = time();
        $ip = getip();
        $browser = md5($_SERVER['HTTP_USER_AGENT']);
        $guests = sql_query("SELECT ip FROM guests WHERE ip = '$ip'") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($guests) == 0)
            sql_query("INSERT INTO guests (ip, time_accessed, browser, loc) VALUES ('$ip', " . sqlesc(time()) . "," . sqlesc($browser) . ", 'http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'] . "')") or sqlerr(__FILE__, __LINE__);
    }

    if (cache_check("guests", 300)) {
        $res = cache_read("guests");
    } else {
        $dt = sqlesc(time() - 300);
        $res = sql_query("SELECT id, time_accessed FROM guests WHERE time_accessed < $dt") or sqlerr(__FILE__, __LINE__);
        $guests_cache = [];
        while ($cache_data = mysqli_fetch_array($res))
            $guests_cache[] = $cache_data;
        cache_write("guests", $guests_cache);
        $res = $guests_cache;
    }

    if ($res > 0) {
        foreach ($res as $arr) {
            sql_query("DELETE FROM guests WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__);
        }
    }
}
?>

<!-- Отображение версии трекера -->
<table class="table_stats" cellspacing="0" cellpadding="0" align="left">
<tr>
    <td id="st_1">&nbsp;</td>
    <td id="st_2"><div class="stats"><br /><br /><?=TBVERSION?></div></td>
    <td id="st_3">&nbsp;</td>
</tr>
</table>

<!-- Блок авторства и ссылок -->
<div id="copyright">
<br /><br />
<a href="/copyrights.php" target="_blank"><?=$lang['copirajteram']?></a>&nbsp;&nbsp;
<a href="/support.php" title="Тех. Поддержка">Тех Поддержка</a><br /><br />
<?=$lang['desinged']?> <noindex><a class="copyright" href="http://strayd.free-lance.ru" target="_blank">strayd</a></noindex>&nbsp;
<?=$lang['desinged2']?> <noindex><a class="copyright" href="http://www.wsmyn.com/" target="_blank">WSMYN</a></noindex>
<br /><br />
</div>
<div style="clear:both;"></div>

<?php
// Отладочная информация
if (get_user_class() == UC_SYSOP && DEBUG_MODE) {
    echo '<div style="padding: 20px;">';

    if (count($query_stat)) {
        foreach ($query_stat as $key => $value) {
            $color = ($value["seconds"] > 0.01) ? "red" : "green";
            echo "<div>[" . ($key + 1) . "] => <b><font color=\"$color\">{$value['seconds']}</font></b> [{$value['query']}]</div>\n";
        }
        echo "<br />";
    }

    foreach ($mcache->getKeyHits('read') as $keyName => $hits) {
        echo "<div><font color=\"green\">R: " . htmlspecialchars($keyName) . " : $hits</font></div>";
    }

    foreach ($mcache->getKeyHits('write') as $keyName => $hits) {
        echo "<div><font color=\"blue\">W: " . htmlspecialchars($keyName) . " : $hits</font></div>";
    }

    echo '</div>';
}
?>
</td></tr></table></td></tr></table>

<!-- Google Analytics -->
<noindex>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-2795113-15']);
  _gaq.push(['_setDomainName', '<?php echo $_SERVER['HTTP_HOST']; ?>']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = (document.location.protocol === 'https:' ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

</noindex>
</body></html>