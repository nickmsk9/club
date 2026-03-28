var loading = "<img src=\"pic/upload.gif\" alt=\"Загрузка..\" />";

jQuery(function() {
    jQuery(".tab").click(function () {
        if (jQuery(this).hasClass("active")) return;

        jQuery("#loading").html(loading);
        var act = jQuery(this).attr("id");

        jQuery(".tab").removeClass("active");
        jQuery(this).addClass("active");

        jQuery.post("main.php", { "act": act }, function (response) {
            jQuery("#body").hide().html(response).fadeIn("fast");
            jQuery("#loading").empty();
        });
    });
    if(jQuery.browser.msie)
    {
        width = jQuery('#main_right h2').width();
        if (width > 422)
            jQuery('#main_right').width(width);
        else
        {
            jQuery('#main_right').width("422");
            jQuery('#main_container').width("686");
        }
    }
});

function togglepic(bu, picid, formid)
{
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);

    if(pic.src == bu + "/pic/plus.gif")
    {
        pic.src = bu + "/pic/minus.gif";
        form.value = "minus";
    }
    else
    {
        pic.src = bu + "/pic/plus.gif";
        form.value = "plus";
    }
}

var azWin = '     Ё               ё       АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдежзийклмнопрстуфхцчшщъыьэюя';
var AZ=azWin;
var b64s  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
var b64a  = b64s.split('');
function enBASE64(str)
{
    var a=Array(), i;
    for( i=0; i<str.length; i++ )
    {
        var cch=str.charCodeAt(i);
        if( cch>127 )
        {
            cch=AZ.indexOf(str.charAt(i))+163;
            if(cch<163)
                continue;
        }
        a.push(cch);
    };
    var s=Array(), lPos = a.length - a.length % 3 ;
    for(i=0;i<lPos;i+=3)
    {
        var t=(a[i]<<16)+(a[i+1]<<8)+a[i+2];
        s.push( b64a[(t>>18)&0x3f]+b64a[(t>>12)&0x3f]+b64a[(t>>6)&0x3f]+b64a[t&0x3f] );
    }
    switch ( a.length-lPos )
    {
        case 1 : var t=a[lPos]<<4;
        s.push(b64a[(t>>6)&0x3f]+b64a[t&0x3f]+'==');
        break;
        case 2 : var t=(a[lPos]<<10)+(a[lPos+1]<<2);
        s.push(b64a[(t>>12)&0x3f]+b64a[(t>>6)&0x3f]+b64a[t&0x3f]+'=');
        break;
    }
    return s.join('');
}