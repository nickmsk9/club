function loadpoll()
	{
		jQuery("#loading_poll").html("Загрузка опроса...");
		jQuery("#loading_poll").fadeIn("fast");
		jQuery("#poll_container").fadeIn("slow", function () {
		 
		  jQuery.post("poll.core.php", {action:"load"}, function (r){ jQuery("#poll_container").html(r); 
			if(jQuery("#results").hasClass("results"))
			{
				jQuery("div[id='poll_result']").each(function(){
					var percentage = jQuery(this).attr("name");
					
					jQuery(this).css({width: "0%"}).animate({
					width: percentage+"%"}, 160);
					
					});
			 jQuery("#loading").fadeOut("fast"); 		
			}
		 
		},"html" );});
	}
	function vote()
	{
		var pollId = jQuery("#pollId").val();
		var choice = jQuery("#choice").val();
		jQuery("#poll_container").empty();
		jQuery("#poll_container").append("<div id=\"loading_poll\" style=\"display:none\"><\/div>");
		jQuery("#loading_poll").fadeIn("fast", function () {jQuery("#loading_poll").html("Пожалуйста подождите пока ваш голос учитывается");});
		
			jQuery.post("poll.core.php",{action:"vote",pollId:pollId,choice:choice}, function(r)
			{
				if(r.status == 0 )
				jQuery("#loading_poll").fadeIn("fast", function () {jQuery("#loading_poll").empty(); jQuery("#loading_poll").html(r.msg);});
				else if(r.status == 1 )
				{
				jQuery("#loading_poll").empty();
				loadpoll();
				}
			},"json");
		
	
	}
	function addvote(val)
	{
		jQuery("#choice").val(val);
		jQuery("#vote_b").show("fast");
	}