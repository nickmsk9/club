<?php
if (!defined('UC_SYSOP')) {
    die('Direct access denied.');
}
global $lang, $gzip, $BASEURL, $CURUSER;
getlang('stdhead');
gzip(true);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<title><?= $title ?> :: <?=$lang['title']?></title>
<?php loadCSS('css/','features.css','all','v=1'); ?>

<?php 	loadCSS('themes/Anime/','Anime.css','all','v=1');  ?>

<?php 	loadJS('js/',array('jquery.js','features.js','resizer.js')); ?>

<script type="text/javascript">
  $.noConflict();
  // Code that uses other library's $ can follow here.
</script>

<link rel="shortcut icon" href="<?=$DEFAULTBASEURL?>/favicon.ico" type="image/x-icon" /> 

<meta name="description" content="У нас вы найдете аниме торренты на любой вкус , AMV , DVD, Games, Hentai хентай торренты, J-Music, Live Action, Mobile, Movie, OST, OVA , Series . Аниме чат , аниме форум и аниме торренты на сайте Аниме клуба ! <?php echo $title;?>" />
<meta name="keywords" content="аниме бесплатно, скачать аниме бесплатно, аниме скачать торрент, аниме торрент, скачать хентай бесплатно, скачать аниме, аниме торренты,<?php echo $title;?>, анимэ клуб, аниме торрент трекер, бесплатное аниме скачать, скачать субтитры к аниме, аниме клуб " />
<meta name="verify-v1" content="ZqhkwDhbgj0d/Hcy3/pilgh5tnAK2IwiTr2CbIGA1HY=" />
<meta name='yandex-verification' content='4705fdc3e8a69b90' />
<meta name="verify-reformal" content="11b7f61a5eba9553137a4780" />
<meta name="robots" content="all" />
<base href="<?php echo $BASEURL; ?>" /> 
</head>

<body>
<?php

 if ($CURUSER) 
   { 
   if ($unread)
    {
      ?>
 <div id="message_box"><img id="close_message" style="float:right;cursor:pointer"  src="<?=$DEFAULTBASEURL?>/pic/12-em-cross.png" />
       <a href="<?=$DEFAULTBASEURL?>/message.php"><font color=#e30e2c><?=sprintf($lang['new_pms'],$unread)?></font></a>
                   </div>
      <?
    } 
$uped = mksize($CURUSER['uploaded']);
$downed = mksize($CURUSER['downloaded']);
if ($CURUSER["downloaded"] > 0)
{
$ratio = $CURUSER['uploaded'] / $CURUSER['downloaded'];
$ratio = number_format($ratio, 3);
$color = get_ratio_color($ratio);
if ($color)
$ratio = "<font color=$color>$ratio</font>";
}
else
if ($CURUSER["uploaded"] > 0)
$ratio = "Inf.";
else
$ratio = "---";
}
?><noindex>	
<div id="uberbar">
<div class="user_menu">
<a href="<?=$DEFAULTBASEURL?>"><img src="<?=$DEFAULTBASEURL?>/pic/home.png" alt="" title="<?=$lang['home']?>" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/message.php"><img src="<?=$DEFAULTBASEURL?>/pic/pms.png" alt="" title="<?=$lang['messages']?>" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/my.php"><img src="<?=$DEFAULTBASEURL?>/pic/profil.png" alt="" title="<?=$lang['profile']?>" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/mybonus.php"><img src="<?=$DEFAULTBASEURL?>/pic/bonus.png" alt="" title="<?=$lang['bonuses']?>" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/bookmarks.php"><img src="<?=$DEFAULTBASEURL?>/pic/starz.png" alt="" title="Закладки" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/sub.php"><img src="<?=$DEFAULTBASEURL?>/pic/subs.png" alt="" title="Подписки" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/blogs.php"><img src="<?=$DEFAULTBASEURL?>/pic/blog/blogs.png" alt="" title="Блоги" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/support.php"><img src="<?=$DEFAULTBASEURL?>/pic/support.png" alt="" title="<?=$lang['support']?>" class="brd" /></a>&nbsp;
<a href="javascript: void(1)" 
   onclick="window.open('/radio/index.html', 
  'windowname1',
  'width=225, height=200'); 
   return false;"><img src="<?=$DEFAULTBASEURL?>/pic/radio.png" alt="" title="<?=$lang['radio']?>" class="brd" /></a>&nbsp;
<a href="<?=$DEFAULTBASEURL?>/photo.php"><img src="<?=$DEFAULTBASEURL?>/pic/foto.png" alt="" title="<?=$lang['fotoalbum']?>" class="brd" /></a>&nbsp;
<a href="http://xrupic.ru/" target="_blank"><img src="<?=$DEFAULTBASEURL?>/pic/foto_hosting.png" alt="" title="Фото Хостинг" class="brd" /></a>&nbsp;
<a href="http://twitter.com/animeclublv" target="_blank"><img src="<?=$DEFAULTBASEURL?>/pic/twitter.png" alt="" title="Лента в Твиттере" class="brd" /></a>&nbsp;
<a href="http://vkontakte.ru/animeclublv" target="_blank"><img src="<?=$DEFAULTBASEURL?>/pic/vkont.png" alt="" title="<?=$lang['vkontakte']?>" class="brd" /></a>&nbsp;
<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/forum/view/topic/id1492&amp;page=1"><img src="<?=$DEFAULTBASEURL?>/pic/cash.png" alt="" title="<?=$lang['helpus']?>" class="brd" /></a>&nbsp;
<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/forum/view/topic/id1578&amp;page=1"><img src="<?=$DEFAULTBASEURL?>/pic/info.png" alt="" title="<?=$lang['how_to']?>" class="brd" /></a>&nbsp;
<? if ($CURUSER) { ?>
<a href="<?=$DEFAULTBASEURL?>/logout.php"><img src="<?=$DEFAULTBASEURL?>/pic/exit.png" alt="" title="<?=$lang['logout']?>" class="brd" /></a>
<? } ?>
</div>
</div></noindex>

<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td class="brd">
 <!--HEADER-->
<table width="1000px" align="center" border="0" cellspacing="0" cellpadding="0"><tr><td class="t_space"></td>

<td class="brd" width="270px" style="padding:6px;">
<? if ($CURUSER) { ?>
<table width="270px" border="0" cellspacing="0" cellpadding="0"><tr><td class="c_1">&nbsp;</td><td class="c_2">&nbsp;</td><td class="c_3">&nbsp;</td></tr><tr><td class="c_l">&nbsp;</td><td id="table_color" class="brd">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="user"><tr><td class="brd">
<? 
$currentdate = date("Y-m-d");

// Убедись, что переменные существуют
$baseurl = isset($DEFAULTBASEURL) ? $DEFAULTBASEURL : '';
$bonus_text = isset($bonus) ? $bonus : '';

// Теперь выводим
print "<font color=\"#c26458\">".$lang['ratio'].":</font>&nbsp;&nbsp;<font color=\"#000000\">$ratio</font><br />
<font color=\"#c26458\">".$lang['uploaded'].":</font>&nbsp;&nbsp;&nbsp;<font color=\"#000000\">$uped</font><br />
<font color=\"#c26458\">".$lang['downloaded'].":</font>&nbsp;&nbsp;&nbsp;<font color=\"#000000\">$downed</font><br />
<font color=\"#c26458\">Баланс:</font>&nbsp;&nbsp;&nbsp;<font color=\"#000000\">".$CURUSER['bal']." руб. <a href=\"".$baseurl."/vip_shop.php\"><font color=\"green\">+++</font></a></font><br />
<font color=\"#c26458\">".$lang['bonus'].":</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"#000000\">".$CURUSER['bonus']."</font> ".$bonus_text." <a href=\"".$baseurl."/mybonus.php\"><font color=\"green\">+++</font></a></font><br />
<font color=\"#c26458\">Репутация:</font>&nbsp;&nbsp;&nbsp;<font color=\"#000000\">".$CURUSER['karma']."</font>";
?>
</td><td class="brd">
<?
print ("<center>".$lang['welcome_back'].( $CURUSER ? "<nobr><a href=\"$DEFAULTBASEURL/user/id" . $CURUSER["id"] . "\">" . get_user_class_color($CURUSER["class"],$CURUSER["username"]) . "</a></nobr>" : "гость" ) ."");  
print ("<br /><a href=\"$DEFAULTBASEURL/my.php\"><img src=\"" . ( $CURUSER["avatar"] ? $CURUSER["avatar"] : "$DEFAULTBASEURL/themes/$ss_uri/images/default_avatar.gif" ) . "\" width=\"86px\" height=\"86px\" title=\"Это мой аватар . Я горжусь им .\" title=\"Это мой аватар . Я горжусь им .\" border=\"0\" /></a></center>");
?>
</td></tr></table></td><td class="c_r">&nbsp;</td></tr><tr><td class="c_4">&nbsp;</td><td class="c_5">&nbsp;</td><td class="c_6">&nbsp;</td></tr>
</table>


<? } else { ?>
<form style="padding-top:20px;" method="post" action="<?=$DEFAULTBASEURL?>/takelogin.php"><table width="300" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td class="f_u"><?=$lang['username'];?>:</td><td class="form_1"><input id="form_1" type="text" size="20" name="username" required="required" /></td></tr>
<tr><td class="f_p"><?=$lang['password'];?>:</td><td class="form_2"><input id="form_2" type="password" size="20" name="password" required="required" /><input type="submit" value="" class="log_btn"/></td></tr></form>
<tr><td colspan="2" class="link_form"><div id="link_form"><a href="<?=$DEFAULTBASEURL?>/signup.php"><?=$lang['signup']?></a>&nbsp; &nbsp; 
&nbsp;<a href="<?=$DEFAULTBASEURL?>/recover.php"><?=$lang['lost_paswd']?></a></div></td></tr></table>
<? } ?>  
</td>

<td class="brd" align="center"><a href="<?php echo $DEFAULTBASEURL;?>" title="<?=$SITENAME?>"><div class="name">&nbsp;</div></a>

</td>

<td class="poter">&nbsp;</td>
</tr></table>
<!--HEADER-->    
</td></tr><tr><td class="m_bg"><table width="1000px" align="center" border="0" cellspacing="0" cellpadding="0">
<tr><td class="t_space">&nbsp;</td><td class="brd">
<div class="cc"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td id="m_1">&nbsp;</td><td id="m_2">&nbsp;

<a class="menu_text" href="<?=$DEFAULTBASEURL?>" title="<?=$lang['home_a']?>"><?=$lang['home']?></a>&nbsp;
<?php if(!$CURUSER)
{ ?>
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/signup.php" title="Регистрация"><b>Регистрация</b></a>&nbsp;
<? } ?>
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/browse" title="<?=$lang['browse_a']?>"><?=$lang['browse']?> / Поиск</a>&nbsp;
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/blogs.php" title="Блоги">Блоги</a>&nbsp;
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/forum" title="<?=$lang['forum_a']?>"><?=$lang['forum']?></a>&nbsp;
<!--<a class="menu_text" href="<?=$DEFAULTBASEURL?>/articles.php" title="<?=$lang['articles_a']?>"><?=$lang['articles']?></a>-->&nbsp;
<? if ($CURUSER){?>

<!--<a class="menu_text" href="<?=$DEFAULTBASEURL?>/vip_donate.php" title="Покупка VIP аккаунтов / Пожертвование"><b>VIP</b></a>&nbsp;-->
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/vip_shop.php" title="Пожертвование / VIP Магазин"><b>VIP / Donate</b></a>&nbsp;
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/upload.php" title="<?=$lang['upload_new_a']?>"><?=$lang['upload_new']?></a>&nbsp;<?
}?>
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/topten.php" title="<?=$lang['topten']?>"><?=$lang['topten']?></a>&nbsp;

<a class="menu_text" href="<?=$DEFAULTBASEURL?>/rules.php" title="<?=$lang['rules_a']?>"><?=$lang['rules']?></a>&nbsp;
<a class="menu_text" href="<?=$DEFAULTBASEURL?>/support.php" title="Тех. поддержка">Тех. поддержка</a>&nbsp;
<!-- <a class="menu_text" href="<?=$DEFAULTBASEURL?>/staff.php" title="<?=$lang['admins']?>"><?=$lang['admins']?></a>-->
</td><td id="m_3">&nbsp;</td></tr></table></div></td><td class="t_space">&nbsp;</td></tr></table></td></tr></table>
<!--ШИРИНА ЦЕНТРАЛЬНОГО БЛОКА - МЕНЯЙ ТУТ!!!-->
<?php
$w = "width=\"1000px\"";
?>
<table <?=$w; ?>  border="0" cellspacing="0" cellpadding="0" align="center"><tr><td class="brd">
<!--CONTENT-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td id="foot_bg">&nbsp;</td>
<? $fn = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], "/") + 1); ?>
<td align="center" valign="top" id="main" style="padding-top: 5px; padding-bottom: 5px">
<?php

// Проверка авторизации и права на восстановление класса
if (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['override_class']) && $CURUSER['override_class'] != 255) {
    echo "<p><table border=0 cellspacing=0 cellpadding=10 bgcolor=green><tr><td style='padding: 10px; background: green'>\n";
    echo "<b><a href=\"$DEFAULTBASEURL/restoreclass.php\"><font color=white>".$lang['lower_class']."</font></a></b>";
    echo "</td></tr></table></p>\n";
}

// Проверка предупреждения за низкий рейтинг
if (isset($CURUSER) && is_array($CURUSER) && isset($CURUSER['leechwarn']) && $CURUSER['leechwarn'] === "yes") {
    $warnUntil = isset($CURUSER['leechwarnuntil']) ? $CURUSER['leechwarnuntil'] : 'неизвестно';
    echo "<p><table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style='padding: 10px; background: red; color: #fff;'>\n";
    echo "<b>У Вас слишком низкий рейтинг! Пожалуйста, по возможности оставайтесь дольше на раздаче.<br />Если Вы не исправите рейтинг до {$warnUntil} числа, Ваш аккаунт может быть отключен! <a href='/forum.php?action=viewtopic&topicid=1799&page=last'>Подробнее ...</a></b>";
    echo "</td></tr></table></p>\n";
}

?>


<br>
<?
 show_blocks('c');
 
 show_blocks('l');