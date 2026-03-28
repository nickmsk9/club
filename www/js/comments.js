var SE_loadingImage = 'ajax-loader-big.gif';
var SE_CommentLoadingBar = '<div style="padding:20px;text-align:center;"><img src="/pic/'+SE_loadingImage+'" /></div>';

/*

SE_Comments JavaScript Code v.02Beta by Strong

*/

function SE_EditComment(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_edit_panel'+cid).hide();
	jQuery('#comment_text'+cid).html( SE_CommentLoadingBar );
	jQuery.post('/addcomment.php',{'do':'edit_comment',cid:cid,tid:tid}, 
		   function(response) { 
		     jQuery('#comment_text'+cid).empty();
			 jQuery('#comment_text'+cid).html(response);
		   }, 'html');
}

function SE_SaveComment(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	var text = jQuery('#edit_post').val();
	
	if(text.length > 0 && text.replace(/ /g, '') != '') {
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( SE_CommentLoadingBar );
	jQuery.post('/addcomment.php',{'do':'save_comment',cid:cid,tid:tid,text:text},
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

function SE_CommentCancel(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( SE_CommentLoadingBar );
	jQuery.post('/addcomment.php',{'do':'save_cancel',cid:cid,tid:tid},
		   function(response){
			   jQuery('#comment_text'+cid).empty();
			   jQuery('#comment_text'+cid).html(response);
			   jQuery('#comment_edit_panel'+cid).show();
		   },'html');
			   
}

function SE_CommentQuote(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	var text = jQuery('#text').val();
	
	jQuery.post('/addcomment.php',{'do':'comment_quote',cid:cid,tid:tid,text:text},
		   function(response) {
			  jQuery('#text').empty(); 
			  jQuery('#text').val(response);
		   },'html');
	
}

function SE_SendComment(tid) {
	tid = parseInt(tid);
	var comments = jQuery('#comments_list');
	var text     = jQuery('#text').val();
	var bValid   = true;
	
	if(text.length > 0 && text.replace(/ /g, '') != '') {
	jQuery('#send_comment').get(0).disabled = 'disabled';	
	jQuery.post('/addcomment.php',{'do':'add_comment',tid:tid,text:text},
		   function(response) {
			  comments.html(response);
			  jQuery('#send_comment').get(0).disabled = '';
			  jQuery('#text').val('');
		   },'html');
	} else {
		alert( 'Comment can\'t be empty. Please fill the form!' );
		jQuery('#text').focus();
		return false;
	}
}

function SE_DeleteComment(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	var comments = jQuery('#comments_list');
	var cfrm = null;
	
	cfrm = confirm( 'Удалить комментарий?');
	if(cfrm) {
	jQuery.post('/addcomment.php',{'do':'delete_comment',cid:cid,tid:tid},
		   function(response) {
			  comments.html(response);
		   },'html');
	} else {
		return false;
	}
}

function SE_ViewOriginal(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
	
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( SE_CommentLoadingBar );
	jQuery.post('/addcomment.php',{'do':'view_original',cid:cid,tid:tid},
		   function(response) {
			   jQuery('#comment_text'+cid).empty();			   
			   jQuery('#comment_text'+cid).html(response);
		   },'html');
}

function SE_RecoverOriginal(cid,tid) {
	cid = parseInt(cid);
	tid = parseInt(tid);
		
	jQuery('#comment_text'+cid).empty();
	jQuery('#comment_text'+cid).html( SE_CommentLoadingBar );
	jQuery.post('/addcomment.php',{'do':'recover_original',cid:cid,tid:tid},
		   function(response) {
			   jQuery('#comment_text'+cid).empty();
			   jQuery('#comment_text'+cid).html(response);
		   },'html');
}