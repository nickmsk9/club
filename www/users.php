<?php
require "include/bittorrent.php";

dbconn();

loggedinorreturn();

stdhead("Пользователи");
begin_main_frame();

require_once 'include/secrets.php';

$res = $mysqli->query("SELECT * FROM users WHERE username LIKE 'a%' ORDER BY username LIMIT 50");

$num = $res->num_rows;

$ut = "";
$ut .= "<table border=0 align=center cellspacing=0 cellpadding=5>\n";
$ut .= "<tr><td class=colhead align=left>Аккаунт</td><td class=colhead>Зарегистрирован</td><td class=colhead>Последний вход</td><td class=colhead align=left>Статус</td></tr>\n";
for ($i = 0; $i < $num; ++$i)
{
  $arr = $res->fetch_assoc();
  if ($arr['country'] > 0)
  {
    $cres = $mysqli->query("SELECT name,flagpic FROM countries WHERE id=" . $arr['country']) or die($mysqli->error);
    if ($cres->num_rows == 1)
    {
      $carr = $cres->fetch_assoc();
      $country = "<td style='padding: 0px' align=center><img src=/pic/flag/$carr[flagpic] alt=\"$carr[name]\"></td>";
    }
  }
  else
    $country = "<td align=center>---</td>";
  if ($arr['added'] == '0000-00-00 00:00:00')
    $arr['added'] = '-';
  if ($arr['last_access'] == '0000-00-00 00:00:00')
    $arr['last_access'] = '-';
  $ut .= "<tr><td align=left><a href=user/id$arr[id]><b>$arr[username]</b></a>" .($arr["donated"] > 0 ? "<img src=/pic/star.gif border=0 alt='Donor'>" : "")."</td>" .
  "<td>$arr[added]</td><td>$arr[last_access]</td>".
    "<td align=left>" . get_user_class_name($arr["class"], $arr['doljuploader']) . "</td></tr>\n";
}
$ut .= "</table>\n";

?>
<script language="JavaScript">
function utf8_encode ( argString ) {
  
    var string = (argString+''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");

    var utftext = "";
    var start, end;
    var stringl = 0;

    start = end = 0;
    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;

        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.substring(start, end);
            }
            utftext += enc;
            start = end = n+1;
        }
    }

    if (end > start) {
        utftext += string.substring(start, string.length);
    }

    return utftext;
}
function base64_encode (data) {
  
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, enc="", tmp_arr = [];

    if (!data) {
        return data;
    }

    data = this.utf8_encode(data+'');
    
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1<<16 | o2<<8 | o3;

        h1 = bits>>18 & 0x3f;
        h2 = bits>>12 & 0x3f;
        h3 = bits>>6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    
    enc = tmp_arr.join('');
    
    switch (data.length % 3) {
        case 1:
            enc = enc.slice(0, -2) + '==';
        break;
        case 2:
            enc = enc.slice(0, -1) + '=';
        break;
    }

    return enc;
}

var lasttext = "";

function usearch(hehe) {

  var texxt = document.getElementById("usearch").value;
  texxt = texxt.replace(/^\s+|\s+$/g, '');
  texxt = base64_encode(texxt);
  if(texxt != lasttext || hehe == 1)
  {
    document.getElementById("loading").innerHTML = '<img src="pic/loading.gif" width="16" height="16">';
    window.location.href = 'users.php#usearch=' + escape(texxt);
    lasttext = texxt;

    try{
    uajax.abort()
    }
    catch(e){
    }

    var url = 'ajax_user.php?text=' + escape(texxt);
    if(window.XMLHttpRequest)
    {
      uajax = new XMLHttpRequest();
    }
    else
    {
      uajax = new ActiveXObject("Microsoft.XMLHTTP");
    }
    uajax.open("GET", url, true);
    uajax.onreadystatechange = ugo;
    uajax.send(null);

	}


}

function ugo() {
  if (uajax.readyState == 4) {
	  if (uajax.status == 200) {
      fejda();
      var urespons = uajax.responseText;
      document.getElementById("userdiv").innerHTML = urespons;
      document.getElementById("loading").innerHTML = '';
	  }
  }
}

</script>
<h1>Пользователи</h1>
<table align=center>
<tr><td class="embeded" valign="top" height="20">
Поиск: <input type="text" size="30" name="usearch" onchange="usearch();" onKeyUp="usearch();" id="usearch"></td>
<td class="embeded" valign="top" width="30">&nbsp;&nbsp;<var id="loading"></var></td></tr></table>
<br>

<div id="userdiv"><?=$ut?></div>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type="text/javascript">
var count = 0;
var kvar = 0;
var ruta = document.getElementById('usearch');
var divruta = document.getElementById('userdiv');

var url = window.location.href;
var pos = url.indexOf('#usearch=');
if(pos > -1)
{
  var ord = url.substr(pos+9);

  if(ord.length > 1)
  {
    ruta.value = ord;
    usearch(ord);
  }
}

function dold()
{
  divruta.style.opacity = 0;
  divruta.style.filter = 'alpha(opacity = 0)';
}

function fejda()
{
  divruta.style.opacity = 0;
  divruta.style.filter = 'alpha(opacity = 0)';
  kvar = 0;
  count++;
  doFejda(count);
}

function doFejda(c)
{
  if(kvar <= 90 && c == count)
  {
    kvar += 5;
    divruta.style.opacity = kvar/100;
    divruta.style.filter = 'alpha(opacity = ' + kvar + ')';

    setTimeout("doFejda("+c+")",40);
  }
}



</script>


<?
end_main_frame();
stdfoot();

?>