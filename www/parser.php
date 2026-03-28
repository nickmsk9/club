<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require "include/bittorrent.php";
gzip();
dbconn();
stdhead("Парсер описания фильма", 'all');
begin_main_frame();
?>

<form method="post" action="">
<table cellpadding="5" cellspacing="0" border="0">
<tr>
    <td>Название фильма:</td>
    <td><input type="text" name="film_name" size="60" value="<?=htmlspecialchars($_POST['film_name'] ?? '')?>"></td>
</tr>
<tr>
    <td>Год выпуска:</td>
    <td><input type="text" name="film_year" size="6" value="<?=htmlspecialchars($_POST['film_year'] ?? '')?>"></td>
</tr>
<tr>
    <td colspan="2"><input type="submit" value="Получить описание"></td>
</tr>
</table>
</form>

<?php
function translit($s) {
    $rus = [
        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
        'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
    ];
    $lat = [
        'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','shch','','y','','e','yu','ya',
        'A','B','V','G','D','E','E','Zh','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','Ts','Ch','Sh','Shch','','Y','','E','Yu','Ya'
    ];
    return str_replace($rus, $lat, $s);
}

function wiki_desc_by_url($url, $context) {
    $html = @file_get_contents($url, false, $context);
    if (!$html) return '';
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//div[@class='mw-parser-output']/p");
    foreach ($paras as $p) {
        $txt = trim($p->textContent);
        if (
            mb_strlen($txt, 'UTF-8') > 40 &&
            !preg_match('/^(В Википедии есть статьи|Эта статья|См\\. также|Подробнее|Материал из Википедии|Для этого фильма|У этого термина)/ui', $txt)
        ) {
            return $txt;
        }
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['film_name'] ?? '');
    $year = trim($_POST['film_year'] ?? '');
    if (!$name) {
        echo '<p style="color:red;"><strong>Ошибка:</strong> введите название фильма!</p>';
    } else {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (compatible; WikiBot/1.0; +https://yourdomain.com)\r\n"
            ]
        ]);
        $rus = str_replace(' ', '_', $name);
        $ucRus = ucfirst($rus);
        $en  = str_replace(' ', '_', translit($name));

        // Самый вероятный первый вариант — сразу правильный!
        $variants = [
            "https://ru.wikipedia.org/wiki/{$ucRus}_(фильм,_{$year})",
            "https://ru.wikipedia.org/wiki/{$rus}_(фильм,_{$year})",
            "https://ru.wikipedia.org/wiki/{$ucRus}_(фильм)",
            "https://ru.wikipedia.org/wiki/{$rus}_(фильм)",
            "https://ru.wikipedia.org/wiki/{$ucRus}",
            "https://ru.wikipedia.org/wiki/{$rus}",
            "https://en.wikipedia.org/wiki/{$en}_({$year}_film)",
            "https://en.wikipedia.org/wiki/{$en}_film",
            "https://en.wikipedia.org/wiki/{$en}"
        ];

        $desc = '';
        $found_url = '';
        foreach ($variants as $u) {
            $desc = wiki_desc_by_url($u, $context);
            if ($desc) {
                $found_url = $u;
                break;
            }
        }

        if ($desc) {
            echo '<p>Найдено: <a href="' . htmlspecialchars($found_url) . '" target="_blank">' . htmlspecialchars($found_url) . '</a></p>';
            echo '<h2>Описание фильма:</h2><div style="font-size:1.1em; background:#fffbe3; border:1px solid #ddd; padding:12px; margin-bottom:2em;">'
                . nl2br(htmlspecialchars($desc)) . '</div>';
        } else {
            echo '<p><b>Описание не найдено.</b><br>Попробуй изменить название или ввести ссылку вручную.<br>404-ошибки — это этап подбора варианта, их можно игнорировать.</p>';
        }
    }
}

end_main_frame();
stdfoot();
?>