<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($whiteboard_language as $i => $l) {
			$whiteboard_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccwhiteboard = (function () {

		var title = '<?php echo $whiteboard_language[0];?>';
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
					$.post(baseUrl+'plugins/whiteboard/index.php?action=request', {to: id, id: random});
					lastcall = currenttime;

					baseUrl = $.cometchat.getBaseUrl();
					var w =window.open (baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+random, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=640,height=480");
					w.focus();

				} else {
					alert('<?php echo $whiteboard_language[1];?>');
				}
			},

			accept: function (id,random) {
				baseUrl = $.cometchat.getBaseUrl();
				$.post(baseUrl+'plugins/whiteboard/index.php?action=accept', {to: id});
				var w = window.open (baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+random, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=640,height=480"); 
				w.focus();
			}
        };
    })();
 
})(jqcc);