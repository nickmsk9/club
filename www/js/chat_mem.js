var loading = "<img src=\"pic/upload.gif\" alt=\"Загрузка..\" />"; 
jQuery(function() {
    var params = jQuery.extend({refresh:5},params);
    jQuery("#shbox").submit ( function(){
	    jQuery('#text').attr("disabled", true); 
		jQuery('input[type=submit]', this).attr('disabled', 'disabled');
        var data = jQuery('#text').val();
		var text = jQuery.jSEND(data);
        if (data == ''){
            alert('Ошибка. Пустое сообщение.');
			jQuery('#text').removeAttr("disabled");
			jQuery('input[type=submit]').removeAttr('disabled');	
            return;
        }
        jQuery("#loading-chat").html(loading);
        jQuery.ajax({
            url: "atom.php",
            type: "POST",
            data: "text=" + encodeURIComponent(text) + "&action=add",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(response) {
                jQuery("#shout").empty();
                jQuery("#shout").append(response);
                jQuery("#loading-chat").empty();
                jQuery('#text').removeAttr("disabled");
                jQuery('input[type=submit]').removeAttr('disabled');
            }
        });
        document.shbox.shbox_text.value = '';
    });
    jQuery(".delmess").click ( function(){
        var messid = jQuery(this).attr("id");
        jQuery("#loading-chat").html(loading);
        jQuery.post("atom.php",{"id":messid,"action":"delete"},function (response) {
            jQuery("#shout").empty();
            jQuery("#shout").append(response);
            jQuery("#loading-chat").empty();
        });
    });

    var readMessages = function(){
        jQuery("#loading-chat").html(loading);
        jQuery.get("atom.php", function (response) {
            jQuery("#shout").empty();
            jQuery("#shout").append(response);
            jQuery("#loading-chat").empty();
    	});
        setTimeout(readMessages,params.refresh*2000);
    }
    readMessages();
});


function doImage(obj)
{
textarea = document.getElementById(obj);
var url = prompt('Enter the Image URL:','http://');
var scrollTop = textarea.scrollTop;
var scrollLeft = textarea.scrollLeft;
	if (document.selection) 
			{
				textarea.focus();
				var sel = document.selection.createRange();
				sel.text = '[img]' + url + '[/img]';
			}
   else 
    {
		var len = textarea.value.length;
	    var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		
        var sel = textarea.value.substring(start, end);
	    //alert(sel);
		var rep = '[img]' + url + '[/img]';
        textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		
			
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
	}

}

function doURL(obj)
{
textarea = document.getElementById(obj);
var url = prompt('Введите URL ссылки:','http://');
var scrollTop = textarea.scrollTop;
var scrollLeft = textarea.scrollLeft;

	if (document.selection) 
			{
				textarea.focus();
				var sel = document.selection.createRange();
				
			if(sel.text==""){
					sel.text = '[url]'  + url + '[/url]';
					} else {
					sel.text = '[url=' + url + ']' + sel.text + '[/url]';
					}			

				//alert(sel.text);
				
			}
   else 
    {
		var len = textarea.value.length;
	    var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		
        var sel = textarea.value.substring(start, end);
		
		if(sel==""){
				var rep = '[url]' + url + '[/url]';
				} else
				{
				var rep = '[url=' + url + ']' + sel + '[/url]';
				}
	    //alert(sel);
		
        textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		
			
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
	}
}

function doAddTags(tag1,tag2,obj)
{
textarea = document.getElementById(obj);
	// Code for IE
		if (document.selection) 
			{
				textarea.focus();
				var sel = document.selection.createRange();
				//alert(sel.text);
				sel.text = tag1 + sel.text + tag2;
			}
   else 
    {  // Code for Mozilla Firefox
		var len = textarea.value.length;
	    var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		
		
		var scrollTop = textarea.scrollTop;
		var scrollLeft = textarea.scrollLeft;

		
        var sel = textarea.value.substring(start, end);
	    //alert(sel);
		var rep = tag1 + sel + tag2;
        textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
		
		
	}
}
function clock(){ 
    var currentTime = new Date(); 
    var currentHours = currentTime.getHours(); 
    var currentMinutes = currentTime.getMinutes(); 
    var currentSeconds = currentTime.getSeconds(); 
    currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes; 
    currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds; 
    jQuery('#hour_min').text(currentHours + ':' + currentMinutes); 
    jQuery('#sec').css({"font-size":"9px", "vertical-align":"text-top"}).text(currentSeconds); 
} 
 
//создадим массив дней недели 
var days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота']; 
//и массив месяцев по-русски 
var months = ['января', 'Февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 
              'августа', 'сентября', 'октября', 'ноября', 'декабря']; 
 
jQuery(document).ready(function() { 
   var currentTime = new Date();//Получаем текущую дату 
   var currentDay = days[currentTime.getDay()];//Вытаскваем из нашего массива текущий день недели 
   var currentDate = currentTime.getDate();//День 
   var currentMonth = months[currentTime.getMonth()];//Месяц 
   var currentYear = currentTime.getFullYear();//Год 
//В элемент с id=date выводим текущую дату в красивом формате 
   jQuery('#date').text(currentDay + ' ' + currentDate + ' ' + currentMonth + ' ' + currentYear + 'г'); 
   clock(); //вызываем функцию времени 
   window.setInterval(clock, 1000); //вызываем функцию clock() каждую секунду 
});