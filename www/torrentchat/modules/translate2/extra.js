
var languageAdded = 0;

function fireEvent(element,event){
    if (document.createEventObject){
    var evt = document.createEventObject();
    return element.fireEvent('on'+event,evt)
    } else {
    var evt = document.createEvent("HTMLEvents");
    evt.initEvent(event, true, true ); 
    return !element.dispatchEvent(evt);
    }
}

function changeLanguage() {
	jqcc('.goog-te-combo').attr('id','cclanguagebutton');
	fireEvent(document.getElementById('cclanguagebutton'),'change');
}

function addLanguageCode() {
	if (!languageAdded) {
		jqcc.getScript('//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit',function(data) {
			jqcc("body").append('<div id="google_translate_element"></div><style>#google_translate_element {display:none!important;}</style>');
			languageAdded++;
		});
	}
}

jqcc(document).ready(function() {
	if (jqcc.cookie('googtrans')) {
		addLanguageCode();
	}
});

function googleTranslateElementInit() {
  new google.translate.TranslateElement({
	  pageLanguage: 'en',
	  autoDisplay: false
  }, 'google_translate_element');
}