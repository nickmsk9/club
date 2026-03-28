<?php

// Используемые глобальные объекты
global $mysqli, $memcached;

// Получение тэгов из Memcached или базы данных
function get_tags() {
    global $mysqli, $memcached;
    // Ключ кеша
    $cacheKey = 'tag_cloud_cache';
    // Пытаемся получить из Memcached
    $cacheData = $memcached->get($cacheKey);
    if ($memcached->getResultCode() === Memcached::RES_SUCCESS && is_string($cacheData)) {
        $arr = unserialize($cacheData);
    } else {
        // Запрос к базе
        $arr = [];
        // Показывать все теги (хотя бы один) и сортировать по имени
        $sql = "SELECT name, howmuch FROM tags WHERE howmuch > 0 ORDER BY name ASC";
        if ($result = $mysqli->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $arr[$row['name']] = $row['howmuch'];
            }
            $result->free();
            // Сортировка по ключу
            ksort($arr);
        }
        // Сохраняем в Memcached на 30 минут
        $memcached->set($cacheKey, serialize($arr), 1800);
    }
    return $arr;
}

// Построение HTML-облака тегов
function cloud($small, $big, $colour = false) {
    $tags = get_tags();

    if (empty($tags)) {
        $data = "Нет тэгов";
    } else {
        $minimum_count = min(array_values($tags));
        $maximum_count = max(array_values($tags));
        $spread = $maximum_count - $minimum_count;

        if ($spread == 0) {
            $spread = 1;
        }

        $cloud = [];

        foreach ($tags as $tag => $count) {
            $size = $small + ($count - $minimum_count) * ($big - $small) / $spread;
            $colours = ['#990000', '#aa0000', '#bc5349', '#c26458', '#D08A80']; // Цвета

            $cloud[] = "<a href=\"browse.php?tag=" . urlencode($tag) . "\" style=\"" .
                ($colour ? "color:" . $colours[mt_rand(0, 4)] . "; " : "") .
                "font-size:" . floor($size) . "px;\" title=\"Содержится в $count торрентах\">" .
                htmlentities($tag, ENT_QUOTES, "utf-8") . "</a>\n";
        }

        $data = join($cloud);
    }

    return $data;
}

// Flash-облако (старый SWF способ, сейчас редко используется)
function flash_cloud($width, $height, $small, $big) {
    $divname = 'tagcloud';
    $soname = 'settings';
    $movie = '/swf/tagcloud.swf';
    $path = '/js/';
    $options = [
        'bgcolor' => 'FFFFFF',
        'trans' => 'true',
        'tcolor' => '000000',
        'tcolor2' => '111111',
        'hicolor' => '222222',
        'speed' => '100',
        'distr' => 'true',
        'mode' => 'tags'
    ];

    ob_start();
    echo cloud($small, $big);
    $tags = urlencode(str_replace("&nbsp;", " ", ob_get_clean()));

    $flashtag = '<script type="text/javascript" src="'.$path.'swfobject.js"></script>';
    $flashtag .= '<div id="'.$divname.'"><p style="display:none;">' . urldecode($tags) . '</p></div>';
    $flashtag .= '<script type="text/javascript">';
    $flashtag .= 'var rnumber = Math.floor(Math.random()*9999999);';
    $flashtag .= 'var '.$soname.' = new SWFObject("'.$movie.'?r="+rnumber, "tagcloudflash", "'.$width.'", "'.$height.'", "9", "#'.$options['bgcolor'].'");';

    if ($options['trans'] == 'true') {
        $flashtag .= $soname.'.addParam("wmode", "transparent");';
    }

    $flashtag .= $soname.'.addParam("allowScriptAccess", "always");';
    $flashtag .= $soname.'.addVariable("tcolor", "0x'.$options['tcolor'].'");';
    $flashtag .= $soname.'.addVariable("tcolor2", "0x'.($options['tcolor2'] == "" ? $options['tcolor'] : $options['tcolor2']).'");';
    $flashtag .= $soname.'.addVariable("hicolor", "0x'.($options['hicolor'] == "" ? $options['tcolor'] : $options['hicolor']).'");';
    $flashtag .= $soname.'.addVariable("tspeed", "'.$options['speed'].'");';
    $flashtag .= $soname.'.addVariable("distr", "'.$options['distr'].'");';
    $flashtag .= $soname.'.addVariable("mode", "'.$options['mode'].'");';
    $flashtag .= $soname.'.addVariable("tagcloud", "'.urlencode('<tags>') . $tags . urlencode('</tags>').'");';
    $flashtag .= $soname.'.write("'.$divname.'");';
    $flashtag .= '</script>';

    return $flashtag;
}

// Простое HTML-облако
function simple_cloud($small, $big) {
    $data = '<style>
        #tag_cloud a {padding : 3px;text-decoration: none;font-family : verdana;font-weight: normal;}
        #tag_cloud a:link {text-decoration: none;border : 1px solid transparent;}
        #tag_cloud a:visited {border : 1px solid transparent;}
        #tag_cloud a:hover {background: #ddd;border : 1px solid #bbb;}
        #tag_cloud a:active {background : #fff;border : 1px solid transparent;}
        #tag_cloud p {line-height : 28px;text-align : justify;}
        </style>';
    $data .= '<div id="tag_cloud">';
    $data .= '<p>' . cloud($small, $big, true) . '</p>';
    $data .= '</div>';
    return $data;
}
?>