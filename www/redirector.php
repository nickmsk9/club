<?php
require_once 'include/bittorrent.php';
dbconn();

$url = $_GET['url'] ?? '';

if (!$url) {
    stderr($lang['error'], $lang['invalid_id']);
}

$scriptName = basename($_SERVER['SCRIPT_NAME']);

if (isset($_GET['do']) && $_GET['do'] === 'nodelay') {
    header('Location: ' . $url);
    exit();
} else {
    header('Refresh: 5; url=' . $scriptName . '?do=nodelay&url=' . urlencode($url));
}

stdhead('Переадресация...');
stdmsg(
    'Переадресация...',
    '<noindex>Вы покидаете ' . $SITENAMEL .
    ' и переходите на <b><a href="' . $DEFAULTBASEURL . '/' . $scriptName .
    '?do=nodelay&amp;url=' . urlencode($url) .
    '" rel="nofollow">' . htmlspecialchars($url) . '</a></b>.<br />' .
    'Вы будете переадресованы через <span id="delay">5</span> секунд.</noindex>'
);
?>
<script type="text/javascript">
let x = 5;
function countdown() {
    if (x > 0) {
        document.getElementById('delay').textContent = x;
        x--;
        setTimeout(countdown, 1000);
    }
}
countdown();
</script>
<?php
stdfoot();
?>