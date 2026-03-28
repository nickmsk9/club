<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($report_language as $i => $l) {
			$report_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccreport = (function () {

		var title = '<?php echo $report_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				if ($("#cometchat_user_"+id+"_popup .cometchat_tabcontenttext").html() != '') {
					baseUrl = $.cometchat.getBaseUrl();
					window.open (baseUrl+'plugins/report/index.php?id='+id, 'report',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220"); 
				} else {
					alert('<?php echo $report_language[1];?>');
				}
				
			}

        };
    })();
 
})(jqcc);