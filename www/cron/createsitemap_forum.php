<?php
declare(strict_types=1);
#
#    GOOGLE SITEMAP CREATION
#        by n-sw-bit
#
#
require_once dirname(__DIR__, 2) . '/include/bittorrent.php';
dbconn();
gensitemap_forum();


function gensitemap_forum(){
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? '');

    $txt  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $txt .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
         . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
         . 'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 '
         . 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'
         . PHP_EOL;

    $sql = sql_query("SELECT id FROM forums");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/forum/view/forum/id' . urlencode((string)$a['id'])
             . '</loc><lastmod>' . tf() . '</lastmod>'
             . '<changefreq>daily</changefreq><priority>0.50</priority></url>'
             . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM topics");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/forum/view/topic/id' . urlencode((string)$a['id'])
             . '</loc><lastmod>' . tf() . '</lastmod>'
             . '<changefreq>hourly</changefreq><priority>0.50</priority></url>'
             . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM overforums");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/forum/view/forid/id' . urlencode((string)$a['id'])
             . '</loc><lastmod>' . tf() . '</lastmod>'
             . '<changefreq>hourly</changefreq><priority>0.50</priority></url>'
             . PHP_EOL;
    }
    $txt .=
    '</urlset>
    
    ';

    if (file_put_contents(ROOT_PATH . '/sitemap_forum.xml', $txt) === false) {
        stderr('Ошибка!', 'Невозможно записать файл!');
    }
}

function tf(?string $t = null): string {
    if ($t === null) {
        return date('c');
    }
    return date('c', strtotime($t));
}

#
#    GOOGLE SITEMAP CREATION
#        by n-sw-bit
#
#
?>