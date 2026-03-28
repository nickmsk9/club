<?php
require "./include/bittorrent.php";
dbconn(false);
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR ) stderr("Ошибка", "Доступ запрещён.");

$file = __DIR__ . "/logs/anime_access.log";
$bad = array();
$txt = ""; // Инициализация переменной $txt

if(isset($_GET['do']) && $_GET['do']=="clean"){
    $handle = fopen($file, "w");
    fwrite($handle, "");
    fclose($handle);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$log_dir = dirname($file);
if (!is_dir($log_dir)) {
    die("Ошибка: директория $log_dir не существует!");
}

if (!is_writable($log_dir)) {
    die("Ошибка: директория $log_dir недоступна для записи!");
}

if (!file_exists($file)) {
    file_put_contents($file, '');
}

$handle = @fopen($file, "r");

if(!$handle) die('log <b>'.$file.'</b> not opened!');

// Проверяем размер файла перед чтением
$file_size = filesize($file);
if ($file_size > 0) {
    $contents = fread($handle, $file_size);
} else {
    $contents = '';
}
fclose($handle);

$strings = explode("\n",$contents);
$count = count($strings);

if(isset($_GET['do']) && $_GET['do']=="ip"){
    $ip = strval($_GET['ip']);
    for($i=0; $i < $count; $i++){
        $str = $strings[$i];
        $pos = strpos($str," ");
        if ($pos === false) continue;
        $cip = substr($str,0,$pos);
        if($ip == $cip){
            $addr = substr($str, strpos($str,"\""), 175);
            echo htmlspecialchars($addr) . "<br>";
        }
    }
    exit;
}

$ips = array();

for($i=0; $i < $count; $i++){
    $str = $strings[$i];
    $pos = strpos($str," ");
    if ($pos === false) continue;
    $ip = substr($str, 0, $pos);
    if(isset($ips[$ip])){
        $ips[$ip] += 1;
    } else {
        $ips[$ip] = 1;
    }
}

uasort($ips, "cmp");

stdhead("IP");
begin_main_frame();

if(!is_writable($file)) echo "Файл незаписываемый<br>";
if(!is_readable($file)) echo "Файл нечитаемый<br>";

?>

<center>
    <input type="button" onClick="document.location='<?= $_SERVER['PHP_SELF'] ?>';" value="Обновить"> | 
    <input type="button" onClick="document.location='<?= $_SERVER['PHP_SELF'] ?>?do=clean';" value="Очистить">
</center>
<br>
Зелёные - пользователи

<?php
$result = printarr($ips);

begin_frame("IP");

foreach($bad as $ip){
    $val = "iptables -A INPUT -s ".$ip."/255.255.255.0 -j DROP";
    $txt .= $val."\n";
    if(isset($_GET['do']) && $_GET['do']=="ban"){
        exec($val);
        print($val.'<br>');
    }
}

echo "Их стоит забанить (более 500 незарегистрированных соединений):<br>";
echo '<textarea cols="70" rows="10" onClick="this.select();">'.htmlspecialchars($txt).'</textarea><br>';
echo '<input type="button" onClick="document.location=\''.FILE.'?do=ban\';" value="Забанить"><br>';

begin_table();
echo "<tr><td class='row2'>IP</td><td class='row2'>Кол-во</td><td class='row2'>ban</td></tr>";
uasort($ips, "cmp");
echo $result;
end_table();
end_frame();
end_main_frame();
stdfoot();

function cmp($a, $b){
    if ($a == $b){
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function printarr($arr){
    $ret = "";
    global $bad;
    $c = 0;
    foreach ($arr as $key => $value) {
        if ($c >= 50) break;
        $c++;
        $a = sql_query("SELECT COUNT(*) FROM users WHERE ip='".sqlesc($key)."'");
        $a = mysqli_fetch_row($a);
        $a = $a[0];
        $b = sql_query("SELECT COUNT(*) FROM peers WHERE ip='".sqlesc($key)."'");
        $b = mysqli_fetch_row($b);
        $b = $b[0];

        $a += $b;

        $color = ($a > 0) ? "green" : "red";
        if ($key !== "127.0.0.1" && $key !== "0") {
            if ($a == 0 && $c < 40 && $value > 500) $bad[] = $key;
            $ret .= "<tr><td class='row2'><b>".$c."</b> <a target='_blank' href='/usersearch.php?ip=".htmlspecialchars($key)."' style=\"color:".$color.";\">&#10070;</a> ".$key."</td><td class='row1'><a href='".$_SERVER['PHP_SELF']."?do=ip&ip=".htmlspecialchars($key)."'>".$value."</a></td><td class='row1'><textarea onClick='this.select();' cols='85' rows='1'>iptables -A INPUT -s ".$key."/255.255.255.0 -j DROP\n</textarea></td></tr>\n";
        }
    }
    return $ret;
}

function str($a){
    return htmlspecialchars($a);
}
?>