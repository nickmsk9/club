<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($colors_language as $i => $l) {
			$colors_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.cccolors = (function () {

		var title = '<?php echo $colors_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				baseUrl = getBaseUrl();
				window.open (baseUrl+'plugins/colors/index.php?id='+id, 'colors',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=260,height=130"); 
			},

			updatecolor: function (text) {

				if (text != '' && text != null) {
					document.cookie = '<?php echo $cookiePrefix;?>chatroomcolor='+text;
				}

				$('#currentroom .cometchat_textarea').focus();
				
			}

        };
    })();
 
})(jqcc);