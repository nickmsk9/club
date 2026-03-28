var BL_loadingImage = 'ajax-loader-big.gif';
var BL_CommentLoadingBar = '<div style="padding:20px;text-align:center;"><img src="/pic/'+BL_loadingImage+'" /></div>';

/*

BL_Comments JavaScript Code v.01 Beta by webnet

*/

function BL_EditComment(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_edit_panel'+cid).hide();
	jQuery('#comment_text'+cid).html( BL_CommentLoadingBar );
	jQuery.post('/blog_comm.php',{'do':'edit_comment',cid:cid,bid:bid}, 
		   function(response) { 
		     jQuery('#comment_text'+cid).empty();
			 jQuery('#comment_text'+cid).html(response);
		   }, 'html');
}

function BL_SaveComment(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	var text = jQuery('#edit_post').val();
	
	if(text.length > 0 && text.replace(/ /g, '') != '') {
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( BL_CommentLoadingBar );
	jQuery.post('/blog_comm.php',{'do':'save_comment',cid:cid,bid:bid,text:text},
		   function(response) {
			 jQuery('#comment_text'+cid).empty();
			 jQuery('#comment_text'+cid).html(response);
			 jQuery('#comment_edit_panel'+cid).show(); 
		   },'html');
	} else {
		alert( 'Comment can\'t be empty. Please fill the form!' );
		jQuery('#edit_post').focus();
		return false;
	}
}

function BL_CommentCancel(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( BL_CommentLoadingBar );
	jQuery.post('/blog_comm.php',{'do':'save_cancel',cid:cid,bid:bid},
		   function(response){
			   jQuery('#comment_text'+cid).empty();
			   jQuery('#comment_text'+cid).html(response);
			   jQuery('#comment_edit_panel'+cid).show();
		   },'html');
			   
}

function BL_CommentQuote(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	var text = jQuery('#text').val();
	
	jQuery.post('/blog_comm.php',{'do':'comment_quote',cid:cid,bid:bid,text:text},
		   function(response) {
			  jQuery('#text').empty(); 
			  jQuery('#text').val(response);
		   },'html');
	
}

function BL_SendComment(bid) {
	bid = parseInt(bid);
	var comments = jQuery('#comments_list');
	var text     = jQuery('#text').val();
	var bValid   = true;
	
	if(text.length > 0 && text.replace(/ /g, '') != '') {
	jQuery('#send_comment').get(0).disabled = 'disabled';	
	jQuery.post('/blog_comm.php',{'do':'add_comment',bid:bid,text:text},
		   function(response)  {
			  comments.html(response);
			  jQuery('#send_comment').get(0).disabled = '';
			  jQuery('#text').val('');
		   } ,'html');
	} else {
		alert( 'Comment can\'t be empty. Please fill the form!' );
		jQuery('#text').focus();
		return false;
	}
}

function BL_DeleteComment(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	var comments = jQuery('#comments_list');
	var cfrm = null;
	
	cfrm = confirm( 'Удалить комментарий?' );
	if(cfrm) {
	jQuery.post('/blog_comm.php',{'do':'delete_comment',cid:cid,bid:bid},
		   function(response) {
			  comments.html(response);
		   },'html');
	} else {
		return false;
	}
}

function BL_ViewOriginal(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( BL_CommentLoadingBar );
	jQuery.post('/blog_comm.php',{'do':'view_original',cid:cid,bid:bid},
		   function(response) {
			   jQuery('#comment_text'+cid).empty();			   
			   jQuery('#comment_text'+cid).html(response);
		   },'html');
}

function BL_RecoverOriginal(cid,bid) {
	cid = parseInt(cid);
	bid = parseInt(bid);
		
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( BL_CommentLoadingBar );
	jQuery.post('/blog_comm.php',{'do':'recover_original',cid:cid,bid:bid},
		   function(response) {
			   jQuery('#comment_text'+cid).empty();
			   jQuery('#comment_text'+cid).html(response);
		   },'html');
}