
jQuery(document).ready(
function(){
  jQuery('div.news-head')
  .click(function() {
    jQuery(this).toggleClass('unfolded');
    jQuery(this).next('div.news-body').slideToggle('fast');
  });
  }
);
    /*<![CDATA[*/
 function karma(id, type, act) {
  jQuery.post("karma.php",{"id":id,"act":act,"type":type},function (response) {
  jQuery("#karma" + id).empty();
  jQuery("#karma" + id).append(response);
  });
 }
/*]]>*/

var b64s  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='

function ajaxpreview(objname) {
     var ajax = new tbdev_ajax();
     ajax.onShow ('');
     var varsString = "";
     ajax.requestFile = "preview.php?ajax";
     var txt = enBASE64(document.getElementById(objname).value);
     ajax.setVar("msg", txt);
     ajax.method = 'POST';
     ajax.element = 'preview';
     ajax.sendAJAX(varsString);

}

 //  base64_�����������

	function enBASE64(input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input =_utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this.b64s.charAt(enc1) + this.b64s.charAt(enc2) +
			this.b64s.charAt(enc3) + this.b64s.charAt(enc4);

		}

		return output;
	}


//utf8_�����������
function _utf8_encode (string) {
	string = string.replace(/\r\n/g,"\n");
	var utftext = "";

	for (var n = 0; n < string.length; n++) {

	var c = string.charCodeAt(n);

	if (c < 128) {
	utftext += String.fromCharCode(c);
			                            }
			
	else if((c > 127) && (c < 2048)) {
	
	utftext += String.fromCharCode((c >> 6) | 192);
	utftext += String.fromCharCode((c & 63) | 128);
				}
			
	else {
	
	utftext += String.fromCharCode((c >> 12) | 224);
	utftext += String.fromCharCode(((c >> 6) & 63) | 128);
	utftext += String.fromCharCode((c & 63) | 128);
	        }

		}

	return utftext;
	}

function block_switch(id) {
  const textBlock = document.getElementById('sb' + id);
  const icon = document.getElementById('picb' + id);

  if (!textBlock || !icon) return; // Защита от ошибки, если элемент отсутствует

  const isVisible = jQuery(textBlock).is(':visible');
  const type = isVisible ? "hide" : "show";

  icon.src = isVisible ? 'pic/plus.gif' : 'pic/minus.gif';

  jQuery.get("switch_blocks.php", { type: type, bid: id });

  jQuery(textBlock).slideToggle("fast");
}

jQuery(document).ready(function() {
	(function() {
		//settings
		var fadeSpeed = 200, fadeTo = 0.3, topDistance = 30;
		var topbarME = function() { jQuery('#uberbar').fadeTo(fadeSpeed,1); }, topbarML = function() { jQuery('#uberbar').fadeTo(fadeSpeed,fadeTo); };
		var inside = false;
		//do
		jQuery(window).scroll(function() {
			position = jQuery(window).scrollTop();
			if(position > topDistance && !inside) {
				//add events
				topbarML();
				jQuery('#uberbar').bind('mouseenter',topbarME);
				jQuery('#uberbar').bind('mouseleave',topbarML);
				inside = true;
			}
			else if (position < topDistance){
				topbarME();
				jQuery('#uberbar').unbind('mouseenter',topbarME);
				jQuery('#uberbar').unbind('mouseleave',topbarML);
				inside = false;
			}
		});
	})();
});

 this.tooltip = function(){ 
   xOffset = 6; 
   yOffset = 16; 
   jQuery("[title],[alt]").hover(function(e){ 
   if(this.title != ''){ 
      this.t = this.title; 
      this.title = ""; 
   } else { 
      this.t = this.alt; 
      this.alt = ""; 
 }
 if(this.t != '' && jQuery("#tooltip").size() == 0) {
 jQuery("body").append('<div id="tooltip"></div>');
 jQuery("#tooltip").empty().text(this.t);
 jQuery("#tooltip")
       .css("top",(e.pageY - xOffset) + "px") 
       .css("left",(e.pageX + yOffset) + "px") 
       .show(); 
     }
   }, 
   function(){ 
      if(this.t != '') {
      this.title = this.t; 
      this.alt = this.t; 
      jQuery("#tooltip").remove();
      }
   }); 
   jQuery("[title],[alt]").mousemove(function(e){ 
      jQuery("#tooltip") 
         .css("top",(e.pageY - xOffset) + "px") 
         .css("left",(e.pageX + yOffset) + "px"); 
   }); 
}; 

jQuery(document).ready(function() {
   tooltip(); 
});  

jQuery(document).ready(function()
{if(getCookie('show_message')!='no')
{var pos=parseInt(jQuery(window).scrollTop())+parseInt(jQuery(window).height());jQuery('#message_box').css("top",pos-53+"px");jQuery('#message_box').show();
jQuery(window).scroll(function()
{var pos=parseInt(jQuery(window).scrollTop())+parseInt(jQuery(window).height());jQuery('#message_box').animate({top:pos-53+"px"},{queue:false,duration:500});});}
jQuery('#close_message').click(function()
{jQuery('#message_box').animate({top:"-=15px",opacity:0},"slow");setCookie('show_message','no',1);});});function setCookie(c_name,value,expireHours)
{var exhour=new Date();exhour.setHours(exhour.getHours()+1);document.cookie=c_name+"="+escape(value)+
((expireHours==null)?"":";expires="+exhour.toGMTString());}
function getCookie(c_name)
{if(document.cookie.length>0)
{c_start=document.cookie.indexOf(c_name+"=");if(c_start!=-1)
{c_start=c_start+c_name.length+1;c_end=document.cookie.indexOf(";",c_start);if(c_end==-1)c_end=document.cookie.length;return unescape(document.cookie.substring(c_start,c_end));}}
return"";}