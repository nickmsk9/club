<?php

/*

CometChat
Copyright (c) 2011 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

include dirname(dirname(dirname(__FILE__)))."/plugins.php";

include dirname(__FILE__)."/lang/en.php";

if (file_exists(dirname(__FILE__)."/lang/".$lang.".php")) {
	include dirname(__FILE__)."/lang/".$lang.".php";
}

include dirname(__FILE__)."/config.php";


if ($rtl == 1) {
	$rtl = "_rtl";
} else {
	$rtl = "";
}

if (!file_exists(dirname(__FILE__)."/themes/".$theme."/jabber".$rtl.".css")) {
	$theme = "default";
}

$domain = '';
if (!empty($_GET['basedomain'])) {
	$domain = $_GET['basedomain'];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><?php echo $jabber_language[0];?></title> 
<link type="text/css" rel="stylesheet" media="all" href="themes/<?php echo $theme;?>/jabber<?php echo $rtl;?>.css" /> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script>

function login() {
	var username = $('#username').val();
	var password = $('#password').val();

	$.getJSON("<?php echo $jabberServer;?>?json_callback=?", {'action':'login', username: username, password: password, server: 'talk.google.com', port: '5222'} , function(data){
		if (data[0].error == '0') {
			$.cookie('<?php echo $cookiePrefix;?>jabber','true',{ path: '/' });
			$.cookie('<?php echo $cookiePrefix;?>jabber_type','gtalk',{ path: '/' });
			$('.container_body_2').remove();
			$('#gtalk_box').html('<span><?php echo $jabber_language[7];?></span>');

			setTimeout(function() {
				try {
					window.opener.jqcc.ccjabber.process();
					window.close();					
				} catch (e) {
					crossDomain();
				}
			}, 4000);


			
		} else {
			alert('<?php echo $jabber_language[9];?>');
		}
	});

	return false;
}

function login_facebook(session) {
	var currenttime = new Date();
	currenttime = parseInt(currenttime.getTime()/1000);

	$.getJSON("<?php echo $jabberServer;?>?json_callback=?", {'action':'login', username: 'dummy'+currenttime, password: 'dummy'+currenttime, session_key: session, server: 'chat.facebook.com', port: '5222'} , function(data){
		if (data[0].error == '0') {
			$.cookie('<?php echo $cookiePrefix;?>jabber','true',{ path: '/' });
			$.cookie('<?php echo $cookiePrefix;?>jabber_type','facebook',{ path: '/' });
	
			setTimeout(function() {
				try {
					window.opener.jqcc.ccjabber.process();
					window.close();					
				} catch (e) {
					crossDomain();
				}
			}, 4000);

		} else {
			alert('<?php echo $jabber_language[9];?>');
		}
	});

	return false;
}

$(document).ready(function() {
	$.cookie('<?php echo $cookiePrefix;?>jabber','false',{ path: '/' });
	$.getJSON("<?php echo $jabberServer;?>?json_callback=?", {'action':'logout'});
});

function crossDomain() {
	var ts = Math.round((new Date()).getTime() / 1000);
	location.href= '//<?php echo $domain;?>/chat.htm?ts='+ts+'&jabber='+$.cookie('<?php echo $cookiePrefix;?>jabber')+'&jabber_type='+$.cookie('<?php echo $cookiePrefix;?>jabber_type');
}

// Copyright (c) 2006 Klaus Hartl (stilbuero.de)
// http://www.opensource.org/licenses/mit-license.php

jQuery.cookie=function(a,b,c){if(typeof b!='undefined'){c=c||{};if(b===null){b='';c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=jQuery.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};


</script>
</head>

<body><form name="upload" onsubmit="return login();">
<div class="container">
<div class="container_title"><?php echo $jabber_language[1];?></div>

<div class="container_body">

<?php if(empty($_GET['session'])):?>

	<div class="container_body_1">
		<span><h1><?php echo $jabber_language[4];?></h1></span><br/>
		<div id="gtalk_box">
			<table>
				<tr>
					<td><?php echo $jabber_language[2];?></td>
					<td><input type="text" id="username" name="username"></td>
				</tr>
				<tr>
					<td><?php echo $jabber_language[3];?></td>
					<td><input type="password" id="password" name="password"></td>
				</tr>
				<tr>
					<td><input type="submit" value="<?php echo $jabber_language[6];?>" id="gtalk"></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="container_body_2">
		<span><h1><?php echo $jabber_language[5];?></h1></span><br/>
	<script>
		document.write('<iframe src="<?php echo $jabberServerRoot;?>fb.jsp?r='+location.href.replace('&','AND').replace('?','QUESTION')+'" frameborder="0" border="0" width="150" height="30"></iframe>');
	</script>

	</div>
<?php else:?>
	<div class="container_body_1">
		<span><?php echo $jabber_language[7];?></span>
	</div>
	<script>
		$(document).ready(function() {
			login_facebook('<?php echo $_GET['session'];?>');
		});
	</script>
<?php endif;?>

<div style="clear:both"></div>

</div>
</div>
</div>

</form>


</body>
</html>