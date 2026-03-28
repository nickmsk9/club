<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($smilies_language as $i => $l) {
			$smilies_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccsmilies = (function () {

		var title = '<?php echo $smilies_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				window.open (baseUrl+'plugins/smilies/index.php?id='+id+'&basedata='+baseData, 'smilies',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=220,height=130"); 
			},

			addtext: function (id,text) {

				var string = $('#cometchat_user_'+id+'_popup .cometchat_textarea').val();
				
				if (string.charAt(string.length-1) == ' ') {
					$('#cometchat_user_'+id+'_popup .cometchat_textarea').val($('#cometchat_user_'+id+'_popup .cometchat_textarea').val()+text);
				} else {
					$('#cometchat_user_'+id+'_popup .cometchat_textarea').val($('#cometchat_user_'+id+'_popup .cometchat_textarea').val()+' '+text);
				}
				
				$('#cometchat_user_'+id+'_popup .cometchat_textarea').focus();
				
			}

        };
    })();
 
})(jqcc);