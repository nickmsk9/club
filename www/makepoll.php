<?php

require "include/bittorrent.php";
dbconn();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
  stderr($tracker_lang['error'],$tracker_lang['access_denied']);

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pollid = (int)($_GET['pollid'] ?? $_POST['pollid'] ?? 0);

// Значения по умолчанию для полей опроса
$poll = [
    'question' => '',
    'option0'  => '',
    'option1'  => '',
    'option2'  => '',
    'option3'  => '',
    'option4'  => '',
    'option5'  => '',
    'option6'  => '',
    'option7'  => '',
    'option8'  => '',
    'sort'     => 'yes',
    'id'       => 0,
];

if ($action == "edit")
{
	if (!is_valid_id($pollid))
		stderr($tracker_lang['error'],$tracker_lang['invalid_id']);
	$res = sql_query("SELECT * FROM polls WHERE id = $pollid")
			or sqlerr(__FILE__, __LINE__);
	if (mysqli_num_rows($res) === 0)
		stderr($tracker_lang['error'],"No poll found with ID.");
	$poll = mysqli_fetch_assoc($res);
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($action=='edit' && !is_valid_id($pollid))
		stderr($tracker_lang['error'],$tracker_lang['invalid_id']);
  $question = isset($_POST["question"]) ? htmlspecialchars($_POST["question"]) : '';
  $option0 = isset($_POST["option0"]) ? htmlspecialchars($_POST["option0"]) : '';
  $option1 = isset($_POST["option1"]) ? htmlspecialchars($_POST["option1"]) : '';
  $option2 = isset($_POST["option2"]) ? htmlspecialchars($_POST["option2"]) : '';
  $option3 = isset($_POST["option3"]) ? htmlspecialchars($_POST["option3"]) : '';
  $option4 = isset($_POST["option4"]) ? htmlspecialchars($_POST["option4"]) : '';
  $option5 = isset($_POST["option5"]) ? htmlspecialchars($_POST["option5"]) : '';
  $option6 = isset($_POST["option6"]) ? htmlspecialchars($_POST["option6"]) : '';
  $option7 = isset($_POST["option7"]) ? htmlspecialchars($_POST["option7"]) : '';
  $option8 = isset($_POST["option8"]) ? htmlspecialchars($_POST["option8"]) : '';
  $sort = isset($_POST["sort"]) ? $_POST["sort"] : 'yes';
  $returnto = isset($_POST["returnto"]) ? htmlentities($_POST["returnto"]) : '';

  if (!$question || !$option0 || !$option1)
    stderr($tracker_lang['error'], "Заполните все поля формы!");

  if ($pollid)
		sql_query("UPDATE polls SET " .
		"question = " . sqlesc($question) . ", " .
		"option0 = " . sqlesc($option0) . ", " .
		"option1 = " . sqlesc($option1) . ", " .
		"option2 = " . sqlesc($option2) . ", " .
		"option3 = " . sqlesc($option3) . ", " .
		"option4 = " . sqlesc($option4) . ", " .
		"option5 = " . sqlesc($option5) . ", " .
		"option6 = " . sqlesc($option6) . ", " .
		"option7 = " . sqlesc($option7) . ", " .
		"option8 = " . sqlesc($option8) . ", " .
		"sort = " . sqlesc($sort) . " " .
    "WHERE id = $pollid") or sqlerr(__FILE__, __LINE__);
  else
  	sql_query("INSERT INTO polls VALUES(0" .
		", '" . get_date_time() . "'" .
    ", " . sqlesc($question) .
    ", " . sqlesc($option0) .
    ", " . sqlesc($option1) .
    ", " . sqlesc($option2) .
    ", " . sqlesc($option3) .
    ", " . sqlesc($option4) .
    ", " . sqlesc($option5) .
    ", " . sqlesc($option6) .
    ", " . sqlesc($option7) .
    ", " . sqlesc($option8) .
    ", " . sqlesc($sort) .
  	")") or sqlerr(__FILE__, __LINE__);

  if ($returnto == "main")
		header("Location: $DEFAULTBASEURL");
  elseif ($pollid)
		header("Location: $DEFAULTBASEURL/polls.php#$pollid");
	else
		header("Location: $DEFAULTBASEURL");
	die;
}

stdhead('Редактирование Опроса');
// Заголовок блока формы опроса
$frame_caption = $pollid ? 'Редактирование опроса' : 'Создание опроса';
begin_main_frame();
begin_frame($frame_caption, true);

if ($pollid)
	print("<h1>Опрос</h1>");
else
{
	// Warn if current poll is less than 3 days old
	$res = sql_query("SELECT question,added FROM polls ORDER BY added DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res);
	if ($arr)
	{
	  $hours = floor((gmtime() - sql_timestamp_to_unix_timestamp($arr["added"])) / 3600);
	  $days = floor($hours / 24);
	  if ($days < 3)
	  {
	    $hours -= $days * 24;
	    if ($days)
	      $t = "$days дней";
	    else
	      $t = "$hours часов";
	    print("<p><font color=red><b>Нынешний опрос - (<i>" . $arr["question"] . "</i>) был создан $t назад.</b></font></p>");
	  }
	}
	print("<h1>Создать опрос</h1>");
}
?>

<table border=0 cellspacing=0 cellpadding=5>
<form method="post" action="makepoll.php">
<tr><td class=rowhead>Вопрос <font color=red>*</font></td><td align=left><input name=question size=80 maxlength=255 value="<?=$poll['question']?>"></td></tr>
<tr><td class=rowhead>Ответ 1</td><td align=left><input name=option0 size=80 maxlength=255 value="<?=$poll['option0']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 2</td><td align=left><input name=option1 size=80 maxlength=255 value="<?=$poll['option1']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 3</td><td align=left><input name=option2 size=80 maxlength=255 value="<?=$poll['option2']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 4</td><td align=left><input name=option3 size=80 maxlength=255 value="<?=$poll['option3']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 5</td><td align=left><input name=option4 size=80 maxlength=255 value="<?=$poll['option4']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 6</td><td align=left><input name=option5 size=80 maxlength=255 value="<?=$poll['option5']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 7</td><td align=left><input name=option6 size=80 maxlength=255 value="<?=$poll['option6']?>"><br /></td></tr>
<tr><td class=rowhead>Ответ 8</td><td align=left><input name=option7 size=80 maxlength=255 value="<?=$poll['option7']?>"><br /></td></tr>
<tr><td class=rowhead>Сортировать</td><td>
<input type="radio" name="sort" value="yes" <?= $poll["sort"] !== 'no' ? "checked" : "" ?>> Да
<input type="radio" name="sort" value="no" <?= $poll["sort"] === 'no' ? "checked" : "" ?>> Нет
</td></tr>
<tr><td colspan=2 align=center><input type=submit value=<?=$pollid?"'Редактировать'":"'Создать'"?> style='height: 20pt'></td></tr>
</table>
<p><font color=red>*</font> обязательно</p>
<input type="hidden" name="pollid" value="<?=$poll["id"]?>">
<input type="hidden" name="action" value="<?= $pollid ? 'edit' : 'create' ?>">
<input type="hidden" name="returnto" value="<?= htmlspecialchars($_GET['returnto'] ?? '') ?>">
</form>

<?php

end_frame();
end_main_frame();

stdfoot(); ?>