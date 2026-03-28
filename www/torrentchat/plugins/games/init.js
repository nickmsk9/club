<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}


		foreach ($games_language as $i => $l) {
			$games_language[$i] = str_replace("'", "\'", $l);
		}
?>

(function($){   
  
	$.ccgames = (function () {

		var title = '<?php echo $games_language[0];?>';
		var lastcall = 0;

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				window.open (baseUrl+'plugins/games/index.php?id='+id+'&basedata='+baseData, 'games_init',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=440,height=260"); 
			},

			accept: function (id,fid,tid,rid,gameId,gameWidth) {
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
                $.getJSON(baseUrl+'plugins/games/index.php?action=accept&callback=?', {to: id,fid: fid,tid: tid, rid: rid, gameId: gameId, gameWidth: gameWidth, basedata: baseData});
				var w = window.open (baseUrl+'plugins/games/index.php?action=play&fid='+fid+'&tid='+tid+'&rid='+rid+'&gameId='+gameId+'&basedata='+baseData, 'games'+fid+''+tid,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(gameWidth-30)+",height=600"); 
				w.focus(); // add popup blocker check
			},

			accept_fid: function (id,fid,tid,rid,gameId,gameWidth) {
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				var w =window.open (baseUrl+'plugins/games/index.php?action=play&fid='+fid+'&tid='+tid+'&rid='+rid+'&gameId='+gameId+'&basedata='+baseData, 'games'+fid+''+tid,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(gameWidth-30)+",height=600");
				w.focus(); // add popup blocker check
			}

        };
    })();
 
})(jqcc);