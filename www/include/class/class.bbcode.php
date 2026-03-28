<?php

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');
  
  function textbbcode($form, $name, $text='') { 
  global $DEFAULTBASEURL;
?>
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/markitup/jquery.markitup.pack.js"></script>
<script type="text/javascript" src="<?=$DEFAULTBASEURL?>/markitup/sets/bbcode/set.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$DEFAULTBASEURL?>/markitup/skins/simple/style.css" />
<link rel="stylesheet" type="text/css" href="<?=$DEFAULTBASEURL?>/markitup/sets/bbcode/style.css" />
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function()	{
jQuery('#<?=$name?>').markItUp(mySettings);			
});

function winop()
{
windop = window.open("moresmiles.php?form=<?= $form ?>&text=<?= $name ?>","mywin","height=500,width=500,resizable=yes,scrollbars=yes");
}
function winop2()
{
windop = window.open("moresmiles2.php?form=<?= $form ?>&text=<?= $name ?>","mywin2","height=450,width=550,resizable=yes,scrollbars=yes");
}
</script>
<textarea name="<?=$name?>" id="<?=$name?>" width="98%"/><?=$text?></textarea> 
<? 
}
?>