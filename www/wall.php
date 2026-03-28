<?php
require "include/bittorrent.php";
gzip();
dbconn(true);
loggedinorreturn();
include_once 'include/Wall_Updates.php';
include_once 'include/tolink.php';
include_once 'include/textlink.php';
include_once 'include/htmlcode.php';
include_once 'include/Expand_URL.php';

$Wall = new Wall_Updates();
stdhead("Стена");

begin_main_frame();
?>

<link href="css/wall.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.wallform.js"></script>
<script type="text/javascript" src="js/jquery.webcam.js"></script>
<script type="text/javascript" src="js/jquery.color.js"></script>
<script type="text/javascript" src="js/wall.js"></script>



<div id="wall_container">

<div id="updateboxarea">
<h4>Что нового?</h4>
<textarea name="update" id="update" maxlength="200" ></textarea>
<br />
<div id="webcam_container" class='border'>
<div id="webcam" >
</div>
<div id="webcam_preview">

</div>

<div id='webcam_status'></div>
<div id='webcam_takesnap'>

<input type="button" value=" Чпок! " onclick="return takeSnap();" class="camclick button"/>
<input type="hidden" id="webcam_count" />
</div>
</div>
<div  id="imageupload" class="border">
<form id="imageform" method="post" enctype="multipart/form-data" action='image_ajax.php'> 
<div id='preview'>
</div>

<span id='addphoto'>Фото :</span> <input type="file" name="photoimg" id="photoimg" />
<input type='hidden' id='uploadvalues' />
</form>
</div>
<div style="width:100%;clear:both">
<input type="submit"  value=" Отправить "  id="update_button"  class="update_button"/> 
<span style="float:right">
<a href="javascript:void(0);" id="camera"><img src="icons/cameraa.png" border="0" title="Загрузить"/></a> 
 <a href="javascript:void(0);" id="webcam_button"><img src="icons/web-cam.png"  border="0" title="Снимок камеры" style='margin-top:5px'/></a>
</span>
</div>

</div>

<div id='flashmessage'>
<div id="flash" align="left"  ></div>
</div>
<div id="content">

<?php 
// Loading Messages
include('load_messages.php'); 
?>

</div>
</div>

<?php 
end_main_frame();
stdfoot();
?>
