<?
require "include/bittorrent.php";

dbconn(false);

loggedinorreturn();

// The following line may need to be changed to UC_MODERATOR if you don't have Forum Moderators
if ($CURUSER['class'] < UC_MODERATOR) die(); // No acces to below this rank
if ($CURUSER['override_class'] != 255) die(); // No access to an overridden user class either - just in case

if ($_GET['action'] == 'editclass') //Process the querystring - No security checks are done as a temporary class higher
{                                   //than the actual class mean absoluetly nothing.
$newclass = 0+$_GET['class'];
   $returnto = $_GET['returnto'];

   sql_query("UPDATE users SET override_class = ".sqlesc($newclass)." WHERE id = ".$CURUSER['id']); // Set temporary class

   header("Location: ".$DEFAULTBASEURL."/"/*.$returnto*/);
   die();
}

// HTML Code to allow changes to current class
stdhead("Смена класса");
?>

<form method=get action='setclass.php'>
<input type=hidden name='action' value='editclass'>
<input type=hidden name='returnto' value='user/id<?=$CURUSER['id']?>'> <!-- Change to any page you want -->
<table width=150 border=2 cellspacing=5 cellpadding=5>
<tr><td>Класс</td><td align=left><select name=class> <!-- Populate drop down box with all lower classes -->
<?
$maxclass = get_user_class() - 1;
for ($i = 0; $i <= $maxclass; ++$i)
print("<option value=$i" .">" . get_user_class_name($i) . "\n");
?>
</select></td></tr>
</td></tr>
<tr><td colspan=3 align=center><input type=submit class=btn value='Смена класса'></td></tr></form>
</form>
</table>
<br />
<?
stdfoot();
?>