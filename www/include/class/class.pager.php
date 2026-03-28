<?php

# ВАЖНО: Не редактируйте ниже, если не уверены в своих действиях!
if (!defined('IN_TRACKER')) {
    die('Попытка взлома!');
}

/**
 * Построитель постраничной навигации (пагинации)
 * @param int $rpp Количество записей на страницу
 * @param int $count Всего записей
 * @param string $href URL без page=
 * @param array $opts Опции (например, lastpagedefault)
 * @return array [верхняя_навигация, нижняя_навигация, SQL LIMIT]
 */
function pager($rpp, $count, $href, $opts = array())
{
    $pages = (int)ceil($count / $rpp);

    // Последняя страница по умолчанию
    if (!isset($opts["lastpagedefault"])) {
        $pagedefault = 0;
    } else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    // Определяем текущую страницу
    if (isset($_GET["page"])) {
        $page = max(0, (int)$_GET["page"]);
        if ($page > $pages - 1) {
    $page = $pages - 1;
}
    } else {
        $page = $pagedefault;
    }
    $pages = (int)ceil($count / $rpp);

    // Гарантируем правильный суффикс для параметров
    if (strpos($href, '?') === false) {
        $href .= '?';
    } elseif (substr($href, -1) !== '&' && substr($href, -1) !== '?') {
        $href .= '&';
    }
    $pager = "<div id='Pager'>";
    $pager2 = "";
    $bregs = "";
    $mp = $pages - 1;

    // Кнопка "назад"
    $as = "<b>«««</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"" . htmlspecialchars($href) . "page=" . ($page - 1) . "\" style=\"text-decoration: none;\">$as</a>";
    }

    // Кнопка "вперёд"
    $as = "<b>»»»</b>";
    if ($page < $mp && $mp >= 0) {
        $pager2 .= "<a href=\"" . htmlspecialchars($href) . "page=" . ($page + 1) . "\" style=\"text-decoration: none;\">$as</a>";
        $pager2 .= "$bregs ";
        $pager2 .= "</div><br clear=\"all\">";
    } else {
        $pager2 .= $bregs . "</div>";
    }

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 5;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "<a>...</a>";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;

            $text = $i + 1;
            if ($i != $page)
                $pagerarr[] = "<a title=\"$start&nbsp;-&nbsp;$end\" href=\"" . htmlspecialchars($href) . "page=$i\" style=\"text-decoration: none;\">$text</a>";
            else
                $pagerarr[] = "<div>$text</div>";
        }
        $pagerstr = join("", $pagerarr);
        $pagertop = "$pager $pagerstr $pager2\n";
        $pagerbottom = "$pager $pagerstr $pager2\n";
    } else {
        $pagertop = $pager;
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

?>