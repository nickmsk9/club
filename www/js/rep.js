	jQuery(document).ready(function() {
		
	jQuery("#set_rep").fancybox({
	'scrolling'		: 'no',
	'titleShow'		: false,

	'onClosed'		: function() {
	    jQuery("#rep_error").hide()
		   ; 
	}
	});

	jQuery("#rep_form").bind("submit", function() {
	
	if (jQuery("#descr").val().length < 1) {
	    jQuery("#rep_error").show();
	    jQuery.fancybox.resize();
	    return false;
	}
	
	jQuery.fancybox.showActivity();
	
	jQuery.ajax({
		type		: "POST",
		cache	: false,
		url		: "/rep.php",
		data		: jQuery(this).serializeArray(),
		success: function(data) {
			jQuery.fancybox(data);
		}
	});

	return false;
	});	
	
	
	});
	