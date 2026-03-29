var loading = "<img src=\"pic/upload.gif\" alt=\"Загрузка..\" />";

jQuery(function () {
    var chatParams = jQuery.extend({ refresh: 5 }, window.params || {});
    var isReading = false;

    jQuery("#shbox").submit(function (e) {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        var $form = jQuery(this);
        var $text = jQuery("#text");
        var rawData = $text.val();
        var data = jQuery.trim(rawData);

        if (data === '') {
            alert('Ошибка. Пустое сообщение.');
            $text.removeAttr("disabled").focus();
            jQuery('input[type=submit]', $form).removeAttr('disabled');
            return false;
        }

        $text.attr('disabled', 'disabled');
        jQuery('input[type=submit]', $form).attr('disabled', 'disabled');
        jQuery("#loading-chat").html(loading);

        jQuery.ajax({
            url: "atom.php",
            type: "POST",
            cache: false,
            dataType: "html",
            data: {
                text: data,
                action: "add"
            },
            success: function (response) {
                jQuery("#shout").html(response);
                $text.val('');
            },
            error: function (xhr) {
                alert('Ошибка отправки сообщения: ' + xhr.status);
            },
            complete: function () {
                jQuery("#loading-chat").empty();
                $text.removeAttr("disabled").focus();
                jQuery('input[type=submit]', $form).removeAttr('disabled');
            }
        });

        return false;
    });

    jQuery(".delmess").live("click", function () {
        var messid = jQuery(this).attr("id");
        var tid = jQuery(this).attr("tid") || '';
        jQuery("#loading-chat").html(loading);

        jQuery.post("atom.php", { id: messid, tid: tid, action: "delete" }, function (response) {
            jQuery("#shout").html(response);
            jQuery("#loading-chat").empty();
        });
    });

    function readMessages() {
        if (isReading) {
            setTimeout(readMessages, chatParams.refresh * 2000);
            return;
        }

        isReading = true;
        jQuery("#loading-chat").html(loading);

        jQuery.ajax({
            url: "atom.php",
            type: "GET",
            cache: false,
            dataType: "html",
            success: function (response) {
                jQuery("#shout").html(response);
            },
            complete: function () {
                jQuery("#loading-chat").empty();
                isReading = false;
                setTimeout(readMessages, chatParams.refresh * 2000);
            }
        });
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
