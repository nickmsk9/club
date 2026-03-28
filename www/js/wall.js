jQuery(document).ready(function() 
{
var webcamtotal=2; // Min 2 Max 6 Recommended 
// Update Status
jQuery(".update_button").click(function() 
{
var updateval = jQuery("#update").val();

var uploadvalues=jQuery("#uploadvalues").val();

var X=jQuery('.preview').attr('id');
var Y=jQuery('.webcam_preview').attr('id');
if(X)
var Z= X+','+uploadvalues;
else if(Y)
var Z= uploadvalues;
else
var Z=0;
var dataString = 'update='+ updateval+'&uploads='+Z;
if(jQuery.trim(updateval).length==0)
{
alert("Please Enter Some Text");
}
else
{
jQuery("#flash").show();
jQuery("#flash").fadeIn(400).html('Loading Update...');
jQuery.ajax({
type: "POST",
url: "message_ajax.php",
data: dataString,
cache: false,
success: function(html)
{
jQuery("#webcam_container").slideUp('fast');
jQuery("#flash").fadeOut('slow');
jQuery("#content").prepend(html);
jQuery("#update").val('');	
jQuery("#update").focus();
jQuery('#preview').html('');
jQuery('#webcam_preview').html('');
jQuery('#uploadvalues').val('');
jQuery('#photoimg').val('');


  }
 });
 jQuery("#preview").html();
jQuery('#imageupload').slideUp('fast');
}
return false;
	});
	
//Commment Submit

jQuery('.comment_button').live("click",function() 
{

var ID = jQuery(this).attr("id");

var comment= jQuery("#ctextarea"+ID).val();
var dataString = 'comment='+ comment + '&msg_id=' + ID;

if(jQuery.trim(comment).length==0)
{
alert("Please Enter Comment Text");
}
else
{
jQuery.ajax({
type: "POST",
url: "comment_ajax.php",
data: dataString,
cache: false,
success: function(html){
jQuery("#commentload"+ID).append(html);
jQuery("#ctextarea"+ID).val('');
jQuery("#ctextarea"+ID).focus();
 }
 });
}
return false;
});
// commentopen 
jQuery('.commentopen').live("click",function() 
{
var ID = jQuery(this).attr("id");
jQuery("#commentbox"+ID).slideToggle('fast');
return false;
});	


//WebCam 6 clicks
jQuery(".camclick").live("click",function() 
{
var X=jQuery("#webcam_count").val();
if(X)
var i=X;
else
var i=1;
var j=parseInt(i)+1; 
jQuery("#webcam_count").val(j);

if(j>webcamtotal)
{
jQuery(this).hide();
jQuery("#webcam_count").val(1);
}

});

// delete comment
jQuery('.stcommentdelete').live("click",function() 
{
var ID = jQuery(this).attr("id");
var dataString = 'com_id='+ ID;

if(confirm("Sure you want to delete this update? There is NO undo!"))
{

jQuery.ajax({
type: "POST",
url: "delete_comment_ajax.php",
data: dataString,
cache: false,
beforeSend: function(){jQuery("#stcommentbody"+ID).animate({'backgroundColor':'#fb6c6c'},300);},
success: function(html){
// jQuery("#stcommentbody"+ID).slideUp('slow');
jQuery("#stcommentbody"+ID).fadeOut(300,function(){jQuery("#stcommentbody"+ID).remove();});
 }
 });

}
return false;
});


// Camera image
jQuery('#camera').live("click",function() 
{
jQuery('#webcam_container').slideUp('fast');
jQuery('#imageupload').slideToggle('fast');
return false;
});

//Web Camera image
jQuery('#webcam_button').live("click",function() 
{
jQuery(".camclick").show();
jQuery('#imageupload').slideUp('fast');
jQuery('#webcam_container').slideToggle('fast');
return false;
});

// Uploading Image

jQuery('#photoimg').live('change', function()			
{ 
var values=jQuery("#uploadvalues").val();
jQuery("#previeww").html('<img src="icons/loader.gif"/>');
jQuery("#imageform").ajaxForm({target: '#preview'  }).submit();

var X=jQuery('.preview').attr('id');
var Z= X+','+values;
if(Z!='undefined,')
jQuery("#uploadvalues").val(Z);

});


// delete update
jQuery('.stdelete').live("click",function() 
{
var ID = jQuery(this).attr("id");
var dataString = 'msg_id='+ ID;

if(confirm("Sure you want to delete this update? There is NO undo!"))
{

jQuery.ajax({
type: "POST",
url: "delete_message_ajax.php",
data: dataString,
cache: false,
beforeSend: function(){ jQuery("#stbody"+ID).animate({'backgroundColor':'#fb6c6c'},300);},
success: function(html){
 //jQuery("#stbody"+ID).slideUp();
 jQuery("#stbody"+ID).fadeOut(300,function(){jQuery("#stbody"+ID).remove();});
 }
 });
}
return false;
});
// View all comments
jQuery(".view_comments").live("click",function()  
{
var ID = jQuery(this).attr("id");

jQuery.ajax({
type: "POST",
url: "view_ajax.php",
data: "msg_id="+ ID, 
cache: false,
success: function(html){
jQuery("#commentload"+ID).html(html);
}
});
return false;
});
// Load More

jQuery('.more').live("click",function() 
{

var ID = jQuery(this).attr("id");
if(ID)
{
jQuery.ajax({
type: "POST",
url: "moreupdates_ajax.php",
data: "lastid="+ ID, 
cache: false,
beforeSend: function(){ jQuery("#more"+ID).html('<img src="icons/ajaxloader.gif" />'); },
success: function(html){
jQuery("div#content").append(html);
jQuery("#more"+ID).remove();
}
});
}
else
{
jQuery("#more").html('The End');// no results
}

return false;
});

// Web Cam-----------------------
var pos = 0, ctx = null, saveCB, image = [];
var canvas = document.createElement("canvas");
canvas.setAttribute('width', 320);
canvas.setAttribute('height', 240);
if (canvas.toDataURL) 
{
ctx = canvas.getContext("2d");
image = ctx.getImageData(0, 0, 320, 240);
saveCB = function(data) 
{
var col = data.split(";");
var img = image;
for(var i = 0; i < 320; i++) {
var tmp = parseInt(col[i]);
img.data[pos + 0] = (tmp >> 16) & 0xff;
img.data[pos + 1] = (tmp >> 8) & 0xff;
img.data[pos + 2] = tmp & 0xff;
img.data[pos + 3] = 0xff;
pos+= 4;
}
if (pos >= 4 * 320 * 240)
 {
ctx.putImageData(img, 0, 0);
jQuery.post("webcam_image_ajax.php", {type: "data", image: canvas.toDataURL("image/png")},
function(data)
 {
 
 if(jQuery.trim(data) != "false")
{
var dataString = 'webcam='+ 1;
jQuery.ajax({
type: "POST",
url: "webcam_imageload_ajax.php",
data: dataString,
cache: false,
success: function(html){
var values=jQuery("#uploadvalues").val();
jQuery("#webcam_preview").prepend(html);
var X=jQuery('.webcam_preview').attr('id');
var Z= X+','+values;
if(Z!='undefined,')
jQuery("#uploadvalues").val(Z);
 }
 });
 }
 else
{
  jQuery("#webcam").html('<div id="camera_error"><b>Camera Not Found</b><br/>Please turn your camera on or make sure that it <br/>is not in use by another application</div>');
jQuery("#webcam_status").html("<span style='color:#cc0000'>Camera not found please reload this page.</span>");
jQuery("#webcam_takesnap").hide();
	return false;
}
 });
pos = 0;
 }
  else {
saveCB = function(data) {
image.push(data);
pos+= 4 * 320;
 if (pos >= 4 * 320 * 240)
 {
jQuery.post("webcam_image_ajax.php", {type: "pixel", image: image.join('|')},
function(data)
 {
 
var dataString = 'webcam='+ 1;
jQuery.ajax({
type: "POST",
url: "webcam_imageload_ajax.php",
data: dataString,
cache: false,
success: function(html){
var values=jQuery("#uploadvalues").val();
jQuery("#webcam_preview").prepend(html);
var X=jQuery('.webcam_preview').attr('id');
var Z= X+','+values;
if(Z!='undefined,')
jQuery("#uploadvalues").val(Z);
 }
 });
 
 });
 pos = 0;
 }
 };
 }
 };
 } 


jQuery("#webcam").webcam({
width: 320,
height: 240,
mode: "callback",
 swffile: "js/jscam_canvas_only.swf",
onSave: saveCB,
onCapture: function () 
{
webcam.save();
 },
debug: function (type, string) {
 jQuery("#webcam_status").html(type + ": " + string);
}

});
//-------------------
});
 /**
Taking snap
**/
function takeSnap(){
//console.log(webcam.getCameraList());
webcam.capture();
 }
