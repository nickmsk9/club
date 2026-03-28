<?

require "include/bittorrent.php";

gzip();

// 0 - No debug; 1 - Show and run SQL query; 2 - Show SQL query only
$DEBUG_MODE = 0;
/*
function get_user_icons($arr, $big = false)
{
	if ($big)
	{
		$donorpic = "starbig.gif";
		$warnedpic = "warnedbig.gif";
		$disabledpic = "disabledbig.gif";
	}
	else
	{
		$donorpic = "star.gif";
		$warnedpic = "warned.gif";
		$disabledpic = "disabled.gif";
	}
	$pics = $arr["donor"] == "yes" ? "<img src=pic/$donorpic alt='Donor' border=0 style=\"margin-left: 2pt\">" : "";
	if ($arr["enabled"] == "yes")
		$pics .= $arr["warned"] == "yes" ? "<img src=pic/$warnedpic alt=\"Warned\" border=0>" : "";
	else
		$pics .= "<img src=pic/$disabledpic alt=\"Disabled\" border=0 style=\"margin-left: 2pt\">\n";
	return $pics;
}
*/

dbconn();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
	stderr($tracker_lang['error'], "Отказано в доступе.");

stdhead("Административный поиск");
begin_main_frame();
echo "<h1>Административный поиск</h1>\n";

// Безопасно проверяем наличие параметра h в $_GET
if (!empty($_GET['h']))
{
	begin_frame("Инструкция<font color=#009900> - Читать обязательно</font>");
?>
<ul>
<li>Пустые поля будут проигнорированы</li>
<li>Шаблоны * и ? могут быть использованы в Имени, Email и Комментариях, так-же и в нескольких значениях разделенными пробелами (т.е. 'wyz Max*' в Имени выведет обоих пользователей
'wyz' и тех у которых имена начинаються на 'Max'. Похожим образом может быть использована '~' для отрицания, т.е. '~alfiest' в комментариях ограничит поиск пользователей
к тем у которых нету выражения 'alfiest' в ихних комментариях).</li>
<li>Поле Рейтинг принимает 'Inf' и '---' наравне с числовыми значениями.</li>
<li>Маска подсети может быть введена или в десятично точечной или CIDR записи
(т.е. 255.255.255.0 то-же самое что и /24).</li>
<li>Раздал и Скачал вводиться в GB.</li>
<li>For search parameters with multiple text fields the second will be
ignored unless relevant for the type of search chosen.</li>
<li>'Только активных' ограничивает поиск к тем пользователям которые сейчас что-то качают или раздают,
'Отключенные IP' к тем чьи IP отключены.</li>
<li>The 'p' columns in the results show partial stats, that is, those
of the torrents in progress.</li>
<li>Колонка история отображает количество постов в форуме и комментариев к торрентам,
соотвественно, как и ведет на страницу истории.
<?
	end_frame();
}
else
{
	echo "<p align=center>(<a href='".$_SERVER["PHP_SELF"]."?h=1'>Инструкиця</a>)";
	echo "&nbsp;-&nbsp;(<a href='".$_SERVER["PHP_SELF"]."'>Сброс</a>)</p>\n";
}

$highlight = " bgcolor=#BBAF9B";

?>

<form method=get action=<?=$_SERVER["PHP_SELF"]?>>
<table border="1" cellspacing="0" cellpadding="5">
<tr>

  <td valign="middle" class=rowhead>Имя:</td>
  <td<?= (isset($_GET['n']) && $_GET['n'] ? $highlight : "") ?>><input name="n" type="text" value="<?= (isset($_GET['n']) ? htmlspecialchars($_GET['n']) : '') ?>" size=35></td>

  <td valign="middle" class=rowhead>Рейтинг:</td>
  <td<?= (isset($_GET['r']) && $_GET['r'] ? $highlight : "") ?>><select name="rt">
    <?
     $options = array("равен","выше","ниже","между");
    for ($i = 0; $i < count($options); $i++){
        echo "<option value=$i ".((isset($_GET['rt']) && (int)$_GET['rt'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select>
    <input name="r" type="text" value="<?= (isset($_GET['r']) ? htmlspecialchars($_GET['r']) : '') ?>" size="5" maxlength="4">
    <input name="r2" type="text" value="<?= (isset($_GET['r2']) ? htmlspecialchars($_GET['r2']) : '') ?>" size="5" maxlength="4"></td>

  <td valign="middle" class=rowhead>Статус:</td>
  <td<?= (isset($_GET['st']) && $_GET['st'] ? $highlight : "") ?>><select name="st">
    <?
    $options = array("(Любой)","Подтвержден","Не подтвержден");
    for ($i = 0; $i < count($options); $i++){
        echo "<option value=$i ".((isset($_GET['st']) && (int)$_GET['st'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select></td></tr>
<tr><td valign="middle" class=rowhead>Email:</td>
  <td<?= (isset($_GET['em']) && $_GET['em'] ? $highlight : "") ?>><input name="em" type="text" value="<?= (isset($_GET['em']) ? htmlspecialchars($_GET['em']) : '') ?>" size="35"></td>
  <td valign="middle" class=rowhead>IP:</td>
  <td<?= (isset($_GET['ip']) && $_GET['ip'] ? $highlight : "") ?>><input name="ip" type="text" value="<?= (isset($_GET['ip']) ? htmlspecialchars($_GET['ip']) : '') ?>" maxlength="17"></td>

  <td valign="middle" class=rowhead>Отключен:</td>
  <td<?= (isset($_GET['as']) && $_GET['as'] ? $highlight : "") ?>><select name="as">
    <?
   $options = array("(Любой)","Нет","Да");
    for ($i = 0; $i < count($options); $i++){
      echo "<option value=$i ".((isset($_GET['as']) && (int)$_GET['as'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select></td></tr>
<tr>
  <td valign="middle" class=rowhead>Комментарий:</td>
  <td<?= (isset($_GET['co']) && $_GET['co'] ? $highlight : "") ?>><input name="co" type="text" value="<?= (isset($_GET['co']) ? htmlspecialchars($_GET['co']) : '') ?>" size="35"></td>
  <td valign="middle" class=rowhead>Маска:</td>
  <td<?= (isset($_GET['ma']) && $_GET['ma'] ? $highlight : "") ?>><input name="ma" type="text" value="<?= (isset($_GET['ma']) ? htmlspecialchars($_GET['ma']) : '') ?>" maxlength="17"></td>
  <td valign="middle" class=rowhead>Класс:</td>
  <td<?= (isset($_GET['c']) && (int)$_GET['c'] && (int)$_GET['c'] != 1 ? $highlight : "") ?>><select name="c"><option value='1'>(Любой)</option>
  <?
  // Корректно инициализируем переменную $class и формируем список классов
  if (!isset($_GET['c']) || !is_valid_id($_GET['c'])) {
      $class = '';
  }
  $class = (isset($_GET['c']) ? (int)$_GET['c'] : 0);
  if (get_user_class() == UC_SYSOP)
    $maxclass = UC_ADMINISTRATOR;
  elseif (get_user_class() == UC_ADMINISTRATOR)
    $maxclass = UC_UPLOADER;
  else
    $maxclass = get_user_class() - 0;
  // Формируем опции без неинициализированных переменных
  for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=\"$i\"" . ($class  == $i ? " selected" : "") . ">" . get_user_class_name($i) . "\n");
  ?>
    </select></td></tr>
<tr>

    <td valign="middle" class=rowhead>Регистрация:</td>

  <td<?= (isset($_GET['d']) && $_GET['d'] ? $highlight : "") ?>><select name="dt">
    <?
    $options = array("в","раньше","после","между");
    for ($i = 0; $i < count($options); $i++){
      echo "<option value=$i ".((isset($_GET['dt']) && (int)$_GET['dt'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select>

   <input name="d" type="text" value="<?= (isset($_GET['d']) ? htmlspecialchars($_GET['d']) : '') ?>" size="12" maxlength="10">

    <input name="d2" type="text" value="<?= (isset($_GET['d2']) ? htmlspecialchars($_GET['d2']) : '') ?>" size="12" maxlength="10"></td>


  <td valign="middle" class=rowhead>Раздал:</td>

  <td<?= (isset($_GET['ul']) && $_GET['ul'] ? $highlight : "") ?>><select name="ult" id="ult">
    <?
    $options = array("ровно","больше","меньше","между");
    for ($i = 0; $i < count($options); $i++){
        echo "<option value=$i ".((isset($_GET['ult']) && (int)$_GET['ult'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select>

     <input name="ul" type="text" id="ul" size="8" maxlength="7" value="<?= (isset($_GET['ul']) ? htmlspecialchars($_GET['ul']) : '') ?>">

    <input name="ul2" type="text" id="ul2" size="8" maxlength="7" value="<?= (isset($_GET['ul2']) ? htmlspecialchars($_GET['ul2']) : '') ?>"></td>
  <td valign="middle" class="rowhead">Донор:</td>

  <td<?= (isset($_GET['do']) && $_GET['do'] ? $highlight : "") ?>><select name="do">
    <?
    $options = array("(Любой)","Да","Нет");
    for ($i = 0; $i < count($options); $i++){
      echo "<option value=$i ".((isset($_GET['do']) && (int)$_GET['do'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select></td></tr>
<tr>

 <td valign="middle" class=rowhead>Последняя активность:</td>

  <td <?= (isset($_GET['ls']) && $_GET['ls'] ? $highlight : "") ?>><select name="lst">
  <?
  $options = array("в","раньше","после","между");
  for ($i = 0; $i < count($options); $i++){
    echo "<option value=$i ".((isset($_GET['lst']) && (int)$_GET['lst'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
  }
  ?>
  </select>

  <input name="ls" type="text" value="<?= (isset($_GET['ls']) ? htmlspecialchars($_GET['ls']) : '') ?>" size="12" maxlength="10">

  <input name="ls2" type="text" value="<?= (isset($_GET['ls2']) ? htmlspecialchars($_GET['ls2']) : '') ?>" size="12" maxlength="10"></td>
      <td valign="middle" class=rowhead>Скачал:</td>

  <td<?= (isset($_GET['dl']) && $_GET['dl'] ? $highlight : "") ?>><select name="dlt" id="dlt">
  <?
    $options = array("ровно","больше","меньше","между");
    for ($i = 0; $i < count($options); $i++){
      echo "<option value=$i ".((isset($_GET['dlt']) && (int)$_GET['dlt'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
    }
    ?>
    </select>

    <input name="dl" type="text" id="dl" size="8" maxlength="7" value="<?= (isset($_GET['dl']) ? htmlspecialchars($_GET['dl']) : '') ?>">

    <input name="dl2" type="text" id="dl2" size="8" maxlength="7" value="<?= (isset($_GET['dl2']) ? htmlspecialchars($_GET['dl2']) : '') ?>"></td>

     <td valign="middle" class=rowhead>Предупрежден:</td>

    <td<?= (isset($_GET['w']) && $_GET['w'] ? $highlight : "") ?>><select name="w">
  <?
  $options = array("(Любой)","Да","Нет");
    for ($i = 0; $i < count($options); $i++){
        echo "<option value=$i ".((isset($_GET['w']) && (int)$_GET['w'] == $i) ? "selected" : "").">".$options[$i]."</option>\n";
  }
  ?>
    </select></td></tr>

<tr><td class="rowhead"></td><td></td>
  <td valign="middle" class=rowhead>Только&nbsp;активные:</td>
    <td<?= (isset($_GET['ac']) && $_GET['ac'] ? $highlight : "") ?>><input name="ac" type="checkbox" value="1" <?= (isset($_GET['ac']) && $_GET['ac'] ? "checked" : "") ?>></td>
  <td valign="middle" class=rowhead>Забаненые&nbsp;IP: </td>
  <td<?= (isset($_GET['dip']) && $_GET['dip'] ? $highlight : "") ?>><input name="dip" type="checkbox" value="1" <?= (isset($_GET['dip']) && $_GET['dip'] ? "checked" : "") ?>></td>
  </tr>
<tr><td colspan="6" align=center><input name="submit" type=submit class=btn value=Искать></td></tr>
</table>
<br /><br />
</form>

<?

// Validates date in the form [yy]yy-mm-dd;
// Returns date if valid, 0 otherwise.
function mkdate($date){
  if (strpos($date,'-'))
  	$a = explode('-', $date);
  elseif (strpos($date,'/'))
  	$a = explode('/', $date);
  else
  	return 0;
  for ($i=0;$i<3;$i++)
  	if (!is_numeric($a[$i]))
    	return 0;
    if (checkdate($a[1], $a[2], $a[0]))
    	return  date ("Y-m-d", mktime (0,0,0,$a[1],$a[2],$a[0]));
    else
			return 0;
}

// ratio as a string
function ratios($up,$down, $color = True)
{
	if ($down > 0)
	{
		$r = number_format($up / $down, 2);
    if ($color)
			$r = "<font color=".get_ratio_color($r).">$r</font>";
	}
	else
		if ($up > 0)
	  	$r = "Inf.";
	  else
	  	$r = "---";
	return $r;
}

// checks for the usual wildcards *, ? plus mySQL ones
function haswildcard($text){
	if (strpos($text,'*') === False && strpos($text,'?') === False
			&& strpos($text,'%') === False && strpos($text,'_') === False)
  	return False;
  else
  	return True;
}

///////////////////////////////////////////////////////////////////////////////

// Безопасно проверяем наличие параметра h в $_GET
if (count($_GET) > 0 && !empty($_GET['h']))
{
    // Имя пользователя (username) - фильтрация по wildcards, инклюзив и эксклюзив фильтры
    // Всегда инициализируем массивы для включающих и исключающих фильтров
    $names_inc = [];
    $names_exc = [];
    $n_param = (isset($_GET['n']) ? $_GET['n'] : '');
    $names = explode(' ',trim(htmlspecialchars($n_param)));
    if ($names[0] !== "") {
        // Разделяем имена на включающие и исключающие
        foreach($names as $name) {
            if (substr($name,0,1) == '~') {
                if ($name == '~') continue;
                $names_exc[] = substr($name,1);
            } else {
                $names_inc[] = $name;
            }
        }
        // Обработка включающих фильтров
        $name_is = [];
        if (count($names_inc)) {
            // Собираем условия для всех включающих фильтров
            foreach($names_inc as $name) {
                // Обработка wildcards: * и ? -> % и _
                if (!haswildcard($name))
                    $name_is[] = "u.username = ".sqlesc($name);
                else {
                    $name = str_replace(['?','*'], ['_','%'], $name);
                    $name_is[] = "u.username LIKE ".sqlesc($name);
                }
            }
            // Добавляем условие только если есть элементы
            if (count($name_is)) {
                // Конкатенация с where_is
                $where_is .= ($where_is !== "" ? " AND " : "") . "(" . implode(" OR ", $name_is) . ")";
            }
        }
        // Сброс массива условий
        $name_is = [];
        // Обработка исключающих фильтров
        if (count($names_exc)) {
            foreach($names_exc as $name) {
                // Обработка wildcards: * и ? -> % и _
                if (!haswildcard($name))
                    $name_is[] = "u.username = ".sqlesc($name);
                else {
                    $name = str_replace(['?','*'], ['_','%'], $name);
                    $name_is[] = "u.username LIKE ".sqlesc($name);
                }
            }
            // Добавляем условие только если есть элементы
            if (count($name_is)) {
                // Конкатенация с where_is для NOT
                $where_is .= ($where_is !== "" ? " AND NOT (" : "NOT (") . implode(" OR ", $name_is) . ")";
            }
        }
        // Сброс массива условий
        $name_is = [];
        $q .= ($q ? "&amp;" : "") . "n=".urlencode(trim(htmlspecialchars($_GET['n'])));
    }

    // Email - фильтрация по wildcards, всегда через массив условий
    $email_is = [];
    $em_param = (isset($_GET['em']) ? $_GET['em'] : '');
    $emaila = explode(' ', trim(htmlspecialchars($em_param)));
    if ($emaila[0] !== "") {
        foreach($emaila as $email) {
            // Обработка wildcards: * и ? -> % и _
            if (strpos($email,'*') === false && strpos($email,'?') === false && strpos($email,'%') === false) {
                if (validemail($email) !== 1) {
                    stdmsg($tracker_lang['error'], "Неправильный E-mail.");
                    stdfoot();
                    die();
                }
                $email_is[] = "u.email = ".sqlesc($email);
            } else {
                $sql_email = str_replace(['?','*'], ['_','%'], $email);
                $email_is[] = "u.email LIKE ".sqlesc($sql_email);
            }
        }
        // Добавляем условие только если есть элементы
        if (count($email_is)) {
            $where_is .= ($where_is !== "" ? " AND " : "") . "(" . implode(" OR ", $email_is) . ")";
        }
        // Сброс массива условий
        $email_is = [];
        $q .= ($q ? "&amp;" : "") . "em=".urlencode(trim(htmlspecialchars($_GET['em'])));
    }

  //class
  // NB: the c parameter is passed as two units above the real one
  $c_param = (isset($_GET['c']) ? $_GET['c'] : 0);
  $class = $c_param - 20;
  if (is_valid_id($class + 10))
  {
    $where_is .= (isset($where_is)?" AND ":"")."u.class=$class";
    $q .= ($q ? "&amp;" : "") . "c=".($class+20);
  }

  // IP
  // Безопасно получаем параметр ip
  $ip_param = (isset($_GET['ip']) ? $_GET['ip'] : '');
  $ip = trim(htmlspecialchars($ip_param));
  if ($ip)
  {
  	$regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
    if (!preg_match($regex, $ip))
    {
    	stdmsg($tracker_lang['error'], "Неверный IP.");
    	stdfoot();
    	die();
    }

    $mask = (isset($_GET['ma']) ? trim($_GET['ma']) : '');
    if ($mask == "" || $mask == "255.255.255.255")
    	$where_is .= (isset($where_is)?" AND ":"")."u.ip = '$ip'";
    else
    {
    	if (substr($mask,0,1) == "/")
    	{
      	$n = substr($mask, 1, strlen($mask) - 1);
        if (!is_numeric($n) or $n < 0 or $n > 32)
        {
        	stdmsg($tracker_lang['error'], "Неверная макса подсети.");
        	stdfoot();
          die();
        }
        else
	      	$mask = long2ip(pow(2,32) - pow(2,32-$n));
      }
      elseif (!preg_match($regex, $mask))
      {
				stdmsg($tracker_lang['error'], "Неверная макса подсети.");
				stdfoot();
	      die();
      }
      $where_is .= (isset($where_is)?" AND ":"")."INET_ATON(u.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
      $q .= ($q ? "&amp;" : "") . "ma=$mask";
    }
    $q .= ($q ? "&amp;" : "") . "ip=$ip";
  }

  // ratio
  // Безопасно получаем параметр r
  $r_param = (isset($_GET['r']) ? $_GET['r'] : '');
  $ratio = trim(htmlspecialchars($r_param));
  if ($ratio)
  {
  	if ($ratio == '---')
  	{
    	$ratio2 = "";
      $where_is .= isset($where_is)?" AND ":"";
      $where_is .= " u.uploaded = 0 and u.downloaded = 0";
    }
    elseif (strtolower(substr($ratio,0,3)) == 'inf')
    {
    	$ratio2 = "";
      $where_is .= isset($where_is)?" AND ":"";
      $where_is .= " u.uploaded > 0 and u.downloaded = 0";
    }
    else
    {
    	if (!is_numeric($ratio) || $ratio < 0)
    	{
      	stdmsg($tracker_lang['error'], "Неверный рейтинг.");
      	stdfoot();
        die();
      }
	  $where_is .= isset($where_is)?" AND ":"";
      $where_is .= " (u.uploaded/u.downloaded)";
      $ratiotype = (int) $_GET['rt'];  
      $q .= ($q ? "&amp;" : "") . "rt=$ratiotype";
      if ($ratiotype == "3")
      {
        // Безопасно получаем параметр r2
        $ratio2_param = (isset($_GET['r2']) ? $_GET['r2'] : '');
        $ratio2 = trim(htmlspecialchars($ratio2_param));
        if(!$ratio2)  
        {
        	stdmsg($tracker_lang['error'], "Нужны два рейтинга для этого типа поиска.");
        	stdfoot();
          die();
        }
        if (!is_numeric($ratio2) or $ratio2 < $ratio)
        {
        	stdmsg($tracker_lang['error'], "Плохой второй рейтинг.");
        	stdfoot();
        	die();
        }
        $where_is .= " BETWEEN $ratio and $ratio2";
        $q .= ($q ? "&amp;" : "") . "r2=$ratio2";
      }
      elseif ($ratiotype == "2")
      	$where_is .= " < $ratio";
      elseif ($ratiotype == "1")
      	$where_is .= " > $ratio";
      else
      	$where_is .= " BETWEEN ($ratio - 0.004) and ($ratio + 0.004)";
    }
    $q .= ($q ? "&amp;" : "") . "r=$ratio";
  }

    // Комментарий - фильтрация по wildcards, инклюзив и эксклюзив фильтры
    $comments_inc = [];
    $comments_exc = [];
    $co_param = (isset($_GET['co']) ? $_GET['co'] : '');
    $comments = explode(' ',trim(htmlspecialchars($co_param)));
    if ($comments[0] !== "") {
        foreach($comments as $comment) {
            if (substr($comment,0,1) == '~') {
                if ($comment == '~') continue;
                $comments_exc[] = substr($comment,1);
            } else {
                $comments_inc[] = $comment;
            }
        }
        // Обработка включающих фильтров
        $comment_is = [];
        if (count($comments_inc)) {
            foreach($comments_inc as $comment) {
                // Обработка wildcards: * и ? -> % и _
                if (!haswildcard($comment))
                    $comment_is[] = "u.modcomment LIKE ".sqlesc("%".$comment."%");
                else {
                    $comment = str_replace(['?','*'], ['_','%'], $comment);
                    $comment_is[] = "u.modcomment LIKE ".sqlesc($comment);
                }
            }
            // Добавляем условие только если есть элементы
            if (count($comment_is)) {
                $where_is .= ($where_is !== "" ? " AND " : "") . "(" . implode(" OR ", $comment_is) . ")";
            }
        }
        // Сброс массива условий
        $comment_is = [];
        // Обработка исключающих фильтров
        if (count($comments_exc)) {
            foreach($comments_exc as $comment) {
                // Обработка wildcards: * и ? -> % и _
                if (!haswildcard($comment))
                    $comment_is[] = "u.modcomment LIKE ".sqlesc("%".$comment."%");
                else {
                    $comment = str_replace(['?','*'], ['_','%'], $comment);
                    $comment_is[] = "u.modcomment LIKE ".sqlesc($comment);
                }
            }
            // Добавляем условие только если есть элементы
            if (count($comment_is)) {
                $where_is .= ($where_is !== "" ? " AND NOT (" : "NOT (") . implode(" OR ", $comment_is) . ")";
            }
        }
        // Сброс массива условий
        $comment_is = [];
        $q .= ($q ? "&amp;" : "") . "co=".urlencode(trim($_GET['co']));
    }

  $unit = 1073741824;		// 1GB

  // uploaded
  // Безопасно получаем параметр ul
  $ul_param = (isset($_GET['ul']) ? $_GET['ul'] : '');
  $ul = trim((int)$ul_param);
  if ($ul)
  {
  	if (!is_numeric($ul) || $ul < 0)
  	{
    	stdmsg($tracker_lang['error'], "Неправильное количество залитой информации.");
    	stdfoot();
      die();
    }
    $where_is .= isset($where_is)?" AND ":"";
    $where_is .= " u.uploaded ";
    $ultype = (int)$_GET['ult'];  
    $q .= ($q ? "&amp;" : "") . "ult=$ultype";
    if ($ultype == "3")
    {
      // Безопасно получаем параметр ul2
      $ul2_param = (isset($_GET['ul2']) ? $_GET['ul2'] : '');
      $ul2 = trim((int)$ul2_param);
      if(!$ul2)  
    	{
      	stdmsg($tracker_lang['error'], "Нужны два количества залитой информации для этого типа поиска.");
      	stdfoot();
        die();
      }
      if (!is_numeric($ul2) or $ul2 < $ul)
      {
      	stdmsg($tracker_lang['error'], "Неправильный второй параметр залитой информации.");
      	stdfoot();
        die();
      }
      $where_is .= " BETWEEN ".$ul*$unit." and ".$ul2*$unit;
      $q .= ($q ? "&amp;" : "") . "ul2=$ul2";
    }
    elseif ($ultype == "2")
    	$where_is .= " < ".$ul*$unit;
    elseif ($ultype == "1")
    	$where_is .= " >". $ul*$unit;
    else
    	$where_is .= " BETWEEN ".($ul - 0.004)*$unit." and ".($ul + 0.004)*$unit;
    $q .= ($q ? "&amp;" : "") . "ul=$ul";
  }

  // downloaded
  // Безопасно получаем параметр dl
  $dl_param = (isset($_GET['dl']) ? $_GET['dl'] : '');
  $dl = trim((int)$dl_param);
  if ($dl)
  {
  	if (!is_numeric($dl) || $dl < 0)
  	{
    	stdmsg($tracker_lang['error'], "Bad downloaded amount.");
    	stdfoot();
      die();
    }
    $where_is .= isset($where_is)?" AND ":"";
    $where_is .= " u.downloaded ";
    $dltype = (int)$_GET['dlt'];
    $q .= ($q ? "&amp;" : "") . "dlt=$dltype";
    if ($dltype == "3")
    {
      // Безопасно получаем параметр dl2
      $dl2_param = (isset($_GET['dl2']) ? $_GET['dl2'] : '');
      $dl2 = trim((int)$dl2_param);
      if(!$dl2)  
      {
      	stdmsg($tracker_lang['error'], "Two downloaded amounts needed for this type of search.");
      	stdfoot();
        die();
      }
      if (!is_numeric($dl2) or $dl2 < $dl)
      {
      	stdmsg($tracker_lang['error'], "Bad second downloaded amount.");
      	stdfoot();
        die();
      }
      $where_is .= " BETWEEN ".$dl*$unit." and ".$dl2*$unit;
      $q .= ($q ? "&amp;" : "") . "dl2=$dl2";
    }
    elseif ($dltype == "2")
    	$where_is .= " < ".$dl*$unit;
    elseif ($dltype == "1")
     	$where_is .= " > ".$dl*$unit;
    else
     	$where_is .= " BETWEEN ".($dl - 0.004)*$unit." and ".($dl + 0.004)*$unit;
    $q .= ($q ? "&amp;" : "") . "dl=$dl";
  }

  // date joined
  // Безопасно получаем параметр d
  $d_param = (isset($_GET['d']) ? $_GET['d'] : '');
  $date = trim($d_param);
  if ($date)
  {
  	if (!$date = mkdate($date))
  	{
    	stdmsg($tracker_lang['error'], "Неправильная дата.");
    	stdfoot();
      die();
    }
    $q .= ($q ? "&amp;" : "") . "d=$date";
    $datetype = (int)$_GET['dt'];
        $q .= ($q ? "&amp;" : "") . "dt=$datetype";
    if ($datetype == "0")  
    // For mySQL 4.1.1 or above use instead
    // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
    $where_is .= (isset($where_is)?" AND ":"").
    		"(UNIX_TIMESTAMP(added) - UNIX_TIMESTAMP('$date')) BETWEEN 0 and 86400";
    else
    {
      $where_is .= (isset($where_is)?" AND ":"")."u.added ";
      if ($datetype == "3")
      {
        // Безопасно получаем параметр d2
        $d2_param = (isset($_GET['d2']) ? $_GET['d2'] : '');
        $date2 = mkdate(trim($d2_param));
        if ($date2)
        {
          if (!$date = mkdate($date))
          {
            stdmsg($tracker_lang['error'], "Неправильная дата.");
            stdfoot();
            die();
          }
          $q .= ($q ? "&amp;" : "") . "d2=$date2";
          $where_is .= " BETWEEN '$date' and '$date2'";
        }
        else
        {
          stdmsg($tracker_lang['error'], "Нужны две даты для этого типа поиска.");
          stdfoot();
          die();
        }
      }
      elseif ($datetype == "1")
        $where_is .= "< '$date'";
      elseif ($datetype == "2")
        $where_is .= "> '$date'";
    }
  }

	// date last seen
  // Безопасно получаем параметр ls
  $ls_param = (isset($_GET['ls']) ? $_GET['ls'] : '');
  $last = trim($ls_param);
  if ($last)
  {
  	if (!$last = mkdate($last))
  	{
    	stdmsg($tracker_lang['error'], "Неправильная дата.");
    	stdfoot();
      die();
    }
    $q .= ($q ? "&amp;" : "") . "ls=$last";
    $lasttype = (int)$_GET['lst'];
    $q .= ($q ? "&amp;" : "") . "lst=$lasttype";
    if ($lasttype == "0")
    // For mySQL 4.1.1 or above use instead
    // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
    	$where_is .= (isset($where_is)?" AND ":"").
      		"(UNIX_TIMESTAMP(last_access) - UNIX_TIMESTAMP('$last')) BETWEEN 0 and 86400";
    else
    {
    	$where_is .= (isset($where_is)?" AND ":"")."u.last_access ";
      if ($lasttype == "3")
      {
        // Безопасно получаем параметр ls2
        $ls2_param = (isset($_GET['ls2']) ? $_GET['ls2'] : '');
        $last2 = mkdate(trim($ls2_param));
        if ($last2)
        {
        	$where_is .= " BETWEEN '$last' and '$last2'";
	        $q .= ($q ? "&amp;" : "") . "ls2=$last2";
        }
        else
        {
        	stdmsg($tracker_lang['error'], "Вторая дата неверна.");
        	stdfoot();
        	die();
        }
      }
      elseif ($lasttype == "1")
    		$where_is .= "< '$last'";
      elseif ($lasttype == "2")
      	$where_is .= "> '$last'";
    }
  }

  // status
  // Безопасно получаем параметр st
  $st_param = (isset($_GET['st']) ? $_GET['st'] : '');
  $status = (int)$st_param;
  if ($status)
  {
  	$where_is .= ((isset($where_is))?" AND ":"");
    if ($status == "1")
    	$where_is .= "u.status = 'confirmed'";
    else
    	$where_is .= "u.status = 'pending'";
    $q .= ($q ? "&amp;" : "") . "st=$status";
  }

  // account status
  // Безопасно получаем параметр as
  $as_param = (isset($_GET['as']) ? $_GET['as'] : '');
  $accountstatus = (int)$as_param;
  if ($accountstatus)
  {
  	$where_is .= (isset($where_is))?" AND ":"";
    if ($accountstatus == "1")
    	$where_is .= " u.enabled = 'yes'";
    else
    	$where_is .= " u.enabled = 'no'";
    $q .= ($q ? "&amp;" : "") . "as=$accountstatus";
  }

  //donor
  // Безопасно получаем параметр do
  $do_param = (isset($_GET['do']) ? $_GET['do'] : '');
  $donor = (int)$do_param;
  if ($donor)
  {
		$where_is .= (isset($where_is))?" AND ":"";
    if ($donor == 1)
    	$where_is .= " u.donor = 'yes'";
    else
    	$where_is .= " u.donor = 'no'";
    $q .= ($q ? "&amp;" : "") . "do=$donor";
  }

  //warned
  // Безопасно получаем параметр w
  $w_param = (isset($_GET['w']) ? $_GET['w'] : '');
  $warned = (int)$w_param;
  if ($warned)
  {
		$where_is .= (isset($where_is))?" AND ":"";
    if ($warned == 1)
    	$where_is .= " u.warned = 'yes'";
    else
    	$where_is .= " u.warned = 'no'";
    $q .= ($q ? "&amp;" : "") . "w=$warned";
  }

  // disabled IP
// Безопасно получаем параметр dip
$dip_param = (isset($_GET['dip']) ? $_GET['dip'] : '');
$disabled = htmlspecialchars($dip_param);
  if ($disabled)
  {
  	$distinct = "DISTINCT ";
    $join_is .= " LEFT JOIN users AS u2 ON u.ip = u2.ip";
		$where_is .= ((isset($where_is))?" AND ":"")."u2.enabled = 'no'";
    $q .= ($q ? "&amp;" : "") . "dip=$disabled";
  }

  // active
  // Безопасно получаем параметр ac
  $ac_param = (isset($_GET['ac']) ? $_GET['ac'] : '');
  $active = (int)$ac_param;
  if ($active == "1")
  {
  	$distinct = "DISTINCT ";
    $join_is .= " LEFT JOIN peers AS p ON u.id = p.userid";
    $q .= ($q ? "&amp;" : "") . "ac=$active";
  }


  // Инициализируем переменные, если они не были определены
  if (!isset($where_is)) $where_is = "";
  if (!isset($join_is)) $join_is = "";
  if (!isset($q)) $q = "";
  $from_is = "users AS u".$join_is;
  $distinct = isset($distinct)?$distinct:"";

  $queryc = "SELECT COUNT(".$distinct."u.id) FROM ".$from_is.
      (($where_is == "")?"":" WHERE $where_is ");

  $querypm = "FROM ".$from_is.(($where_is == "")?" ":" WHERE $where_is ");

  $select_is = "u.id, u.username, u.email, u.status, u.added, u.last_access, u.ip,
    u.class, u.uploaded, u.downloaded, u.donor, u.modcomment, u.enabled, u.warned";

  $query = "SELECT ".$distinct." ".$select_is." ".$querypm;

//    <temporary>    /////////////////////////////////////////////////////
  if ($DEBUG_MODE > 0)
  {
  	stdmsg("Запрос подсчета",$queryc);
    echo "<BR><BR>";
    stdmsg("Поисковый запрос",$query);
    echo "<BR><BR>";
    stdmsg("URL ",$q);
    if ($DEBUG_MODE == 2)
    	die();
    echo "<BR><BR>";
  }
//    </temporary>   /////////////////////////////////////////////////////

  $res = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
  $arr = mysql_fetch_row($res);
  $count = $arr[0];

  $q = isset($q)?($q."&amp;"):"";

  $perpage = 30;

  list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"]."?".$q);

  $query .= $limit;

  $res = sql_query($query) or sqlerr(__FILE__, __LINE__);

  if (mysql_num_rows($res) == 0)
  	stdmsg("Внимание","Пользователь не был найден.");
  else
  {
  	if ($count > $perpage)
  		echo $pagertop;
    echo "<table border=1 cellspacing=0 cellpadding=5>\n";
    echo "<tr><td class=colhead align=left>Пользователь</td>
    		<td class=colhead align=left>Рейтинг</td>
        <td class=colhead align=left>IP</td>
        <td class=colhead align=left>Email</td>".
        "<td class=colhead align=left>Регистрация:</td>".
        "<td class=colhead align=left>Последняя активность:</td>".
        "<td class=colhead align=left>Статус</td>".
        "<td class=colhead align=left>Включен</td>".
        "<td class=colhead>pR</td>".
        "<td class=colhead>pUL</td>".
        "<td class=colhead>pDL</td>".
        "<td class=colhead>История</td></tr>";
    while ($user = mysql_fetch_array($res))
    {
    	if ($user['added'] == '0000-00-00 00:00:00')
      	$user['added'] = '---';
      if ($user['last_access'] == '0000-00-00 00:00:00')
      	$user['last_access'] = '---';

      if ($user['ip'])
      {
	    	$nip = ip2long($user['ip']);
        $auxres = sql_query("SELECT COUNT(*) FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
        $array = mysql_fetch_row($auxres);
    	  if ($array[0] == 0)
      		$ipstr = $user['ip'];
	  	  else
	      	$ipstr = "<a href='testip.php?ip=" . $user['ip'] . "'><font color='#FF0000'><b>" . $user['ip'] . "</b></font></a>";
			}
			else
      	$ipstr = "---";

      $auxres = sql_query("SELECT SUM(uploaded) AS pul, SUM(downloaded) AS pdl FROM peers WHERE userid = " . $user['id']) or sqlerr(__FILE__, __LINE__);
      $array = mysql_fetch_array($auxres);

      $pul = $array['pul'];
      $pdl = $array['pdl'];

      $n_posts = $n[0];

      $auxres = sql_query("SELECT COUNT(id) FROM comments WHERE user = ".$user['id']) or sqlerr(__FILE__, __LINE__);
			// Use LEFT JOIN to exclude orphan comments
      // $auxres = sql_query("SELECT COUNT(c.id) FROM comments AS c LEFT JOIN torrents as t ON c.torrent = t.id WHERE c.user = '".$user['id']."'") or sqlerr(__FILE__, __LINE__);
      $n = mysql_fetch_row($auxres);
      $n_comments = $n[0];

    	echo "<tr><td><b><a href='user/id" . $user['id'] . "'>" .
      		$user['username']."</a></b>" . get_user_icons($user) . "</td>" .
//      		($user["donor"] == "yes" ? "<img src=pic/star.gif alt=\"Donor\">" : "") .
//					($user["warned"] == "yes" ? "<img src=\"pic/warned.gif\" alt=\"Warned\">" : "") . "</td>
          "<td>" . ratios($user['uploaded'], $user['downloaded']) . "</td>
          <td>" . $ipstr . "</td><td>" . $user['email'] . "</td>
          <td><div align=center>" . $user['added'] . "</div></td>
          <td><div align=center>" . $user['last_access'] . "</div></td>
          <td><div align=center>" . $user['status'] . "</div></td>
          <td><div align=center>" . $user['enabled']."</div></td>
          <td><div align=center>" . ratios($pul,$pdl) . "</div></td>" .
          "<td><div align=right>" . mksize($pul) . "</div></td>
          <td><div align=right>" . mksize($pdl) . "</div></td>".
           "<td><div align=center>".($n_comments?"<a href=userhistory.php?action=viewcomments&id=".$user['id'].">$n_comments</a>":$n_comments).  
          "</div></td></tr>\n";
    }
    echo "</table>";
    if ($count > $perpage)
    	echo "$pagerbottom";

?>
    <br /><br />
    <form method=post action=message.php> 
      <table border="1" cellpadding="5" cellspacing="0"> 
        <tr> 
          <td> 
            <div align="center"> 
            Рассылка сообщений найденным юзерам<br /> 
              <input name="pmees" type="hidden" value="<?echo $querypm?>" size=10> 
              <input name="PM" type="submit" value="PM" class=btn> 
              <input name="n_pms" type="hidden" value="<?echo $count?>" size=10> 
              <input name="action" type="hidden" value="mass_pm" size=10> 
            </div></td> 
        </tr> 
      </table> 
    </form>
<?

  }
}

// Безопасно выводим меню, если переменные определены
if (isset($pagemenu)) echo "<p>$pagemenu<br />";
if (isset($browsemenu)) echo "$browsemenu</p>";
end_main_frame();
stdfoot();
die;

?>