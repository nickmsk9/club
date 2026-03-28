<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($clearconversation_language as $i => $l) {
			$clearconversation_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccclearconversation = (function () {

		var title = '<?php echo $clearconversation_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				if ($("#cometchat_user_"+id+"_popup .cometchat_tabcontenttext").html() != '') {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();

					$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&callback=?', {clearid: id, basedata: baseData});
					$("#cometchat_user_"+id+"_popup .cometchat_tabcontenttext").html('');
				}
			}

        };
    })();
 
})(jqcc);