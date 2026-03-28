<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($save_language as $i => $l) {
			$save_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccsave = (function () {

		var title = '<?php echo $save_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				if ($("#cometchat_user_"+id+"_popup .cometchat_tabcontenttext").html() != '') {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					location.href=(baseUrl+'plugins/save/index.php?id='+id+'&basedata='+baseData);
				} else {
					alert('<?php echo $save_language[1];?>');
				}
				
			}

        };
    })();
 
})(jqcc);