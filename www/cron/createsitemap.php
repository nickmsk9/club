<?php
declare(strict_types=1);
#
#    GOOGLE SITEMAP CREATION
#        by n-sw-bit
#
#
require_once dirname(__DIR__, 2) . '/include/bittorrent.php';
global $dbconn, $friendly_title;
dbconn();
gensitemap();


function gensitemap(){
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? '');

    $txt  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $txt .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
         . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
         . 'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 '
         . 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'
         . PHP_EOL;

    $txt .= '<url><loc>' . $baseUrl . '/index.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/sitemap.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/browse</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/afisha.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/forum</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/faq.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/articles.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/rules.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;
    $txt .= '<url><loc>' . $baseUrl . '/anewsarchive.php</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;

    $txt .= '<url><loc>' . $baseUrl . '/</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>1</priority></url>' . PHP_EOL;

    $sql = sql_query("SELECT id,added, name FROM torrents ORDER BY id DESC");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>'
             . $baseUrl . '/details/id' . $a['id'] . '-' . friendly_title($a['name'])
             . '</loc><lastmod>' . gmdate('c', (int)$a['added']) . '</lastmod>'
             . '<changefreq>daily</changefreq><priority>0.50</priority></url>'
             . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM categories");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>' . $baseUrl . '/browse/cat' . urlencode((string)$a['id']) . '</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>0.50</priority></url>' . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM album");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>' . $baseUrl . '/album.php?id=' . urlencode((string)$a['id']) . '</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>0.50</priority></url>' . PHP_EOL;
    }

    $sql = sql_query("SELECT id FROM albumcat");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>' . $baseUrl . '/allalbum.php?cat=' . urlencode((string)$a['id']) . '</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>0.50</priority></url>' . PHP_EOL;
    }
    $sql = sql_query("SELECT id FROM anews");
    while ($a = mysqli_fetch_assoc($sql)) {
        $txt .= '<url><loc>' . $baseUrl . '/anewsoverview.php?id=' . urlencode((string)$a['id']) . '</loc><lastmod>' . t() . '</lastmod><changefreq>hourly</changefreq><priority>0.50</priority></url>' . PHP_EOL;
    }
    $txt .= '</urlset>' . PHP_EOL;

    if (file_put_contents(ROOT_PATH . '/sitemap.xml', $txt) === false) {
        stderr('Ошибка!', 'Невозможно записать файл!');
    }
}

function t(?string $t = null): string {
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