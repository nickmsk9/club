<?php
declare(strict_types=1);
#
#    GOOGLE SITEMAP CREATION
#        by n-sw-bit
#
#
require_once dirname(__DIR__, 2) . '/include/bittorrent.php';
dbconn();
gensitemap_articles();


function gensitemap_articles() {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? '');
    $txt  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $txt .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
         . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
         . 'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 '
         . 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'
         . PHP_EOL;

    $sql = sql_query("SELECT id FROM article_categories");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/articles.php?category=' . urlencode((string)$a['id'])
             . '</loc><lastmod>' . t() . '</lastmod>'
             . '<changefreq>hourly</changefreq><priority>0.70</priority></url>'
             . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM articles");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/articleDetails.php?article=' . urlencode((string)$a['id'])
             . '</loc><lastmod>' . t() . '</lastmod>'
             . '<changefreq>hourly</changefreq><priority>0.50</priority></url>'
             . PHP_EOL;
    }

    $txt .= '</urlset>' . PHP_EOL . PHP_EOL;

    if (file_put_contents(ROOT_PATH . '/sitemap_articles.xml', $txt) === false) {
        stderr('Ошибка!', 'Невозможно записать файл!');
    }
}

function t(?string $t = null): string {
    if ($t === null) {
        return date('c'); //2004-02-12T15:19:21+00:00
    }
    return date('c', strtotime($t));
}

#
#    GOOGLE SITEMAP CREATION
#        by n-sw-bit
#
#
?>