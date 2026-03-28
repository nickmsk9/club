var uagent    = navigator.userAgent.toLowerCase();
var is_safari = ( (uagent.indexOf('safari') != -1) || (navigator.vendor == "Apple Computer, Inc.") );
var is_ie     = ( (uagent.indexOf('msie') != -1) && (!is_opera) && (!is_safari) && (!is_webtv) );
var is_ie4    = ( (is_ie) && (uagent.indexOf("msie 4.") != -1) );
var is_moz    = (navigator.product == 'Gecko');
var is_ns     = ( (uagent.indexOf('compatible') == -1) && (uagent.indexOf('mozilla') != -1) && (!is_opera) && (!is_webtv) && (!is_safari) );
var is_ns4    = ( (is_ns) && (parseInt(navigator.appVersion) == 4) );
var is_opera  = (uagent.indexOf('opera') != -1);
var is_kon    = (uagent.indexOf('konqueror') != -1);
var is_webtv  = (uagent.indexOf('webtv') != -1);

var is_win    =  ( (uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") !=- 1) );
var is_mac    = ( (uagent.indexOf("mac") != -1) || (navigator.vendor == "Apple Computer, Inc.") );
var ua_vers   = parseInt(navigator.appVersion);

var b_open = 0;
var i_open = 0;
var li_open = 0;
var u_open = 0;
var s_open = 0;
var s_open = 0;
var size_open = 0;
var font_open = 0;
var youtube_open = 0;
var rutube_open = 0;
var quote_open = 0;
var code_open = 0;
var sql_open = 0;
var html_open = 0;
var left_open = 0;
var center_open = 0;
var right_open = 0;
var spoiler_open = 0;
var color_open = 0;
var ie_range_cache = '';
var bbtags   = new Array();

var rus_lr2 = ('Е-е-О-о-Ё-Ё-Ё-Ё-Ж-Ж-Ч-Ч-Ш-Ш-Щ-Щ-Ъ-Ь-Э-Э-Ю-Ю-Я-Я-Я-Я-ё-ё-ж-ч-ш-щ-э-ю-я-я').split('-');
var lat_lr2 = ('/E-/e-/O-/o-ЫO-Ыo-ЙO-Йo-ЗH-Зh-ЦH-Цh-СH-Сh-ШH-Шh-ъ'+String.fromCharCode(35)+'-ь'+String.fromCharCode(39)+'-ЙE-Йe-ЙU-Йu-ЙA-Йa-ЫA-Ыa-ыo-йo-зh-цh-сh-шh-йe-йu-йa-ыa').split('-');
var rus_lr1 = ('А-Б-В-Г-Д-Е-З-И-Й-К-Л-М-Н-О-П-Р-С-Т-У-Ф-Х-Х-Ц-Щ-Ы-Я-а-б-в-г-д-е-з-и-й-к-л-м-н-о-п-р-с-т-у-ф-х-х-ц-щ-ъ-ы-ь-ь-я').split('-');
var lat_lr1 = ('A-B-V-G-D-E-Z-I-J-K-L-M-N-O-P-R-S-T-U-F-H-X-C-W-Y-Q-a-b-v-g-d-e-z-i-j-k-l-m-n-o-p-r-s-t-u-f-h-x-c-w-'+String.fromCharCode(35)+'-y-'+String.fromCharCode(39)+'-'+String.fromCharCode(96)+'-q').split('-');

function stacksize(thearray)
{
	for (i = 0; i < thearray.length; i++ )
	{
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') )
		{
			return i;
		}
	}
	
	return thearray.length;
};

function pushstack(thearray, newval)
{
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
};

function popstack(thearray)
{
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
};

function setFieldName(which)
{
            if (which != selField)
            {
				allcleartags();
                selField = which;

            }
};


function cstat()
{
	var c = stacksize(bbtags);
	
	if ( (c < 1) || (c == null) ) {
		c = 0;
	}
	
	if ( ! bbtags[0] ) {
		c = 0;
	}
	

};


function closeall()
{
	if (bbtags[0])
	{
		while (bbtags[0])
		{
			tagRemove = popstack(bbtags);
			var closetags = "[/" + tagRemove + "]";

			eval ("fombj." +selField+ ".value += closetags");
			
			if ( (tagRemove != 'font') && (tagRemove != 'size') )
			{
				eval(tagRemove + "_open = 0");
				document.getElementById( 'b_' + tagRemove ).className = 'editor_button';

			}
		}
	}

	bbtags = new Array();

};

function allcleartags()
{
	if (bbtags[0])
	{
		while (bbtags[0])
		{
			tagRemove = popstack(bbtags);
			
				eval(tagRemove + "_open = 0");
				document.getElementById( 'b_' + tagRemove ).className = 'editor_button';

		}
	}

	bbtags = new Array();

};

function emoticon(theSmilie)
{
	doInsert(" " + theSmilie + " ", "", false);
};

function pagebreak()
{
	doInsert("{PAGEBREAK}", "", false);
};

function add_code(NewCode)
{
    fombj.selField.value += NewCode;
    fombj.selField.focus();
};

function simpletag(thetag)
{
	var tagOpen = eval(thetag + "_open");
	

		if (tagOpen == 0)
		{
			if(doInsert("[" + thetag + "]", "[/" + thetag + "]", true))
			{
				eval(thetag + "_open = 1");
				document.getElementById( 'b_' + thetag ).className = 'editor_buttoncl';
				
				pushstack(bbtags, thetag);
				cstat();

			}
		}
		else
		{
			lastindex = 0;
			
			for (i = 0 ; i < bbtags.length; i++ )
			{
				if ( bbtags[i] == thetag )
				{
					lastindex = i;
				}
			}
			
			while (bbtags[lastindex])
			{
				tagRemove = popstack(bbtags);
				doInsert("[/" + tagRemove + "]", "", false);


				if ( (tagRemove != 'font') && (tagRemove != 'size') )
				{
					eval(tagRemove + "_open = 0");
					document.getElementById( 'b_' + tagRemove ).className = 'editor_button';
				}
			}
			
			cstat();
		}

};
function tag_url()
{
    var FoundErrors = '';
	var thesel ='';
	if ( (ua_vers >= 4) && is_ie && is_win)
	{
	thesel = document.selection.createRange().text;
	} else thesel ='My Webpage';

    if (!thesel) {
        thesel ='My Webpage';
    }

    var enterURL   = prompt(text_enter_url, "http://");
    var enterTITLE = prompt(text_enter_url_name, thesel);

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }
    if (!enterTITLE) {
        FoundErrors += " " + error_no_title;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[url="+enterURL+"]"+enterTITLE+"[/url]", "", false);
};

function tag_leech()
{
    var FoundErrors = '';
	var thesel ='';
	if ( (ua_vers >= 4) && is_ie && is_win)
	{
	thesel = document.selection.createRange().text;
	} else thesel ='My Webpage';

    if (!thesel) {
        thesel ='My Webpage';
    }

    var enterURL   = prompt(text_enter_url, "http://");
    var enterTITLE = prompt(text_enter_url_name, thesel);

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }
    if (!enterTITLE) {
        FoundErrors += " " + error_no_title;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[leech="+enterURL+"]"+enterTITLE+"[/leech]", "", false);
};

function tag_image()
{
    var FoundErrors = '';
    var enterURL   = prompt(text_enter_image, "http://");

   // var Title = prompt(img_title);

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[img]"+enterURL+"[/img]", "", false);
           

};

function doInsert(ibTag, ibClsTag, isSingle)
{
	var isClose = false;
	var obj_ta = eval('fombj.'+ selField);

	if ( (ua_vers >= 4) && is_ie && is_win)
	{
		if (obj_ta.isTextEdit)
		{
			obj_ta.focus();
			var sel = document.selection;
			var rng = ie_range_cache ? ie_range_cache : sel.createRange();
			rng.colapse;
			if((sel.type == "Text" || sel.type == "None") && rng != null)
			{
				if(ibClsTag != "" && rng.text.length > 0)
					ibTag += rng.text + ibClsTag;
				else if(isSingle)
					isClose = true;
	
				rng.text = ibTag;
			}
		}
		else
		{
			if(isSingle)
			{
				isClose = true;
			}
			
			obj_ta.value += ibTag;
		}
		rng.select();
	ie_range_cache = null;

	}
	else if ( obj_ta.selectionEnd )
	{ 
		var ss = obj_ta.selectionStart;
		var st = obj_ta.scrollTop;
		var es = obj_ta.selectionEnd;
		
		if (es <= 2)
		{
			es = obj_ta.textLength;
		}
		
		var start  = (obj_ta.value).substring(0, ss);
		var middle = (obj_ta.value).substring(ss, es);
		var end    = (obj_ta.value).substring(es, obj_ta.textLength);
		
		if (obj_ta.selectionEnd - obj_ta.selectionStart > 0)
		{
			middle = ibTag + middle + ibClsTag;
		}
		else
		{
			middle = ibTag + middle;
			
			if (isSingle)
			{
				isClose = true;
			}
		}
		
		obj_ta.value = start + middle + end;
		
		var cpos = ss + (middle.length);
		
		obj_ta.selectionStart = cpos;
		obj_ta.selectionEnd   = cpos;
		obj_ta.scrollTop      = st;


	}
	else
	{
		if (isSingle)
		{
			isClose = true;
		}
		
		obj_ta.value += ibTag;
	}

	obj_ta.focus();
	return isClose;
};

function getOffsetTop(obj)
{
	var top = obj.offsetTop;
	
	while( (obj = obj.offsetParent) != null )
	{
		top += obj.offsetTop;
	}
	
	return top;
};

function getOffsetLeft(obj)
{
	var top = obj.offsetLeft;
	
	while( (obj = obj.offsetParent) != null )
	{
		top += obj.offsetLeft;
	}
	
	return top;
};
function ins_color()
{

	if (color_open == 0) {
		var buttonElement = document.getElementById('b_color');
		document.getElementById(selField).focus();

		if ( is_ie )
		{
			document.getElementById(selField).focus();
			ie_range_cache = document.selection.createRange();
		}

		var iLeftPos  = getOffsetLeft(buttonElement);
		var iTopPos   = getOffsetTop(buttonElement) + (buttonElement.offsetHeight + 3);

		document.getElementById('cp').style.left = (iLeftPos) + "px";
		document.getElementById('cp').style.top  = (iTopPos)  + "px";
		
		if (document.getElementById('cp').style.visibility == "hidden")
		{
			document.getElementById('cp').style.visibility = "visible";
			document.getElementById('cp').style.display    = "block";
		}
		else
		{
			document.getElementById('cp').style.visibility = "hidden";
			document.getElementById('cp').style.display    = "none";
			ie_range_cache = null;
		}
	}
	else
	{
			lastindex = 0;
			
			for (i = 0 ; i < bbtags.length; i++ )
			{
				if ( bbtags[i] == 'color' )
				{
					lastindex = i;
				}
			}
			
			while (bbtags[lastindex])
			{
				tagRemove = popstack(bbtags);
				doInsert("[/" + tagRemove + "]", "", false);
				eval(tagRemove + "_open = 0");
				document.getElementById( 'b_' + tagRemove ).className = 'editor_button';
			}
	}
};
function setColor(color)
{

		if ( doInsert("[color=" +color+ "]", "[/color]", true ) )
		{
			color_open = 1;
			document.getElementById( 'b_color' ).className = 'editor_buttoncl';
			pushstack(bbtags, "color");
		}

	document.getElementById('cp').style.visibility = "hidden";
	document.getElementById('cp').style.display    = "none";
    cstat();
};

function ins_size()
{

	if (size_open == 0) {
		var buttonElement = document.getElementById('b_size');
		document.getElementById(selField).focus();

		if ( is_ie )
		{
			document.getElementById(selField).focus();
			ie_range_cache = document.selection.createRange();
		}

		var iLeftPos  = getOffsetLeft(buttonElement);
		var iTopPos   = getOffsetTop(buttonElement) + (buttonElement.offsetHeight + 3);

		document.getElementById('sizepanel').style.left = (iLeftPos) + "px";
		document.getElementById('sizepanel').style.top  = (iTopPos)  + "px";

		if (document.getElementById('sizepanel').style.visibility == "hidden")
		{
			document.getElementById('sizepanel').style.visibility = "visible";
			document.getElementById('sizepanel').style.display    = "block";
		}
		else
		{
			document.getElementById('sizepanel').style.visibility = "hidden";
			document.getElementById('sizepanel').style.display    = "none";
			ie_range_cache = null;
		}
	}
	else
	{
			lastindex = 0;

			for (i = 0 ; i < bbtags.length; i++ )
			{
				if ( bbtags[i] == 'size' )
				{
					lastindex = i;
				}
			}

			while (bbtags[lastindex])
			{
				tagRemove = popstack(bbtags);
				doInsert("[/" + tagRemove + "]", "", false);
				eval(tagRemove + "_open = 0");
				document.getElementById( 'b_' + tagRemove ).className = 'editor_button';
			}
	}
};
function setSize(size)
{

		if ( doInsert("[size=" +size+ "]", "[/size]", true ) )
		{
			size_open = 1;
			document.getElementById( 'b_size').className = 'editor_buttoncl';
			pushstack(bbtags, "size");
		}

	document.getElementById('sizepanel').style.visibility = "hidden";
	document.getElementById('sizepanel').style.display    = "none";
    cstat();
};


function ins_font()
{

	if (font_open == 0) {
		var buttonElement = document.getElementById('b_font');
		document.getElementById(selField).focus();

		if ( is_ie )
		{
			document.getElementById(selField).focus();
			ie_range_cache = document.selection.createRange();
		}

		var iLeftPos  = getOffsetLeft(buttonElement);
		var iTopPos   = getOffsetTop(buttonElement) + (buttonElement.offsetHeight + 3);

		document.getElementById('fontpanel').style.left = (iLeftPos) + "px";
		document.getElementById('fontpanel').style.top  = (iTopPos)  + "px";

		if (document.getElementById('fontpanel').style.visibility == "hidden")
		{
			document.getElementById('fontpanel').style.visibility = "visible";
			document.getElementById('fontpanel').style.display    = "block";
		}
		else
		{
			document.getElementById('fontpanel').style.visibility = "hidden";
			document.getElementById('fontpanel').style.display    = "none";
			ie_range_cache = null;
		}
	}
	else
	{
			lastindex = 0;

			for (i = 0 ; i < bbtags.length; i++ )
			{
				if ( bbtags[i] == 'font' )
				{
					lastindex = i;
				}
			}

			while (bbtags[lastindex])
			{
				tagRemove = popstack(bbtags);
				doInsert("[/" + tagRemove + "]", "", false);
				eval(tagRemove + "_open = 0");
				document.getElementById( 'b_' + tagRemove ).className = 'editor_button';
			}
	}
};
function setFont(font)
{

		if ( doInsert("[font=" +font+ "]", "[/font]", true ) )
		{
			font_open = 1;
			document.getElementById( 'b_font' ).className = 'editor_buttoncl';
			pushstack(bbtags, "font");
		}

	document.getElementById('fontpanel').style.visibility = "hidden";
	document.getElementById('fontpanel').style.display    = "none";
    cstat();
};

function ins_emo()
{
		var buttonElement = document.getElementById('b_emo');
		document.getElementById(selField).focus();

		if ( is_ie )
		{
			document.getElementById(selField).focus();
			ie_range_cache = document.selection.createRange();
		}

		var iLeftPos  = getOffsetLeft(buttonElement);
		var iTopPos   = getOffsetTop(buttonElement) + (buttonElement.offsetHeight + 3);

		document.getElementById('dle_emo').style.left = (iLeftPos) + "px";
		document.getElementById('dle_emo').style.top  = (iTopPos)  + "px";
		
		if (document.getElementById('dle_emo').style.visibility == "hidden")
		{
			document.getElementById('dle_emo').style.zIndex   = 99;
			document.getElementById('dle_emo').style.visibility = "visible";
			document.getElementById('dle_emo').style.display    = "block";
		}
		else
		{
			document.getElementById('dle_emo').style.visibility = "hidden";
			document.getElementById('dle_emo').style.display    = "none";
			ie_range_cache = null;
		}

};

function pagelink()
{
    var FoundErrors = '';
	var thesel ='';
	if ( (ua_vers >= 4) && is_ie && is_win)
	{
	thesel = document.selection.createRange().text;
	} else thesel = text_pages;

    if (!thesel) {
        thesel = text_pages;
    }

    var enterURL   = prompt(text_enter_page, "1");
    var enterTITLE = prompt(text_enter_page_name, thesel);

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }
    if (!enterTITLE) {
        FoundErrors += " " + error_no_title;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[page="+enterURL+"]"+enterTITLE+"[/page]", "", false);
};

function translit()
{
var obj_ta = eval('fombj.' + selField);

if ( (ua_vers >= 4) && is_ie && is_win)
{
if (obj_ta.isTextEdit)
{
obj_ta.focus();
var sel = document.selection;
var rng = sel.createRange();
rng.colapse;
if((sel.type == "Text" || sel.type == "None") && rng != null)
{
rng.text = dotranslate(rng.text);
}
}
else
{
obj_ta.value = dotranslate(obj_ta.value);
}
}
else
{
obj_ta.value = dotranslate(obj_ta.value);
}

obj_ta.focus();

return;
};



function transsymbtocyr(pretxt,txt)
{
	var doubletxt = pretxt+txt;
	var code = txt.charCodeAt(0);
	if (!(((code>=65) && (code<=123))||(code==35)||(code==39))) return doubletxt;
	var ii;
	for (ii=0; ii<lat_lr2.length; ii++)
	{
		if (lat_lr2[ii]==doubletxt) return rus_lr2[ii];
	}
	for (ii=0; ii<lat_lr1.length; ii++)
	{
		if (lat_lr1[ii]==txt) return pretxt+rus_lr1[ii];
	}
	return doubletxt;
};

function insert_font(value, tag)
{
    if (value == 0)
    {
    	return;
	} 

	if ( doInsert("[" +tag+ "=" +value+ "]", "[/" +tag+ "]", true ) )
	{
			pushstack(bbtags, tag);
	}
    fombj.bbfont.selectedIndex  = 0;
    fombj.bbsize.selectedIndex  = 0;
};

function setNewField(which, formname)
{
            if (which != selField)
            {
				allcleartags();
				fombj    = formname;
                selField = which;

            }
};