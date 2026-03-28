<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($writeboard_language as $i => $l) {
			$writeboard_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccwriteboard = (function () {

		var title = '<?php echo $writeboard_language[0];?>';
		var lastcall = 0;

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				var currenttime = new Date();
				currenttime = parseInt(currenttime.getTime()/1000);
				if (currenttime-lastcall > 10) {
					baseUrl = $.cometchat.getBaseUrl();

					var random = currenttime;
					$.post(baseUrl+'plugins/writeboard/index.php?action=request', {to: id, id: random});
					lastcall = currenttime;

					baseUrl = $.cometchat.getBaseUrl();
					var w =window.open (baseUrl+'plugins/writeboard/index.php?action=writeboard&type=1&id='+random, 'writeboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=800,height=600");
					w.focus();

				} else {
					alert('<?php echo $writeboard_language[1];?>');
				}
			},

			accept: function (id,random) {
				baseUrl = $.cometchat.getBaseUrl();
				$.post(baseUrl+'plugins/writeboard/index.php?action=accept', {to: id});
				var w = window.open (baseUrl+'plugins/writeboard/index.php?action=writeboard&type=0&id='+random, 'writeboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=800,height=600"); 
				w.focus();
			}
        };
    })();
 
})(jqcc);