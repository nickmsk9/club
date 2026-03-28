<?php
function htmlcode($text){
$stvarno = array ("<", ">");
$zamjenjeno = array ("&lt;","&gt;");
$final = str_replace($stvarno, $zamjenjeno, $text);
return $final;
}
function clear($text){
$final = stripslashes(stripslashes( $text));
return $final;
}
?>